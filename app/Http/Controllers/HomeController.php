<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use App\Models\Food;

use App\Models\Order;

use App\Models\Book;

use App\Models\Cart;

use App\Services\PayMongoService;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function update_profile_photo(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'photo.mimes' => 'The profile picture must be a .jpg, .jpeg, .png, or .webp file.',
            'photo.max' => 'The profile picture must not be larger than 5 MB.',
        ]);

        $request->user()->updateProfilePhoto($request->file('photo'));

        return redirect()->back()->with('message', 'Profile picture updated successfully.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('message', 'You have been logged out.');
    }



        public function my_home()
        {
            if(Auth::check() && in_array(Auth::user()->usertype, ['admin', 'staff']))
            {
                return redirect()->route('dashboard');
            }

            $data = Food::all();
            $soldCounts = $this->soldCounts();

            return view('home.index', compact('data', 'soldCounts'));
        }
    public function index()
    {
        if(Auth::id())

        {

            $usertype = Auth()->user()->usertype;

            if($usertype=='user')
            {
                return redirect('/');
            }

            else
            if($usertype=='admin')
            {
            $total_user = User::where('usertype','=','user')->count();

              $total_food = Food::count();

              $total_stock = Food::sum('stock');

              $lowStockThreshold = (int) config('services.low_stock.threshold', 5);

              $low_stock = Food::where('stock', '>', 0)->where('stock', '<=', $lowStockThreshold)->count();

              $out_of_stock = Food::where('stock', '<=', 0)->count();

              $inventory_alerts = Food::where('stock', '<=', $lowStockThreshold)
                  ->orderByRaw('CASE WHEN stock <= 0 THEN 0 ELSE 1 END')
                  ->orderBy('stock')
                  ->orderBy('title')
                  ->get();

              $total_order = Order::count();

              $total_delivered = Order::where('delivery_status','=','Delivered')->count();

              $today_orders = Order::whereDate('created_at', now()->toDateString())->count();

              $yesterday_sales = Order::whereDate('created_at', now()->subDay()->toDateString())->sum('price')
                  + Book::where('status', 'Approved')->whereDate('approved_at', now()->subDay()->toDateString())->sum('deposit_amount');

              $pending_orders = Order::where('delivery_status','=','In Progress')->count();

              $processing_orders = Order::where('delivery_status','=','On The Way')->count();

              $daily_sales = Order::whereDate('created_at', now()->toDateString())->sum('price')
                  + Book::where('status', 'Approved')->whereDate('approved_at', now()->toDateString())->sum('deposit_amount');

              $total_sales = Order::sum('price') + Book::where('status', 'Approved')->sum('deposit_amount');

              $monthly_sales = Order::whereYear('created_at', now()->year)
                  ->whereMonth('created_at', now()->month)
                  ->sum('price')
                  + Book::where('status', 'Approved')
                      ->whereYear('approved_at', now()->year)
                      ->whereMonth('approved_at', now()->month)
                      ->sum('deposit_amount');

              $weekly_sales = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('price');

              $last_month_sales = Order::whereYear('created_at', now()->subMonth()->year)
                  ->whereMonth('created_at', now()->subMonth()->month)
                  ->sum('price')
                  + Book::where('status', 'Approved')
                      ->whereYear('approved_at', now()->subMonth()->year)
                      ->whereMonth('approved_at', now()->subMonth()->month)
                      ->sum('deposit_amount');

              $ordered_foods = Order::selectRaw('title, SUM(quantity) as total_quantity, SUM(price) as total_sales')
                  ->groupBy('title')
                  ->orderByDesc('total_quantity')
                  ->get();

              $daily_sales_labels = [];
              $daily_sales_values = [];

              // Keep historical dashboards useful when there has been no recent activity.
              // Summary cards still represent the real current day/month, while charts end
              // on the most recent order or approved reservation date.
              $latestOrderDate = Order::max('created_at');
              $latestReservationDate = Book::where('status', 'Approved')->max('approved_at');
              $latestActivityDate = collect([$latestOrderDate, $latestReservationDate])
                  ->filter()
                  ->map(fn ($date) => \Carbon\Carbon::parse($date))
                  ->sortDesc()
                  ->first() ?? now();

              for($i = 6; $i >= 0; $i--)
              {
                  $date = $latestActivityDate->copy()->subDays($i);
                  $daily_sales_labels[] = $date->format('M d');
                  $daily_sales_values[] = Order::whereDate('created_at', $date->toDateString())->sum('price')
                      + Book::where('status', 'Approved')->whereDate('approved_at', $date->toDateString())->sum('deposit_amount');
              }

              $monthly_sales_labels = [];
              $monthly_sales_values = [];

              for($i = 5; $i >= 0; $i--)
              {
                  $month = now()->subMonths($i);
                  $monthly_sales_labels[] = $month->format('M Y');
                  $monthly_sales_values[] = Order::whereYear('created_at', $month->year)
                      ->whereMonth('created_at', $month->month)
                      ->sum('price')
                      + Book::where('status', 'Approved')
                          ->whereYear('approved_at', $month->year)
                          ->whereMonth('approved_at', $month->month)
                          ->sum('deposit_amount');
              }

              $weekly_sales_labels = [];
              $weekly_sales_values = [];

              for($i = 5; $i >= 0; $i--)
              {
                  $weekStart = $latestActivityDate->copy()->startOfWeek()->subWeeks($i);
                  $weekEnd = $weekStart->copy()->endOfWeek();
                  $weekly_sales_labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                  $weekly_sales_values[] = Order::whereBetween('created_at', [$weekStart, $weekEnd])->sum('price')
                      + Book::where('status', 'Approved')->whereBetween('approved_at', [$weekStart, $weekEnd])->sum('deposit_amount');
              }

              $best_selling_foods = Order::selectRaw('title, SUM(quantity) as total_quantity')
                  ->whereNotNull('title')
                  ->groupBy('title')
                  ->orderByDesc('total_quantity')
                  ->limit(5)
                  ->get();

              $best_selling_labels = $best_selling_foods->pluck('title');
              $best_selling_values = $best_selling_foods->pluck('total_quantity');
              $best_selling_items = $best_selling_foods->map(function ($food) {
                  return [
                      'title' => $food->title,
                      'sold' => (int) $food->total_quantity,
                  ];
              });

              $daily_sales_max = max((float) collect($daily_sales_values)->max(), 1);
              $weekly_sales_max = max((float) collect($weekly_sales_values)->max(), 1);
              $monthly_sales_max = max((float) collect($monthly_sales_values)->max(), 1);
              $best_selling_max = max((int) $best_selling_items->max('sold'), 1);

              $popular_foods = Food::orderByDesc('stock')->limit(4)->get();

              $recent_orders = Order::latest()->limit(4)->get();

              $reservation_sales_labels = [];
              $reservation_sales_values = [];

              for($i = 6; $i >= 0; $i--)
              {
                  $date = now()->subDays($i);
                  $reservation_sales_labels[] = $date->format('M d');
                  $reservation_sales_values[] = Book::where('status', 'Approved')
                      ->whereDate('approved_at', $date->toDateString())
                      ->sum('deposit_amount');
              }

              $riders = User::where('staff_role', 'rider')->orderBy('name')->get();
              $available_riders = $riders->where('rider_available', true)->count();
              $busy_riders = $riders->count() - $available_riders;

                return view('admin.index',compact('total_user','total_food','total_stock','low_stock','out_of_stock','lowStockThreshold','inventory_alerts','total_order','total_delivered','today_orders','yesterday_sales','pending_orders','processing_orders','daily_sales','weekly_sales','total_sales','monthly_sales','last_month_sales','ordered_foods','daily_sales_labels','daily_sales_values','daily_sales_max','weekly_sales_labels','weekly_sales_values','weekly_sales_max','monthly_sales_labels','monthly_sales_values','monthly_sales_max','best_selling_labels','best_selling_values','best_selling_items','best_selling_max','popular_foods','recent_orders','reservation_sales_labels','reservation_sales_values','riders','available_riders','busy_riders'));
            }
            else
            if($usertype=='staff')
            {
                $today_delivery_sales = Order::where('delivery_status','=','Delivered')
                    ->where('payment_status','=','Paid')
                    ->whereDate('created_at', now()->toDateString())
                    ->sum('price');

                $monthly_delivery_sales = Order::where('delivery_status','=','Delivered')
                    ->where('payment_status','=','Paid')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->sum('price');

                $total_delivery_sales = Order::where('delivery_status','=','Delivered')
                    ->where('payment_status','=','Paid')
                    ->sum('price');

                $pending_orders = Order::where('delivery_status','=','In Progress')->count();

                $on_the_way_orders = Order::where('delivery_status','=','On The Way')->count();

                $delivered_orders = Order::where('delivery_status','=','Delivered')->count();

                $canceled_orders = Order::where('delivery_status','=','Canceled')->count();

                $paid_orders = Order::where('payment_status','=','Paid')->count();

                $unpaid_orders = Order::where('payment_status','=','Unpaid')->count();

                $reservations_today = Book::where('date', now()->toDateString())->count();

                $total_reservations = Book::count();

                $recent_orders = Order::latest()->limit(8)->get();

                $upcoming_reservations = Book::orderByRaw("CASE WHEN status = 'Pending' THEN 0 ELSE 1 END")
                    ->orderBy('date')
                    ->orderBy('time')
                    ->limit(8)
                    ->get();

                $riders = User::where('staff_role', 'rider')->orderBy('name')->get();
                $available_riders = $riders->where('rider_available', true)->count();
                $busy_riders = $riders->count() - $available_riders;

                return view('staff.index', compact('today_delivery_sales','monthly_delivery_sales','total_delivery_sales','pending_orders','on_the_way_orders','delivered_orders','canceled_orders','paid_orders','unpaid_orders','reservations_today','total_reservations','recent_orders','upcoming_reservations','riders','available_riders','busy_riders'));
            }
        }
    }

    public function add_cart(Request $request, $id)
    {
        if(Auth::id())
        {
          $request->validate([
              'qty' => ['required', 'integer', 'min:1'],
          ]);

          $food = Food::findOrFail($id);
          $redirectToCart = $request->boolean('buy_now');

          if($food->stock <= 0)
          {
              return redirect()->back()->with('message', 'This food is out of stock.');
          }

          $requestedQty = (int) $request->qty;
          $existingCart = Cart::where('userid', Auth::id())
              ->where('food_id', $food->id)
              ->first();
          $existingQty = $existingCart ? (int) $existingCart->quantity : 0;

          if(($existingQty + $requestedQty) > $food->stock)
          {
              return redirect()->back()->with('message', 'Only ' . $food->stock . ' item(s) available in stock.');
          }

          $cart_title = $food->title;  
          
          $cart_details = $food->detail;

          $cart_price = Str::remove('₱', $food->price);

          $cart_image = $food->image;

          if($existingCart)
          {
              $unitPrice = $existingQty > 0 ? ((float) $existingCart->price / $existingQty) : (float) $cart_price;
              $existingCart->quantity = $existingQty + $requestedQty;
              $existingCart->price = $unitPrice * $existingCart->quantity;
              $existingCart->save();

              return ($redirectToCart ? redirect('my_cart') : redirect()->back())
                  ->with('message', 'Added ' . $requestedQty . ' ' . Str::plural('item', $requestedQty) . ' to your cart.');
          }

          $data = new Cart;

          $data->food_id = $food->id;

          $data->title = $cart_title;

          $data->details = $cart_details;

          $data->price = $cart_price * $requestedQty;

          $data->image = $cart_image;

           $data->quantity = $requestedQty;

           $data->userid = Auth()->user()->id;

           $data->save();

           return ($redirectToCart ? redirect('my_cart') : redirect()->back())
               ->with('message', 'Added ' . $requestedQty . ' ' . Str::plural('item', $requestedQty) . ' to your cart.');



        }
        else
        {
            return redirect('login');
        }

    }


    public function add_cart_ajax(Request $request, $id)
    {
        if(!Auth::id())
        {
            return response()->json(['message' => 'Please log in first.'], 401);
        }

        $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $food = Food::findOrFail($id);
        $requestedQty = (int) $request->qty;
        $existingCart = Cart::where('userid', Auth::id())
            ->where('food_id', $food->id)
            ->first();
        $existingQty = $existingCart ? (int) $existingCart->quantity : 0;

        if($food->stock <= 0)
        {
            return response()->json(['message' => 'This food is out of stock.'], 422);
        }

        if(($existingQty + $requestedQty) > $food->stock)
        {
            return response()->json(['message' => 'Only ' . $food->stock . ' item(s) available in stock.'], 422);
        }

        $unitPrice = (float) Str::remove('â‚±', $food->price);

        if($existingCart)
        {
            $existingCart->quantity = $existingQty + $requestedQty;
            $existingCart->price = $unitPrice * $existingCart->quantity;
            $existingCart->save();
        }
        else
        {
            Cart::create([
                'food_id' => $food->id,
                'title' => $food->title,
                'details' => $food->detail,
                'price' => $unitPrice * $requestedQty,
                'image' => $food->image,
                'quantity' => $requestedQty,
                'userid' => Auth::id(),
            ]);
        }

        return response()->json([
            'message' => 'Added ' . $requestedQty . ' ' . Str::plural('item', $requestedQty) . ' to your cart.',
            'cart_count' => Cart::where('userid', Auth::id())->sum('quantity'),
        ]);
    }


    public function my_cart()
    {

    $user_id = Auth()->user()->id;

    $data = Cart::where('userid', '=', $user_id)->get();

    return view('home.my_cart',compact('data'));

    
    }

    public function checkout()
    {
        $data = Cart::where('userid', Auth::id())->get();

        if ($data->isEmpty()) {
            return redirect('my_cart')->with('message', 'Your cart is empty. Add something from the menu first.');
        }

        return view('home.checkout', compact('data'));
    }

    public function update_cart(Request $request, $id)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::where('id', $id)
            ->where('userid', Auth::id())
            ->firstOrFail();

        $food = Food::find($cart->food_id) ?? Food::where('title', $cart->title)->first();
        $quantity = (int) $request->quantity;

        if($food && $quantity > (int) $food->stock)
        {
            return redirect()->back()->with('message', 'Only ' . $food->stock . ' item(s) available in stock.');
        }

        $oldQuantity = max(1, (int) $cart->quantity);
        $unitPrice = (float) $cart->price / $oldQuantity;

        $cart->quantity = $quantity;
        $cart->price = $unitPrice * $quantity;
        $cart->save();

        return redirect()->back()->with('message', 'Cart quantity updated.');
    }

    public function my_orders()
    {
        $orders = Order::where('email', Auth::user()->email)
            ->latest()
            ->get();

        $orderGroups = $orders->groupBy(function ($order) {
            return optional($order->created_at)->format('Y-m-d H:i:s') ?? $order->id;
        })->map(function ($group) {
            $firstOrder = $group->first();
            $statuses = $group->pluck('delivery_status');
            $status = 'In Progress';

            if($statuses->contains('Canceled'))
            {
                $status = 'Canceled';
            }
            elseif($statuses->every(fn ($item) => $item === 'Delivered'))
            {
                $status = 'Delivered';
            }
            elseif($statuses->contains('On The Way'))
            {
                $status = 'On The Way';
            }

                return (object) [
                    'id' => $firstOrder->id,
                    'order_number' => str_pad($firstOrder->id, 6, '0', STR_PAD_LEFT),
                    'created_at' => $firstOrder->created_at,
                    'delivery_status' => $status,
                    'title' => $firstOrder->title,
                    'image' => $firstOrder->image,
                    'item_count' => $group->sum(fn ($order) => (int) $order->quantity),
                    'total' => $group->sum(fn ($order) => (float) $order->price),
            ];
        })->values();

        return view('home.my_orders', compact('orderGroups'));
    }

    public function track_order($id)
    {
        $order = Order::where('id', $id)
            ->where('email', Auth::user()->email)
            ->firstOrFail();

        $relatedOrders = Order::where('email', Auth::user()->email)
            ->where('created_at', $order->created_at)
            ->orderBy('id')
            ->get();

        if($relatedOrders->count() <= 1)
        {
            $relatedOrders = Order::where('email', Auth::user()->email)
                ->whereBetween('created_at', [
                    $order->created_at->copy()->subSeconds(5),
                    $order->created_at->copy()->addSeconds(5),
                ])
                ->orderBy('id')
                ->get();
        }

        if($relatedOrders->isEmpty())
        {
            $relatedOrders = collect([$order]);
        }

        $statusOrder = ['Canceled' => 4, 'Delivered' => 3, 'On The Way' => 2, 'In Progress' => 1];
        $displayOrder = $relatedOrders->sortByDesc(fn ($item) => $statusOrder[$item->delivery_status] ?? 0)->first() ?? $order;
        $riderOrder = $relatedOrders->first(fn ($item) => !empty($item->rider_id));
        $assignedRiderId = $riderOrder ? $riderOrder->rider_id : $displayOrder->rider_id;
        $rider = Schema::hasColumn('orders', 'rider_id') && $assignedRiderId ? User::find($assignedRiderId) : null;
        $allOrders = Order::where('email', Auth::user()->email)
            ->latest()
            ->get()
            ->groupBy(function ($item) {
                return optional($item->created_at)->format('Y-m-d H:i:s') ?? $item->id;
            })->map(function ($group) {
                $firstOrder = $group->first();
                $statuses = $group->pluck('delivery_status');
                $status = 'In Progress';

                if($statuses->contains('Canceled'))
                {
                    $status = 'Canceled';
                }
                elseif($statuses->every(fn ($item) => $item === 'Delivered'))
                {
                    $status = 'Delivered';
                }
                elseif($statuses->contains('On The Way'))
                {
                    $status = 'On The Way';
                }

                return (object) [
                    'id' => $firstOrder->id,
                    'order_number' => str_pad($firstOrder->id, 6, '0', STR_PAD_LEFT),
                    'created_at' => $firstOrder->created_at,
                    'delivery_status' => $status,
                    'title' => $firstOrder->title,
                    'image' => $firstOrder->image,
                    'item_count' => $group->sum(fn ($item) => (int) $item->quantity),
                    'total' => $group->sum(fn ($item) => (float) $item->price),
                ];
            })->values();

        return view('home.track_order', ['order' => $displayOrder, 'relatedOrders' => $relatedOrders, 'rider' => $rider, 'allOrders' => $allOrders]);
    }

    /** A small read-only endpoint used by the tracking page polling. */
    public function order_status($id)
    {
        $order = $this->customerOrder($id);
        $orders = $this->relatedCustomerOrders($order);
        $status = $orders->contains('delivery_status', 'Canceled') ? 'Canceled'
            : ($orders->every(fn (Order $item) => $item->delivery_status === 'Delivered') ? 'Delivered'
            : ($orders->contains('delivery_status', 'On The Way') ? 'On The Way' : 'In Progress'));

        return response()->json(['status' => $status, 'updated_at' => optional($orders->max('updated_at'))->toIso8601String()]);
    }

    public function cancel_order($id)
    {
        $order = $this->customerOrder($id);
        $orders = $this->relatedCustomerOrders($order);
        $canCancel = $orders->every(fn (Order $item) => $this->orderCanBeCancelled($item));
        if (! $canCancel) {
            return back()->with('message', 'This order can no longer be cancelled because it is already being prepared, paid, or dispatched.');
        }

        Order::whereKey($orders->pluck('id'))->update(['delivery_status' => 'Canceled']);
        return redirect()->route('my_orders')->with('message', 'Your order has been cancelled.');
    }

    public function my_bookings()
    {
        $bookings = Book::where('user_id', Auth::id())->latest()->get();
        return view('home.my_bookings', compact('bookings'));
    }

    public function track_booking($id)
    {
        $booking = Book::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('home.track_booking', compact('booking'));
    }

    public function booking_status($id)
    {
        $booking = Book::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return response()->json(['status' => $booking->status, 'payment_status' => $booking->payment_status, 'updated_at' => optional($booking->updated_at)->toIso8601String()]);
    }

    public function cancel_booking($id)
    {
        $booking = Book::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        if (! $this->bookingCanBeCancelled($booking)) {
            return back()->with('message', 'This reservation can no longer be cancelled.');
        }
        $booking->update(['status' => 'Canceled']);
        return redirect()->route('bookings.index')->with('message', 'Your reservation has been cancelled. Reservation downpayments are non-refundable.');
    }

    public function order_receipt()
    {
        $orderIds = session('receipt_order_ids', []);

        if(empty($orderIds))
        {
            return redirect('my_orders')->with('message', 'Your latest orders are listed below.');
        }

        $orders = Order::whereIn('id', $orderIds)
            ->where('email', Auth::user()->email)
            ->orderBy('id')
            ->get();

        if($orders->isEmpty())
        {
            return redirect('my_orders')->with('message', 'Receipt not found for this account.');
        }

        return view('home.order_receipt', compact('orders'));
    }

    public function remove_cart($id)
    {
        Cart::where('id', $id)
            ->where('userid', Auth::id())
            ->firstOrFail()
            ->delete();

        return redirect()->back()->with('message', 'Item removed from cart.');
    }

    

    public function confirm_order(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'in:'.Auth::user()->email],
            'phone' => ['required', 'string', 'max:255'],
            'municipality' => ['required', 'in:Bantayan,Madridejos,Santa Fe'],
            'barangay' => ['required', 'string', 'max:100'],
            'purok' => ['required', 'string', 'max:100'],
            'address_details' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:Cash on Delivery,GCash'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $user_id = Auth()->user()->id;
        $deliveryAddress = 'Purok ' . $request->purok . ', Barangay ' . $request->barangay . ', ' . $request->municipality . ', Bantayan Island, Cebu';
        if($request->filled('address_details'))
        {
            $deliveryAddress .= ' - ' . $request->address_details;
        }

        Auth()->user()->forceFill([
            'phone' => $request->phone,
            'address' => $deliveryAddress,
        ])->save();

        $cartItems = Cart::where('userid', '=', $user_id)->get();

        if($cartItems->isEmpty())
        {
            return redirect()->back()->with('message', 'Your cart is empty.');
        }

        foreach($cartItems as $cart)
        {
            $food = Food::find($cart->food_id) ?? Food::where('title', $cart->title)->first();

            if(!$food || $food->stock < (int) $cart->quantity)
            {
                return redirect()->back()->with('message', $cart->title . ' does not have enough stock available.');
            }
        }

        $orderIds = [];
        $hasPaymentReferenceColumn = Schema::hasColumn('orders', 'payment_reference');
        $checkoutTime = now();

        foreach($cartItems as $cart)
       {
        $food = Food::find($cart->food_id) ?? Food::where('title', $cart->title)->first();

        $order = new Order;

        $order->name = $request->name;

        $order->email = $request->email;

        $order->phone = $request->phone;

        $order->address = $deliveryAddress;

        $order->title = $cart->title;

        $order->quantity = $cart->quantity;

        $order->price = $cart->price;

        $order->image = $cart->image;

        $order->delivery_status = 'In Progress';

        $order->payment_method = $request->payment_method;

        $order->payment_status = $request->payment_method === 'Cash on Delivery' ? 'Unpaid' : 'Pending Verification';

        if($hasPaymentReferenceColumn)
        {
            $order->payment_reference = $request->payment_reference;
        }

        $order->created_at = $checkoutTime;
        $order->updated_at = $checkoutTime;

        $order->save();

        $orderIds[] = $order->id;

        if($food)
        {
            $food->stock = max(0, $food->stock - (int) $cart->quantity);
            $food->save();
        }

        $data = Cart::find($cart->id);

        $data->delete();


        
        }

        $orders = Order::whereIn('id', $orderIds)
            ->where('email', Auth::user()->email)
            ->orderBy('id')
            ->get();

        $paymentReference = $request->payment_reference;

        return view('home.order_receipt', compact('orders', 'paymentReference'));



    }

    public function book_table(Request $request, PayMongoService $payMongo)
    {
        if(!Auth::check())
        {
            return redirect('login')->with('message', 'Please log in or register before booking a table.');
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^(09[0-9]{9}|\+639[0-9]{9})$/'],
            'n_guest' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            // GCash is accepted only for older app clients; all new reservations use PayMongo.
            'payment_method' => ['required', 'in:PayMongo,GCash'],
        ]);

        $guestCount = (int) $request->n_guest;
        $reservationPrice = 250;
        $depositAmount = $reservationPrice * 0.5;
        $fullName = trim($request->first_name . ' ' . $request->last_name);

        $data = new Book;

        $data->user_id = Auth::id();

        $data->first_name = $request->first_name;

        $data->last_name = $request->last_name;

        $data->name = $fullName;

        $data->email = $request->email;

        $data->phone = str_replace('+63', '0', $request->phone);

        $data->guest = $request->n_guest;

        $data->time = $request->time;

        $data->date = $request->date;

        $data->reservation_price = $reservationPrice;

        $data->deposit_amount = $depositAmount;

        $data->payment_method = 'PayMongo';

        $data->payment_status = 'Pending';

        $data->status = 'Awaiting Payment';

        $data->save();

        $data->gcash_reference = 'BK-' . str_pad((string) $data->id, 6, '0', STR_PAD_LEFT);
        $data->save();

        try {
            $checkout = $payMongo->createCheckout($data);
            $data->paymongo_checkout_id = data_get($checkout, 'id');
            $data->paymongo_checkout_url = data_get($checkout, 'attributes.checkout_url');
            $data->save();

            return redirect()->away(data_get($checkout, 'attributes.checkout_url'));
        } catch (\Throwable $exception) {
            report($exception);
            $data->delete();

            return redirect()->back()->withInput()->withErrors([
                'payment' => 'Secure payment could not be started. Please try again.',
            ]);
        }
    }

    private function customerOrder($id): Order
    {
        return Order::where('id', $id)->where(function ($query) {
            $query->where('user_id', Auth::id())->orWhere(function ($legacy) {
                $legacy->whereNull('user_id')->where('email', Auth::user()->email);
            });
        })->firstOrFail();
    }

    private function relatedCustomerOrders(Order $order)
    {
        if (Schema::hasColumn('orders', 'checkout_group_id') && $order->checkout_group_id) {
            return Order::where('checkout_group_id', $order->checkout_group_id)->where('user_id', Auth::id())->get();
        }
        return Order::where('email', Auth::user()->email)->whereBetween('created_at', [$order->created_at->copy()->subSeconds(5), $order->created_at->copy()->addSeconds(5)])->get();
    }

    private function orderCanBeCancelled(Order $order): bool
    {
        return in_array($order->delivery_status, ['In Progress', 'Pending', 'Awaiting Confirmation', 'Awaiting Payment'], true)
            && strtolower((string) $order->payment_status) !== 'paid'
            && empty($order->rider_id);
    }

    private function bookingCanBeCancelled(Book $booking): bool
    {
        if (in_array($booking->status, ['Completed', 'Canceled'], true)) return false;
        try { return \Carbon\Carbon::parse($booking->date.' '.$booking->time, 'Asia/Manila')->isFuture(); }
        catch (\Throwable) { return false; }
    }

    public function chatbot_message(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:300'],
        ]);

        $message = trim($request->message);
        $normalized = Str::of($message)->lower()->squish()->toString();

        return response()->json([
            'reply' => $this->buildChatbotReply($normalized),
        ]);
    }

    private function buildChatbotReply(string $message): string
    {
        if ($this->hasAny($message, ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'])) {
            return 'Hi! I can answer using Mi Cusina system data only. You can ask about foods, prices, booking, contact details, your cart, or your order status.';
        }

        if ($this->hasAny($message, ['my order', 'order status', 'track order', 'where is my order', 'delivery status', 'delivery', 'status', 'delivered', 'on the way', 'in progress', 'canceled'])) {
            return $this->orderReply();
        }

        if ($this->hasAny($message, ['contact', 'phone', 'call', 'email', 'location', 'address', 'find us', 'where are you', 'where located'])) {
            return "Mi Cusina contact details in the system:\nPhone: 09672045250\nEmail: Micusina@gmail.com\nAddress: Barangay Bunakan Madridejos Cebu In Front of Madridejos Community College";
        }

        if ($this->hasAny($message, ['book', 'booking', 'reservation', 'reserve', 'table'])) {
            return 'You can book a table from the Book-Table section on the front page. The system asks for name, 11-digit phone number, number of customers, reservation date, use time, and payment method. The system calculates the price and 50% deposit automatically.';
        }

        if ($this->hasAny($message, ['cart', 'basket'])) {
            return $this->cartReply();
        }

        if ($this->hasAny($message, ['how to order', 'add to cart', 'buy food', 'purchase'])) {
            return 'To order from the system, log in, choose a food from the Food section, select quantity, click Add to Cart, then confirm your order from the Cart page.';
        }

        if ($this->hasAny($message, ['open time', 'opening time', 'opening hours', 'business hours', 'store hours', 'open hours', 'what time open', 'what time close', 'close time', 'schedule'])) {
            return 'The system does not have a saved opening-hours setting yet. For the confirmed schedule, please contact Mi Cusina at 09672045250.';
        }

        if ($this->hasAny($message, ['foodlist', 'food list', 'all food', 'all foods', 'foods', 'menu', 'price list', 'prices', 'available foods'])) {
            return $this->foodListReply();
        }

        if ($this->hasAny($message, ['cheapest', 'lowest price', 'low price'])) {
            $food = Food::all()->sortBy(fn ($item) => (float) $item->price)->first();

            if (!$food) {
                return 'There are no foods saved in the system yet.';
            }

            return 'The cheapest food in the system is ' . $food->title . ' at ' . $this->formatPrice($food->price) . '.';
        }

        if ($this->hasAny($message, ['expensive', 'highest price', 'high price'])) {
            $food = Food::all()->sortByDesc(fn ($item) => (float) $item->price)->first();

            if (!$food) {
                return 'There are no foods saved in the system yet.';
            }

            return 'The highest priced food in the system is ' . $food->title . ' at ' . $this->formatPrice($food->price) . '.';
        }

        if ($this->hasAny($message, ['best seller', 'bestseller', 'best-selling', 'best selling', 'popular', 'most ordered', 'top food', 'top seller'])) {
            return $this->bestSellerReply();
        }

        $food = $this->findMatchingFood($message);

        if ($food) {
            $detail = $food->detail ? "\nDetails: {$food->detail}" : '';

            return "{$food->title} is in the system at {$this->formatPrice($food->price)}.{$detail}";
        }

        if ($this->hasAny($message, ['food', 'price', 'available', 'eat', 'meal'])) {
            return $this->foodListReply();
        }

        return 'I can only answer using information stored in this Mi Cusina system. I could not find that information here.';
    }

    private function foodListReply(): string
    {
        $foods = Food::orderBy('title')->get();

        if ($foods->isEmpty()) {
            return 'There are no foods saved in the system yet.';
        }

        $lines = $foods->map(function ($food) {
            return '- ' . $food->title . ': ' . $this->formatPrice($food->price);
        })->implode("\n");

        return "Foods currently in the system:\n" . $lines;
    }

    private function bestSellerReply(): string
    {
        $foods = Order::selectRaw('title, SUM(CAST(quantity AS INTEGER)) as total_quantity')
            ->whereNotNull('title')
            ->groupBy('title')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        if ($foods->isEmpty()) {
            return 'There are no completed order records in the system yet, so I cannot identify a best seller.';
        }

        $topFood = $foods->first();
        $lines = $foods->map(function ($food, $index) {
            return ($index + 1) . '. ' . $food->title . ' - ' . (int) $food->total_quantity . ' ordered';
        })->implode("\n");

        return "The current best seller in the system is {$topFood->title}.\n\nTop ordered foods:\n" . $lines;
    }

    private function cartReply(): string
    {
        if (!Auth::check()) {
            return 'Please log in first so I can check your cart in the system.';
        }

        $items = Cart::where('userid', Auth::id())->get();

        if ($items->isEmpty()) {
            return 'Your cart is empty in the system.';
        }

        $lines = $items->map(function ($item) {
            return '- ' . $item->title . ' x ' . $item->quantity . ': ' . $this->formatPrice($item->price);
        })->implode("\n");

        return "Your cart in the system:\n" . $lines;
    }

    private function orderReply(): string
    {
        if (!Auth::check()) {
            return 'Please log in first so I can check your orders in the system.';
        }

        $orders = Order::where('email', Auth::user()->email)->latest()->limit(5)->get();

        if ($orders->isEmpty()) {
            return 'I could not find any orders for your account email in the system.';
        }

        $lines = $orders->map(function ($order) {
            return '- ' . $order->title . ' x ' . $order->quantity . ': ' . $order->delivery_status;
        })->implode("\n");

        return "Your latest orders in the system:\n" . $lines;
    }

    private function findMatchingFood(string $message): ?Food
    {
        $foods = Food::all();
        $ignoredWords = ['about', 'available', 'best', 'close', 'food', 'foods', 'give', 'have', 'list', 'menu', 'open', 'order', 'price', 'show', 'tell', 'time', 'what', 'when'];
        $words = collect(preg_split('/\s+/', $message))
            ->map(fn ($word) => trim($word, " \t\n\r\0\x0B.,?!'\"()[]{}"))
            ->filter(fn ($word) => strlen($word) >= 3 && !in_array($word, $ignoredWords, true));

        return $foods->first(function ($food) use ($message, $words) {
            $title = Str::of($food->title)->lower()->toString();

            if (Str::contains($message, $title) || Str::contains($title, $message)) {
                return true;
            }

            return $words->contains(fn ($word) => Str::contains($title, $word));
        });
    }

    private function hasAny(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (Str::contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function formatPrice($price): string
    {
        $cleanPrice = (float) Str::of((string) $price)->replace('â‚±', '')->replace('₱', '')->trim()->toString();

        return '₱' . number_format($cleanPrice, 2);
    }

    private function soldCounts()
    {
        return Order::selectRaw('title, SUM(CAST(quantity AS INTEGER)) as total_quantity')
            ->whereNotNull('title')
            ->groupBy('title')
            ->pluck('total_quantity', 'title');
    }

} 
