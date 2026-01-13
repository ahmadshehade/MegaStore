<?php

namespace Modules\ProductManagement\Http\Controllers\Api\V1\Attributes;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\ProductManagement\Http\Requests\Api\V1\Attributes\StoreAttributeRequest;
use Modules\ProductManagement\Http\Requests\Api\V1\Attributes\UpdateAttributeRequest;
use Modules\ProductManagement\Models\Attribute;
use Modules\ProductManagement\Services\AttributeService;

class AttributeController extends Controller
{
    use AuthorizesRequests;
    protected $attributes;

    /**
     * Summary of __construct
     * @param AttributeService $attributes
     */
    public function __construct(AttributeService $attributes)
    {
        $this->attributes = $attributes;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Attribute::class);
        $filters = $request->only(['name', 'slug', 'type']);
        $attributes = $this->attributes->getAll($filters);
        return $this->SuccessMessage(
            ['Attributes' => $attributes],
            'Successfully Get All Attributes.',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttributeRequest $request)
    {
        $this->authorize('create', Attribute::class);
        $attribute = $this->attributes->store($request->validated());
        return $this->SuccessMessage(
            ['attribute' => $attribute],
            'Successfully Make Attribute .',
            201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(Attribute $attribute)
    {
        $this->authorize('view', $attribute);
        $attribute = $this->attributes->get($attribute);
        return $this->SuccessMessage(
            ['attribute' => $attribute],
            'Successfully Get Attribute .',
            200
        );
    }


    /**
     * Summary of update
     * @param UpdateAttributeRequest $request
     * @param Attribute $attribute
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $this->authorize('update', $attribute);
        $attribute = $this->attributes->update(
            $request->validated(),
            $attribute
        );
        return $this->SuccessMessage(
            ['attribute' => $attribute],
            'Successfully Update Attribute.',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        $this->authorize('delete', $attribute);
        $attribute = $this->attributes->destroy($attribute);
        return $this->SuccessMessage(
            ['success' => true],
            'Successfully Delete Attribute.',
            200
        );
    }
}
