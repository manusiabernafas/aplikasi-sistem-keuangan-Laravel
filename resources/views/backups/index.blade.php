<x-app-layout title="Database Backup">
    <x-page-header title="Database Backup" description="Buat, unduh, atau hapus backup database.">
        <x-slot name="actions">
            <form method="POST" action="{{ route('backups.store') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-database-add me-1"></i>Buat Backup</button>
            </form>
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            @if (empty($backups))
                <x-empty-state icon="bi-database" title="Belum ada backup" description="Buat backup pertama untuk mengamankan data Anda." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Nama File</th>
                                <th>Ukuran</th>
                                <th>Dibuat</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($backups as $backup)
                                <tr>
                                    <td><code class="small">{{ $backup['name'] }}</code></td>
                                    <td>{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                                    <td>{{ $backup['created_at'] }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('backups.download', $backup['name']) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                        <form action="{{ route('backups.destroy', $backup['name']) }}" method="POST" class="d-inline" data-confirm-delete="Hapus backup {{ $backup['name'] }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
