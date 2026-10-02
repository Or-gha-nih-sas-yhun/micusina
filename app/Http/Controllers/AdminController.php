<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use App\Notifications\RiderAssignedToOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function update_profile_photo(Request $request)
    {
        abort_unless($request->user() && in_array($request->user()->usertype, ['admin', 'staff']), 403);

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'photo.mimes' => 'The profile picture must be a .jpg, .jpeg, .png, or .webp file.',
            'photo.max' => 'The profile picture must not be larger than 5 MB.',
        ]);

        $request->user()->updateProfilePhoto($request->file('photo'));

        return redirect()->back()->with('message', 'Profile picture updated successfully.');
    }

    public function add_food()
    {
        $this->requireAdmin();

        return view('admin.add_food');
    }

    public function upload_food(Request $request)
    {
        $this->requireAdmin();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string'],
            'category' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'img' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
        ], [
            'img.mimes' => 'The food image must be a .jpg, .jpeg, .png, or .webp file.',
            'img.max' => 'The food image must not be larger than 50 MB.',
        ]);

        $data = new Food;

        $data->title = $request->title;

        $data->detail = $request->details;

        $data->category = $request->category;

        $data->price = $request->price;

        $data->stock = $request->stock;

        $image = $request->img;

        $filename = Str::uuid().'.'.$image->guessExtension();

        $request->img->move('food_img', $filename);

        $data->image = $filename;

        $data->save();

        return redirect('view_food')->with('message', 'Food Added Successfully');

    }

    public function view_food()
    {
        $this->requireAdmin();

        $data = Food::orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.show_food', compact('data'));
    }

    public function inventory()
    {
        $this->requireAdmin();

        $data = Food::orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.inventory', compact('data'));
    }

    public function update_stock(Request $request, $id)
    {
        $this->requireAdmin();

        $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $food = Food::findOrFail($id);
        $food->stock = $request->stock;
        $food->save();

        return redirect()->back()->with('message', 'Stock Updated Successfully');
    }

    public function delete_food($id)
    {
        $this->requireAdmin();

        $data = Food::findOrFail($id);

        $data->delete();

        return redirect()->back()->with('message', 'Food Deleted Successfully');
    }

    public function update_food($id)
    {
        $this->requireAdmin();

        $food = Food::findOrFail($id);

        return view('admin.update_food', compact('food'));
    }

    public function edit_food(Request $request, $id)
    {
        $this->requireAdmin();

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string'],
            'category' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
        ], [
            'image.mimes' => 'The food image must be a .jpg, .jpeg, .png, or .webp file.',
            'image.max' => 'The food image must not be larger than 50 MB.',
        ]);

        $data = Food::findOrFail($id);

        $data->title = $request->title;

        $data->detail = $request->details;

        $data->category = $request->category;

        $data->price = $request->price;

        $data->stock = $request->stock;

        $image = $request->image;

        if ($image) {
            $imagename = Str::uuid().'.'.$image->guessExtension();

            $request->image->move('food_img', $imagename);

            $data->image = $imagename;

        }

        $data->save();

        return redirect('view_food');

    }

    public function orders()
    {
        $this->requireStaffOrAdmin();

        // The Orders page is the acknowledgement point for the sidebar badge.
        // Orders created after this timestamp will make the badge appear again.
        session(['admin_orders_seen_at' => now()->toDateTimeString()]);

        $orders = Order::query();
        if (auth()->user()->staff_role === 'rider') {
            $orders->where('rider_id', auth()->id());
        }

        $data = $orders->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $availableRiders = auth()->user()->staff_role === 'rider' ? collect() : User::where('staff_role', 'rider')
            ->where('rider_available', true)
            ->whereDoesntHave('riderOrders', function ($query) {
                $query->whereNotIn('delivery_status', ['Delivered', 'Canceled']);
            })
            ->orderBy('name')
            ->get();

        return view('admin.order', compact('data', 'availableRiders'));
    }

    public function order_updates()
    {
        $this->requireStaffOrAdmin();
        $latest = Order::query()
            ->when(auth()->user()->staff_role === 'rider', fn ($query) => $query->where('rider_id', auth()->id()))
            ->latest('updated_at')
            ->first();

        return response()->json([
            'latest' => $latest?->updated_at?->toIso8601String(),
            'pending' => Order::where('delivery_status', 'In Progress')->count(),
        ]);
    }

    public function markNotificationsRead(Request $request)
    {
        $this->requireAdmin();

        session(['admin_notifications_seen_at' => now()->toDateTimeString()]);

        return response()->noContent();
    }

    public function sales_report(Request $request)
    {
        $this->requireAdmin();

        return view('admin.sales.sales_report', $this->salesReportData($request));
    }

    public function sales_report_pdf(Request $request)
    {
        $this->requireAdmin();

        $data = $this->salesReportData($request);

        return Pdf::loadView('admin.sales.sales_report_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('sales-report-'.$data['from']->toDateString().'-to-'.$data['to']->toDateString().'.pdf');
    }

    private function salesReportData(Request $request): array
    {

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->subDays(29)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();

        $salesOrders = Order::query()
            ->where('payment_status', 'Paid')
            ->where('delivery_status', '!=', 'Canceled')
            ->whereBetween('created_at', [$from, $to]);

        $orderSales = (clone $salesOrders)->sum('price');
        $paidOrders = (clone $salesOrders)->count();
        $reservationSales = Book::where('status', 'Approved')
            ->whereBetween('approved_at', [$from, $to])
            ->sum('deposit_amount');
        $approvedReservations = Book::where('status', 'Approved')
            ->whereBetween('approved_at', [$from, $to])
            ->count();

        $topItems = (clone $salesOrders)
            ->selectRaw('title, SUM(quantity) as quantity_sold, SUM(price) as sales_total')
            ->whereNotNull('title')
            ->groupBy('title')
            ->orderByDesc('sales_total')
            ->limit(8)
            ->get();

        $recentSales = (clone $salesOrders)->latest()->limit(12)->get();

        $salesByDay = [];
        for ($date = $from->copy()->startOfDay(); $date->lte($to); $date->addDay()) {
            $salesByDay[] = [
                'label' => $date->format('M d'),
                'total' => Order::where('payment_status', 'Paid')
                    ->where('delivery_status', '!=', 'Canceled')
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('price')
                    + Book::where('status', 'Approved')
                        ->whereDate('approved_at', $date->toDateString())
                        ->sum('deposit_amount'),
            ];
        }

        return [
            'from' => $from,
            'to' => $to,
            'orderSales' => $orderSales,
            'reservationSales' => $reservationSales,
            'totalSales' => $orderSales + $reservationSales,
            'paidOrders' => $paidOrders,
            'approvedReservations' => $approvedReservations,
            'topItems' => $topItems,
            'recentSales' => $recentSales,
            'salesByDay' => $salesByDay,
        ];
    }

    public function transaction_history(Request $request)
    {
        $this->requireAdmin();

        return view('admin.sales.transaction_history', $this->transactionHistoryData($request));
    }

    public function transaction_history_pdf(Request $request)
    {
        $this->requireAdmin();

        $data = $this->transactionHistoryData($request);

        return Pdf::loadView('admin.sales.transaction_history_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('transaction-history-'.$data['from']->toDateString().'-to-'.$data['to']->toDateString().'.pdf');
    }

    private function transactionHistoryData(Request $request): array
    {

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->subDays(29)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();

        $orderTransactions = Order::whereBetween('created_at', [$from, $to])->get()->map(function ($order) {
            return (object) [
                'date' => $order->created_at,
                'type' => 'Food order',
                'customer' => $order->name ?: $order->email,
                'description' => $order->title.' x '.$order->quantity,
                'amount' => (float) $order->price,
                'method' => $order->payment_method ?: 'Cash on Delivery',
                'status' => $order->delivery_status === 'Canceled' ? 'Canceled' : ($order->payment_status ?: 'Unpaid'),
                'reference' => $order->payment_reference,
            ];
        });

        $reservationTransactions = Book::whereBetween('created_at', [$from, $to])->get()->map(function ($book) {
            return (object) [
                'date' => $book->created_at,
                'type' => 'Table reservation',
                'customer' => $book->name ?: trim(($book->first_name ?? '').' '.($book->last_name ?? '')),
                'description' => $book->guest.' guest(s) · '.$book->date.' '.$book->time,
                'amount' => (float) $book->deposit_amount,
                'method' => $book->payment_method,
                'status' => $book->status === 'Approved' ? 'Approved' : ($book->payment_status ?: $book->status),
                'reference' => $book->gcash_reference ?: $book->paymongo_payment_id,
            ];
        });

        $transactions = $orderTransactions->concat($reservationTransactions)->sortByDesc('date')->values();

        return compact('transactions', 'from', 'to');
    }

    public function assign_rider(Request $request, $id)
    {
        $this->requireOrderManager();

        $request->validate([
            'rider_id' => ['required', 'exists:users,id'],
        ]);

        $assignment = DB::transaction(function () use ($request, $id): array {
            $order = Order::query()->lockForUpdate()->findOrFail($id);
            $orders = $this->matchingOrderRows($order)->lockForUpdate()->get();
            abort_if($orders->contains(fn (Order $item) => $item->delivery_status !== 'In Progress'), 422, 'Only an unassigned order in progress can receive a rider.');
            abort_if($orders->contains(fn (Order $item) => $item->rider_id !== null), 422, 'A rider has already been assigned to this delivery.');

            $rider = User::query()->whereKey($request->rider_id)->where('staff_role', 'rider')->lockForUpdate()->firstOrFail();
            $activeDelivery = Order::query()->where('rider_id', $rider->id)
                ->whereNotIn('delivery_status', ['Delivered', 'Canceled'])->lockForUpdate()->exists();
            abort_if(! $rider->rider_available || $activeDelivery, 422, 'This rider is unavailable or already assigned to an active delivery.');
            Order::whereKey($orders->pluck('id'))->update(['rider_id' => $rider->id, 'confirmed_by' => auth()->id(), 'confirmed_at' => now(), 'delivery_status' => 'On The Way']);
            $rider->forceFill(['rider_available' => false])->save();

            return ['rider' => $rider, 'order' => $order->fresh()];
        }, 3);

        // Database notifications are retained for the rider's next dashboard/API refresh.
        $assignment['rider']->notify(new RiderAssignedToOrder($assignment['order']));

        // A form submitted without a reliable Referer used to redirect to an
        // invalid URL on some deployments. Always return to the Orders page.
        return redirect()->route('admin.orders')->with('message', $assignment['rider']->name.' assigned to this delivery.');
    }

    public function users()
    {
        $this->requireAdmin();

        $users = User::orderByDesc('created_at')->get();

        return view('admin.users', compact('users'));
    }

    public function riders()
    {
        $this->requireStaffOrAdmin();

        $riders = User::where('staff_role', 'rider')
            ->orderByDesc('rider_available')
            ->orderBy('name')
            ->get();

        $availableRiders = $riders->where('rider_available', true)->count();
        $unavailableRiders = $riders->count() - $availableRiders;

        return view('admin.riders', compact('riders', 'availableRiders', 'unavailableRiders'));
    }

    public function on_the_way($id)
    {
        $this->requireAdminOrCashier();

        DB::transaction(function () use ($id): void {
            $data = Order::query()->lockForUpdate()->findOrFail($id);
            $orders = $this->matchingOrderRows($data)->lockForUpdate()->get();
            abort_if($orders->contains(fn (Order $order) => in_array($order->delivery_status, ['Delivered', 'Canceled'], true)), 422, 'This order is already final.');
            Order::whereKey($orders->pluck('id'))->update(['delivery_status' => 'On The Way']);
        }, 3);

        return redirect()->back();
    }

    public function delivered($id)
    {
        $this->requireStaffOrAdmin();

        $riderId = DB::transaction(function () use ($id): ?int {
            $data = Order::query()->lockForUpdate()->findOrFail($id);
            $orders = $this->matchingOrderRows($data)->lockForUpdate()->get();
            $user = auth()->user();
            if ($user->staff_role === 'rider') {
                abort_unless($orders->every(fn (Order $order) => (int) $order->rider_id === (int) $user->id), 403, 'You can only complete deliveries assigned to you.');
            } else {
                $this->requireAdminOrCashier();
            }
            abort_if($orders->contains(fn (Order $order) => in_array($order->delivery_status, ['Delivered', 'Canceled'], true)), 422, 'This order is already final.');
            abort_unless($orders->every(fn (Order $order) => $order->delivery_status === 'On The Way'), 422, 'Only an order that is On The Way can be marked Delivered.');
            abort_unless($orders->every(fn (Order $order) => strtolower((string) $order->payment_status) === 'paid'), 422, 'Payment must be marked as paid before an order can be delivered.');
            Order::whereKey($orders->pluck('id'))->update(['delivery_status' => 'Delivered']);

            return $data->rider_id;
        }, 3);

        if ($riderId) {
            $this->refreshRiderAvailability($riderId);
        }

        return redirect()->back();
    }

    public function canceled($id)
    {
        $this->requireAdminOrCashier();

        $riderId = DB::transaction(function () use ($id): ?int {
            $data = Order::query()->lockForUpdate()->findOrFail($id);
            $orders = $this->matchingOrderRows($data)->lockForUpdate()->get();
            abort_if($orders->contains(fn (Order $order) => in_array($order->delivery_status, ['Delivered', 'Canceled', 'On The Way'], true)), 422, 'An order that is dispatched or final cannot be cancelled.');
            abort_if($orders->contains(fn (Order $order) => strtolower((string) $order->payment_status) === 'paid'), 422, 'A paid order cannot be cancelled.');
            Order::whereKey($orders->pluck('id'))->update(['delivery_status' => 'Canceled']);

            return $data->rider_id;
        }, 3);

        if ($riderId) {
            $this->refreshRiderAvailability($riderId);
        }

        return redirect()->back();
    }

    public function set_rider_availability(Request $request, $id)
    {
        $this->requireStaffOrAdmin();

        $request->validate([
            'rider_available' => ['required', 'boolean'],
        ]);

        if (auth()->user()->usertype !== 'admin' && auth()->id() !== (int) $id && auth()->user()->staff_role !== 'cashier') {
            abort(403);
        }

        $rider = User::where('id', $id)->where('staff_role', 'rider')->firstOrFail();
        if ($request->boolean('rider_available') && Order::where('rider_id', $rider->id)->whereNotIn('delivery_status', ['Delivered', 'Canceled'])->exists()) {
            return back()->with('message', 'A rider with an active delivery cannot be marked available.');
        }
        $rider->forceFill(['rider_available' => (bool) $request->rider_available])->save();

        return redirect()->back()->with('message', $rider->name.' availability updated.');
    }

    public function paid($id)
    {
        $this->requireAdminOrCashier();

        $data = Order::findOrFail($id);

        $data->payment_status = 'Paid';

        $data->save();

        return redirect()->back()->with('message', 'Payment marked as paid.');
    }

    public function reservations()
    {
        $this->requireStaffOrAdmin();

        $book = Book::orderByRaw("CASE WHEN status = 'Pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        $salesLabels = [];
        $depositValues = [];
        $bookingValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $salesLabels[] = $date->format('M d');
            $depositValues[] = Book::where('status', 'Approved')
                ->whereDate('approved_at', $date->toDateString())
                ->sum('deposit_amount');
            $bookingValues[] = Book::whereDate('created_at', $date->toDateString())->count();
        }

        return view('admin.reservation', compact('book', 'salesLabels', 'depositValues', 'bookingValues'));
    }

    public function approve_reservation($id)
    {
        $this->requireStaffOrAdmin();

        $book = Book::findOrFail($id);

        if (($book->payment_status ?? 'Pending') !== 'Paid') {
            return redirect()->back()->with('message', 'This reservation cannot be approved until payment is verified.');
        }

        $book->status = 'Approved';
        $book->approved_by = auth()->id();
        $book->approved_at = now();
        $book->save();

        return redirect()->back()->with('message', 'Reservation approved and added to sales.');
    }

    public function verify_reservation_payment($id)
    {
        $this->requireStaffOrAdmin();

        $book = Book::findOrFail($id);

        if (
            $book->payment_method !== 'GCash'
            || $book->payment_status !== 'Pending Verification'
            || ! $book->gcash_transaction_reference
        ) {
            return redirect()->back()->with('message', 'This GCash payment is not awaiting verification.');
        }

        $book->payment_status = 'Paid';
        $book->paid_at = now();
        $book->save();

        return redirect()->back()->with('message', 'GCash payment verified. The reservation can now be approved.');
    }

    public function add_staff()
    {
        $this->requireAdmin();

        return view('admin.add_staff');
    }

    public function store_staff(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\+639[0-9]{9}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'staff_role' => ['required', 'in:cashier,rider'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $staffData = [
            'name' => $request->name,
            'email' => $request->email,
            'usertype' => 'staff',
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
        ];

        if (Schema::hasColumn('users', 'staff_role')) {
            $staffData['staff_role'] = $request->staff_role;
        }

        User::create($staffData);

        return redirect()->back()->with('message', 'Staff Created Successfully');
    }

    private function requireAdmin()
    {
        if (! auth()->check() || auth()->user()->usertype !== 'admin') {
            abort(403);
        }
    }

    private function requireStaffOrAdmin()
    {
        if (! auth()->check() || ! in_array(auth()->user()->usertype, ['admin', 'staff'])) {
            abort(403);
        }
    }

    private function requireAdminOrCashier()
    {
        if (! auth()->check()) {
            abort(403);
        }

        if (auth()->user()->usertype === 'admin') {
            return;
        }

        if (auth()->user()->usertype === 'staff' && auth()->user()->staff_role === 'cashier') {
            return;
        }

        abort(403);
    }

    /** Allow office staff to dispatch deliveries, but never a delivery rider. */
    private function requireOrderManager()
    {
        if (! auth()->check() || auth()->user()->staff_role === 'rider') {
            abort(403);
        }

        if (in_array(auth()->user()->usertype, ['admin', 'staff'], true)) {
            return;
        }

        abort(403);
    }

    private function matchingOrderRows(Order $order)
    {
        return Order::where('email', $order->email)
            ->whereBetween('created_at', [
                $order->created_at->copy()->subSeconds(5),
                $order->created_at->copy()->addSeconds(5),
            ]);
    }

    private function refreshRiderAvailability(int $riderId): void
    {
        $hasActiveDelivery = Order::where('rider_id', $riderId)->whereNotIn('delivery_status', ['Delivered', 'Canceled'])->exists();
        User::whereKey($riderId)->update(['rider_available' => ! $hasActiveDelivery]);
    }
}
