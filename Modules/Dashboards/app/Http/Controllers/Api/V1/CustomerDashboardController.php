<?php

namespace Modules\Dashboards\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboards\Services\CustomerService;

class CustomerDashboardController extends Controller
{
    protected CustomerService $customer;

    /**
     * Summary of __construct
     * @param CustomerService $customer
     */
    public function __construct(CustomerService $customer)
    {
        $this->customer = $customer;
    }
    /**
     * Summary of dashboard
     * @return \Illuminate\Http\JsonResponse
     */
    public  function dashboard()
    {
        $data = $this->customer->getDashboard();
        return $this->SuccessMessage([$data], 'Customer Dashboard ', 200);
    }
}
