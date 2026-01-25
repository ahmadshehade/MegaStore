<?php

namespace Modules\PaymentManagement\Services;

use App\Models\User;
use App\Services\BaseService;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\OrderManagement\Models\Order;
use Modules\PaymentManagement\Emails\Payments\MakeNewPaymentMail;
use Modules\PaymentManagement\Models\Invoice;
use Modules\PaymentManagement\Models\LedgerEntry;
use Modules\PaymentManagement\Models\Payment;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\PaymentManagement\Models\Refund;

class PaymentService extends BaseService
{
    use  CacheTrait;

    protected $paymentEntry;
    /**
     * Summary of __construct
     * @param Payment $payment
     */
    public function __construct(Payment $payment, LedgerEntryService $paymentEntry)
    {
        parent::__construct($payment);
        $this->paymentEntry = $paymentEntry;
    }

    /**
     * Summary of getAll
     * @param array $filters
     * @return Model[]|\Traversable<int|string, Model>
     */
    public function  getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user ? $user->id . "_" . implode($user->roles()->pluck('name')->toArray()) : "guest";
        $cacheKey = $userKey . ((empty($filters)) ? "" : md5(json_encode($filters)));
        return Cache::tags(['payments'])->remember($cacheKey, now()->addMinute(), function () use ($filters) {
            return parent::getAll($filters)->load(['invoice', 'invoice.refunds', 'paymentMethod', 'ledgerEntries']);
        });
    }

    /**
     * Summary of store
     * @param array $data
     * @throws HttpClientException
     * @return Model
     */
public function store(array $data): Model
{
    DB::beginTransaction();

    try {
        $scale = 2;

        $invoice = Invoice::where('id', $data['invoice_id'])
            ->lockForUpdate()
            ->firstOrFail();

        if (in_array($invoice->status, ['paid', 'cancelled', 'revised'])) {
            throw new HttpClientException(
                'This invoice cannot be paid.',
                403
            );
        }

        $total = (string) $invoice->tot_amount;
        $paid  = (string) LedgerEntry::where('invoice_id', $invoice->id)
            ->whereIn('entry_type', ['payment'])
            ->sum('credit');

        $remaining = bcsub($total, $paid, $scale);

        if (bccomp($remaining, '0', $scale) <= 0) {
            throw new HttpClientException(
                'This invoice has already been fully paid.',
                409
            );
        }

        $paymentMethod = null;
        $fee = '0.00';
        if (! empty($data['payment_method_id'])) {
            $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);
            $fee = number_format($paymentMethod->fee, $scale, '.', '');
        }

        // Gross amount sent by the client
        $grossAmount = number_format((string) $data['amount'], $scale, '.', '');

        // Gross amount must be greater than the fee
        if (bccomp($grossAmount, $fee, $scale) <= 0) {
            throw new HttpClientException(
                sprintf(
                    'The sent amount (%s) must be greater than the payment method fee (%s).',
                    $grossAmount,
                    $fee
                ),
                422
            );
        }

        if (bccomp($remaining, $fee, $scale) <= 0) {
            $requiredGross = bcadd($remaining, $fee, $scale);

            if (bccomp($grossAmount, $requiredGross, $scale) !== 0) {
                throw new HttpClientException(
                    sprintf(
                        'Only %s remains on this invoice and the fee is %s. You must send exactly %s as the gross amount.',
                        $remaining,
                        $fee,
                        $requiredGross
                    ),
                    422
                );
            }

            $netAmount = $remaining;
        } else {

            $netAmount = bcsub($grossAmount, $fee, $scale);


            if (bccomp($netAmount, $remaining, $scale) === 1) {
                $maxAllowedGross = bcadd($remaining, $fee, $scale);

                throw new HttpClientException(
                    sprintf(
                        'The sent amount results in a net payment greater than the remaining invoice balance. The maximum allowed gross amount is %s (remaining %s + fee %s).',
                        $maxAllowedGross,
                        $remaining,
                        $fee
                    ),
                    422
                );
            }
        }

        $data['amount']       = $netAmount;
        $data['customer_id']  = $invoice->order->customer_id ?? null;
        $data['gross_amount'] = $grossAmount;
        $data['fee']          = $fee;

        $payment = parent::store($data);

        $newRemaining = bcsub($remaining, $netAmount, $scale);

        if (bccomp($newRemaining, '0', $scale) === 0) {
            $invoice->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
            $invoice->order->update(['status' => 'completed']);
        } else {
            $invoice->update(['status' => 'partial']);
            $invoice->order->update(['status' => 'processing']);
        }


        $this->paymentEntry->createPaymentEntry($payment);

        DB::commit();


        Mail::to(
            User::admins()
                ->pluck('email')
                ->push(optional(Auth::user())->email)
                ->filter()
                ->unique()
        )->queue(new MakeNewPaymentMail($payment));


        try {
            $payer = optional($payment->payer) ?: optional(Auth::user());
            if ($payer) {
                $message = sprintf(
                    'Your payment has been processed. Gross amount: %s, fee: %s, net amount applied to the invoice: %s.',
                    $grossAmount,
                    $fee,
                    $netAmount
                );


            }
        } catch (Exception $notifyEx) {
            Log::warning(
                'Failed to notify payer about fee deduction: ' . $notifyEx->getMessage()
            );
        }

        return $payment->load([
            'invoice',
            'invoice.refunds',
            'paymentMethod',
            'invoice.ledgerEntries',
        ]);
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Fail Make Payment: ' . $e->getMessage());
        throw $e;
    }
}







    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        $payment = parent::get($model);
        return $payment
            ->load([
                'invoice',
                'invoice.refunds',
                'paymentMethod',
                'ledgerEntries'
            ]);
    }
}
