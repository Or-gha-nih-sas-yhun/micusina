@extends('admin.body')

@section('admin_content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-3">Audit Logs</h1>
    <form method="GET" action="{{ route('admin.audit-logs') }}" class="mb-3">
        <div class="input-group" style="max-width: 480px">
            <input class="form-control" name="event" value="{{ request('event') }}" placeholder="Filter by event">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead><tr><th>Time</th><th>Actor</th><th>Event</th><th>Record</th><th>Changes</th><th>IP</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user?->name ?? 'System / deleted user' }}</td>
                    <td>{{ $log->event }}</td>
                    <td>{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td>
                    <td><code>{{ json_encode(['from' => $log->old_values, 'to' => $log->new_values], JSON_UNESCAPED_UNICODE) }}</code></td>
                    <td>{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No audit events found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection
