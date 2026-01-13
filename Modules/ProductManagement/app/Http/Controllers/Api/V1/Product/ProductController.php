<?php

namespace Modules\ProductManagement\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\ProductManagement\Http\Requests\Api\V1\Product\StoreProductRequest;
use Modules\ProductManagement\Http\Requests\Api\V1\Product\UpdateProductRequest;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Services\ProductService;

class ProductController extends Controller
{
    use AuthorizesRequests;
    protected $product;

    /**
     * Summary of __construct
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
        $this->authorize("viewAny", Product::class);
        $filters = $request->only([
            'name',
            'description',
            'category_id',
            'slug',
            'short_description',
            'status',
            'is_featured',
            'created_by'
        ]);
        $products = $this->product->getAll($filters);
        return $this->SuccessMessage(
            ['products' => $products],
            'Successfully Get All Products .',
            200
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);
        $product = $this->product->store($request->validated());
        return  $this->SuccessMessage(
            ['products' => $product],
            'Successfully Add New Product .',
            201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);
        $product = $this->product->get($product);
        return $this->SuccessMessage(
            ['products' => $product],
            'Suucessfully Get Product .',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $product = $this->product->update($request->validated(), $product);
        return $this->SuccessMessage(
            ['products' => $product],
            'Sucessfully Update Product.',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $this->product->destroy($product);
        return $this->successMessage(
            ['success' => true],
            'Successfully Delete Product.',
            200
        );
    }
}
