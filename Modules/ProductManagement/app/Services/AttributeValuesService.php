<?php

namespace Modules\ProductManagement\Services;

use App\Services\BaseService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\ProductManagement\Models\AttributeValue;

class AttributeValuesService extends BaseService{


    /**
     * Summary of __construct
     * @param AttributeValue $model
     */
    public function __construct(AttributeValue $model)
    {
        return parent::__construct($model);
    }


    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
   public  function  getAll(array $filters = [], ?Closure $scope = null): iterable
   {
       $user=Auth::user();
        $userKey=$user?$user->id.implode(",",$user->roles->pluck("name")->toArray()):"guest";
        $cacheKey="attributes_".$userKey.((!empty($filters)?md5(json_encode($filters)):""));
        return Cache::remember($cacheKey,now()->addHour(), function () use ($filters) {
            return parent::getAll($filters)->load('attribute');
        });
   }



    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function  store(array $data): Model
    {
        return parent::store($data);
    }


    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public  function get(Model $model): Model
    {
        return parent::get($model)->load('attribute');
    }

    /**
     * Summary of update
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model{
        return parent::update($data, $model);
    }

    /**
     * Summary of destroy
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool{

        return parent::destroy($model);
    }


}
