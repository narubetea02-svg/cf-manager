<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>LIVE Print</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 24px; }
        h1 { margin-bottom: 4px; }
        p { color: #4b5563; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .muted { color: #6b7280; font-size: 12px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; }
        .online { background: #dcfce7; color: #166534; }
        .offline { background: #e5e7eb; color: #374151; }
    </style>
</head>
<body onload="window.print()">
    <h1>LIVE</h1>
    <p>สรุปรายการ LIVE สำหรับตรวจสอบหรือพิมพ์เก็บไว้หน้างาน</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>LIVE</th>
                <th>รหัส</th>
                <th>สถานะ</th>
                <th>จัดการข้อมูล</th>
            </tr>
        </thead>
        <tbody>
            @forelse($streams as $index => $stream)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div><strong>{{ $stream->shop?->name ?? '-' }}</strong></div>
                        <div class="muted">{{ ucfirst($stream->platform) }} · {{ $stream->live_url }}</div>
                    </td>
                    <td>{{ $stream->portalSession?->sid ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $stream->status === 'active' ? 'online' : 'offline' }}">
                            {{ $stream->status === 'active' ? 'online' : 'offline' }}
                        </span>
                    </td>
                    <td class="muted">
                        เริ่ม {{ optional($stream->started_at)->format('d/m/Y H:i') ?: '-' }}
                        @if($stream->ended_at)
                            <br>จบ {{ optional($stream->ended_at)->format('d/m/Y H:i') }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">ยังไม่มี LIVE ในระบบ</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
