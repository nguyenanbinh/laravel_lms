<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\Order;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function instructorAllOrder(){

        $id = auth()->user()->id;
        $orderItem = Order::where('instructor_id',$id)->orderBy('id','desc')->get();

        return view('instructor.orders.all_orders',compact('orderItem'));

    }// End Method

    public function instructorOrderDetails($payment_id){

        $payment = Payment::where('id',$payment_id)->first();
        $orderItem = Order::where('payment_id',$payment_id)->orderBy('id','DESC')->get();

        return view('instructor.orders.instructor_order_details',compact('payment','orderItem'));

    }// End Method

    public function instructorOrderInvoice($payment_id){

        $payment = Payment::where('id',$payment_id)->first();
        $orderItem = Order::where('payment_id',$payment_id)->orderBy('id','DESC')->get();

        $pdf = Pdf::loadView('instructor.orders.order_pdf',compact('payment','orderItem'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);

        $nameInvoice = "invoice_{$payment->invoice_no}.pdf";
        return $pdf->download($nameInvoice);

    }// End Method

    public function myCourse(){
        $id = auth()->user()->id;
        $myCourse = Order::where('user_id', $id)->orderBy('id','DESC')->get();

        return view('frontend.mycourse.my_all_course',compact('myCourse'));

    }// End Method

    public function courseView($course_id){
        $id = auth()->user()->id;

        $course = Order::where('course_id',$course_id)->where('user_id',$id)->first();
        $section = CourseSection::where('course_id', $course_id)->orderBy('id','asc')->get();

        return view('frontend.mycourse.course_view',compact('course', 'section'));


    }// End Method
}
