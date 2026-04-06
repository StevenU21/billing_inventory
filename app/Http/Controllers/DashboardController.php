<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index()
    {
        $data = $this->dashboardService->getBasicInfo();

        return view('dashboard', $data);
    }
}
