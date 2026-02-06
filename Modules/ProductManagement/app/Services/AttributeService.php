<?php

namespace Modules\ProductManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ProductManagement\Models\Attribute;

class AttributeService extends BaseService
{

    use CacheTrait;
    /**
     * Summary of __construct
     * @param Attribute $attribute
     */
    public function __construct(Attribute $attribute)
    {
        parent::__construct($attribute);
    }

    /**
     * Summary of getAll
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Model[]|\Traversable<int|string, \Illuminate\Database\Eloquent\Model>
     */
    public  function getAll(array $filters = [], ?Closure $scope = null): iterable
    {
            $user = Auth::user();
        $userKey = $user ? $user->id . '_' . (implode(',', $user->roles->pluck('name')->toArray())) : "guest";
        $cacheKey = "Attributes_" . $userKey . (!empty($filters) ? md5(json_encode($filters)) : "*");
        return Cache::tags(['attributes'])->remember($cacheKey, now()->addHour(), function () use ($filters) {
            return parent::getAll($filters)->load('values');
        });
    }

    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        try {
            DB::beginTransaction();
            $attribute = parent::store($data);
            if ($attribute && $attribute->type === 'select') {
                foreach ($data['values'] as $valueData) {
                    $attribute->values()->create([
                        'value' => $valueData['value'],
                        'label' => $valueData['label'] ?? null
                    ]);
                }
            }
            $pivotData = [];
            foreach ($data['variant_values'] as $variantId => $valueId) {
                $pivotData[$variantId] = ['product_variant_id' => $valueId];
            }
            $attribute->variants()->attach($pivotData);
            DB::commit();
            $this->cacheFlush('attributes');
            return $attribute->load(['values', 'variants']);
        } catch (Exception $e) {
            Log::error("Fail To Make Attribute" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {

        return parent::get($model)->load('values');
    }

    /**
     * Summary of update
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        try {
            DB::beginTransaction();
            $attribute = parent::update($data, $model);
            if ($attribute && $attribute->type === 'text') {
                if ($attribute->values()->exists()) {
                    foreach ($attribute->values as $value) {
                        $value->delete();
                    }
                }
            }
            if ($attribute && $attribute->type === 'select' && !empty($data['values'])) {
                foreach ($data['values'] as $valueData) {
                    if (isset($valueData['id'])) {
                        $attributeValue = $attribute->values()->find($valueData['id']);
                        if ($attributeValue) {
                            $attributeValue->update([
                                'value' => $valueData['value'],
                                'label' => $valueData['label'] ?? null
                            ]);
                        }
                    } else {
                        $attribute->values()->create([
                            'value' => $valueData['value'],
                            'label' => $valueData['label'] ?? null
                        ]);
                    }
                }
            }
            if (isset($data['variant_values'])) {
                $attribute->variants()->sync($data['variant_values']);
            }

            DB::commit();
            $this->cacheFlush('attributes');
            return $attribute->load(['values','variants']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Fail To Update Attribute: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Summary of destroy
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool
    {
        $this->cacheFlush('attributes');
        return parent::destroy($model);
    }
}
