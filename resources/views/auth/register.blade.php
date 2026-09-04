<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สมัครสมาชิก | CF Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center}
.card{border:0;border-radius:1rem;box-shadow:0 0.5rem 1rem rgba(0,0,0,.15)}.btn-primary{background:#667eea;border:0;width:100%}
.logo{font-size:1.5rem;font-weight:700;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
</style></head>
<body><div class="container"><div class="row justify-content-center"><div class="col-md-5">
<div class="card"><div class="card-body p-5">
<div class="text-center mb-4"><div class="logo">CF Manager</div><p class="text-muted">สร้างบัญชีผู้ดูแลระบบฟรี</p></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ url('/register') }}">
@csrf
<div class="mb-3"><label class="form-label">ชื่อ</label><input type="text" name="name" class="form-control form-control-lg" required></div>
<div class="mb-3"><label class="form-label">อีเมล</label><input type="email" name="email" class="form-control form-control-lg" required></div>
<div class="mb-3"><label class="form-label">รหัสผ่าน</label><input type="password" name="password" class="form-control form-control-lg" minlength="6" required></div>
<div class="mb-3"><label class="form-label">ยืนยันรหัสผ่าน</label><input type="password" name="password_confirmation" class="form-control form-control-lg" required></div>
<button type="submit" class="btn btn-primary btn-lg">สมัครสมาชิก</button>
</form>
<div class="text-center mt-3 text-muted">มีบัญชีแล้ว? <a href="{{ url('/login') }}">เข้าสู่ระบบ</a></div>
</div></div></div></div></div></body></html>
