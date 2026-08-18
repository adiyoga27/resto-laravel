<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Order;
use App\Services\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CashFlowController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $entries = CashFlow::betweenDates($startDate, $endDate)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totals = CashFlow::posted()->betweenDates($startDate, $endDate)
            ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit")
            ->selectRaw("SUM(CASE WHEN type = 'kredit' THEN amount ELSE 0 END) as total_kredit")
            ->first();

        $totalDebit = $totals->total_debit ?? 0;
        $totalKredit = $totals->total_kredit ?? 0;
        $saldo = $totalDebit - $totalKredit;

        return view('reports.cash-flow', compact('entries', 'startDate', 'endDate', 'totalDebit', 'totalKredit', 'saldo'));
    }

    public function create(): View
    {
        return view('reports.cash-flow.create');
    }

    public function edit(CashFlow $cashFlow): View
    {
        if ($cashFlow->is_posted) {
            abort(403);
        }

        return view('reports.cash-flow.edit', compact('cashFlow'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:debit,kredit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        CashFlow::create([
            'date' => $validated['date'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reference' => $validated['reference'],
            'is_posted' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('reports.cash-flow', request()->only(['start_date', 'end_date']))
            ->with('success', 'Entri arus kas berhasil ditambahkan.');
    }

    public function update(Request $request, CashFlow $cashFlow): RedirectResponse
    {
        if ($cashFlow->is_posted) {
            return redirect()->back()->with('error', 'Entri yang sudah diposting tidak dapat diubah.');
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:debit,kredit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $cashFlow->update($validated);

        return redirect()->route('reports.cash-flow', request()->only(['start_date', 'end_date']))
            ->with('success', 'Entri arus kas berhasil diperbarui.');
    }

    public function destroy(CashFlow $cashFlow): RedirectResponse
    {
        if ($cashFlow->is_posted) {
            return redirect()->back()->with('error', 'Entri yang sudah diposting tidak dapat dihapus.');
        }

        $cashFlow->delete();

        return redirect()->route('reports.cash-flow', request()->only(['start_date', 'end_date']))
            ->with('success', 'Entri arus kas berhasil dihapus.');
    }

    public function post(CashFlow $cashFlow): RedirectResponse
    {
        if ($cashFlow->is_posted) {
            return redirect()->back()->with('error', 'Entri sudah diposting.');
        }

        $cashFlow->post(auth()->id());

        return redirect()->route('reports.cash-flow', request()->only(['start_date', 'end_date']))
            ->with('success', 'Entri arus kas berhasil diposting.');
    }

    public function unpost(CashFlow $cashFlow): RedirectResponse
    {
        if (! $cashFlow->is_posted) {
            return redirect()->back()->with('error', 'Entri belum diposting.');
        }

        $cashFlow->unpost();

        return redirect()->route('reports.cash-flow', request()->only(['start_date', 'end_date']))
            ->with('success', 'Posting entri arus kas berhasil dibatalkan.');
    }

    public function posting(Request $request): View
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $orders = Order::where('order_status', 'selesai')
            ->where('is_posted_to_cash_flow', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reports.cash-flow.posting', compact('orders', 'startDate', 'endDate'));
    }

    public function postTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['exists:orders,id'],
            'posting_date' => ['required', 'date'],
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])
            ->where('is_posted_to_cash_flow', false)
            ->where('order_status', 'selesai')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada transaksi valid untuk diposting.');
        }

        foreach ($orders as $order) {
            CashFlow::create([
                'date' => $validated['posting_date'],
                'description' => 'Penjualan #'.$order->order_number,
                'type' => 'debit',
                'amount' => $order->total,
                'reference' => $order->order_number,
                'is_posted' => true,
                'posted_at' => now(),
                'posted_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]);

            $order->update(['is_posted_to_cash_flow' => true]);
        }

        return redirect()->route('reports.cash-flow.posting', request()->only(['start_date', 'end_date']))
            ->with('success', $orders->count().' transaksi berhasil diposting ke Arus Kas.');
    }

    public function export(Request $request): Response
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $entries = CashFlow::betweenDates($startDate, $endDate)
            ->orderBy('date', 'desc')
            ->get();

        $excel = new ExcelExport;
        $excel->addRow(['Tanggal', 'Keterangan', 'Ref', 'Tipe', 'Jumlah', 'Status', 'Dibuat']);
        foreach ($entries as $entry) {
            $excel->addRow([
                $entry->date->format('d/m/Y'),
                $entry->description,
                $entry->reference ?? '-',
                $entry->type->label(),
                ($entry->type->value === 'debit' ? '+' : '-').' '.number_format($entry->amount, 0, ',', '.'),
                $entry->is_posted ? 'Posted' : 'Draft',
                $entry->creator?->name ?? '-',
            ]);
        }

        return $excel->download('laporan-arus-kas-'.now()->format('Ymd').'.xlsx');
    }
}
