<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reportService): View
    {
        $user = $request->user();
        $todaySummary = $reportService->getSalesSummary(today(), today(), $user->id);
        $latestTransactions = Transaction::query()
            ->completed()
            ->with('items')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('kasir.dashboard.index', compact('todaySummary', 'latestTransactions'));
    }
}
