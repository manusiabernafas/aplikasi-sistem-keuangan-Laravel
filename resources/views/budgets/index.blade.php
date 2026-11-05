<x-app-layout title="Budget">
    <x-page-header title="Budget" description="Pantau anggaran pengeluaran per kategori atau account.">
        <x-slot name="actions">
            @can('create', \App\Models\Budget::class)
                <a href="{{ route('budgets.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Budget</a>
            @endcan
        </x-slot>
    </x-page-header>

    @if ($budgets->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body">
                <x-empty-state icon="bi-pie-chart" title="Belum ada budget" description="Buat anggaran bulanan pertama Anda untuk memantau pengeluaran.">
                    <x-slot name="action">
                        @can('create', \App\Models\Budget::class)
                            <a href="{{ route('budgets.create') }}" class="btn btn-primary btn-sm">Tambah Budget</a>
                        @endcan
                    </x-slot>
                </x-empty-state>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($budgets as $budget)
                @php $progress = $budget->progress; @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $budget->name }}</div>
                                    <div class="small text-secondary">
                                        {{ $budget->period_type === 'MONTHLY' ? 'Bulanan' : 'Tahunan' }}
                                        @if ($budget->category) &middot; {{ $budget->category->name }} @endif
                                        @if ($budget->account) &middot; {{ $budget->account->account_name }} @endif
                                    </div>
                                </div>
                                <div>
                                    @can('update', $budget)
                                        <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('delete', $budget)
                                        <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline" data-confirm-delete="Hapus budget {{ $budget->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>

                            <div class="progress mb-2" style="height: .5rem;">
                                <div class="progress-bar bg-{{ $progress['level'] === 'exceeded' ? 'danger' : ($progress['level'] === 'warning' ? 'warning' : 'success') }}"
                                    style="width: {{ min(100, $progress['percent']) }}%"></div>
                            </div>

                            <div class="d-flex justify-content-between small">
                                <span class="text-secondary">Rp {{ number_format($progress['actual'], 0, ',', '.') }} terpakai</span>
                                <span class="fw-semibold">{{ $progress['percent'] }}%</span>
                            </div>
                            <div class="d-flex justify-content-between small text-secondary">
                                <span>dari Rp {{ number_format($progress['planned'], 0, ',', '.') }}</span>
                                <span>Sisa Rp {{ number_format($progress['remaining'], 0, ',', '.') }}</span>
                            </div>

                            @if ($progress['level'] === 'exceeded')
                                <div class="alert alert-danger small mt-2 mb-0 py-1">Budget terlampaui!</div>
                            @elseif ($progress['level'] === 'warning')
                                <div class="alert alert-warning small mt-2 mb-0 py-1">Mendekati batas budget.</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $budgets->links() }}</div>
    @endif
</x-app-layout>
