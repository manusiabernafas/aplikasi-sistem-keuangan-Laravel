<x-app-layout title="Financial Insights">
    <x-page-header title="Financial Insights" description="Analisis otomatis dari data transaksi Anda." />

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($insights->isEmpty())
                <x-empty-state icon="bi-lightbulb" title="Belum ada insight" description="Catat lebih banyak transaksi agar sistem dapat memberikan analisis." />
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach ($insights as $insight)
                        <div class="alert alert-{{ $insight['type'] === 'danger' ? 'danger' : ($insight['type'] === 'warning' ? 'warning' : ($insight['type'] === 'success' ? 'success' : 'info')) }} d-flex align-items-start gap-2 mb-0">
                            <i class="bi bi-lightbulb-fill mt-1"></i>
                            <span>{{ $insight['message'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
