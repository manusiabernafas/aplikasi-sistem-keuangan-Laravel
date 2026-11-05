<x-app-layout title="Edit Budget">
    <x-page-header title="Edit Budget" description="{{ $budget->name }}" />

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('budgets.update', $budget) }}">
                        @csrf
                        @method('PUT')
                        @include('budgets._form')
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Progress Periode Ini</h2>
                    <div class="progress mb-2" style="height: .5rem;">
                        <div class="progress-bar bg-{{ $progress['level'] === 'exceeded' ? 'danger' : ($progress['level'] === 'warning' ? 'warning' : 'success') }}"
                            style="width: {{ min(100, $progress['percent']) }}%"></div>
                    </div>
                    <div class="small text-secondary">Terpakai Rp {{ number_format($progress['actual'], 0, ',', '.') }} ({{ $progress['percent'] }}%)</div>
                    <div class="small text-secondary">Sisa Rp {{ number_format($progress['remaining'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
