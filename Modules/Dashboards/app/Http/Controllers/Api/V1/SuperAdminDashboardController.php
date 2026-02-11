<?php

namespace Modules\Dashboards\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboards\Services\SuperAdminService;

class SuperAdminDashboardController extends Controller
{

    protected  SuperAdminService $admin;
    /**
     * Summary of __construct
     * @param SuperAdminService $admin
     */
    public   function __construct(SuperAdminService $admin)
    {
        $this->admin=$admin;
    }
    /**
     * Summary of dashboard
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard() {
        $data=$this->admin->getDashboard();
        return $this->SuccessMessage([$data],'Admin Dashboard ',200);
    }
}
