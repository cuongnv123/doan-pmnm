@extends('front.layouts.app')

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="#">{{ __('HOME') }}</a></li>
                    <li class="breadcrumb-item"><a class="white-text" href="#">{{ __('SHOP') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Checkout') }}</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="section-9 pt-4">
        <div class="container">
            <form action="{{ route('front.processCheckout') }}" method="POST" id="orderForm">
                @csrf
                <div class="row">
                    {{-- Left: Shipping --}}
                    <div class="col-md-8">
                        <div class="sub-title">
                            <h2>{{ __('Shipping Address') }}</h2>
                        </div>
                        <div class="card shadow-lg border-0">
                            <div class="card-body checkout-form">
                                <div class="row">
                                    @php $address = $customerAddress ?? null; @endphp
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="first_name" class="form-control"
                                               value="{{ old('first_name', $address->first_name ?? '') }}"
                                               placeholder="{{ __('First Name') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="last_name" class="form-control"
                                               value="{{ old('last_name', $address->last_name ?? '') }}"
                                               placeholder="{{ __('Last Name') }}">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <input type="email" name="email" class="form-control"
                                               value="{{ old('email', $address->email ?? '') }}"
                                               placeholder="Email">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <select name="country" class="form-control">
                                            <option value="">{{ __('Select a Country') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    {{ old('country', $address->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <textarea name="address" class="form-control"
                                                  placeholder="{{ __('Address') }}">{{ old('address', $address->address ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="city" class="form-control"
                                               value="{{ old('city', $address->city ?? '') }}"
                                               placeholder="{{ __('City') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="mobile" class="form-control"
                                               value="{{ old('mobile', $address->mobile ?? '') }}"
                                               placeholder="{{ __('Mobile') }}">
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <textarea name="order_notes" class="form-control"
                                                  placeholder="{{ __('Order Notes') }}">{{ old('order_notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Order Summary --}}
                    <div class="col-md-4">
                        <div class="sub-title">
                            <h2>{{ __('Order Summary') }}</h2>
                        </div>
                        <div class="card cart-summary">
                            <div class="card-body">
                                @php
                                    $cart = session('cart', []);
                                    $subtotal = 0;
                                @endphp
                                @forelse($cart as $item)
                                    @php $subtotal += $item['quantity'] * $item['price']; @endphp
                                    <div class="d-flex justify-content-between pb-2">
                                        <div>{{ $item['title'] }} x {{ $item['quantity'] }}</div>
                                        <div>{{ number_format($item['price'] * $item['quantity'], 3, '.', '.') }} VND</div>
                                    </div>
                                @empty
                                    <p>{{ __('Cart is empty') }}</p>
                                @endforelse

                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>{{ __('Subtotal') }}</strong>
                                    <strong>{{ number_format($subtotal, 3, '.', '.') }} VND</strong>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <strong>{{ __('Discount') }}</strong>
                                    <strong>{{ $discount }} VND</strong>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <strong>{{ __('Shipping') }}</strong>
                                    <strong>{{ number_format($totalShippingCharge, 3) }} VND</strong>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <strong>{{ __('Total') }}</strong>
                                    <strong>{{ number_format($grandTotal, 3, '.', '.') }} VND</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body">
                                <h5>{{ __('Payment Method') }}</h5>
                                <div class="form-check">
                                    <input type="radio" id="cod" name="payment_method" value="cod" checked class="form-check-input">
                                    <label for="cod" class="form-check-label">COD</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark btn-block mt-3">{{ __('Pay Now') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection


@section('customJs')
    <script>
        $(document).ready(function() {
            $('#payment_method_one').on('click', function() {
                if ($(this).is(":checked") == true) {
                    $("#card-payment-form").addClass("d-none");
                }
            });

            $('#payment_method_two').on('click', function() {
                if ($(this).is(":checked") == true) {
                    $("#card-payment-form").removeClass("d-none");
                }
            });
        });
        $("#orderForm").submit(function(event) {
            event.preventDefault();

            $('button[type="submit"]').prop('disabled', true);
            $.ajax({
                url: '{{ route('front.processCheckout') }}',
                type: 'POST',
                data: $(this).serializeArray(),
                dataType: 'json',
                success: function(response) {
                    var errors = response.errors;
                    $('button[type="submit"]').prop('disabled', false);

                    if (response.status == false) {
                        if (errors.first_name) {
                            $('#first_name')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.first_name)

                        } else {
                            $('#first_name')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                        if (errors.last_name) {
                            $('#last_name')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.last_name)

                        } else {
                            $('#last_name')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                        if (errors.email) {
                            $('#email')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.email)

                        } else {
                            $('#email')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                        if (errors.country) {
                            $('#country')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.country)

                        } else {
                            $('#country')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                        if (errors.address) {
                            $('#address')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.address)

                        } else {
                            $('#address')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                        if (errors.city) {
                            $('#city')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.city)

                        } else {
                            $('#city')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                        if (errors.state) {
                            $('#state')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.state)

                        } else {
                            $('#state')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                        if (errors.zip) {
                            $('#zip')
                                .addClass('is-invalid')
                                .siblings('p')
                                .addClass('invalid-feedback')
                                .html(errors.zip)

                        } else {
                            $('#zip')
                                .removeClass('is-invalid')
                                .siblings('p')
                                .removeClass('invalid-feedback')
                                .html('')
                        }
                    } else {
                        window.location.href = "{{ url('/thanks/') }}/" + response.orderId;
                    }


                }
            });

        });
        $("#country").change(function() {
            $.ajax({
                url: '{{ route('front.getOrderSummery') }}',
                type: 'post',
                data: {

                    country_id: $(this).val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status == true) {
                        $("#shippingAmount").html(response.shippingCharge + 'VND');
                        $("#grandTotal").html(response.grandTotal + 'VND');
                    }
                }
            });
        })

        $("#apply_discount").click(function() {
            $.ajax({
                url: '{{ route('front.applyDiscount') }}',
                type: 'post',
                data: {
                    code: $("#discount_code").val(),
                    country_id: $("#country").val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status == true) {
                        $("#shippingAmount").html(response.shippingCharge + 'VND');
                        $("#grandTotal").html(response.grandTotal + 'VND');
                        $("#discount_value").html(response.discount + 'VND');
                        $("#discount-response-wrapper").html(response.discountString);
                    } else {
                        $("#discount-response-wrapper").html("<span class='text-danger'>" + response
                            .message + "</span>");

                    }
                }
            });
        });
        $('body').on('click', "#remove-discount", function() {
            $.ajax({
                url: '{{ route('front.removeCoupon') }}',
                type: 'post',
                data: {
                    country_id: $("#country").val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status == true) {
                        $("#shippingAmount").html(response.shippingCharge + 'VND');
                        $("#grandTotal").html(response.grandTotal + 'VND');
                        $("#discount_value").html(response.discount + 'VND');
                        $("#discount-response").html('');
                        $("#discount_code").val('');

                    }
                }
            });
        });
    </script>
@endsection
