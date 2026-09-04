@extends('layouts.admin')
@section('title', 'CF Manager Messenger Messages')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">CF Manager Messenger Messages</h4>
                <p class="text-muted mb-0">ดูข้อความจริงที่เข้าจากเพจ และตรวจว่า match กับ mapping หรือ order candidate แล้วหรือยัง</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url('/customers') }}" class="btn btn-outline-secondary">กลับหน้า Customers</a>
                <a href="{{ route('customers.messenger.messages') }}" class="btn btn-primary">รีเฟรช</a>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.messenger.messages') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">PSID</label>
                <input type="text" name="psid" class="form-control" value="{{ $psid }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Page ID</label>
                <input type="text" name="page_id" class="form-control" value="{{ $pageId }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Match</label>
                <select name="match" class="form-select">
                    <option value="" {{ $match === '' ? 'selected' : '' }}>ทั้งหมด</option>
                    <option value="mapped" {{ $match === 'mapped' ? 'selected' : '' }}>Mapped</option>
                    <option value="unmapped" {{ $match === 'unmapped' ? 'selected' : '' }}>Unmapped</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">กรอง</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Message</th>
                        <th>Page / PSID</th>
                        <th>Match</th>
                        <th>เวลา</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>#{{ $message->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($message->message_text, 80) }}</div>
                                <div class="text-muted small">{{ $message->direction }}</div>
                            </td>
                            <td>
                                <div class="small">page_id: <code>{{ $message->page_id ?: '-' }}</code></div>
                                <div class="small">psid: <code>{{ $message->psid }}</code></div>
                            </td>
                            <td>
                                @if($message->matched_mapping)
                                    <div class="badge bg-success-subtle text-success-emphasis">mapped</div>
                                    <div class="small mt-1">session #{{ $message->matched_mapping->portal_session_id }}</div>
                                    <div class="small text-muted">{{ $message->matched_mapping->tiktok_username ? '@'.$message->matched_mapping->tiktok_username : '-' }}</div>
                                @else
                                    <div class="badge bg-light text-dark">unmapped</div>
                                @endif
                            </td>
                            <td>{{ $message->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <a href="{{ route('customers.messenger.messages.show', $message->id) }}" class="btn btn-outline-primary btn-sm">รายละเอียด</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">ยังไม่มีข้อความ Messenger</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $messages->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
