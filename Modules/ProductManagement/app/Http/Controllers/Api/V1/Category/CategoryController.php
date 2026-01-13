<?php

namespace Modules\ProductManagement\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\ProductManagement\Http\Requests\Api\V1\Catgories\StoreCategoryRequest;
use Modules\ProductManagement\Http\Requests\Api\V1\Catgories\UpdateCategoryRequest;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Services\CategoryService;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    protected $category;


    /**
     * Summary of __construct
     * @param CategoryService $category
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
        $this->authorize("viewAny", Category::class);
        $filters = $request->only(['name', 'descriptions', 'parent_id', 'slug']);
        $categories = $this->category->getAll($filters);
        return $this->SuccessMessage(['data' => $categories], 'Successfully Get All Categories .', 200);
    }
    /**
     * Summary of store
     * @param StoreCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);
        $category = $this->category->store($request->validated());
        return $this->SuccessMessage(['data' => $category], 'Successfully Make New Category ', 201);
    }
    /**
     * Show the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);
        $category = $this->category->get($category);
        return $this->SuccessMessage(['data' => $category], 'Successfully Get Category .', 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);
        $category = $this->category->update($request->validated(), $category);
        return $this->SuccessMessage(['data' => $category], 'Successfully Update Category .', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        $this->category->destroy($category);
        return $this->SuccessMessage(['success' => true], 'Successfully Delete Category .', 200);
    }
}
