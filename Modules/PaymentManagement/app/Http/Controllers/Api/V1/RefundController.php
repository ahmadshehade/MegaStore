<?php

namespace Modules\PaymentManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PaymentManagement\Models\Refund;
use Modules\PaymentManagement\Services\RefundService;

class RefundController extends Controller
{

    /**
     * Summary of refundService
     * @var
     */
    protected  $refundService;
    public function  __construct(RefundService $refundService)
    {
        $this->refundService=$refundService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
           $filters=$request->only(['invoice_id','payment_id','refund_date']);
           $refunds=$this->refundService->getAll($filters);
           return $this->SuccessMessage(['refunds'=>$refunds],'Successfully Get All Refund .',200);
    }



    /**
     * Summary of show
     * @param Refund $refund
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Refund  $refund){
        $data=$this->refundService->get($refund);
        return  $this->SuccessMessage(['Refund'=>$data],'Successfully Get Refund .',200);
    }



}
