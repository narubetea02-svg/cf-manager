<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ | CF Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center}
.card{border:0;border-radius:1rem;box-shadow:0 0.5rem 1rem rgba(0,0,0,.15)}.btn-primary{background:#667eea;border:0;width:100%}
.btn-facebook{background:#1877f2;color:#fff;width:100%}.btn-facebook:hover{background:#166fe5;color:#fff}
.logo{font-size:1.5rem;font-weight:700;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
</style></head>
<body><div class="container"><div class="row justify-content-center"><div class="col-md-5">
<div class="card"><div class="card-body p-5">
<div class="text-center mb-4"><div class="logo">CF Manager</div><p class="text-muted">ระบบจัดการไลฟ์ แชท และออเดอร์</p></div>
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<form method="POST" action="{{ url('/login') }}">
@csrf
<div class="mb-3"><label class="form-label">อีเมล</label><input type="email" name="email" class="form-control form-control-lg" required></div>
<div class="mb-3"><label class="form-label">รหัสผ่าน</label><input type="password" name="password" class="form-control form-control-lg" required></div>
<div class="mb-3 form-check"><input type="checkbox" name="remember" class="form-check-input" id="remember"><label class="form-check-label" for="remember">จดจำฉัน</label></div>
<button type="submit" class="btn btn-primary btn-lg">เข้าสู่ระบบ</button>
</form>
<hr class="my-4">
<a href="{{ url('/auth/facebook') }}" class="btn btn-facebook btn-lg mb-3"><x-ui-icon name="facebook" class="me-2" size="18" />เข้าสู่ระบบด้วย Facebook</a>
<div class="text-center text-muted">ยังไม่มีบัญชี? <a href="{{ url('/register') }}">สมัครสมาชิก</a></div>
</div></div></div></div></div></body></html>
