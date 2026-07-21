<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __invoke(FinanceService $finance): View
    {
        return view('finance.index', $finance->summary());
    }
}
