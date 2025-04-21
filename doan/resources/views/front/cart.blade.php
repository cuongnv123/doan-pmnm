@extends('front.layouts.app')

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">{{ __('HOME') }}</a></li>
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.shop') }}">{{ __('SHOP') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Cart') }}</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="section-9 pt-4">
        <div class="container">
            <div class="row">

                {{-- Hiển thị thông báo --}}
                @if (session('success'))
                    <div class="col-md-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if (count($cartContent) > 0)
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table" id="cart">
                                <thead>
                                    <tr>
                                        <th>{{ __('Item') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Remove') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp
                                    @foreach ($cartContent as $item)
                                        @php
                                            $total = $item['price'] * $item['quantity'];
                                            $subtotal += $total;
                                        @endphp
                                        <tr>
                                            <td class="text-start">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <img src="{{ $item['image'] ? asset('uploads/product/small/' . $item['image']) : asset('admin-assets/img/default-150x150.png') }}" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <h6 class="ms-2">{{ $item['title'] }}</h6>
                                                </div>
                                            </td>
                                            <td>{{ number_format($item['price'], 3, '.', '.') }} VND</td>
                                            <td>
                                                <div class="input-group quantity mx-auto" style="width: 100px;">
                                                    <button class="btn btn-sm btn-dark p-2 pt-1 pb-1 sub" data-id="{{ $item['id'] }}">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <input type="text" class="form-control form-control-sm text-center border-0" value="{{ $item['quantity'] }}">
                                                    <button class="btn btn-sm btn-dark p-2 pt-1 pb-1 add" data-id="{{ $item['id'] }}">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>{{ number_format($total, 3, '.', '.') }} VND</td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" onclick="deleteItem('{{ $item['id'] }}')">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Cart Summary --}}
                    <div class="col-md-4">
                        <div class="card cart-summery">
                            <div class="sub-title">
                                <h2 class="bg-white">{{ __('CART SUMMARY') }}</h2>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between pb-2">
                                    <div>{{ __('Subtotal') }}</div>
                                    <div>{{ number_format($subtotal, 3, '.', '.') }} VND</div>
                                </div>
                                <div class="pt-2">
                                    <a href="{{ route('front.checkout') }}" class="btn btn-dark btn-block w-100">
                                        {{ __('Proceed Checkout to COD') }}
                                    </a>
                                </div>
                                <div class="mt-3">
                                    <form action="{{ route('front.showCheckout') }}" method="GET">
                                        <button type="submit" class="btn btn-primary w-100" name="redirect">
                                            {{ __('Proceed Checkout to VnPay') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Giỏ hàng trống --}}
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <h4>{{ __('Your cart is empty!') }}</h4>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('customJs')
    <script>
        $('.add').click(function () {
            var qtyInput = $(this).siblings('input');
            var qty = parseInt(qtyInput.val());
            var id = $(this).data('id');

            if (qty < 10) {
                qtyInput.val(qty + 1);
                updateCart(id, qty + 1);
            }
        });

        $('.sub').click(function () {
            var qtyInput = $(this).siblings('input');
            var qty = parseInt(qtyInput.val());
            var id = $(this).data('id');

            if (qty > 1) {
                qtyInput.val(qty - 1);
                updateCart(id, qty - 1);
            }
        });

        function updateCart(rowId, qty) {
            $.ajax({
                url: '{{ route('front.updateCart') }}',
                type: 'POST',
                data: {
                    rowId: rowId,
                    qty: qty,
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    location.reload();
                }
            });
        }

        function deleteItem(rowId) {
            if (confirm("Are you sure you want to delete?")) {
                $.ajax({
                    url: '{{ route('front.deleteItem.cart') }}',
                    type: 'POST',
                    data: {
                        rowId: rowId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        location.reload();
                    }
                });
            }
        }
    </script>
@endsection
