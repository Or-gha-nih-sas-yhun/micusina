<!DOCTYPE html>
<html lang="en">
<head>
    @include('home.css')
    <style>
        :root { --accent: #f88379; --ink: #1e293b; --line: #e2e8f0; }
        body { background:#f8fafc; color:var(--ink); font-family:Arial, Helvetica, sans-serif; }
        .checkout-page { margin:0 auto; max-width:1240px; padding:142px 24px 70px; }
        .checkout-title { border-bottom:3px solid var(--accent); font-family:Georgia, serif; font-size:46px; font-weight:400; margin:0 0 32px; padding-bottom:18px; }
        .checkout-layout { align-items:start; display:grid; gap:42px; grid-template-columns:minmax(0, 1fr) 410px; }
        .billing-card, .order-card { background:#fff; border:1px solid var(--line); border-radius:18px; box-shadow:0 12px 32px rgba(15,23,42,.06); padding:30px; }
        .billing-card h2, .order-card h2 { font-size:23px; margin:0 0 24px; }
        .checkout-form { display:grid; gap:18px; }
        .form-grid { display:grid; gap:18px; grid-template-columns:1fr 1fr; }
        label { display:block; font-size:14px; font-weight:800; margin-bottom:7px; }
        .required { color:#d64d4d; }
        input, select, textarea { background:#fff; border:1px solid #cbd5e1; border-radius:9px; box-sizing:border-box; font:inherit; min-height:48px; padding:11px 13px; width:100%; }
        input:focus, select:focus, textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(248,131,121,.16); outline:0; }
        textarea { min-height:88px; resize:vertical; }
        .notice { background:#fff7ed; border-left:4px solid var(--accent); color:#6b4b45; font-size:14px; margin-bottom:22px; padding:13px 15px; }
        .order-card { position:sticky; top:112px; }
        .order-head, .order-item, .total-line { align-items:start; display:flex; gap:18px; justify-content:space-between; }
        .order-head { border-bottom:1px solid var(--line); font-size:13px; font-weight:800; padding-bottom:13px; text-transform:uppercase; }
        .order-item { border-bottom:1px solid #edf2f7; padding:18px 0; }
        .order-item strong, .order-item span { display:block; }
        .order-item span { color:#64748b; font-size:14px; margin-top:4px; }
        .order-item > strong { white-space:nowrap; }
        .total-line { border-top:2px solid var(--ink); font-size:20px; font-weight:900; margin-top:14px; padding-top:18px; }
        .payment-box { background:#f8fafc; border:1px solid var(--line); border-radius:11px; margin-top:24px; padding:16px; }
        .payment-box h3 { font-size:15px; margin:0 0 7px; }
        .payment-box p { color:#64748b; font-size:14px; line-height:1.5; margin:0; }
        .payment-qr { display:none; margin-top:13px; max-width:170px; }
        .place-order { background:var(--accent); border:0; border-radius:10px; color:#fff; cursor:pointer; font-size:16px; font-weight:900; margin-top:24px; min-height:54px; width:100%; }
        .place-order:hover { background:#ed6d62; }
        .back-link { color:#475569; display:inline-block; font-size:14px; font-weight:800; margin-top:24px; text-decoration:none; }
        .back-link:hover { color:var(--accent); }
        @media(max-width:900px) { .checkout-layout { grid-template-columns:1fr; } .order-card { position:static; } }
        @media(max-width:560px) { .checkout-page { padding:112px 16px 42px; } .checkout-title { font-size:36px; } .billing-card,.order-card { padding:22px; } .form-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body class="content-page">
    @include('home.header', ['forceInnerNavbar' => true])
    @php($total = $data->sum(fn ($item) => (float) $item->price))
    <main class="checkout-page">
        <h1 class="checkout-title">Checkout</h1>
        <div class="notice">Please review your delivery details before placing your order. We will use these details to prepare and deliver your food.</div>
        <form action="{{ url('confirm_order') }}" method="post" class="checkout-layout">
            @csrf
            <section class="billing-card">
                <h2>Billing & delivery details</h2>
                <input type="hidden" name="email" value="{{ Auth::user()->email }}">
                <div class="checkout-form">
                    <div class="form-grid">
                        <div><label for="name">Full name <span class="required">*</span></label><input id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required></div>
                        <div><label for="phone">Phone <span class="required">*</span></label><input id="phone" type="tel" name="phone" value="{{ old('phone', Auth::user()->phone) }}" inputmode="tel" pattern="\+639[0-9]{9}" maxlength="13" placeholder="+639XXXXXXXXX" required></div>
                    </div>
                    <div class="form-grid">
                        <div><label for="municipality">Municipality <span class="required">*</span></label><select id="municipality" name="municipality" required><option value="">Select municipality</option><option value="Bantayan">Bantayan</option><option value="Madridejos">Madridejos</option><option value="Santa Fe">Santa Fe</option></select></div>
                        <div><label for="barangay">Barangay <span class="required">*</span></label><select id="barangay" name="barangay" required><option value="">Select barangay</option></select></div>
                    </div>
                    <div><label for="purok">Purok / street address <span class="required">*</span></label><input id="purok" name="purok" value="{{ old('purok') }}" placeholder="Purok, house number, or street" required></div>
                    <div><label for="address_details">Delivery notes <small>(optional)</small></label><textarea id="address_details" name="address_details" placeholder="House color, landmark, gate number, or delivery instructions">{{ old('address_details') }}</textarea></div>
                </div>
            </section>
            <aside class="order-card">
                <h2>Your order</h2>
                <div class="order-head"><span>Product</span><span>Subtotal</span></div>
                @foreach($data as $item)
                    <div class="order-item"><div><strong>{{ $item->title }}</strong><span>&#8369;{{ number_format((float) $item->price / max(1, $item->quantity), 2) }} × {{ $item->quantity }}</span></div><strong>&#8369;{{ number_format((float) $item->price, 2) }}</strong></div>
                @endforeach
                <div class="total-line"><span>Total</span><span>&#8369;{{ number_format($total, 2) }}</span></div>
                <div class="payment-box"><h3>Payment method</h3><select id="payment_method" name="payment_method" required><option value="Cash on Delivery">Cash on Delivery</option></select><p id="paymentCopy">Pay with cash when your order arrives.</p><img id="paymentQr" class="payment-qr" alt="Payment QR"></div>
                <button class="place-order" type="submit">Place order</button>
                <a class="back-link" href="{{ url('my_cart') }}">← Back to Cart</a>
            </aside>
        </form>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var places = { Bantayan:['Atop-atop','Baigad','Baod','Binaobao','Botigues','Doong','Guiwanon','Hilotongan','Kabac','Kabangbang','Kampingganon','Kangkaibe','Lipayran','Luyongbaybay','Mojon','Obo-ob','Patao','Putian','Sillon','Suba','Sulangan','Sungko','Tamiao','Ticad'], Madridejos:['Bunakan','Kangwayan','Kaongkod','Kodia','Maalat','Malbago','Mancilang','Pili','Poblacion','San Agustin','Tabagak','Talangnan','Tarong','Tugas'], 'Santa Fe':['Balidbid','Hagdan','Hilantagaan','Kinatarkan','Langub','Maricaban','Okoy','Poblacion','Pooc','Talisay'] };
            var municipality=document.getElementById('municipality'), barangay=document.getElementById('barangay'), payment=document.getElementById('payment_method'), qr=document.getElementById('paymentQr'), copy=document.getElementById('paymentCopy');
            municipality.addEventListener('change', function(){ barangay.innerHTML='<option value="">Select barangay</option>'; (places[municipality.value]||[]).forEach(function(place){ var option=new Option(place,place); barangay.add(option); }); });
            document.getElementById('phone').addEventListener('input', function(){ this.value='+639'+this.value.replace(/[^0-9]/g,'').replace(/^639/,'').slice(0,9); });
            payment.addEventListener('change', function(){ copy.textContent='Pay with cash when your order arrives.'; qr.style.display='none'; });
        });
    </script>
</body>
</html>
