<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function adminPendingOrder()
    {
        $payment = Payment::where('status', 'pending')->orderBy('id', 'DESC')->get();
        return view('admin.backend.orders.pending_orders', compact('payment'));
    } // End Method


    public function adminOrderDetails($payment_id)
    {
        $payment = Payment::where('id', $payment_id)->first();
        $orderItem = Order::where('payment_id', $payment_id)->orderBy('id', 'DESC')->get();

        return view('admin.backend.orders.admin_order_details', compact('payment', 'orderItem'));
    } // End Method

    public function pendingToConfirm($payment_id){
        Payment::find($payment_id)->update(['status' => 'confirm']);

        $notification = array(
            'message' => 'Order confirmed successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.confirm.order')->with($notification);
    }// End Method

    public function adminConfirmOrder(){

        $payment = Payment::where('status','confirm')->orderBy('id','DESC')->get();
        return view('admin.backend.orders.confirm_orders',compact('payment'));

    } // End Method
}
