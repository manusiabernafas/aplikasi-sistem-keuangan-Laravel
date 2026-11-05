<x-app-layout title="Edit Financial Goal">
    <x-page-header title="Edit Financial Goal" :description="$goal->goal_name" />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('goals.update', $goal) }}">
                @csrf
                @method('PUT')
                @include('goals._form')
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('goals.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
