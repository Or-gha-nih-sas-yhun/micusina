<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Audit
{
    /** Record a business action without storing credentials or payment secrets. */
    public static function record(string $event, ?Model $subject = null, array $old = [], array $new = []): void
    {
        $request = request();
        $filter = static fn (array $values): array => array_intersect_key($values, array_flip([
            'name', 'email', 'usertype', 'staff_role', 'rider_available', 'title', 'category', 'price', 'stock',
            'quantity', 'delivery_status', 'payment_status', 'payment_method', 'status', 'guest', 'date', 'time',
            'rider_id', 'user_id', 'phone', 'address', 'deposit_amount',
        ]));

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'old_values' => $filter($old),
            'new_values' => $filter($new),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
