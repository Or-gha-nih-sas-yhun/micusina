<!DOCTYPE html>
<html>
<head>
    @include('admin.css')
    <style>
        .audit-frame { background: radial-gradient(circle at top left, rgba(255,255,255,.08), transparent 28%), #0c0d10; border: 1px solid #2b2f38; border-radius: 8px; color: #fff; margin: 0; min-height: calc(100vh - 120px); padding: 24px; }
        .audit-frame h2 { color: #fff; font-size: 26px; font-weight: 800; margin-bottom: 18px; }
        .audit-panel { background: #15171c; border: 1px solid #2b2f38; border-radius: 8px; overflow-x: auto; }
        .audit-table { border-collapse: collapse; min-width: 900px; width: 100%; }
        .audit-table th { background: #101116; color: #a6abb6; font-size: 12px; padding: 14px 16px; text-align: left; text-transform: uppercase; }
        .audit-table td { border-top: 1px solid #2b2f38; color: #f3f4f6; padding: 12px 16px; vertical-align: top; }
        .audit-table code { color: #bae6fd; white-space: pre-wrap; word-break: break-word; }
        .audit-filter { display: flex; gap: 8px; margin-bottom: 16px; max-width: 480px; }
        .audit-pagination { margin-top: 16px; }
        .audit-pagination nav div p, .audit-pagination nav span, .audit-pagination nav a { color: #fff !important; }
    </style>
</head>
<body>
    @include('admin.header')
    @include('admin.sidebar')
    <div class="page-content">
        <div class="page-header">
            <div class="container-fluid audit-frame">
                <h2>Audit Logs</h2>
                <form method="GET" action="{{ route('admin.audit-logs') }}" class="audit-filter">
                    <input class="form-control" name="event" value="{{ request('event') }}" placeholder="Filter by event">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </form>
                <div class="audit-panel">
                    <table class="audit-table">
                        <thead><tr><th>Time</th><th>Actor</th><th>Event</th><th>Record</th><th>Changes</th><th>IP</th></tr></thead>
                        <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $log->user?->name ?? 'System / deleted user' }}</td>
                                <td>{{ $log->event }}</td>
                                <td>{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '-' }}</td>
                                <td><code>{{ json_encode(['from' => $log->old_values, 'to' => $log->new_values], JSON_UNESCAPED_UNICODE) }}</code></td>
                                <td>{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No audit events found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="audit-pagination">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
    @include('admin.js')
</body>
</html>
