<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // Thêm sản phẩm vào giỏ hàng
    public function addToCart(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn cần đăng nhập để thêm vào giỏ hàng'
        ]);
    }

    $product = Product::with('product_images')->find($request->id);
    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found'
        ]);
    }

    $cart = session()->get('cart', []);

    if (isset($cart[$product->id])) {
        return response()->json([
            'status' => false,
            'message' => $product->title . ' is already in the cart'
        ]);
    }

    $cart[$product->id] = [
        'id' => $product->id,
        'title' => $product->title,
        'quantity' => 1,
        'price' => $product->price,
        'image' => optional($product->product_images->first())->image
    ];

    session()->put('cart', $cart);

    return response()->json([
        'status' => true,
        'message' => $product->title . ' has been added to the cart'
    ]);
}

public function cart()
{
    if (!auth()->check()) {
        return redirect()->route('account.login')->with('error', 'Bạn cần đăng nhập để xem giỏ hàng');
    }

    $cartContent = session()->get('cart', []);
    return view('front.cart', compact('cartContent'));
}

public function updateCart(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn cần đăng nhập để cập nhật giỏ hàng'
        ]);
    }

    $productId = $request->rowId;
    $qty = (int)$request->qty;

    $cart = session()->get('cart', []);

    if (!isset($cart[$productId])) {
        return response()->json([
            'status' => false,
            'message' => 'Item not found in cart'
        ]);
    }

    $product = Product::find($productId);
    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found'
        ]);
    }

    if ($product->track_qty === 'Yes' && $qty > $product->qty) {
        return response()->json([
            'status' => false,
            'message' => 'Requested quantity exceeds available stock'
        ]);
    }

    $cart[$productId]['quantity'] = $qty;
    session()->put('cart', $cart);

    return response()->json([
        'status' => true,
        'message' => 'Cart updated successfully'
    ]);
}

public function deleteItem(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn cần đăng nhập để xoá sản phẩm khỏi giỏ hàng'
        ]);
    }

    $productId = $request->rowId;
    $cart = session()->get('cart', []);

    if (!isset($cart[$productId])) {
        return response()->json([
            'status' => false,
            'message' => 'Item not found in cart'
        ]);
    }

    unset($cart[$productId]);
    session()->put('cart', $cart);

    return response()->json([
        'status' => true,
        'message' => 'Item removed from cart successfully'
    ]);
}


