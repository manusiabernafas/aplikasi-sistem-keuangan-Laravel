@props(['route', 'params' => []])

<div class="d-flex gap-2 no-print">
    <a href="{{ route($route, array_merge($params, ['export' => 'xlsx'])) }}" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
    <a href="{{ route($route, array_merge($params, ['export' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
    <a href="{{ route($route, array_merge($params, ['export' => 'pdf'])) }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
    <button type="button" onclick="window.print()" class="btn btn-outline-primary btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
</div>
