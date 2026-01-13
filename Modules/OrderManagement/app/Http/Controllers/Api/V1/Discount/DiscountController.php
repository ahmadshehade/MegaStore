<?php

namespace Modules\OrderManagement\Http\Controllers\Api\V1\Discount;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OrderManagement\Http\Requests\Api\V1\Discount\StoreDiscountRequest;
use Modules\OrderManagement\Http\Requests\Api\V1\Discount\UpdateDiscountRequest;
use Modules\OrderManagement\Models\Discount;
use Modules\OrderManagement\Services\DiscountService;

class DiscountController extends Controller
{

    protected $discount;

    /**
     * Summary of __construct
     * @param DiscountService $discount
     */
    public function __construct(DiscountService $discount)
    {
        $this->discount = $discount;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'code',
            'value',
            'start_date',
            'end_date',
            'status',
            'created_by'
        ]);

        $discounts = $this->discount->getAll($filters);
        return $this->SuccessMessage(
            ['discounts' => $discounts],
            'Successfully Get All Discounts .',
            200
        );
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiscountRequest $request)
    {
        $discount = $this->discount->store($request->validated());
        return $this->SuccessMessage(
            ['discount' => $discount],
            'Successfully Make New Discount .',
            201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(Discount $discount)
    {
        $disc = $this->discount->get($discount);
        return $this->SuccessMessage(
            ['discount' => $disc],
            'Successfully Get Discount .',
            200
        );
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        $disc = $this->discount->update($request->validated(), $discount);
        return $this->SuccessMessage(
            ['discount' => $disc],
            'Successfully Update Discount .',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount) {
        $disc=$this->discount->destroy($discount);
        return $this->SuccessMessage(['success'=>true],'Successfully Deleted Discount .',200);
    }
}
