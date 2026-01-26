<?php


namespace Modules\OrderManagement\Services;

use App\Enum\UserRoles;
use App\Models\User;
use App\Services\BaseService;
use App\Traits\CacheTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\OrderManagement\Notifications\OrderCreatedNotification;
use Modules\OrderManagement\DataTransferObjects\OrderItemProcessor;
use Modules\OrderManagement\Models\Order;
use Modules\PaymentManagement\Services\InvoiceService;
use Modules\PaymentManagement\Services\LedgerEntryService;
use Throwable;

class OrderService extends BaseService
{
    use CacheTrait;
    protected $makeTotAmount;
    protected $invoice;
    protected $ledgerEntry;

    /**
     * Summary of __construct
     * @param Order $model
     */
    public function __construct(Order $model, OrderItemProcessor $makeTotalAmount, InvoiceService $invoice, LedgerEntryService $ledgerEntry)
    {
        parent::__construct($model);
        $this->makeTotAmount = $makeTotalAmount;
        $this->invoice = $invoice;
        $this->ledgerEntry = $ledgerEntry;
    }

    /**
     * Get all (with caching).
     *
     * @param array $filters
     * @return iterable
     */
    public function getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user
            ? $user->id . "_" . implode('_', $user->getRoleNames()->toArray())
            : 'guest';
        $cacheKey = "Orders_" . $userKey . "_" . ((empty($filters) ? "" : md5(json_encode($filters))));
        return Cache::tags(['orders'])->remember($cacheKey, now()->addMinute(), function () use ($filters) {
            return parent::getAll($filters)->load([
                'items',
                'customer',
                'discounts',
                'histories',
                'invoice',
                'ledgerEntries' => function ($q) {
                    $q->visibleFor(Auth::user());
                }
            ]);
        });
    }

    /**
     * Store order (transactional).
     *
     * @param array $data
     * @return Model
     * @throws Throwable
     */

    public function store(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data['customer_id'] = Auth::id();
            $data['tot_amount'] = $this->makeTotAmount->makeTotAmount($data);
            $order = parent::store($data);
            $this->makeTotAmount->processOrderItems($data, $order);
            if (isset($data['discounts'])) {
                $tot = $this->makeTotAmount->processDiscount($order, $data);
                $order->update([
                    'tot_amount' => $tot,
                ]);
                $this->makeTotAmount->syncDiscountsAndHistory($data, $order, $order->tot_amount);
            }
            $invoice = $this->invoice->makeInvoice($order);
            $this->ledgerEntry->createInvoiceEntry($invoice);
            $admins = User::admins()->get();


            return $order->load([
                'items',
                'discounts',
                'invoice',
                'ledgerEntries' => function ($q) {
                    $q->visibleFor(Auth::user());
                }
            ]);
        });
    }



    /**
     * Summary of update
     * @param array $data
     * @param mixed $model
     * @return Model
     */
    public function update(array $data, $model): Model
    {
        return DB::transaction(function () use ($data, $model) {
            $invoice = $model->invoice;
            if ($invoice && $invoice->status === 'paid') {
                throw new HttpResponseException(response()->json(['message' => 'Invoice Order is Paid .']));
            }
            if (isset($data['variants'])) {
                $data['tot_amount'] = $this->makeTotAmount->processOrderItems($data, $model);
            }
            $total = $model->tot_amount;
            if (isset($data['discounts'])) {
                $tot = $this->makeTotAmount->processDiscount($model, $data);
                $model->update([
                    'tot_amount' => $tot,
                ]);
                $this->makeTotAmount->syncDiscountsAndHistory($data, $model, $total);
                $paid = $invoice->payments()->sum('amount') ?? 0;
                $overPayment = bcsub($paid, $tot, 2);
                if (bccomp($overPayment, '0', 2) === 1) {
                    $this->ledgerEntry->overPaymentEntry($overPayment, $model->customer);
                }
            }
            $order = parent::update($data, $model);
            $invoice = $this->invoice->updateInvoice($model);
            $this->ledgerEntry->reviseInvoiceEntry($invoice);

            $this->cacheFlushMultiple();

            return $order->load([
                'items',
                'discounts',
                'invoice',
                'ledgerEntries' => function ($q) {
                    $q->visibleFor(Auth::user());
                }
            ]);
        });
    }


    /**
     * Get single order with relations.
     *
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        $order = parent::get($model);
        return $order->load([
            'items',
            'discounts',
            'invoice',
            'ledgerEntries' => function ($q) {
                $q->visibleFor(Auth::user());
            }
        ]);
    }

    /**
     * Destroy order: allow only when invoice is issued (unpaid). Do NOT delete ledger entries or invoices.
     *
     * @param Model $order
     * @return bool
     */
    public function destroy(Model $order): bool
    {
        $this->cacheFlushMultiple();
        $success = parent::destroy($order);
        return $success;
    }

    /**
     * Helper: flush all related caches in one place.
     */
    protected function cacheFlushMultiple(): void
    {
        $this->cacheFlush('orders');
        $this->cacheFlush('invoices');
        $this->cacheFlush('trashed_invoices');
        $this->cacheFlush('LedgerEntries');
        $this->cacheFlush('T_LedgerEntries');
        $this->cacheFlush('refunds');
    }
}
