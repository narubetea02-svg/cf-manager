<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เชื่อมต่อสำเร็จ | CF MANAGER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #eff6ff, #f8fafc);
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
        }
        .portal-card { max-width: 420px; margin: 50px auto; border-radius: 24px; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12); }
        .success-icon { font-size: 60px; color: #16a34a; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card portal-card border-0 p-4 text-center">
            <div class="success-icon">✅</div>
            <h4 class="mb-3">เชื่อมต่อสำเร็จ!</h4>
            <p class="text-muted">คุณได้ระบุตัวตนเป็น <strong>{{ $mapping->tiktok_username }}</strong> เรียบร้อยแล้ว</p>
            <p class="small text-muted mb-4">เชื่อมต่อเสร็จแล้ว กลับไปที่ไลฟ์ได้เลย</p>

            @if(!empty($session?->shop?->messengerSetting?->fb_page_id))
                <a href="https://m.me/{{ $session->shop->messengerSetting->fb_page_id }}?ref={{ $session->sid }}" target="_blank" rel="noopener" class="btn btn-dark rounded-pill w-100 mb-2">เปิดแชท Messenger</a>
            @endif
            <a href="#" onclick="window.close();" class="btn btn-outline-secondary rounded-pill w-100">ปิดหน้านี้ และกลับไปที่ไลฟ์</a>
        </div>
    </div>
</body>
</html>
