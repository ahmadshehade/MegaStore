<?php

namespace Modules\PaymentManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\PaymentManagement\Http\Requests\Api\V1\PaymentMethod\StorePaymentMethodRequest;
use Modules\PaymentManagement\Http\Requests\Api\V1\PaymentMethod\UpdatePaymentMethodRequest;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\PaymentManagement\Services\PaymentMethodService;

class PaymentMethodController extends Controller
{
    use AuthorizesRequests;
    protected $paymentMethod;

    /**
     * Summary of __construct
     * @param PaymentMethodService $paymentMethod
     */
    public function __construct(PaymentMethodService $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny",PaymentMethod::class);
        $filters = $request->only(['name', 'description', 'type', 'fee', 'security_features', 'is_active']);
        $paymentMethods = $this->paymentMethod->getAll($filters);
        return $this->SuccessMessage([
            'paymentMethods' => $paymentMethods,
        ], 'Successfully Get All Payment Method .', 200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentMethodRequest $request)
    {
        $this->authorize('create', PaymentMethod::class);
        $paymentMethod = $this->paymentMethod->store($request->validated());
        return $this->SuccessMessage(
            ['PaymentMethod' => $paymentMethod],
            'Successfully Add New Payment Method',
            200
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        $this->authorize('view', $paymentMethod);
        $data = $this->paymentMethod->get($paymentMethod);
        return $this->SuccessMessage(
            ['PaymentMethod' => $data],
            'Successfully Get PaymentMethod .',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $this->authorize('update', $paymentMethod);
        $data = $this->paymentMethod->update($request->validated(), $paymentMethod);
        return $this->SuccessMessage(
            ['PaymentMethod' => $data],
            'Successfully Update PaymentMethod .',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authorize('delete', $paymentMethod);
        $success = $this->paymentMethod->destroy($paymentMethod);
        return $this->SuccessMessage(
            ['Success' => $success],
            'Successfully Delete PaymentMethod .',
            200
        );
    }
}
