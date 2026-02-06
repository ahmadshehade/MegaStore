<?php


namespace Modules\OrderManagement\Http\Controllers\Api\V1\ProductReview;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\OrderManagement\Http\Requests\Api\V1\ProductReviews\StoreProductReviewRequest;
use Modules\OrderManagement\Http\Requests\Api\V1\ProductReviews\UpdateProductReviewRequest;
use Modules\OrderManagement\Models\ProductReview;
use Modules\OrderManagement\Services\ProductReviewService;

class ProductReviewController extends Controller
{
    use AuthorizesRequests;
    protected ProductReviewService $productReviewService;

    /**
     * Summary of __construct
     * @param ProductReviewService $productReviewService
     */
    public function __construct(ProductReviewService $productReviewService)
    {
        $this->productReviewService = $productReviewService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ProductReview::class);
        $filters = $request->only(['user_id', 'product_id', 'order_id', 'rating', 'comment']);
        $reviews = $this->productReviewService->getAll($filters);
        return  $this->SuccessMessage([$reviews], 'Successfully Get All Product Reviews .', 200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductReviewRequest $request)
    {
        $this->authorize('create', ProductReview::class);
        $review = $this->productReviewService->store($request->validated());
        return $this->SuccessMessage([$review], 'Successfully Make New Review . ', 201);
    }

    /**
     * Show the specified resource.
     */
    public function show(ProductReview $productReview)
    {
        $this->authorize('view', $productReview);
        $review = $this->productReviewService->get($productReview);
        return $this->SuccessMessage([$review], 'Successfully Get The Review .', 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductReviewRequest $request, ProductReview $productReview)
    {
        $this->authorize('update', $productReview);
        $review = $this->productReviewService->update($request->validated(), $productReview);
        return $this->SuccessMessage([$review], 'Successfully Update The  Review .', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductReview $productReview)
    {
        $this->authorize('delete', $productReview);
        $review = $this->productReviewService->destroy($productReview);
        return $this->SuccessMessage([$review], 'Successfully Delete The  Review .', 200);
    }
}
