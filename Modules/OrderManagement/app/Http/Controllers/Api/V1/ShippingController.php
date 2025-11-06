<?php

namespace Modules\OrderManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\OrderManagement\Http\Requests\Api\V1\Shapping\ShippingStoreRequest;
use Modules\OrderManagement\Http\Requests\Api\V1\Shapping\ShippingUpdateRequest;
use Modules\OrderManagement\Models\Shipping;
use Modules\OrderManagement\Services\ShippingService;

class ShippingController extends Controller
{
    use AuthorizesRequests;
    protected $shipping;

    /**
     * Summary of __construct
     * @param \Modules\OrderManagement\Services\ShippingService $shipping
     */
    public function __construct(ShippingService $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Shipping::class);
        $options = $request->only(['name', 'cost', 'is_active']);
        $data = $this->shipping->getAll($options);
        return $this->SuccessMessage([
            'data' => $data
        ], 'Successfully Get All Shepping .', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShippingStoreRequest $request)
    {
        $this->authorize('create', Shipping::class);
        $shipping = $this->shipping->store($request->validated());
        return $this->SuccessMessage(['shipping' => $shipping], 'Successfully Add New Shipping .', 201);
    }

    /**
     * Show the specified resource.
     */
    public function show(Shipping $shipping)
    {
        $this->authorize('view', $shipping);
        $data = $this->shipping->get($shipping);
        return $this->SuccessMessage(['shiping' => $data], 'Successfully Get Shipping. ', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ShippingUpdateRequest $request, Shipping $shipping)
    {
        $this->authorize('update', $shipping);
        $shipping = $this->shipping->update($request->validated(), $shipping);
        return $this->SuccessMessage([
            'shipping' => $shipping,
        ], 'Successfully Update Shipping .', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shipping $shipping)
    {
        $this->authorize('delete', $shipping);
        $this->shipping->destroy($shipping);
        return $this->SuccessMessage(['status' => true],
        'Sucessfully Delete Shipping .', 200);
    }
}
