<?php

namespace Modules\ProductManagment\Services;

use App\Jobs\SyncModelImagesJob;
use App\Services\BaseService;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ProductManagment\Models\Product;

class ProductService extends BaseService
{

    use CacheTrait;
    protected $product;

    /**
     * Summary of __construct
     * @param \Modules\ProductManagment\Models\Product $model
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }


    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
    public function getAll(array $filters = []): iterable
    {
        $cacheKey = "products all" . (empty($filters) ? "" : "" . implode(",", $filters));
        return Cache::tags(['products'])->remember($cacheKey, now()->addHours(2), function () use ($filters) {
            return parent::getAll($filters);
        });
    }


    /**
     * Summary of get
     * @param mixed $model
     * @return Model
     */
    public function get($model): Model
    {
        return $model->load(['seller', 'category']);
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
            $data['seller_id'] = Auth::user()->id;
            $product = parent::store($data);
            DB::commit();
            if (!empty($data['images']) && is_array($data['images'])) {
                $temPaths = [];
                foreach ($data['images'] as $file) {
                    $temPaths[] = $file->store('temp', 'media');
                }
                SyncModelImagesJob::dispatch(
                    Product::class,
                    $product->id,
                    $temPaths,
                    'products',
                    'media'
                );
            }
            $this->cacheFlush('products');
            return $product->load(['seller', 'category', 'images']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fail Make New Project' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Summary of update
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return Model
     */
    public function update(array $data, $product): Model
    {

        try {
            DB::beginTransaction();
            $product = parent::update($data, $product);
            DB::commit();
            if (!empty($data['images']) && is_array($data['images'])) {
                $temPaths = [];
                foreach ($data['images'] as $file) {
                    $temPaths[] = $file->store('temp', 'media');
                }
                SyncModelImagesJob::dispatch(
                    Product::class,
                    $product->id,
                    $temPaths,
                    'products',
                    'media'
                );
            }
            $this->cacheFlush('products');
            return $product->load(['seller', 'category', 'images']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fail Update Product: ' . $e->getMessage(), [
                'product_id' => $model->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }


    /**
     * Summary of destroy
     * @param mixed $model
     * @throws \Exception
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return bool
     */
    public function destroy($product): bool
    {

        try {
            DB::beginTransaction();
            $product->clearMediaCollection('products');
            if (!parent::destroy($product)) {
                throw new Exception('Cannot Delete The Product.');
            }
            DB::commit();

            $this->cacheFlush('products');
            return true;


        } catch (Exception $e) {
            DB::rollBack();
            throw new HttpResponseException(response()->json([
                'message' => 'Fail Delete Product',
                'error' => $e->getMessage()
            ], 500));
        }
    }


}
