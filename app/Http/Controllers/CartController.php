<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirm;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Adds a course to the user's cart
     *
     * @param Request $request The request object containing the course name and instructor
     * @param int $id The ID of the course to be added to the cart
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation
     */
    public function addToCart(Request $request, $id)
    {

        // Find the course by ID
        $course = Course::find($id);

        if (Session::has('coupon')) {
            Session::forget('coupon');
        }

        // Check if the course is already in the cart
        $cartItem = Cart::search(function ($cartItem, $rowId) use ($id) {
            return $cartItem->id === $id;
        });

        // If the course is already in the cart, return an error message
        if ($cartItem->isNotEmpty()) {
            return response()->json(['error' => 'Course is already in your cart']);
        }

        // Add the course to the cart
        Cart::add([
            'id' => $id,
            'name' => $request->course_name, // The name of the course
            'qty' => 1,
            'price' => $course->discount_price == NULL ? $course->selling_price : $course->discount_price, // The price of the course
            'weight' => 1,
            'options' => [
                'image' => $course->course_image, // The image of the course
                'slug' => $course->course_name_slug, // The slug of the course
                'instructor' => $request->instructor // The instructor of the course
            ]
        ]);

        // Return a success message
        return response()->json(['success' => 'Successfully Added on Your Cart']);
    } // End Method

    public function cartData()
    {
        $carts = Cart::content();
        $cartTotal = Cart::total();
        $cartQty = Cart::count();

        return response()->json(array(
            'carts' => $carts,
            'cartTotal' => $cartTotal,
            'cartQty' => $cartQty,
        ));
    } // End Method

    public function addMiniCart()
    {
        $carts = Cart::content();
        $cartTotal = Cart::total();
        $cartQty = Cart::count();

        return response()->json(array(
            'carts' => $carts,
            'cartTotal' => $cartTotal,
            'cartQty' => $cartQty,
        ));
    } // End Method

    public function removeMiniCart($rowId)
    {
        Cart::remove($rowId);
        return response()->json(['success' => 'Course Remove From Cart']);
    } // End Method

    public function myCart()
    {
        return view('frontend.mycart.view_mycart');
    } // End Method


    public function cartRemove($rowId)
    {
        Cart::remove($rowId);

        if (Session::has('coupon')) {
            $coupon_name = Session::get('coupon')['coupon_name'];
            $coupon = Coupon::where('coupon_name', $coupon_name)->first();

            Session::put('coupon', [
                'coupon_name' => $coupon->coupon_name,
                'coupon_discount' => $coupon->coupon_discount,
                'discount_amount' => round(Cart::total() * $coupon->coupon_discount / 100),
                'total_amount' => round(Cart::total() - Cart::total() * $coupon->coupon_discount / 100)
            ]);
        }

        return response()->json(['success' => 'Course Remove From Cart']);
    } // End Method

    /**
     * Apply coupon to the cart
     *
     * @param Request $request The request object containing the coupon name
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation
     */
    public function couponApply(Request $request)
    {
        // Find the coupon by name and validity
        $coupon = Coupon::where('coupon_name', $request->coupon_name)
            ->where('coupon_validity', '>=', Carbon::now()->format('Y-m-d'))
            ->first();

        if ($coupon) {
            // Calculate the discount and total amount
            $discount = round(Cart::total() * $coupon->coupon_discount / 100);
            $totalAmount = round(Cart::total() - $discount);

            // Store the coupon details in the session
            Session::put('coupon', [
                'coupon_name' => $coupon->coupon_name,
                'coupon_discount' => $coupon->coupon_discount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount
            ]);

            return response()->json([
                'validity' => true,
                'success' => 'Coupon Applied Successfully'
            ]);
        } else {
            return response()->json(['error' => 'Invalid Coupon']);
        }
    } // End Method

    /**
     * Calculate the coupon discount and return the total amount after applying the coupon.
     * If coupon is not applied, return the total amount without any discount.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the total amount and coupon details if applied,
     *                                        or just the total amount if coupon is not applied.
     */
    public function couponCalculation()
    {
        // Check if coupon is applied
        if (Session::has('coupon')) {
            // Calculate the total amount before applying the coupon
            $subtotal = Cart::total();

            // Get the coupon details from the session
            $coupon = session()->get('coupon');

            // Return the coupon details and total amount after applying the coupon
            return response()->json([
                'subtotal' => $subtotal,
                'coupon_name' => $coupon['coupon_name'],
                'coupon_discount' => $coupon['coupon_discount'],
                'discount_amount' => $coupon['discount_amount'],
                'total_amount' => $coupon['total_amount'],
            ]);
        } else {
            // Return the total amount without any discount
            return response()->json([
                'total' => Cart::total(),
            ]);
        }
    } // End Method

    public function couponRemove()
    {
        Session::forget('coupon');
        return response()->json(['success' => 'Coupon Remove Successfully']);
    } // End Method

    public function checkout()
    {
        if (auth()->check()) {

            if (Cart::total() > 0) {
                $carts = Cart::content();
                $cartTotal = Cart::total();
                $cartQty = Cart::count();

                return view('frontend.checkout.checkout_view', compact('carts', 'cartTotal', 'cartQty'));
            } else {
                $notification = array(
                    'message' => 'Add At list One Course',
                    'alert-type' => 'error'
                );

                return redirect()->to('/')->with($notification);
            }
        } else {
            $notification = array(
                'message' => 'You Need to Login First',
                'alert-type' => 'error'
            );

            return redirect()->route('login')->with($notification);
        }
    } // End Method

    /**
     * Handle the payment process
     *
     * @param Request $request The request object
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payment(Request $request)
    {
        // dd(Cart::total());
        // Calculate the total amount based on the coupon if present
        if (Session::has('coupon')) {
            $total_amount = Session::get('coupon')['total_amount'];
        } else {
            $total_amount = round(Cart::total());
        }
        try {
            // Create a new payment record
            $data = new Payment();
            $data->name = $request->name;
            $data->email = $request->email;
            $data->phone = $request->phone;
            $data->address = $request->address;
            $data->cash_delivery = $request->cash_delivery;
            $data->total_amount = $total_amount;
            $data->payment_type = 'Direct Payment';

            $data->invoice_no = 'INV' . mt_rand(10000000, 99999999);
            $data->order_date = Carbon::now()->format('d F Y');
            $data->order_month = Carbon::now()->format('F');
            $data->order_year = Carbon::now()->format('Y');
            $data->status = 'pending';
            $data->created_at = Carbon::now();

            // Check if the user has already enrolled in the course
            $existingOrder = Order::where('user_id', auth()->user()->id)->whereIn('course_id', $request->course_id)->first();

            if ($existingOrder) {
                $notification = array(
                    'message' => 'You have already enrolled in this course',
                    'alert-type' => 'error'
                );

                return redirect()->back()->with($notification);
            } else {
                $request->session()->forget(['message', 'alert-type']);

                $data->save();
            } // end if

            // Save order details for each course in the cart
            foreach ($request->course_title as $key => $course_title) {
                $order = new Order();
                $order->payment_id = $data->id;
                $order->user_id = auth()->user()->id;
                $order->course_id = $request->course_id[$key];
                $order->instructor_id = $request->instructor_id[$key];
                $order->course_title = $course_title;
                $order->price = $request->price[$key];
                $order->save();
            } // end foreach
            // DB::commit();

            // Clear the cart session
            $request->session()->forget('cart');
            $paymentId = $data->id;

            /// Start Send email to student ///
            $sendmail = Payment::find($paymentId);
            $data = [
                'invoice_no' => $sendmail->invoice_no,
                'amount' => $total_amount,
                'name' => $sendmail->name,
                'email' => $sendmail->email,
            ];

            Mail::to($request->email)->send(new OrderConfirm($data));

            if ($request->cash_delivery == 'stripe') {
                echo "stripe";
            } else {
                $notification = array(
                    'message' => 'Cash Payment Submit Successfully',
                    'alert-type' => 'success'
                );

                return redirect()->route('index')->with($notification);
            }
        } catch (Exception $exception) {
            // DB::rollBack();
            Log::info($exception->getMessage());
        }
    } // End Method

}
