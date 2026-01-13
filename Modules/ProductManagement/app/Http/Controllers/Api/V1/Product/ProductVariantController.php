<?php

namespace Modules\ProductManagement\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\ProductManagement\Http\Requests\Api\V1\ProductVariants\StoreProductVariantsRequest;
use Modules\ProductManagement\Http\Requests\Api\V1\ProductVariants\UpdateProductVariantsRequest;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\ProductManagement\Services\ProductVariantsService;

class ProductVariantController extends Controller
{
    use AuthorizesRequests;
    protected $variant;

    /**
     * Summary of __construct
     * @param ProductVariant $productVariant
     */
    public function __construct(ProductVariantsService $variant)
    {
        $this->variant = $variant;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ProductVariant::class);
        $filters = $request->only(['product_id', 'sku', 'price']);
        $variants = $this->variant->getAll($filters);
        return $this->SuccessMessage(
            ['variants' => $variants],
            'Successfully GetAll Product Variant .',
            200
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductVariantsRequest $request)
    {
        $this->authorize('create', ProductVariant::class);
        $variant = $this->variant->store($request->validated());
        return $this->SuccessMessage(
            ['variant' => $variant],
            'Successfully Add New Variant .',
            201
        );
    }
    /**
     * Show the specified resource.
     */
    public function show(ProductVariant $productvariant)
    {
        $this->authorize('view', $productvariant);
        $variant =$this->variant->get($productvariant);
        return $this->SuccessMessage(['variant'=> $variant],'Successfully Get Product Variant .',200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductVariantsRequest $request, ProductVariant $productvariant)
    {
        $this->authorize('update', $productvariant);
       
        $variant = $this->variant->update($request->validated(), $productvariant);
        return $this->SuccessMessage(
            ['variant' => $variant],
            'Successfully Update Variant .',
            200
        );
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductVariant $productvariant)
    {
        $this->authorize('delete', $productvariant);
        $this->variant->destroy($productvariant);
        return $this->SuccessMessage([
            'success' => true
        ], 'Successfully Delete Variant .', 200);
    }
}
