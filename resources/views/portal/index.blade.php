<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CF MANAGER Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at top, rgba(255,255,255,.96), rgba(248,249,250,.94) 34%, rgba(229,241,255,.88));
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
        }
        .portal-shell { max-width: 560px; margin: 0 auto; }
        .hero-card {
            border: 0;
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .hero-top {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
            padding: 26px 24px 20px;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 999px;
            padding: .35rem .8rem;
            background: rgba(255,255,255,.14);
            font-size: .86rem;
        }
        .action-btn {
            border-radius: 16px;
            padding: 14px 18px;
            font-weight: 700;
        }
        .action-primary {
            background: linear-gradient(135deg, #111827, #334155);
            color: #fff;
            border: 0;
        }
        .action-primary:hover { color: #fff; }
        .action-secondary {
            border: 1px solid #dbe4f0;
            background: #fff;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            padding: .45rem .85rem;
            font-weight: 600;
            font-size: .9rem;
        }
    </style>
</head>
<body>
    <div class="container py-4 py-md-5 portal-shell">
        <div class="card hero-card">
            <div class="hero-top">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="hero-badge mb-3">CF MANAGER Portal</div>
                        <h3 class="mb-2">CF MANAGER ต้องการได้รับสิทธิ์เข้าถึงบัญชี TikTok ของคุณเพิ่มเติม</h3>
                        <p class="mb-0 text-white-50">กดเชื่อมต่อผ่าน Messenger เพื่อผูกบัญชีเพจของร้าน แล้วระบบจะใช้ลิงก์นี้ตามงานไลฟ์ให้เอง</p>
                    </div>
                    <span class="fw-bold text-white fs-4">CF MANAGER</span>
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @php
                    $statusLabels = [
                        'pending_messenger' => ['class' => 'bg-warning-subtle text-warning-emphasis', 'title' => 'รอให้ลูกค้าส่งข้อความใน Messenger', 'desc' => 'กรุณากดปุ่มเปิด Messenger ด้านล่าง แล้วส่งข้อความหาเพจ 1 ข้อความภายใน 10 นาที'],
                        'connected' => ['class' => 'bg-success-subtle text-success-emphasis', 'title' => 'ผูก Messenger สำเร็จ', 'desc' => 'ระบบเชื่อม TikTok username กับ Messenger ของคุณแล้ว สามารถกลับไปพิมพ์ CF ในไลฟ์ได้เลย'],
                        'expired' => ['class' => 'bg-secondary-subtle text-secondary-emphasis', 'title' => 'หมดเวลารอผูก Messenger', 'desc' => 'กรุณากดเชื่อมใหม่อีกครั้ง แล้วกลับไปส่งข้อความหาเพจใหม่'],
                        'ambiguous' => ['class' => 'bg-danger-subtle text-danger-emphasis', 'title' => 'มีหลายรายการรอผูกพร้อมกัน', 'desc' => 'ระบบหยุดผูกอัตโนมัติเพื่อความปลอดภัย กรุณากดเชื่อมใหม่ หรือติดต่อแอดมินให้ตรวจสอบ'],
                    ];
                    $currentStatus = $mappingStatus && isset($statusLabels[$mappingStatus]) ? $statusLabels[$mappingStatus] : null;
                @endphp

                <div class="alert alert-info border-0">
                    <strong>บัญชี TikTok:</strong> {{ $session->liveStream?->platform ? ucfirst($session->liveStream->platform) : 'TikTok' }}<br>
                    <span class="text-muted">Session:</span> <code>{{ $session->sid }}</code>
                </div>

                @if(session('portal_status') === 'saved')
                    <div class="alert alert-success border-0">
                        บันทึก TikTok username แล้ว ขั้นต่อไปให้กดเปิด Messenger และส่งข้อความหาเพจ 1 ข้อความเพื่อผูกบัญชีให้เสร็จ
                    </div>
                @endif

                @if($currentStatus)
                    <div class="p-3 rounded-4 border bg-white mb-4">
                        <div class="status-chip {{ $currentStatus['class'] }}">
                            <span>สถานะ</span>
                            <span>{{ $currentStatus['title'] }}</span>
                        </div>
                        <div class="mt-3 fw-semibold">{{ $mapping?->tiktok_username ? '@' . $mapping->tiktok_username : 'ยังไม่มี TikTok username' }}</div>
                        <div class="text-muted small mt-1">{{ $currentStatus['desc'] }}</div>
                        @if($mapping?->messenger_link_pending_at)
                            <div class="small text-muted mt-2">เริ่มรอผูกเมื่อ {{ $mapping->messenger_link_pending_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>
                @endif

                <div class="d-grid gap-3 mb-4">
                    @if(!empty($messengerUrl))
                        <a href="{{ $messengerUrl }}" class="btn action-btn action-primary" target="_blank" rel="noopener">
                            เปิด Messenger ของเพจร้าน
                        </a>
                        <button type="button" class="btn action-btn action-secondary" onclick="navigator.clipboard.writeText('{{ $messengerUrl }}'); this.innerText='คัดลอกลิงก์แล้ว'">
                            คัดลอกลิงก์ Messenger สำรอง
                        </button>
                    @else
                        <div class="alert alert-warning mb-0">ร้านนี้ยังไม่ได้ตั้งค่า Facebook Page จึงยังไม่มีลิงก์ Messenger</div>
                    @endif
                </div>

                <div class="p-3 rounded-4 border bg-light-subtle mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">@</div>
                            <div>
                            <div class="fw-semibold">ยืนยัน TikTok username</div>
                            <div class="text-muted small">ระบบจะใช้ชื่อนี้ไปรอผูกกับ Messenger ของลูกค้าแบบปลอดภัยในช่วงเวลาสั้น ๆ</div>
                            </div>
                        </div>
                    <form action="{{ url('/pt/connect') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="sid" value="{{ $session->sid }}">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">@</span>
                            <input type="text" class="form-control border-start-0 ps-0" name="tiktok_username" placeholder="username" required>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn action-btn action-primary">เชื่อมต่อบัญชี TikTok</button>
                        </div>
                    </form>
                </div>

                <div class="small text-muted">
                    วิธีใช้งาน:
                    1. กรอก TikTok username แล้วกดเชื่อมต่อ
                    2. กดเปิด Messenger ของเพจร้าน
                    3. ส่งข้อความหาเพจ 1 ข้อความ
                    4. กลับมาดูสถานะด้านบน ถ้าขึ้น “ผูก Messenger สำเร็จ” แปลว่าใช้งานได้แล้ว
                </div>
            </div>
        </div>
    </div>
</body>
</html>
