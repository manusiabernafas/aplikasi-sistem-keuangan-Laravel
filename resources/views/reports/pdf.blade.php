<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1E293B; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h1 { font-size: 16px; margin: 0 0 4px; }
        .header p { margin: 0; color: #64748B; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #E2E8F0; padding: 6px 8px; text-align: left; }
        th { background: #F1F5F9; text-transform: uppercase; font-size: 9px; }
        td.number, th.number { text-align: right; }
        tfoot td { font-weight: bold; background: #F8FAFC; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; color: #94A3B8; font-size: 9px; }
        .footer:after { content: "Halaman " counter(page) " dari " counter(pages); }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'Keuang') }}</h1>
        <p>{{ $title }}</p>
        <p>Periode: {{ $periodLabel }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $index => $column)
                    <th class="{{ $index > 0 ? 'number' : '' }}">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $index => $value)
                        <td class="{{ $index > 0 && is_numeric($value) ? 'number' : '' }}">
                            {{ is_numeric($value) ? number_format((float) $value, 0, ',', '.') : $value }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
