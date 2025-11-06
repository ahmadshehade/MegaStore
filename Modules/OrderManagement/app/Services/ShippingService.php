<?php
namespace Modules\OrderManagement\Services;

use App\Jobs\SyncModelImagesJob;
use App\Services\BaseService;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\OrderManagement\Models\Shipping;
use function Pest\Laravel\instance;
use Illuminate\Http\UploadedFile as HttpUploadedFile;

class ShippingService extends BaseService
{
    use CacheTrait;

    protected $shipping;
    /**
     * Summary of __construct
     * @param \Modules\OrderManagement\Models\Shipping $shipping
     */
    public function __construct(Shipping $shipping)
    {
        parent::__construct($shipping);
        $this->shipping = $shipping;
    }

    /**
     * Summary of getAll
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Model[]|\Traversable<int|string, \Illuminate\Database\Eloquent\Model>
     */
    public function getAll(array $filters = []): iterable
    {
        $cacheKey = "shipping" . ((empty($filters) ? "" : "" . implode(",", $filters)));
        return Cache::tags(['shippings'])->remember($cacheKey, now()->addDay(), function () use ($filters) {
            return parent::getAll($filters);
        });
    }

    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $shipping = null;

        try {
            DB::beginTransaction();

            $shipping = parent::store($data);

            $tempPaths = [];

            if (!empty($data['image'])) {
                $file = $data['image'];
                if ($file instanceof HttpUploadedFile) {
                    $fileTempPath = $file->store('temp', 'media');
                    if ($fileTempPath) {
                        $tempPaths[] = $fileTempPath;
                    } else {
                        Log::warning('Failed to store payment shipping temporarily.', [
                            'shipping' => $shipping->id,
                        ]);
                    }
                } else {
                    Log::warning('Skipped non-uploaded-file item in image array.', [
                        'shipping' => $shipping->id,
                        'item_type' => is_object($file) ? get_class($file) : gettype($file),
                    ]);
                }
            }
            DB::commit();
            if (!empty($tempPaths)) {
                SyncModelImagesJob::dispatch(
                    Shipping::class,
                    $shipping->id,
                    $tempPaths,
                    'shipping',
                    'media'
                );
            }
            $this->cacheFlush('paymentMethods');
            return $shipping;
        } catch (Exception $e) {
            $paymentId = $paymentMethod->id ?? null;
            DB::rollBack();
            Log::error('Fail Make Payment Method : ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }


    /**
     * Summary of get
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return parent::get($model);
    }


    /**
     * Summary of update
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        try {
            DB::beginTransaction();
            $shipping = parent::update($data, $model);
            if (!empty($data['image'])) {
                $file = $data['image'];
                $tempPaths = [];
                if ($file instanceof UploadedFile) {
                    $fileTempPath = $file->store('temp', 'media');
                    if ($fileTempPath) {
                        $tempPaths[] = $fileTempPath;
                    } else {
                        Log::warning('Failed to store shipping image temporarily.', [
                            'shipping' => $shipping->id,
                        ]);
                    }
                } else {
                    Log::warning('Skipped non-uploaded-file item in image array.', [
                        'shipping' => $shipping->id,
                        'item_type' => is_object($file) ? get_class($file) : gettype($file),
                    ]);
                }
            }
            DB::commit();
            if (!empty($tempPaths)) {
                SyncModelImagesJob::dispatch(
                    Shipping::class,
                    $shipping->id,
                    $tempPaths,
                    'shipping',
                    'media'
                );
            }
            $this->cacheFlush('shippings');
            return $shipping;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fial Update Shipping' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Summary of destroy
     * @param \Illuminate\Database\Eloquent\Model $model
     * @throws \Exception
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return bool
     */
    public function destroy(Model $model): bool
    {
        try {
            DB::beginTransaction();
            $model->clearMediaCollection('shipping');
            if (!parent::destroy($model)) {
                throw new Exception('Cannot Delete The Shipping.');
            }
            DB::commit();
            $this->cacheFlush('shippings');
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new HttpResponseException(response()->json([
                'message' => 'Fail Delete Shipping',
                'error' => $e->getMessage()
            ], 500));

        }
    }
}
