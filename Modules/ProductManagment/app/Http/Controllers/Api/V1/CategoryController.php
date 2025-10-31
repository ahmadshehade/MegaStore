<?php

namespace Modules\ProductManagment\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\ProductManagment\Http\Requests\Api\V1\Category\CategoryStoreRequest;
use Modules\ProductManagment\Http\Requests\Api\V1\Category\CategoryUpdateRequest;
use Modules\ProductManagment\Models\Category;
use Modules\ProductManagment\Services\CategoryService;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    protected $category;

    /**
     * Summary of __construct
     * @param \Modules\ProductManagment\Services\CategoryService $category
     */
    public function __construct(CategoryService $category)
    {
        $this->category = $category;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);
        $filters = $request->only(['name', 'description', 'parent_id']);
        $data = $this->category->getAll($filters);
        return $this->SuccessMessage([
            'category' => $data
        ], 'Successfully Get All Categories .', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        $this->authorize('create', Category::class);
        $category = $this->category->store($request->validated());
        return $this->SuccessMessage([
            'category' => $category->load('parent', 'children')
        ], 'Successfully Make New Category', 201);
    }

    /**
     * Show the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);
        $data = $this->category->get($category->load('parent', 'children'));
        return $this->SuccessMessage([
            'category' => $data->load('parent', 'children')
        ], 'Successfully Get Category', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $this->authorize('update', $category);
        $data = $this->category->update($request->validated(), $category);
        return $this->SuccessMessage([
            'category' => $data->load('parent', 'children')
        ], 'Successfully Update Category', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        $this->category->destroy($category);
        return $this->SuccessMessage([
            'success' => true
        ], 'Successfully delete Category', 200);
    }
}
