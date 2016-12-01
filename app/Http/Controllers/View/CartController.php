<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Entity\Product;
use App\Entity\CartItem;
use Illuminate\Http\Request;


class CartController extends Controller
{
    public function toCart(Request $request){
    	$cart_items = array();
    	$bk_cart = $request->cookie('bk_cart');
    	$bk_cart_arr = ($bk_cart != null ? explode(',', $bk_cart) : array());

    	$member = $request->session()->get('member', '');
    	if($member != ''){
    		$cart_items = $this->syncCart($member->id, $bk_cart_arr);//同步cookie中的购物车信息
    		return response()->view('cart', ['cart_items' => $cart_items])->withCookie('bk_cart', null);
    	}

    	foreach ($bk_cart_arr as $key => $value) {
    		$index = strpos($value, ':');
    		$cart_item = new CartItem;
    		$cart_item->id = $key;
    		$cart_item->product_id = substr($value, 0, $index);
    		$cart_item->count = substr($value, $index+1);
    		$cart_item->product = Product::find($cart_item->product_id);
    		if($cart_item->product != null){
    			array_push($cart_items, $cart_item);
    		}

    	}
    	return view('cart')->with('cart_items', $cart_items);
    }
    //将cookie中和数据库中购物车的数据同步，若一个产品两个里面都有，以数量多的为准
    private function syncCart($member_id, $bk_cart_arr){
    	$cart_items = CartItem::where('member_id', $member_id)->get();

        $cart_items_arr = array();
        foreach ($bk_cart_arr as $value) {
            $index = strpos($value, ':');
            $product_id = substr($value, 0, $index);
            $count = substr($value, $index+1);

            $exist = false;//判断cookie中的产品是否存在于数据库中
            foreach ($cart_items as $temp) {
                if($product_id == $temp->product_id){
                    if($temp->count < $count){
                        $temp->count = $count;
                        $temp->save();
                    }
                    $exist = true;
                    break;
                }
            }
            if($exist == false){
                $cart_item = new CartItem;
                $cart_item->member_id = $member_id;
                $cart_item->product_id = $product_id;
                $cart_item->count = $count;
                $cart_item->save();
                $cart_item->product = Product::find($cart_item->product_id);
                array_push($cart_items_arr, $cart_item);
            }
        }
        // 为每个对象附加产品对象便于显示
    foreach ($cart_items as $cart_item) {
      $cart_item->product = Product::find($cart_item->product_id);
      array_push($cart_items_arr, $cart_item);
    }
        return $cart_items_arr;
    }
}
