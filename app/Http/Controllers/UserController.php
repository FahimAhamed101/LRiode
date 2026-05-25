<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        return view("user.index");
    }
    public function orders()
    {
        $orders = Order::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->paginate(10);
        return view('user.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $orders = Order::where('user_id', Auth::user()->id)->where('id', $order_id)->first();
        if ($orders) {
            $orderItem = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
            $transation = Transaction::where('order_id', $order_id)->first();
            return view('user.order_details', compact('orders', 'orderItem', 'transation'));


        } else {
            return redirect()->route('login');
        }
    }

    public function order_canceled(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = 'canceled';
        $order->canceled_date = Carbon::now();
        $order->save();
        return redirect()->back()->with('status', 'Order has been cancelled succssfully!');
    }

    public function wishlist(){
        
    }

}
