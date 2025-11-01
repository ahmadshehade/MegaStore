<?php
namespace Modules\ProductManagment\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use App\Traits\ImageManagement;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ProductManagment\Jobs\SyncCategoryImagesJob;
use Modules\ProductManagment\Models\Category;

class CategoryService extends BaseService
{
    use CacheTrait, ImageManagement;

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
     * store: create record in DB, commit, then store files temporarily and dispatch job
     */
    public function store(array $data): Model
    {
        DB::beginTransaction();
        try {
            $category = parent::store($data);

            DB::commit();

            if (!empty($data['images']) && is_array($data['images'])) {
                $tempPaths = [];
                foreach ($data['images'] as $file) {
                    $tempPaths[] = $file->store('temp', 'media');
                }
                SyncCategoryImagesJob::dispatch(
                    Category::class,      
                    $category->id,       
                    $tempPaths,           
                    'categories',         
                    'media'               
                )->onQueue('media'); 
            }

            $this->cacheFlush('category');

            return $category->fresh()->load(['parent', 'children']);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Category store failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw new HttpResponseException(response()->json([
                'message' => 'Failed To Create Category.',
                'error' => $e->getMessage()
            ], 500));
        }
    }


    /**
     * update: update record in DB, commit, then store files temporarily and dispatch job
     */
    public function update(array $data, $category): Model
    {
        DB::beginTransaction();
        try {
            $category = parent::update($data, $category);

            DB::commit();

            if (!empty($data['images']) && is_array($data['images'])) {
                $tempPaths = [];
                foreach ($data['images'] as $file) {
                    $tempPaths[] = $file->store('temp', 'media');
                }

                SyncCategoryImagesJob::dispatch(
                    Category::class,
                    $category->id,
                    $tempPaths,
                    'categories',
                    'media'
                )->onQueue('media');
            }

            $this->cacheFlush('category');

            return $category->fresh()->load(['images', 'parent', 'children']);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Category update failed: ' . $e->getMessage(), [
                'category_id' => $category->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            throw new HttpResponseException(response()->json([
                'message' => 'Failed To Update Category.',
                'error' => $e->getMessage()
            ], 500));
        }
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
     * @throws \Exception
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return bool
     */
    public function destroy($category): bool
    {
        DB::beginTransaction();
        try {
            $category->clearMediaCollection('categories');

            if (!parent::destroy($category)) {
                throw new Exception('Cannot Delete The Category.');
            }

            DB::commit();
            $this->cacheFlush('category');

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new HttpResponseException(response()->json([
                'message' => 'Fail Delete Category',
                'error' => $e->getMessage()
            ], 500));
        }
    }
}
