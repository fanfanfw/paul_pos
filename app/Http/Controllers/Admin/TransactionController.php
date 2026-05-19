<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $cashiers = User::query()->whereIn('role', ['admin', 'kasir'])->orderBy('name')->get();
        $transactions = Transaction::query()
            ->with(['user', 'items'])
            ->withCount('items')
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($request->filled('search'), fn ($query) => $query->where('invoice_number', 'like', '%'.$request->string('search').'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'cashiers'));
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['user', 'items']);

        return view('admin.transactions.show', compact('transaction'));
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'search' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
