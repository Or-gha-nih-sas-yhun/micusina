<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Throwable;

class PayMongoWebhookController extends Controller
{
    public function complete(Request $request, Book $booking, PayMongoService $payMongo): RedirectResponse
    {
        $this->authorizeCallback($request);

        if ($booking->payment_status !== 'Paid' && $booking->paymongo_checkout_id) {
            try {
                $checkout = $payMongo->retrieveCheckout($booking->paymongo_checkout_id);
                $payment = $payMongo->paidPayment($checkout);
            } catch (Throwable $exception) {
                report($exception);

                return redirect('/?section=book')->withErrors([
                    'payment' => 'We could not verify the payment right now. Your reservation will update automatically once PayMongo confirms it.',
                ]);
            }

            if ($payment) {
                $this->markPaid($booking, $payment);
            }
        }

        if ($booking->fresh()->payment_status !== 'Paid') {
            return redirect('/?section=book')->withErrors([
                'payment' => 'Payment has not been confirmed by PayMongo yet. Please do not submit another payment.',
            ]);
        }

        return redirect('/?section=book')
            ->with('message', 'Payment confirmed. Your reservation was sent to admin/staff records.')
            ->with('booking_receipt', $this->receipt($booking->fresh()));
    }

    public function cancel(Request $request, Book $booking): RedirectResponse
    {
        $this->authorizeCallback($request);

        return redirect('/?section=book')->withErrors([
            'payment' => 'Payment was cancelled. Your booking has not been confirmed.',
        ]);
    }

    public function webhook(Request $request, PayMongoService $payMongo): JsonResponse
    {
        $payload = $request->getContent();

        if (! $payMongo->validWebhookSignature($payload, $request->header('Paymongo-Signature'))) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = json_decode($payload, true);
        $eventType = data_get($event, 'data.attributes.type');
        $resource = data_get($event, 'data.attributes.data');

        if (is_array($resource) && in_array($eventType, ['payment.paid', 'checkout_session.payment.paid'], true)) {
            $isCheckout = $eventType === 'checkout_session.payment.paid';
            $payment = $isCheckout ? $payMongo->paidPayment($resource) : $resource;
            $checkoutId = $isCheckout ? data_get($resource, 'id') : data_get($resource, 'attributes.checkout_session_id');
            $reference = data_get($resource, 'attributes.external_reference_number')
                ?? data_get($resource, 'attributes.reference_number');

            $booking = $checkoutId ? Book::where('paymongo_checkout_id', $checkoutId)->first() : null;
            if (! $booking && is_string($reference) && preg_match('/^BK-0*(\d+)$/', $reference, $matches)) {
                $booking = Book::find((int) $matches[1]);
            }

            if ($booking && $payment) {
                $this->markPaid($booking, $payment);
            }
        }

        return response()->json(['received' => true]);
    }

    private function authorizeCallback(Request $request): void
    {
        abort_unless(URL::hasValidSignature($request), 403, 'This payment return link is invalid or has expired.');
    }

    private function markPaid(Book $booking, array $payment): void
    {
        $status = data_get($payment, 'attributes.status');
        $amount = (int) data_get($payment, 'attributes.amount', 0);
        $expected = (int) round(((float) $booking->deposit_amount) * 100);

        if ($status !== 'paid' || $amount !== $expected || data_get($payment, 'attributes.currency') !== 'PHP') {
            return;
        }

        DB::transaction(function () use ($booking, $payment): void {
            $lockedBooking = Book::query()->lockForUpdate()->findOrFail($booking->id);

            if ($lockedBooking->payment_status === 'Paid') {
                return;
            }

            $lockedBooking->payment_status = 'Paid';
            $lockedBooking->paymongo_payment_id = data_get($payment, 'id');
            $lockedBooking->paid_at = now();

            if ($lockedBooking->status === 'Awaiting Payment') {
                $lockedBooking->status = 'Pending';
            }

            $lockedBooking->save();
        }, 3);
    }

    private function receipt(Book $booking): array
    {
        return [
            'reference' => $booking->gcash_reference,
            'name' => $booking->name,
            'guests' => (int) $booking->guest,
            'date' => $booking->date,
            'time' => $booking->time,
            'payment_method' => $booking->payment_method,
            'payment_reference' => $booking->paymongo_payment_id,
            'payment_status' => $booking->payment_status,
            'total' => (float) $booking->reservation_price,
            'deposit' => (float) $booking->deposit_amount,
            'balance' => (float) $booking->reservation_price - (float) $booking->deposit_amount,
        ];
    }
}
