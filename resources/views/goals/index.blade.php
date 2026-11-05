<x-app-layout title="Financial Goals">
    <x-page-header title="Financial Goals" description="Target keuangan seperti dana darurat atau tabungan liburan.">
        <x-slot name="actions">
            @can('create', \App\Models\FinancialGoal::class)
                <a href="{{ route('goals.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Goal</a>
            @endcan
        </x-slot>
    </x-page-header>

    @if ($goals->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body">
                <x-empty-state icon="bi-flag" title="Belum ada target keuangan" description="Buat target seperti Dana Darurat atau Liburan.">
                    <x-slot name="action">
                        @can('create', \App\Models\FinancialGoal::class)
                            <a href="{{ route('goals.create') }}" class="btn btn-primary btn-sm">Tambah Goal</a>
                        @endcan
                    </x-slot>
                </x-empty-state>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($goals as $goal)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $goal->goal_name }}</div>
                                    @if ($goal->target_date)
                                        <div class="small text-secondary">Target: {{ $goal->target_date->format('d/m/Y') }}</div>
                                    @endif
                                </div>
                                <span class="badge text-bg-{{ $goal->status === 'ACHIEVED' ? 'success' : 'primary' }}-subtle">
                                    {{ $goal->status === 'ACHIEVED' ? 'Tercapai' : 'Berjalan' }}
                                </span>
                            </div>

                            <div class="progress mb-2" style="height: .5rem;">
                                <div class="progress-bar bg-primary" style="width: {{ $goal->progressPercent() }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between small mb-3">
                                <span class="text-secondary">Rp {{ number_format($goal->current_amount, 0, ',', '.') }}</span>
                                <span class="fw-semibold">{{ $goal->progressPercent() }}%</span>
                                <span class="text-secondary">Rp {{ number_format($goal->target_amount, 0, ',', '.') }}</span>
                            </div>

                            <div class="d-flex gap-2">
                                @can('update', $goal)
                                    @if ($goal->status !== 'ACHIEVED')
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#contribute-{{ $goal->id }}">
                                            <i class="bi bi-plus-lg"></i> Kontribusi
                                        </button>
                                    @endif
                                    <a href="{{ route('goals.edit', $goal) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('delete', $goal)
                                    <form action="{{ route('goals.destroy', $goal) }}" method="POST" class="d-inline" data-confirm-delete="Hapus goal {{ $goal->goal_name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <x-modal :name="'contribute-'.$goal->id" max-width="sm">
                    <form method="POST" action="{{ route('goals.contribute', $goal) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="h6 fw-semibold mb-0">Tambah Kontribusi</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <x-input-label :for="'amount-'.$goal->id" value="Nominal (Rp)" />
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount-{{ $goal->id }}" class="form-control" required>
                        </div>
                        <div class="modal-footer">
                            <x-secondary-button data-bs-dismiss="modal">Batal</x-secondary-button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </x-modal>
            @endforeach
        </div>
        <div class="mt-3">{{ $goals->links() }}</div>
    @endif
</x-app-layout>
