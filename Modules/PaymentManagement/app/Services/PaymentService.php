<?php

namespace Modules\PaymentManagement\Services;

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
use Modules\OrderManagement\Models\Order;
use Modules\PaymentManagement\Models\Invoice;
use Modules\PaymentManagement\Models\LedgerEntry;
use Modules\PaymentManagement\Models\Payment;
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
        try {

            DB::beginTransaction();
            $invoice = Invoice::where('id', $data['invoice_id'])
                ->lockForUpdate()
                ->firstOrFail();
            if (in_array($invoice->status, ['paid', 'cancelled', 'revised'])) {
                throw new HttpClientException('Cannot pay this invoice.', 403);
            }
            $scale = 2;
            $total = (string) $invoice->tot_amount;
            $paid = (string) LedgerEntry::where('invoice_id', $invoice->id)
                ->whereIn('entry_type', ['payment'])
                ->sum('credit');
            $remaining = bcsub($total, $paid, $scale);
            if (bccomp($remaining, '0', $scale) <= 0) {
                throw new HttpClientException('Invoice is already fully paid.', 409);
            }
            if (bccomp($data['amount'], $remaining, $scale) === 1) {
                throw new HttpClientException('Payment exceeds remaining invoice balance.', 403);
            }
            $payment = parent::store($data);
            $newRemaining = bcsub($remaining, (string) $payment->amount, $scale);
            if (bccomp($newRemaining, '0', $scale) === 0) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partial']);
                $invoice->order->update(['status' => 'processing']);
            }
            $entry =  $this->paymentEntry->createPaymentEntry($payment);
            DB::commit();
            return $payment
                ->load(['invoice', 'invoice.refunds', 'paymentMethod', 'invoice.ledgerEntries']);
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

    /**
     * Summary of update
     * @param array $data
     * @param Model $payment
     * @return Model
     */



    
}
