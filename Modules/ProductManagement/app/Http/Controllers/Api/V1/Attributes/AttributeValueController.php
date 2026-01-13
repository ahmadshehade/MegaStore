<?php

namespace Modules\ProductManagement\Http\Controllers\Api\V1\Attributes;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\ProductManagement\Http\Requests\Api\V1\AttributeValues\StoreAttributeValuesRequest;
use Modules\ProductManagement\Http\Requests\Api\V1\AttributeValues\UpdateAttributeValuesRequest;
use Modules\ProductManagement\Models\AttributeValue;
use Modules\ProductManagement\Services\AttributeValuesService;

class AttributeValueController extends Controller
{
    use AuthorizesRequests;
    protected $attributeValues;

    /**
     * Summary of __construct
     * @param AttributeValuesService $AttributeValues
     */
    public function __construct(AttributeValuesService $AttributeValues)
    {
        $this->attributeValues = $AttributeValues;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", AttributeValue::class);
        $filters = $request->only(['value', 'label', 'attribute_id']);
        $attributeValues = $this->attributeValues->getAll($filters);
        return $this->SuccessMessage(
            ['attributeValues' => $attributeValues],
            'Successfully Get All AttributeValues.',
            200
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttributeValuesRequest $request)
    {
        $this->authorize('create', AttributeValue::class);
        $attributeValues = $this->attributeValues->store($request->validated());
        return $this->SuccessMessage(
            ['attributeValues' => $attributeValues],
            'Successfully Make new Attribute Values.',
            201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(AttributeValue $attributeValue)
    {
        $this->authorize('view', AttributeValue::class);
        $arrributeValue = $this->attributeValues->get($attributeValue);
        return $this->SuccessMessage(
            ['attributeValue' => $arrributeValue],
            'Successfully Get Attribute Value .',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttributeValuesRequest $request, AttributeValue $attributeValue)
    {
        $this->authorize('update', $attributeValue);
        $data = $this->attributeValues->update($request->validated(), $attributeValue);
        return $this->SuccessMessage(
            ['AttributeValue' => $data],
            'Successfully Update Attribute Value .',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttributeValue $attributeValue)
    {
        $this->authorize('delete', $attributeValue);
        $success = $this->attributeValues->destroy($attributeValue);
        return $this->SuccessMessage(
            ['success' => true],
            'Successfully Delete Attribute Value .',
            200
        );
    }
}
