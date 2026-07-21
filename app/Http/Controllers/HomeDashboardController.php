<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\View\View;

class HomeDashboardController extends Controller
{
    public function __invoke(HomeService $home): View
    {
        return view('home.index', $home->home());
    }
}
