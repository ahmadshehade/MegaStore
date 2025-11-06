<?php

namespace Modules\PaymentManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\PaymentManagement\Http\Requests\Api\V1\PayementMethods\StorePaymentMethodRequest;
use Modules\PaymentManagement\Http\Requests\Api\V1\PayementMethods\UpdatePaymentMethodRequest;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\PaymentManagement\Services\PaymentMethodService;

class PaymentMethodController extends Controller
{

    use AuthorizesRequests;
    protected $payment_methods;

    /**
     * Summary of __construct
     * @param \Modules\PaymentManagement\Services\PaymentMethodService $payment_method
     */
    public function __construct(PaymentMethodService $payment_method)
    {
        $this->payment_methods = $payment_method;
    }


    /**
     * Summary of index
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PaymentMethod::class);
        $fillters = $request->only(['name']);
        $data = $this->payment_methods->getAll($fillters);
        return $this->SuccessMessage(['data' => $data], 'Successfuly get all payment methods', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentMethodRequest $request)
    {
        $this->authorize('create', PaymentMethod::class);
        $val = $request->validated();
        $data = $this->payment_methods->store($val);
        return $this->SuccessMessage([
            'data' => $data
        ], 'Successfully Make New Method', 201);
    }

    /**
     * Show the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        $this->authorize('view', $paymentMethod);
        $data = $this->payment_methods->get($paymentMethod);
        return $this->SuccessMessage([
            'data' => $data
        ], 'Successfully Get The Payment Method', 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $this->authorize('update', $paymentMethod);
        $data = $this->payment_methods->update($request->validated(), $paymentMethod);
        return $this->SuccessMessage(['data' => $data], 'Successfully Update Payment Method .', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authorize('delete', $paymentMethod);
        $data = $this->payment_methods->destroy($paymentMethod);
        return $this->SuccessMessage(['data' => $data], 'Successfully Delete  Payment Method', 200);
    }
}
