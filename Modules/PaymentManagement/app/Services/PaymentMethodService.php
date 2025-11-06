<?php

namespace Modules\PaymentManagement\Services;

use App\Jobs\SyncModelImagesJob;
use App\Services\BaseService;
use App\Traits\CacheTrait;
use Exception;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\PaymentManagement\Models\PaymentMethod;

class PaymentMethodService extends BaseService
{


    use CacheTrait;
    protected $method;

    /**
     * Summary of __construct
     * @param \Modules\PaymentManagement\Models\PaymentMethod $method
     */
    public function __construct(PaymentMethod $method)
    {
        parent::__construct($method);
        $this->method = $method;
    }

    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
    public function getAll(array $filters = []): iterable
    {
        $cacheKey = "paymentMethods" . ((empty($filters) ? "" : "" . md5(json_encode($filters))));
        return Cache::tags(['paymentMethods'])->remember($cacheKey, now()->addWeek(), function () use ($filters) {
            $methods = parent::getAll($filters);
            return $methods;
        });
    }


    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $paymentMethod = null;

        try {
            DB::beginTransaction();

            $paymentMethod = parent::store($data);

            $tempPaths = [];

            if (!empty($data['image'])) {
                $file = $data['image'];
                if ($file instanceof HttpUploadedFile) {
                    $fileTempPath = $file->store('temp', 'media');
                    if ($fileTempPath) {
                        $tempPaths[] = $fileTempPath;
                    } else {
                        Log::warning('Failed to store payment method image temporarily.', [
                            'payment_id' => $paymentMethod->id,
                        ]);
                    }
                } else {
                    Log::warning('Skipped non-uploaded-file item in image array.', [
                        'payment_id' => $paymentMethod->id,
                        'item_type' => is_object($file) ? get_class($file) : gettype($file),
                    ]);
                }
            }
            DB::commit();
            if (!empty($tempPaths)) {
                SyncModelImagesJob::dispatch(
                    PaymentMethod::class,
                    $paymentMethod->id,
                    $tempPaths,
                    'paymentmethod',
                    'media'
                );
            }
            $this->cacheFlush('paymentMethods');
            return $paymentMethod;
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
     * @param mixed $method
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function get($method): Model
    {
        return $method->load('image');
    }



    /**
     * Summary of update
     * @param array $data
     * @param mixed $method
     * @return Model
     */
    public function update(array $data, $method): Model
    {
        try {
            DB::beginTransaction();
            $paymentMethod = parent::update($data, $method);
            DB::commit();
            $fileTemps = [];
            if (!empty($data['image'])) {
                $fileTempPath = $data['image']->store('temp', 'media');
                $fileTemp[] = $fileTempPath;
                SyncModelImagesJob::dispatch(
                    PaymentMethod::class,
                    $paymentMethod->id,
                    $fileTemp,
                    'paymentmethod',
                    'media'
                );
            }
            $this->cacheFlush('paymentMethods');
            return $paymentMethod->load('image');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fail Update Payment Method : ' . $e->getMessage(), [
                'payment_if' => $paymentMethod->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Summary of destroy
     * @param mixed $method
     * @throws \Exception
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return bool
     */
    public function destroy($method): bool
    {
        try {
            DB::beginTransaction();
            $method->clearMediaCollection('products');
            if (!parent::destroy($method)) {
                throw new Exception('Cannot Delete The Payment Method.');
            }
            DB::commit();
            $this->cacheFlush('paymentMethods');
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw new HttpResponseException(response()->json([
                'message' => 'Fail Delete Payment Method',
                'error' => $e->getMessage()
            ], 500));
        }
    }
}


