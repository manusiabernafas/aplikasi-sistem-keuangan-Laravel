<?php

namespace App\Http\Controllers;

use App\Exports\GenericArrayExport;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    private function period(Request $request): array
    {
        $from = $request->filled('date_from') ? Carbon::parse($request->string('date_from')) : now()->startOfMonth();
        $to = $request->filled('date_to') ? Carbon::parse($request->string('date_to')) : now()->endOfMonth();

        return [$from, $to];
    }

    public function cashFlow(Request $request): View|BinaryFileResponse|Response
    {
        $this->authorize('viewAny', Transaction::class);
        [$from, $to] = $this->period($request);
        $data = $this->reportService->cashFlow($from, $to);

        $columns = ['Periode', 'Pemasukan', 'Pengeluaran', 'Net Cash Flow'];
        $rows = collect($data['buckets'])->map(fn ($b) => [$b['label'], $b['income'], $b['expense'], $b['net']])->all();

        return $this->respond($request, 'reports.cash-flow', compact('data', 'from', 'to'), 'Laporan Cash Flow', $from, $to, $columns, $rows);
    }

    public function income(Request $request): View|BinaryFileResponse|Response
    {
        $this->authorize('viewAny', Transaction::class);
        [$from, $to] = $this->period($request);
        $filters = $request->only(['account_id', 'category_id', 'search']);
        $transactions = $this->reportService->transactionsOfType('INCOME', $from, $to, $filters);
        $byCategory = $this->reportService->byCategory('INCOME', $from, $to);
        $accounts = Account::orderBy('account_name')->get();
        $categories = Category::ofType('INCOME')->get();
        $total = $transactions->sum('amount');

        $columns = ['Tanggal', 'No. Transaksi', 'Account', 'Kategori', 'Deskripsi', 'Nominal'];
        $rows = $transactions->map(fn ($t) => [
            $t->transaction_date->format('d/m/Y'), $t->transaction_number, $t->account?->account_name,
            $t->category?->name, $t->description, (float) $t->amount,
        ])->all();

        return $this->respond($request, 'reports.income', compact('transactions', 'byCategory', 'accounts', 'categories', 'total', 'from', 'to'), 'Laporan Pemasukan', $from, $to, $columns, $rows);
    }

    public function expense(Request $request): View|BinaryFileResponse|Response
    {
        $this->authorize('viewAny', Transaction::class);
        [$from, $to] = $this->period($request);
        $filters = $request->only(['account_id', 'category_id', 'search']);
        $transactions = $this->reportService->transactionsOfType('EXPENSE', $from, $to, $filters);
        $byCategory = $this->reportService->byCategory('EXPENSE', $from, $to);
        $accounts = Account::orderBy('account_name')->get();
        $categories = Category::ofType('EXPENSE')->get();
        $total = $transactions->sum('amount');

        $columns = ['Tanggal', 'No. Transaksi', 'Account', 'Kategori', 'Deskripsi', 'Nominal'];
        $rows = $transactions->map(fn ($t) => [
            $t->transaction_date->format('d/m/Y'), $t->transaction_number, $t->account?->account_name,
            $t->category?->name, $t->description, (float) $t->amount,
        ])->all();

        return $this->respond($request, 'reports.expense', compact('transactions', 'byCategory', 'accounts', 'categories', 'total', 'from', 'to'), 'Laporan Pengeluaran', $from, $to, $columns, $rows);
    }

    public function profitLoss(Request $request): View|BinaryFileResponse|Response
    {
        $this->authorize('viewAny', Transaction::class);
        [$from, $to] = $this->period($request);
        $data = $this->reportService->profitLoss($from, $to);

        $columns = ['Kategori', 'Pemasukan', 'Pengeluaran'];
        $rows = [];
        foreach ($data['income_by_category'] as $row) {
            $rows[] = [$row['category'], $row['total'], 0];
        }
        foreach ($data['expense_by_category'] as $row) {
            $rows[] = [$row['category'], 0, $row['total']];
        }

        return $this->respond($request, 'reports.profit-loss', compact('data', 'from', 'to'), 'Laporan Profit & Loss', $from, $to, $columns, $rows);
    }

    public function netWorth(Request $request): View|BinaryFileResponse|Response
    {
        $this->authorize('viewAny', Transaction::class);
        $data = $this->reportService->netWorth();
        $from = null;
        $to = now();

        $columns = ['Komponen', 'Nominal'];
        $rows = [];
        foreach ($data['assets'] as $label => $value) {
            $rows[] = ["Aset: {$label}", $value];
        }
        foreach ($data['liabilities'] as $label => $value) {
            $rows[] = ["Liabilitas: {$label}", -$value];
        }
        $rows[] = ['Net Worth', $data['net_worth']];

        return $this->respond($request, 'reports.net-worth', compact('data'), 'Laporan Net Worth', $from, $to, $columns, $rows);
    }

    public function summary(Request $request): View|BinaryFileResponse|Response
    {
        $this->authorize('viewAny', Transaction::class);
        $year = (int) ($request->input('year') ?: now()->year);
        $rows = $this->reportService->monthlySummary($year);

        $columns = ['Bulan', 'Pemasukan', 'Pengeluaran', 'Net', 'Jumlah Transaksi'];
        $exportRows = collect($rows)->map(fn ($r) => [$r['label'], $r['income'], $r['expense'], $r['net'], $r['transaction_count']])->all();
        $from = Carbon::create($year, 1, 1);
        $to = Carbon::create($year, 12, 31);

        return $this->respond($request, 'reports.summary', compact('rows', 'year'), 'Financial Summary '.$year, $from, $to, $columns, $exportRows);
    }

    /**
     * @param  array<string, mixed>  $viewData
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function respond(Request $request, string $view, array $viewData, string $title, $from, $to, array $columns, array $rows): View|BinaryFileResponse|Response
    {
        $export = $request->string('export')->toString();
        $periodLabel = $from && $to ? $from->format('d/m/Y').' - '.$to->format('d/m/Y') : 'Saat ini';
        $filename = Str::slug($title).'-'.now()->format('Ymd_His');

        if ($export === 'xlsx' || $export === 'csv') {
            $exportRows = array_map(fn ($row) => array_map(fn ($v) => is_numeric($v) ? number_format((float) $v, 2, '.', '') : $v, $row), $rows);

            return Excel::download(
                new GenericArrayExport($exportRows, $columns),
                "{$filename}.{$export}",
                $export === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
            );
        }

        if ($export === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', [
                'title' => $title,
                'periodLabel' => $periodLabel,
                'columns' => $columns,
                'rows' => $rows,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("{$filename}.pdf");
        }

        return view($view, $viewData + ['title' => $title, 'periodLabel' => $periodLabel]);
    }
}
