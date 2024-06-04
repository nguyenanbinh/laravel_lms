<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
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

            Session::put('coupon',[
             'coupon_name' => $coupon->coupon_name,
             'coupon_discount' => $coupon->coupon_discount,
             'discount_amount' => round(Cart::total() * $coupon->coupon_discount/100),
             'total_amount' => round(Cart::total() - Cart::total() * $coupon->coupon_discount/100 )
         ]);
        }

        return response()->json(['success' => 'Course Remove From Cart']);

    }// End Method

    /**
     * Apply coupon to the cart
     *
     * @param Request $request The request object containing the coupon name
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation
     */
    public function couponApply(Request $request){
        // Find the coupon by name and validity
        $coupon = Coupon::where('coupon_name', $request->coupon_name)
                        ->where('coupon_validity','>=', Carbon::now()->format('Y-m-d'))
                        ->first();

        if ($coupon) {
            // Calculate the discount and total amount
            $discount = round(Cart::total() * $coupon->coupon_discount/100);
            $totalAmount = round(Cart::total() - $discount);

            // Store the coupon details in the session
            Session::put('coupon',[
                'coupon_name' => $coupon->coupon_name,
                'coupon_discount' => $coupon->coupon_discount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount
            ]);

            return response()->json([
                'validity' => true,
                'success' => 'Coupon Applied Successfully'
            ]);

        }else {
            return response()->json(['error' => 'Invalid Coupon']);
        }
    }// End Method

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
    }// End Method

    public function couponRemove()
    {
        Session::forget('coupon');
        return response()->json(['success' => 'Coupon Remove Successfully']);

    }// End Method
}
