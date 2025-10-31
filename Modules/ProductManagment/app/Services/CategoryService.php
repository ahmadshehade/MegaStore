<?php
namespace Modules\ProductManagment\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Modules\ProductManagment\Models\Category;

class CategoryService extends BaseService
{

    use CacheTrait;
    protected $category;
    /**
     * Summary of __construct
     * @param \Modules\ProductManagment\Models\Category $category
     */
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }



    /**
     * Summary of getAll
     * @param array $filters
     * @return Model[]|\Traversable<int|string, Model>
     */
    public function getAll(array $filters = []): iterable
    {
        $cacheKey = 'category.all:' . (empty($filters) ? 'default' : md5(json_encode($filters)));
        return Cache::tags(['category'])->remember($cacheKey, now()->addDays(7), function () use ($filters) {
            return parent::getAll($filters);
        });
    }

    /**
     * Summary of store
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $data): Model
    {
        $category = parent::store($data);
        $this->cacheFlush('category');
        return $category;
    }


    /**
     * Summary of update
     * @param array $data
     * @param mixed $category
     * @return Model
     */
    public function update(array $data, $category): Model
    {
        $category = parent::update($data, $category);
        $this->cacheFlush('category');
        return $category;
    }


    /**
     * Summary of get
     * @param mixed $category
     * @return Model
     */
    public function get($category): Model
    {
        return parent::get($category);
    }

    /**
     * Summary of destroy
     * @param mixed $category
     * @return bool
     */
    public function destroy($category): bool
    {
        
        $deleted= parent::destroy($category);
        if($deleted==true){
            $this->cacheFlush('category');
            return true;
        }
        throw  new HttpResponseException(response()->json(['message'=> 'Fail Delete Category'],500));
    }




}
