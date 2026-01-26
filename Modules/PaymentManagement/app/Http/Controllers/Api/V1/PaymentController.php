<?php

namespace Modules\PaymentManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Modules\PaymentManagement\Http\Requests\Api\V1\Payments\StorePaymentRequest;
use Modules\PaymentManagement\Models\Payment;
use Modules\PaymentManagement\Notifications\MakePaymentNotification;
use Modules\PaymentManagement\Services\PaymentService;

class PaymentController extends Controller
{
    protected $payment;

    use AuthorizesRequests;
    /**
     * Summary of __construct
     * @param PaymentService $payment
     */
    public function __construct(PaymentService $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        $filters = $request->only([
            'invoice_id',
            'payment_method_id',
            'amount',
            'currency',
            'payment_notes',
            'status',
            'payment_date'
        ]);
        $payments = $this->payment->getAll($filters);
        return $this->SuccessMessage(
            ['payments' => $payments],
            'Successfully Get All Payment .',
            200,
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        $this->authorize('create', Payment::class);
        $payment = $this->payment->store($request->validated());
        $groups = User::admins()->get()->push(Auth::user());
        Notification::send($groups, new MakePaymentNotification($payment));
        return $this->SuccessMessage(
            ['payment' => $payment],
            'Successfully Make New Payment .',
            201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);
        $payment = $this->payment->get($payment);
        return $this->SuccessMessage(
            ['payment' => $payment],
            'Successfully Get New Payment .',
            200
        );
    }

    /**
     * Summary of refund
     * @param Payment $payment
     * @return \Illuminate\Http\JsonResponse
     */
    // public function makeRefund(Payment $payment)
    // {
    //     $this->authorize('delete',$payment);
    //     $success = $this->payment->Makerefund($payment);
    //     return $this->SuccessMessage(
    //         ['success' => $success],
    //         'Successfully Make Refund  Payment .',
    //         200
    //     );
    // }
}
