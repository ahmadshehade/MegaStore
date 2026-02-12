<?php

namespace Modules\Dashboards\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Dashboards\Services\SellerService;

class SellerDashboardController extends Controller
{
    protected SellerService $seller;
    public function __construct(SellerService $seller)
    {
        $this->seller = $seller;
    }

    /**
     * Summary of dashboard
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard()
    {
        $data = $this->seller->getDashboard(Auth::user());
        return $this->SuccessMessage([$data], 'Seller Dashboard', 200);
    }
}
