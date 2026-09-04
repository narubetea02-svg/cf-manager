<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อผิดพลาด | CF MANAGER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Prompt', sans-serif; }
        .portal-card { max-width: 400px; margin: 50px auto; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card portal-card border-0 p-4 text-center">
            <h4 class="text-danger mb-3">เกิดข้อผิดพลาด</h4>
            <p class="text-muted">{{ $message }}</p>
            <a href="javascript:history.back()" class="btn btn-light mt-3">กลับ</a>
        </div>
    </div>
</body>
</html>
