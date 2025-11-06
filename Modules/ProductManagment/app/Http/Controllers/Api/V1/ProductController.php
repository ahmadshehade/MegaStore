<?php

namespace Modules\ProductManagment\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\ProductManagment\Http\Requests\Api\V1\Product\ProductStoreRequest;
use Modules\ProductManagment\Http\Requests\Api\V1\Product\ProductUpdateRequest;
use Modules\ProductManagment\Models\Product;
use Modules\ProductManagment\Services\ProductService;

class ProductController extends Controller
{
    use AuthorizesRequests;
    protected $product;

    /**
     * Undocumented function
     *
     * @param ProductService $product
     */
    public function __construct(ProductService $product)
    {
        $this->product = $product;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny',Product::class);
        $options = $request->only(['name', 'description', 'price', 'seller_id']);
        $data = $this->product->getAll($options);
        return $this->SuccessMessage([
            'products' => $data
        ], 'Successfully Get All Products .', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
         $this->authorize('create',Product::class);
        $product = $this->product->store($request->validated());
        return $this->SuccessMessage(
            ['product' => $product],
            'Successfully Make New Product',
            200
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(Product $product)
    {
         $this->authorize('view',$product);
        $product = $this->product->get($product);
        return $this->SuccessMessage([
            'product' => $product->load('images', 'seller', 'category')
        ], 'Successfully get Product .', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $product = $this->product->update($request->validated(), $product);
        return $this->SuccessMessage([
            'product' => $product->load(['images', 'seller', 'category'])
        ], 'Successfully Update Product .', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $option = $this->product->destroy($product);
        return $this->SuccessMessage([
            'status' => true
        ], 'Successfully Delete Product .', 200);
    }


}