public function checkout()
{
    $discount = 0;
    $cart = session()->get('cart', []);

    // Nếu giỏ hàng rỗng thì quay lại trang giỏ hàng
    if (empty($cart)) {
        return redirect()->route('front.cart')->with('error', 'Your cart is empty.');
    }

    // Kiểm tra đăng nhập
    if (!Auth::check()) {
        session(['url.intended' => url()->current()]);
        return redirect()->route('account.login');
    }

    $user = Auth::user();
    $customerAddress = CustomerAddress::where('user_id', $user->id)->first();
    session()->forget('url.intended');
    $countries = Country::orderBy('name', 'ASC')->get();

    // Tính tổng tiền
    $subTotal = 0;
    $totalQty = 0;
    foreach ($cart as $item) {
        $subTotal += $item['price'] * $item['quantity'];
        $totalQty += $item['quantity'];
    }

    // Áp dụng mã giảm giá nếu có
    if (session()->has('code')) {
        $code = session()->get('code');
        if ($code->type == 'percent') {
            $discount = ($code->discount_amount / 100) * $subTotal;
        } else {
            $discount = $code->discount_amount;
        }
    }

    // Tính phí vận chuyển
    $totalShippingCharge = 0;
    if ($customerAddress) {
        $shippingInfo = ShippingCharge::where('country_id', $customerAddress->country_id)->first();
        if ($shippingInfo) {
            $totalShippingCharge = $totalQty * $shippingInfo->amount;
        }
    }

    $grandTotal = ($subTotal - $discount) + $totalShippingCharge;

    return view('front.checkout', [
        'countries' => $countries,
        'customerAddress' => $customerAddress,
        'totalShippingCharge' => $totalShippingCharge,
        'discount' => $discount,
        'grandTotal' => $grandTotal,
        'subTotal' => $subTotal
    ]);
}
public function processCheckout(Request $request)
{
    // 1. Validation
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|min:5',
        'last_name' => 'required',
        'email' => 'required|email',
        'address' => 'required|min:5',
        'city' => 'required',
        'country' => 'required',
        'mobile' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => "Please fix the error",
            'errors' => $validator->errors()
        ]);
    }

    $user = Auth::user();

    // 2. Lưu địa chỉ
    CustomerAddress::updateOrCreate(
        ['user_id' => $user->id],
        [
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'country_id' => $request->country,
            'address' => $request->address,
            'city' => $request->city,
            'state' => 'Đang mở khóa',
            'zip' => 'không có'
        
        ]
    );

    // 3. Xử lý thanh toán COD
    if ($request->payment_method === 'cod') {
        $cart = session('cart', []);

        if (empty($cart)) {
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty',
            ]);
        }

        $subTotal = 0;
        $totalQty = 0;
        foreach ($cart as $item) {
            $subTotal += $item['price'] * $item['quantity'];
            $totalQty += $item['quantity'];
        }

        // Tính mã giảm giá
        $discount = 0;
        $discountCodeId = null;
        $promoCode = '';
        if (session()->has('code')) {
            $code = session()->get('code');
            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
            $discountCodeId = $code->id;
            $promoCode = $code->code;
        }

        // Tính phí vận chuyển
        $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
        if (!$shippingInfo) {
            $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
        }
        $shipping = $shippingInfo ? $shippingInfo->amount * $totalQty : 0;

        $grandTotal = ($subTotal - $discount) + $shipping;

        // 4. Lưu đơn hàng
        $order = new Order();
        $order->subtotal = $subTotal;
        $order->shipping = $shipping;
        $order->grand_total = $grandTotal;
        $order->discount = $discount;
        $order->coupon_code_id = $discountCodeId;
        $order->coupon_code = $promoCode;
        $order->payment_status = 'not paid';
        $order->status = 'pending';
        $order->user_id = $user->id;
        $order->first_name = $request->first_name;
        $order->last_name = $request->last_name;
        $order->email = $request->email;
        $order->mobile = $request->mobile;
        $order->country_id = $request->country;
        $order->address = $request->address;
        $order->state = 'chờ duyệt';
        $order->zip = 'không có';
        $order->city = $request->city;
        $order->notes = $request->order_notes;
        $order->save();

        // 5. Lưu từng item trong đơn hàng
        foreach ($cart as $item) {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item['id'];
            $orderItem->order_id = $order->id;
            $orderItem->name = $item['title'];
            $orderItem->qty = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->total = $item['price'] * $item['quantity'];
            $orderItem->save();

            // Cập nhật số lượng tồn kho
            $productData = Product::find($item['id']);
            if ($productData && $productData->track_qty == 'Yes') {
                $productData->qty -= $item['quantity'];
                $productData->save();
            }
        }

        // Gửi email đơn hàng nếu cần
        // orderEmail($order->id, 'customer');

        Session::flash('success', 'You have placed your order successfully.');

        // Xóa giỏ hàng & mã giảm giá
        session()->forget('cart');
        session()->forget('code');

        return response()->json([
            'status' => true,
            'message' => "Order saved successfully",
            'orderId' => $order->id,
        ]);
    }
}

    public function thankyou($id)
    {
        return view('front.thanks', [
            'id' => $id
        ]);
    }

    public function getOrderSummery(Request $request)
    {
        $subTotal = Cart::subtotal(3, '.', '');
        $discount = 0;
        $discountString = '';
        //Apply Discount Here
        if (session()->has('code')) {
            $code = session()->get('code');
            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
            $discountString = '<div class="mt-4" id="discount-response">
        <strong>' . session()->get('code')->code . '</strong>
        <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
        </div>';
        }

        if ($request->country_id > 0) {


            $shippingInfo =  ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;

            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
            if ($shippingInfo != null) {
                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 3),
                    'discount' => number_format($discount, 3),
                    'discountString' =>  $discountString,
                    'shippingCharge' => number_format($shippingCharge, 3)
                ]);
            } else {

                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 3),
                    'discount' => number_format($discount, 3),
                    'discountString' =>  $discountString,
                    'shippingCharge' => number_format($shippingCharge, 3)
                ]);
            }
        } else {

            $grandTotal  = 0;
            return response()->json([
                'status' => true,
                'subTotal' => number_format(($subTotal - $discount), 3),
                'discount' => $discount,
                'discountString' =>  $discountString,
                'shippingCharge' => number_format(0, 3)

            ]);
        }
    }
    public function applyDiscount(Request $request)
    {

        $code = DiscountCoupon::where('code', $request->code)->first();
        if ($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Coupon Code'

            ]);
        }
        // Check if coupon start date is valid or not
        $now = Carbon::now();
        // if ($code->starts_at != "") {
        //     $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->starts_at);
        //     if ($now->lt($startDate)) {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Invalid Coupon Code1'

        //         ]);
        //     }
        // }
        if ($code->expires_at != "") {
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->expires_at);
            if ($now->gt($endDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Coupon Code2'

                ]);
            }
        }

        //Max Uses  check
        if ($code->max_uses > 0) {

            $couponUsed = Order::where('coupon_code_id', $code->id)->count();

            if ($couponUsed >= $code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon'
                ]);
            }
        }


        //Max Uses User check
        if ($code->max_uses_user > 0) {
            $couponUsedByUser = Order::where(['coupon_code_id' => $code->id, 'user_id' => Auth::user()->id])->count();
            if ($couponUsedByUser >= $code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already used this coupon code'
                ]);
            }
        }

        // Min amount  condition check
        $subTotal = Cart::subtotal(2, '.', '');
        if ($code->min_amount) {
            if ($subTotal < $code->min_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your min amount must be $' . $code->min_amount . '.',
                ]);
            }
        }


        session()->put('code', $code);
        return $this->getOrderSummery($request);
    }
    public function removeCoupon(Request $request)
    {
        session()->forget('code');
        return $this->getOrderSummery($request);
    }
}