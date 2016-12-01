<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Entity\Member;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderItem;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    public function toOrderCommit(Request $request, $product_ids){
    	$product_ids_arr=($product_ids != '' ? explode(',', $product_ids) : array());

    	$member = $request->session()->get('member', '');
    	$cart_items = CartItem::where('member_id', $member->id)->whereIn('product_id', $product_ids_arr)->get();
    	// return $cart_items;
    	//附加产品属性，便于前台显示
    	$cart_items_arr = array();
    	$total_price = 0;
    	$name = '';
    	foreach ($cart_items as $cart_item) {
    		$cart_item->product = Product::find($cart_item->product_id);
    		if($cart_item->product != null){
    			$total_price += $cart_item->product->price * $cart_item->count;
    			$name .= ('《'.$cart_item->product->name.'》');//字符串拼接用.=
    			array_push($cart_items_arr, $cart_item);
    		}
    	}
    	//提交订单到数据库
    	$order = new Order;
    	$order->name = $name;
    	$order->total_price = $total_price;
    	$order->member_id = $member->id;
    	$order->save();//先save之后才会有order->id
    	$order->order_no = 'E'.time().$order->id;//生成订单号
    	$order->save();
    	
    	//提交订单中所有产品到数据库
    	foreach ($cart_items_arr as $cart_item) {
    		$order_item = new OrderItem;
    		$order_item->order_id = $order->id;
    		$order_item->product_id = $cart_item->product_id;
    		$order_item->count = $cart_item->count;
    		$order_item->pdt_snapshot = json_encode($cart_item->product);
    		$order_item->save();
    	}
    	//提交订单后，删除购物车中对应的商品
    	// CartItem::where('member_id', $member->id)->whereIn('product_id', $product_ids_arr)->delete();

        return view('order_commit')->with('cart_items', $cart_items_arr)
                                   ->with('total_price', $total_price)
                                   ->with('name', $name)
                                   ->with('order_no', $order->order_no);
    }
    public function toOrderList(Request $request){
    	$member = $request->session()->get('member','');
    	$orders = Order::where('member_id', $member->id)->get();
    	foreach ($orders as $order) {
    		$order_items = OrderItem::where('order_id', $order->id)->get();
    		$order->order_items = $order_items;
    		foreach ($order_items as $order_item) {
    			$order_item->product = Product::find($order_item->product_id);
    		}
    	}
    	return view('order_list')->with('orders',$orders);
    }
}
