<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WishList;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WishListController extends Controller
{
    /**
     * Add a course to the user's wishlist if the user is authenticated
     *
     * @param Request $request The request object
     * @param int $course_id The ID of the course to be added
     * @return \Illuminate\Http\JsonResponse The response with a success or error message
     */
    public function addToWishList(Request $request, $course_id)
    {
        // Check if the user is authenticated
        if (auth()->check()) {
           // Check if the course is already in the wishlist
           $exists = WishList::where('user_id', auth()->id())
                              ->where('course_id', $course_id)
                              ->first();

           if (!$exists) {
               // If the course is not in the wishlist, insert it
               Wishlist::insert([
                   'user_id' => auth()->id(),
                   'course_id' => $course_id,
                   'created_at' => Carbon::now(),
               ]);
               return response()->json(['success' => 'Successfully added on your Wishlist']);
           } else {
               // If the course is already in the wishlist, return an error message
               return response()->json(['error' => 'This Product has already had on your Wishlist']);
           }

        } else {
            // If the user is not authenticated, return an error message
            return response()->json(['error' => 'At First Login Your Account']);
        }

    } // End Method

    public function getWishlist(){

        return view('frontend.wishlist.all_wishlist');

    }// End Method


    /**
     * Get the user's wishlist courses
     *
     * @return \Illuminate\Http\JsonResponse The response with the wishlist courses
     */
    public function getWishlistCourse(){

        // Get the user's wishlist courses, with the associated course data,
        // ordered by the creation date in descending order
        $wishlist = Wishlist::with('course')->where('user_id', auth()->id())->latest()->get();
        $wishQty = Wishlist::count();

        // Return the wishlist courses as a JSON response
        return response()->json(compact('wishlist', 'wishQty'));

    }// End Method

    /**
     * Remove a course from the user's wishlist
     *
     * @param int $id The ID of the course to be removed
     * @return \Illuminate\Http\JsonResponse The response with a success or error message
     */
    public function removeWishlist($id)
    {
        // Delete the wishlist item with the wishlist ID for the authenticated user
        Wishlist::where('user_id', auth()->id())->where('id', $id)->delete();

        // Return a success message as a JSON response
        return response()->json(['success' => 'Successfully Course Remove']);

    }// End Method
}
