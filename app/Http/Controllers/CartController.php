<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

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
        return response()->json(['success' => 'Course Remove From Cart']);

    }// End Method

}
