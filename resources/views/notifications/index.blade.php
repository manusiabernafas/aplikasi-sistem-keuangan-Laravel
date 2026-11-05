<x-app-layout title="Notifikasi">
    <x-page-header title="Notifikasi" description="Pemberitahuan budget, hutang, piutang, dan transaksi berulang.">
        <x-slot name="actions">
            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Tandai semua dibaca</button>
                </form>
            @endif
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($notifications->isEmpty())
                <x-empty-state icon="bi-bell" title="Belum ada notifikasi" />
            @else
                <div class="list-group list-group-flush">
                    @foreach ($notifications as $notification)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-start {{ $notification->read_at ? '' : 'bg-primary-subtle bg-opacity-25' }}">
                            @csrf
                            <div>
                                <div class="fw-medium small">{{ $notification->data['title'] }}</div>
                                <div class="text-secondary small">{{ $notification->data['message'] }}</div>
                                <div class="text-secondary" style="font-size: .7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-link text-decoration-none">Lihat</button>
                        </form>
                    @endforeach
                </div>
                <div class="mt-3">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
