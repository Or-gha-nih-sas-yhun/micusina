<section class="reserve-page" id="book-table">
    <style>
        .reserve-page {
            background: #000;
            color: #2f3344;
            padding: 44px 16px 70px;
        }

        .reserve-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            margin: 0 auto;
            max-width: 920px;
        }

        .reserve-brand {
            align-items: center;
            border-bottom: 1px solid rgba(47,51,68,.18);
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 56px 32px 44px;
            text-align: center;
        }

        .reserve-logo {
            align-items: center;
            color: #000;
            display: flex;
            flex-direction: column;
            font-family: Georgia, serif;
            font-size: 34px;
            font-weight: 900;
            gap: 12px;
            line-height: 1;
        }

        .reserve-logo img {
            display: block;
            height: 92px;
            object-fit: contain;
            width: 92px;
        }

        .reserve-logo small {
            display: block;
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-top: 6px;
            text-transform: uppercase;
        }

        .reserve-brand h2 {
            color: #2f3344;
            font-size: 38px;
            font-weight: 900;
            margin: 0;
        }

        .reserve-form {
            padding: 46px 64px 50px;
        }

        .reserve-row {
            display: grid;
            gap: 18px;
            grid-template-columns: 210px 1fr;
            margin-bottom: 34px;
        }

        .reserve-row label {
            color: #2f3344;
            font-size: 20px;
            font-weight: 800;
            padding-top: 10px;
        }

        .reserve-fields {
            display: grid;
            gap: 12px;
        }

        .reserve-two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .reserve-field input,
        .reserve-field select {
            background: #fff;
            border: 1px solid #cfd2d8;
            border-radius: 4px;
            color: #2f3344;
            font-size: 18px;
            min-height: 50px;
            padding: 9px 12px;
            width: 100%;
        }

        .reserve-field span {
            color: #8f96a5;
            display: block;
            font-size: 14px;
            font-weight: 800;
            margin-top: 10px;
        }

        .reserve-line {
            background: #4c4c4c;
            height: 2px;
            margin: 38px auto;
            max-width: 620px;
        }

        .reserve-payment {
            border: 1px solid rgba(47,51,68,.18);
            border-radius: 8px;
            display: none;
            padding: 18px;
        }

        .reserve-payment.is-visible {
            display: block;
        }

        .reserve-payment img {
            background: #fff;
            border-radius: 6px;
            display: none;
            margin-top: 12px;
            max-width: 180px;
            padding: 8px;
            width: 100%;
        }

        .reserve-summary {
            background: rgba(255,255,255,.34);
            border-radius: 8px;
            color: #2f3344;
            font-weight: 800;
            padding: 16px 18px;
        }

        .reserve-actions {
            margin-top: 34px;
            text-align: center;
        }

        .reserve-actions button,
        .reserve-actions a {
            background: #fff;
            border: 0;
            border-radius: 5px;
            color: #F88379;
            cursor: pointer;
            display: inline-flex;
            font-size: 18px;
            font-weight: 900;
            justify-content: center;
            min-width: 170px;
            padding: 13px 22px;
            text-decoration: none;
        }

        .reserve-actions button {
            border: 2px solid #111;
        }

        .booking-payment-modal {
            align-items: center;
            background: rgba(0, 0, 0, .78);
            display: none;
            inset: 0;
            justify-content: center;
            padding: 20px;
            position: fixed;
            z-index: 4000;
        }

        .booking-payment-modal.is-visible { display: flex; }

        .booking-payment-dialog {
            background: #111;
            border: 1px solid #34343d;
            border-radius: 14px;
            color: #fff;
            max-height: calc(100vh - 40px);
            max-width: 440px;
            overflow-y: auto;
            padding: 26px;
            width: 100%;
        }

        .booking-payment-dialog h3 { color: #fff; margin: 0 0 8px; }
        .booking-payment-dialog p { color: #aaa; margin-bottom: 18px; }

        .booking-payment-options { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; }

        .booking-payment-option {
            background: #1b1b23;
            border: 1px solid #3a3a45;
            border-radius: 9px;
            color: #fff;
            cursor: pointer;
            font-weight: 800;
            padding: 13px;
            text-align: center;
        }

        .booking-payment-option.is-selected { border-color: #F88379; color: #FFA69E; }

        .booking-payment-qr { display: none; margin: 18px auto 0; text-align: center; }
        .booking-payment-qr.is-visible { display: block; }
        .booking-payment-qr img { background: #fff; border-radius: 8px; max-width: 190px; padding: 8px; width: 100%; }
        .booking-bank-open {
            background: #159447;
            border-radius: 999px;
            color: #fff;
            display: block;
            font-size: 18px;
            font-weight: 800;
            margin: 16px auto;
            max-width: 290px;
            padding: 13px 20px;
            text-decoration: none;
        }
        .booking-bank-open:hover { background: #0d7c39; color: #fff; text-decoration: none; }
        .booking-bank-launch-status { color: #aaa; display: none; font-size: 14px; margin: 10px auto 14px; max-width: 330px; }
        .booking-bank-launch-status.is-visible { display: block; }

        .booking-gcash-payment { display: none; }

        .booking-payment-dialog.is-gcash {
            background: #e9eef3;
            border: 0;
            max-width: 520px;
            padding: 0 0 24px;
        }

        .booking-payment-dialog.is-gcash > h3,
        .booking-payment-dialog.is-gcash > p,
        .booking-payment-dialog.is-gcash .booking-payment-options,
        .booking-payment-dialog.is-gcash .booking-payment-qr { display: none; }

        .booking-payment-dialog.is-gcash .booking-gcash-payment { display: block; }

        .booking-gcash-header {
            background: #075fda;
            color: #fff;
            font-size: 31px;
            font-weight: 900;
            letter-spacing: -.5px;
            padding: 32px 24px 66px;
            text-align: center;
        }

        .booking-gcash-logo-mark {
            border: 4px solid #fff;
            border-radius: 50%;
            display: inline-flex;
            font-size: 22px;
            height: 42px;
            justify-content: center;
            margin-right: 9px;
            width: 42px;
        }

        .booking-gcash-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(34, 54, 75, .12);
            color: #213b62;
            margin: -38px 28px 0;
            padding: 30px 28px 34px;
            text-align: center;
        }

        .booking-gcash-card h4 {
            color: #213b62;
            font-size: 22px;
            font-weight: 900;
            line-height: 1.25;
            margin: 0 0 24px;
        }

        .booking-gcash-open {
            align-items: center;
            background: #0868e8;
            border-radius: 999px;
            color: #fff;
            display: flex;
            font-size: 21px;
            justify-content: center;
            margin-bottom: 30px;
            padding: 14px 20px;
            text-decoration: none;
        }

        .booking-gcash-open:hover { background: #0057c8; color: #fff; text-decoration: none; }
        .booking-gcash-launch-status {
            color: #64748b;
            display: none;
            font-size: 14px;
            line-height: 1.4;
            margin: -18px 0 24px;
        }
        .booking-gcash-launch-status.is-visible { display: block; }
        .booking-gcash-instruction { font-size: 16px; font-weight: 800; line-height: 1.45; margin: 0 auto 18px; max-width: 330px; }
        .booking-gcash-card img { display: block; margin: 0 auto; max-width: 260px; width: 100%; }

        .booking-payment-dialog.is-gcash .booking-payment-reference,
        .booking-payment-dialog.is-gcash .booking-payment-summary,
        .booking-payment-dialog.is-gcash .booking-payment-actions { margin-left: 28px; margin-right: 28px; }

        .booking-payment-dialog.is-gcash .booking-payment-reference label { color: #213b62; }
        .booking-payment-dialog.is-gcash .booking-payment-reference input { background: #fff; border-color: #cbd5e1; color: #1f2937; }
        .booking-payment-dialog.is-gcash .booking-payment-summary { background: #fff; border-color: #d8e0e8; color: #213b62; }
        .booking-payment-dialog.is-gcash .booking-payment-summary strong { color: #075fda; }
        .booking-payment-dialog.is-gcash .booking-payment-confirm { background: #0868e8; }

        .booking-payment-reference { display: none; margin-top: 16px; text-align: left; }
        .booking-payment-reference.is-visible { display: block; }
        .booking-payment-reference label { color: #fff; display: block; font-weight: 700; margin-bottom: 7px; }
        .booking-payment-reference input {
            background: #0b0b10;
            border: 1px solid #3a3a45;
            border-radius: 7px;
            color: #fff;
            font-size: 16px;
            padding: 11px 12px;
            width: 100%;
        }
        .booking-payment-reference input:focus { border-color: #F88379; outline: none; }

        .booking-payment-summary {
            background: #1b1b23;
            border: 1px solid #34343d;
            border-radius: 9px;
            margin-top: 18px;
            padding: 14px;
        }

        .booking-payment-summary div { display: flex; justify-content: space-between; margin: 6px 0; }
        .booking-payment-summary strong { color: #FFA69E; }

        .booking-receipt-dialog { max-width: 480px; }
        .booking-receipt-number { color: #FFA69E; font-weight: 900; margin-bottom: 14px; }
        .booking-receipt-row { border-bottom: 1px solid #2d2d36; display: flex; gap: 16px; justify-content: space-between; padding: 9px 0; }
        .booking-receipt-row span { color: #aaa; }
        .booking-receipt-row strong { color: #fff; text-align: right; }
        .booking-receipt-paid { color: #FFA69E !important; font-size: 20px; }

        .booking-payment-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
        .booking-payment-actions button { border: 0; border-radius: 7px; cursor: pointer; font-weight: 800; padding: 11px 16px; }
        .booking-payment-cancel { background: #282832; color: #fff; }
        .booking-payment-confirm { background: #F88379; color: #fff; }
        .booking-payment-confirm:disabled { cursor: not-allowed; opacity: .45; }

        .reserve-actions a {
            background: #F88379;
            color: #fff;
            margin-left: 10px;
        }

        @media (max-width: 767.98px) {
            .reserve-brand {
                padding: 38px 18px 32px;
            }

            .reserve-brand h2 {
                font-size: 30px;
            }

            .reserve-form {
                padding: 32px 20px 38px;
            }

            .reserve-row,
            .reserve-two {
                grid-template-columns: 1fr;
            }

            .reserve-row label {
                padding-top: 0;
            }

            .booking-payment-modal { padding: 10px; }
            .booking-payment-dialog { max-height: calc(100vh - 20px); }
            .booking-gcash-header { font-size: 27px; padding-top: 26px; }
            .booking-gcash-card { margin-left: 14px; margin-right: 14px; padding: 26px 18px; }
            .booking-payment-dialog.is-gcash .booking-payment-reference,
            .booking-payment-dialog.is-gcash .booking-payment-summary,
            .booking-payment-dialog.is-gcash .booking-payment-actions { margin-left: 14px; margin-right: 14px; }
        }
        /* Match the Mi Cusina homepage typography. */
        .reserve-page,
        .reserve-page input,
        .reserve-page select,
        .reserve-page textarea,
        .reserve-page button {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .reserve-logo {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-size: 31px !important;
            font-style: italic;
            font-weight: 700 !important;
        }

        .reserve-brand h2 {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-size: clamp(42px, 3.5vw, 62px) !important;
            font-weight: 400 !important;
            letter-spacing: -.035em !important;
            line-height: 1.05 !important;
            text-align: center;
        }

        .reserve-row label,
        .reserve-field input,
        .reserve-field select,
        .reserve-field span,
        .reserve-page p {
            font-size: 17px !important;
            font-weight: 400 !important;
            line-height: 1.6 !important;
        }

        .reserve-page button { font-size: 14px !important; font-weight: 700 !important; }
    </style>

    <div class="reserve-card">
        <div class="reserve-brand">
            <div class="reserve-logo">
                <img src="{{ asset('assets/imgs/mi-cusina-transparent.png') }}" alt="Mi Cusina logo">
                <span>Mi Cusina</span>
                <small>Restaurant</small>
            </div>
            <h2>Reserve Your Table</h2>
        </div>

        @guest
            <div class="reserve-form text-center">
                <p>Please log in or register before booking a table.</p>
                <div class="reserve-actions">
                    <a href="{{ url('login') }}">Login</a>
                    <a href="{{ url('register') }}">Register</a>
                </div>
            </div>
        @else
            @if(session()->has('message'))
                <div class="alert alert-success m-4">{{ session()->get('message') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger m-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form id="bookTableForm" class="reserve-form" action="{{ url('book_table') }}" method="POST" target="_top">
                @csrf
                <input id="bookingPaymentMethod" type="hidden" name="payment_method" value="">
                <input id="bookingPaymentReference" type="hidden" name="payment_reference" value="">

                <div class="reserve-row">
                    <label>Name</label>
                    <div class="reserve-fields reserve-two">
                        <div class="reserve-field">
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required>
                            <span>First Name</span>
                        </div>
                        <div class="reserve-field">
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required>
                            <span>Last Name</span>
                        </div>
                    </div>
                </div>

                <div class="reserve-row">
                    <label for="book_email">Email</label>
                    <div class="reserve-field">
                        <input id="book_email" type="email" name="email" value="{{ old('email') }}" required>
                        <span>example@example.com</span>
                    </div>
                </div>

                <div class="reserve-row">
                    <label for="book_phone">Phone Number</label>
                    <div class="reserve-field">
                        <input id="book_phone" type="tel" name="phone" value="{{ old('phone') }}" pattern="(09[0-9]{9}|\+639[0-9]{9})" maxlength="13" inputmode="tel" required>
                        <span>Please enter a valid phone number.</span>
                    </div>
                </div>

                <div class="reserve-line"></div>

                <div class="reserve-row">
                    <label for="book_guests">Guests</label>
                    <div class="reserve-field">
                        <input id="book_guests" type="number" name="n_guest" value="{{ old('n_guest', 1) }}" min="1" max="20" required>
                        <span>Number of customers.</span>
                    </div>
                </div>

                <div class="reserve-row">
                    <label>Date & Time</label>
                    <div class="reserve-fields reserve-two">
                        <div class="reserve-field">
                            <input id="book_reservation_date" type="date" name="date" value="{{ old('date') }}" required>
                            <span>Use Date</span>
                        </div>
                        <div class="reserve-field">
                            <input type="time" name="time" value="{{ old('time') }}" required>
                            <span>Use Time</span>
                        </div>
                    </div>
                </div>

                <div class="reserve-actions">
                    <button type="submit">Submit Booking</button>
                </div>
            </form>
        @endguest
    </div>

    @auth
        <div id="bookingPaymentModal" class="booking-payment-modal" role="dialog" aria-modal="true" aria-labelledby="bookingPaymentTitle">
            <div id="bookingPaymentDialog" class="booking-payment-dialog">
                <h3 id="bookingPaymentTitle">Choose Payment Method</h3>
                <p>Complete your downpayment through secure PayMongo checkout.</p>
                <div class="booking-payment-options">
                    <button class="booking-payment-option" type="button" data-method="PayMongo">PayMongo</button>
                </div>
                <div id="bookingPaymentQr" class="booking-payment-qr">
                    <strong id="bookingPaymentQrTitle">PayMongo payment</strong>
                    <p>Continue to PayMongo to view the secure payment information and any QR payment option available for this checkout.</p>
                </div>
                <div class="booking-payment-summary">
                    <div><span>Total Reservation Fee</span><strong>&#8369;<span id="bookingTotalFee">250.00</span></strong></div>
                    <div><span>50% Downpayment</span><strong>&#8369;<span id="bookingDepositFee">125.00</span></strong></div>
                </div>
                <div class="booking-payment-actions">
                    <button id="bookingPaymentCancel" class="booking-payment-cancel" type="button">Cancel</button>
                    <button id="bookingPaymentConfirm" class="booking-payment-confirm" type="button" disabled>Continue to PayMongo</button>
                </div>
            </div>
        </div>
    @endauth

    @if(session('booking_receipt'))
        <div id="bookingReceiptModal" class="booking-payment-modal is-visible" role="dialog" aria-modal="true" aria-labelledby="bookingReceiptTitle">
            <div class="booking-payment-dialog booking-receipt-dialog">
                <h3 id="bookingReceiptTitle">Booking Payment Receipt</h3>
                <div class="booking-receipt-number">{{ session('booking_receipt.reference') }}</div>
                <div class="booking-receipt-row"><span>Customer</span><strong>{{ session('booking_receipt.name') }}</strong></div>
                <div class="booking-receipt-row"><span>Guests</span><strong>{{ session('booking_receipt.guests') }}</strong></div>
                <div class="booking-receipt-row"><span>Schedule</span><strong>{{ session('booking_receipt.date') }} {{ session('booking_receipt.time') }}</strong></div>
                <div class="booking-receipt-row"><span>Payment Method</span><strong>{{ session('booking_receipt.payment_method') }}</strong></div>
                <div class="booking-receipt-row"><span>Payment Reference</span><strong>{{ session('booking_receipt.payment_reference') }}</strong></div>
                <div class="booking-receipt-row"><span>Total Fee</span><strong>&#8369;{{ number_format(session('booking_receipt.total'), 2) }}</strong></div>
                <div class="booking-receipt-row"><span>50% Paid</span><strong class="booking-receipt-paid">&#8369;{{ number_format(session('booking_receipt.deposit'), 2) }}</strong></div>
                <div class="booking-receipt-row"><span>Remaining Balance</span><strong>&#8369;{{ number_format(session('booking_receipt.balance'), 2) }}</strong></div>
                <div class="booking-payment-actions">
                    <button type="button" class="booking-payment-confirm" onclick="document.getElementById('bookingReceiptModal').classList.remove('is-visible')">Done</button>
                </div>
            </div>
        </div>
    @endif
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('bookTableForm');
        var modal = document.getElementById('bookingPaymentModal');
        if (!form || !modal) return;

        var dialog = document.getElementById('bookingPaymentDialog');
        var methodInput = document.getElementById('bookingPaymentMethod');
        var referenceInput = document.getElementById('bookingPaymentReference');
        var totalFee = document.getElementById('bookingTotalFee');
        var depositFee = document.getElementById('bookingDepositFee');
        var options = modal.querySelectorAll('.booking-payment-option');
        var qr = document.getElementById('bookingPaymentQr');
        var qrTitle = document.getElementById('bookingPaymentQrTitle');
        var confirmButton = document.getElementById('bookingPaymentConfirm');
        var cancelButton = document.getElementById('bookingPaymentCancel');
        var confirmed = false;

        function updatePaymentAmount() {
            var total = 250;
            totalFee.textContent = total.toFixed(2);
            depositFee.textContent = (total * 0.5).toFixed(2);
        }

        form.addEventListener('submit', function (event) {
            if (confirmed) return;
            event.preventDefault();
            if (!form.reportValidity()) return;
            updatePaymentAmount();
            modal.classList.add('is-visible');
        });

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                options.forEach(function (item) { item.classList.remove('is-selected'); });
                option.classList.add('is-selected');
                methodInput.value = option.dataset.method;
                dialog.classList.remove('is-gcash');
                qrTitle.textContent = 'PayMongo payment';
                qr.classList.add('is-visible');
                referenceInput.value = '';
                confirmButton.disabled = false;
            });
        });

        cancelButton.addEventListener('click', function () {
            modal.classList.remove('is-visible');
            dialog.classList.remove('is-gcash');
            options.forEach(function (item) { item.classList.remove('is-selected'); });
            qr.classList.remove('is-visible');
            methodInput.value = '';
            referenceInput.value = '';
            confirmButton.disabled = true;
        });
        confirmButton.addEventListener('click', function () {
            if (!methodInput.value) return;
            confirmButton.disabled = true;
            confirmButton.textContent = 'Opening PayMongo...';
            confirmed = true;
            form.submit();
        });
        updatePaymentAmount();
    });
</script>
