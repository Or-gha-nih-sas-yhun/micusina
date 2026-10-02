<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use App\Services\PayMongoService;
use App\Services\RegistrationOtpSender;
use App\Support\MenuImages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class MobileApiController extends Controller
{
    public function login(Request $request, TwoFactorAuthenticationProvider $twoFactorProvider): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:80'],
            'two_factor_code' => ['nullable', 'string', 'regex:/^[0-9]{6}$/'],
        ]);
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'The email address or password is incorrect.'], 422);
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            if (empty($data['two_factor_code'])) {
                return response()->json([
                    'message' => 'Enter the authentication code from your authenticator app.',
                    'two_factor_required' => true,
                ], 202);
            }

            $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);

            if (! $twoFactorProvider->verify($secret, $data['two_factor_code'])) {
                throw ValidationException::withMessages([
                    'two_factor_code' => ['The authentication code is invalid or has expired.'],
                ]);
            }
        }

        $deviceName = trim((string) ($data['device_name'] ?? ''));
        $tokenName = $deviceName === '' ? 'mobile-app' : 'mobile-app: '.$deviceName;
        $abilities = in_array($user->usertype, ['admin', 'staff'], true) ? ['mobile:staff'] : ['mobile:customer'];
        $plainTextToken = DB::transaction(function () use ($user, $deviceName, $tokenName, $abilities): string {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($deviceName !== '') {
                $user->tokens()->where('name', $tokenName)->delete();
            }

            return $user->createToken($tokenName, $abilities, now()->addDays(30))->plainTextToken;
        }, 3);

        return response()->json([
            'token' => $plainTextToken,
            'user' => $this->user($user),
        ]);
    }

    /** Send the same email registration code used by the web registration flow. */
    public function sendRegistrationVerification(Request $request, RegistrationOtpSender $sender): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(09[0-9]{9}|\+639[0-9]{9})$/', 'unique:users,phone'],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:15', 'confirmed'],
        ]);

        $data['phone'] = $this->normalizePhilippinePhone($data['phone']);
        if (User::where('phone', $data['phone'])->exists()) {
            throw ValidationException::withMessages(['phone' => ['The mobile number has already been registered.']]);
        }

        $code = (string) random_int(100000, 999999);
        if (! $sender->sendEmail($data['email'], $code)) {
            return response()->json(['message' => 'The verification email could not be sent. Please try again later.'], 503);
        }

        $registrationId = (string) Str::uuid();
        Cache::put($this->registrationCacheKey($registrationId), [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'password' => Hash::make($data['password']),
            'email_code' => Hash::make($code),
        ], now()->addMinutes(10));

        return response()->json([
            'message' => 'We sent a verification code to your email.',
            'registration_id' => $registrationId,
            'expires_in' => 600,
        ], 202);
    }

    /** Verify a pending mobile registration and create a Sanctum-authenticated customer. */
    public function verifyRegistration(Request $request): JsonResponse
    {
        $data = $request->validate([
            'registration_id' => ['required', 'uuid'],
            'email_code' => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);
        $cacheKey = $this->registrationCacheKey($data['registration_id']);
        $pending = Cache::get($cacheKey);

        if (! $pending) {
            return response()->json(['message' => 'The verification code has expired. Please register again.'], 422);
        }
        if (! Hash::check($data['email_code'], $pending['email_code'])) {
            throw ValidationException::withMessages(['email_code' => ['The verification code is incorrect.']]);
        }
        if (User::where('email', $pending['email'])->orWhere('phone', $pending['phone'])->exists()) {
            Cache::forget($cacheKey);
            return response()->json(['message' => 'That email address or mobile number is already registered.'], 422);
        }

        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'address' => $pending['address'],
            'usertype' => 'user',
            'password' => $pending['password'],
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        Cache::forget($cacheKey);

        $deviceName = trim((string) ($data['device_name'] ?? '')) ?: 'Expo mobile app';
        $token = $user->createToken('mobile-app: '.$deviceName, ['mobile:customer'], now()->addDays(30))->plainTextToken;

        return response()->json(['token' => $token, 'user' => $this->user($user)], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->user($request->user())]);
    }

    public function foods(): JsonResponse
    {
        return response()->json(['foods' => Food::orderBy('title')->get()->map(fn (Food $food) => $this->food($food))]);
    }

    public function cart(Request $request): JsonResponse
    {
        return response()->json(['items' => Cart::where('userid', $request->user()->id)->latest()->get()->map(fn (Cart $cart) => $this->cartItem($cart))]);
    }

    public function addCart(Request $request, Food $food): JsonResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $userId = $request->user()->getKey();

        $cart = DB::transaction(function () use ($userId, $food, $data): Cart {
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $lockedFood = Food::query()->lockForUpdate()->findOrFail($food->id);
            $attributes = ['userid' => (string) $userId, 'food_id' => $lockedFood->id];
            $cart = Cart::query()->where($attributes)->lockForUpdate()->first();

            if (! $cart) {
                $cart = Cart::query()->createOrFirst($attributes, [
                    'title' => $lockedFood->title,
                    'details' => $lockedFood->detail,
                    'image' => $lockedFood->image,
                    'quantity' => 0,
                    'price' => 0,
                ]);

                if (! $cart->wasRecentlyCreated) {
                    $cart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
                }
            }

            $quantity = (int) $cart->quantity + $data['quantity'];
            abort_if($lockedFood->stock <= 0 || $quantity > $lockedFood->stock, 422, 'Only '.$lockedFood->stock.' item(s) are available.');
            $cart->fill([
                'title' => $lockedFood->title,
                'details' => $lockedFood->detail,
                'image' => $lockedFood->image,
                'quantity' => $quantity,
                'price' => round($this->price($lockedFood->price) * $quantity, 2),
            ])->save();

            return $cart->fresh();
        }, 3);

        return response()->json(['message' => 'Added to cart.', 'item' => $this->cartItem($cart)]);
    }

    public function updateCart(Request $request, Cart $cart): JsonResponse
    {
        abort_unless((int) $cart->userid === (int) $request->user()->getKey(), 403);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $food = Food::find($cart->food_id);
        abort_if($food && $data['quantity'] > $food->stock, 422, 'Not enough stock available.');
        $unit = $this->price($cart->price) / max(1, (int) $cart->quantity);
        $cart->update(['quantity' => $data['quantity'], 'price' => $unit * $data['quantity']]);

        return response()->json(['item' => $this->cartItem($cart->fresh())]);
    }

    public function removeCart(Request $request, Cart $cart): JsonResponse
    {
        abort_unless((int) $cart->userid === (int) $request->user()->getKey(), 403);
        $cart->delete();

        return response()->json(['message' => 'Removed from cart.']);
    }

    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'phone' => ['required', 'regex:/^(09[0-9]{9}|\+639[0-9]{9})$/'],
            'municipality' => ['required', 'in:Bantayan,Madridejos,Santa Fe'], 'barangay' => ['required', 'string', 'max:100'],
            'purok' => ['required', 'string', 'max:100'], 'address_details' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:Cash on Delivery,GCash'],
            'payment_reference' => ['required_unless:payment_method,Cash on Delivery', 'nullable', 'string', 'max:100'],
        ]);
        $user = $request->user();
        $addressDetails = $data['address_details'] ?? null;
        $address = 'Purok '.$data['purok'].', Barangay '.$data['barangay'].', '.$data['municipality'].', Bantayan Island, Cebu'.($addressDetails ? ' - '.$addressDetails : '');
        $checkoutGroupId = (string) Str::uuid();
        $orders = DB::transaction(function () use ($user, $data, $address, $checkoutGroupId) {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $items = Cart::where('userid', $user->id)
                ->orderBy('food_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            abort_if($items->isEmpty(), 422, 'Your cart is empty.');
            $created = collect();
            foreach ($items as $item) {
                $food = Food::lockForUpdate()->find($item->food_id);
                abort_if(! $food || $food->stock < $item->quantity, 422, $item->title.' is no longer available in this quantity.');
                $lineTotal = round($this->price($food->price) * (int) $item->quantity, 2);
                $orderData = ['name' => $data['name'], 'email' => $user->email, 'phone' => $data['phone'], 'address' => $address, 'title' => $item->title, 'quantity' => $item->quantity, 'price' => $lineTotal, 'image' => $item->image, 'delivery_status' => 'In Progress', 'payment_method' => $data['payment_method'], 'payment_status' => $data['payment_method'] === 'Cash on Delivery' ? 'Unpaid' : 'Pending Verification', 'payment_reference' => $data['payment_reference'] ?? null];

                if (Schema::hasColumn('orders', 'user_id')) {
                    $orderData['user_id'] = $user->id;
                }

                if (Schema::hasColumn('orders', 'checkout_group_id')) {
                    $orderData['checkout_group_id'] = $checkoutGroupId;
                }

                $created->push(Order::create($orderData));
                $food->decrement('stock', $item->quantity);
                $item->delete();
            }
            $user->update(['phone' => $data['phone'], 'address' => $address]);

            return $created;
        }, 3);

        return response()->json(['message' => 'Order placed.', 'orders' => $orders], 201);
    }

    public function orders(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = Order::query();

        if (Schema::hasColumn('orders', 'user_id')) {
            $orders->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('user_id')->where('email', $user->email);
                    });
            });
        } else {
            $orders->where('email', $user->email);
        }

        $orders = $orders->latest()->get();

        return response()->json(['orders' => $orders]);
    }

    public function cancelOrder(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id || (empty($order->user_id) && $order->email === $user->email), 404);
        $orders = $order->checkout_group_id
            ? Order::where('checkout_group_id', $order->checkout_group_id)->where('user_id', $user->id)->get()
            : Order::where('email', $user->email)->whereBetween('created_at', [$order->created_at->copy()->subSeconds(5), $order->created_at->copy()->addSeconds(5)])->get();
        $allowed = $orders->every(fn (Order $item) => in_array($item->delivery_status, ['In Progress', 'Preparing', 'Pending', 'Awaiting Confirmation', 'Awaiting Payment'], true)
            && strtolower((string) $item->payment_status) !== 'paid' && empty($item->rider_id));
        abort_unless($allowed, 422, 'This order can no longer be cancelled because it is already being prepared, paid, or dispatched.');
        Order::whereKey($orders->pluck('id'))->update(['delivery_status' => 'Canceled']);
        return response()->json(['message' => 'Order cancelled.']);
    }

    public function reservations(Request $request): JsonResponse
    {
        $reservations = Book::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function (Book $booking): array {
                $reservation = $booking->toArray();

                if ($booking->payment_status === 'Pending' && $booking->status === 'Awaiting Payment' && $booking->paymongo_checkout_url) {
                    $reservation['checkout_url'] = $booking->paymongo_checkout_url;
                }

                return $reservation;
            });

        return response()->json(['reservations' => $reservations]);
    }

    public function createReservation(Request $request, PayMongoService $payMongo): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'], 'last_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^(09[0-9]{9}|\+639[0-9]{9})$/'], 'guest' => ['required', 'integer', 'min:1', 'max:20'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'], 'time' => ['required', 'date_format:g:i A'],
            // GCash remains accepted only to let an installed older client reach the hosted PayMongo flow.
            'payment_method' => ['required', 'in:PayMongo,GCash'],
        ]);
        $reservationAt = Carbon::createFromFormat('Y-m-d g:i A', $data['date'].' '.$data['time'], 'Asia/Manila')->startOfMinute();
        abort_if($reservationAt->lte(Carbon::now('Asia/Manila')), 422, 'Choose a future reservation time.');
        $user = $request->user();
        $booking = Book::create([
            'user_id' => $user->id, 'first_name' => $data['first_name'], 'last_name' => $data['last_name'],
            'name' => trim($data['first_name'].' '.$data['last_name']), 'email' => $user->email, 'phone' => str_replace('+63', '0', $data['phone']),
            'guest' => $data['guest'], 'date' => $data['date'], 'time' => $data['time'], 'reservation_price' => 250,
            'deposit_amount' => 125, 'payment_method' => 'PayMongo', 'payment_status' => 'Pending', 'status' => 'Awaiting Payment',
        ]);
        $booking->update(['gcash_reference' => 'BK-'.str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT)]);
        try {
            $checkout = $payMongo->createCheckout($booking);
            $checkoutUrl = data_get($checkout, 'attributes.checkout_url');
            $booking->update([
                'paymongo_checkout_id' => data_get($checkout, 'id'),
                'paymongo_checkout_url' => $checkoutUrl,
            ]);

            return response()->json(['message' => 'Continue payment in your browser.', 'reservation' => $booking->fresh(), 'checkout_url' => $checkoutUrl], 201);
        } catch (\Throwable $exception) {
            report($exception);
            $booking->delete();

            return response()->json(['message' => 'Secure payment could not be started. Please try again.'], 422);
        }
    }

    public function cancelReservation(Request $request, Book $booking): JsonResponse
    {
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 404);
        abort_if(in_array($booking->status, ['Completed', 'Canceled'], true), 422, 'This reservation can no longer be cancelled.');
        try {
            abort_unless(Carbon::parse($booking->date.' '.$booking->time, 'Asia/Manila')->isFuture(), 422, 'This reservation can no longer be cancelled.');
        } catch (\Throwable) {
            abort(422, 'This reservation can no longer be cancelled.');
        }
        $booking->update(['status' => 'Canceled']);
        return response()->json(['message' => 'Reservation cancelled. Reservation downpayments are non-refundable.', 'reservation' => $booking->fresh()]);
    }

    public function staffDashboard(Request $request): JsonResponse
    {
        $this->staff($request->user());

        return response()->json(['pending_orders' => Order::where('delivery_status', 'In Progress')->count(), 'on_the_way_orders' => Order::where('delivery_status', 'On The Way')->count(), 'delivered_orders' => Order::where('delivery_status', 'Delivered')->count(), 'low_stock' => Food::where('stock', '<=', config('services.low_stock.threshold', 5))->count()]);
    }

    public function staffOrders(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->staff($user);

        $orders = Order::query()->latest();

        if ($user->staff_role === 'rider') {
            $orders->where('rider_id', $user->id);
        } else {
            abort_unless($user->usertype === 'admin' || $user->staff_role === 'cashier', 403, 'Order management access required.');
        }

        return response()->json(['orders' => $orders->limit(100)->get()]);
    }

    public function updateOrder(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        $this->staff($user);
        $data = $request->validate(['delivery_status' => ['required', 'in:Delivered,Canceled']]);
        $nextStatus = $data['delivery_status'];

        $updatedOrder = DB::transaction(function () use ($order, $user, $nextStatus): Order {
            if (Schema::hasColumn('orders', 'checkout_group_id') && $order->checkout_group_id) {
                $relatedOrders = Order::query()
                    ->where('checkout_group_id', $order->checkout_group_id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
                $lockedOrder = $relatedOrders->firstWhere('id', $order->id);
                abort_unless($lockedOrder, 409, 'The checkout group changed. Refresh and try again.');
            } else {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                $relatedOrders = collect([$lockedOrder]);
            }

            if ($user->staff_role === 'rider') {
                abort_unless($relatedOrders->every(fn (Order $related) => (int) $related->rider_id === (int) $user->id), 403, 'Every item in this checkout must be assigned to you.');
                abort_unless($nextStatus === 'Delivered' && $relatedOrders->every(fn (Order $related) => $related->delivery_status === 'On The Way'), 422, 'This delivery status change is not allowed.');
            } else {
                abort_unless($user->usertype === 'admin' || $user->staff_role === 'cashier', 403, 'Order management access required.');
                abort_if($relatedOrders->contains(fn (Order $related) => in_array($related->delivery_status, ['Delivered', 'Canceled'], true)), 422, 'This order is already final.');
                abort_if($nextStatus === 'Delivered' && ! $relatedOrders->every(fn (Order $related) => $related->delivery_status === 'On The Way'), 422, 'Assign a rider before marking this order delivered.');
                abort_if($nextStatus === 'Canceled' && $relatedOrders->contains(fn (Order $related) => $related->delivery_status === 'On The Way' || strtolower((string) $related->payment_status) === 'paid'), 422, 'A paid or dispatched order cannot be cancelled.');
            }

            Order::whereKey($relatedOrders->pluck('id')->all())->update(['delivery_status' => $nextStatus]);

            if (in_array($nextStatus, ['Delivered', 'Canceled'], true)) {
                foreach ($relatedOrders->pluck('rider_id')->filter()->unique() as $riderId) {
                    $rider = User::query()->whereKey($riderId)->lockForUpdate()->first();

                    if (! $rider) {
                        continue;
                    }

                    $hasActiveDelivery = Order::where('rider_id', $riderId)
                        ->where('delivery_status', 'On The Way')
                        ->exists();
                    $rider->update(['rider_available' => ! $hasActiveDelivery]);
                }
            }

            return $lockedOrder->fresh();
        }, 3);

        return response()->json(['order' => $updatedOrder]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $this->staff($request->user());

        return $this->foods();
    }

    public function updateStock(Request $request, Food $food): JsonResponse
    {
        $this->staff($request->user());
        abort_unless($request->user()->usertype === 'admin', 403, 'Only administrators can edit inventory.');
        $data = $request->validate(['stock' => ['required', 'integer', 'min:0']]);
        $food->update($data);

        return response()->json(['food' => $this->food($food->fresh())]);
    }

    private function staff(User $user): void
    {
        abort_unless(in_array($user->usertype, ['admin', 'staff'], true), 403, 'Staff access required.');
    }

    private function price($value): float
    {
        return (float) preg_replace('/[^0-9.]/', '', (string) $value);
    }

    private function food(Food $food): array
    {
        return ['id' => $food->id, 'title' => $food->title, 'detail' => $food->detail, 'category' => $food->category ?: 'All menu', 'price' => $this->price($food->price), 'stock' => (int) $food->stock, 'image' => $food->image, 'image_url' => MenuImages::url($food->title, $food->image)];
    }

    private function cartItem(Cart $cart): array
    {
        return $cart->toArray() + ['image_url' => MenuImages::url($cart->title, $cart->image)];
    }

    private function user(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone, 'address' => $user->address, 'usertype' => $user->usertype, 'staff_role' => $user->staff_role];
    }

    private function normalizePhilippinePhone(string $phone): string
    {
        return str_starts_with($phone, '09') ? '+63'.substr($phone, 1) : $phone;
    }

    private function registrationCacheKey(string $registrationId): string
    {
        return 'mobile-registration:'.$registrationId;
    }
}
