<?php

//Collection of static and basic functions

class Model_Shop extends Model_Generic {


	public function addtoCart($product_id,$quantity=1,$attributes=null) {
		/***
[PHPSESSID] => 5vegtj1r6iap75i73761mj7715
    [cart_items] => Array
        (
            [0204220623] => 706
            [0204220717] => 67
            [0204220727] => 706
            [0204221256] => 706
        )

    [cart_item_qty] => Array
        (
            [67] => 1
            [706] => 2
        )

    [cart_item_att] => Array
        (
            [67] => red
        )
		***/
		$now = date('mdHis');//@todo change to securityid
		
		if(isset($_COOKIE['cart_item_qty'])) {
		
			foreach ($_COOKIE['cart_item_qty'] as $key => $val) {
				
				if($key == $product_id) {
				
					$this->removefromCart($product_id);
				
					$new_quantity = $quantity + $val ;
					setcookie('cart_item_qty['.$product_id.']', $new_quantity, time() + 604800, '/');
				} else {
					setcookie('cart_items['.$now.']', $product_id, time() + 604800, '/');
					setcookie('cart_item_qty['.$product_id.']', $quantity, time() + 604800, '/');
				}
			}
			
			if($attributes) {			
				$attribute = '';
				foreach($attributes as $row) {
					$attribute .= $row.',';
				}
				$attribute .= ',';
				
				$attribute = str_replace(',,','',$attribute);
				setcookie('cart_item_att['.$product_id.']', $attribute, time() + 604800, '/');
			}
		}
		else {
		
			setcookie('cart_items['.$now.']', $product_id, time() + 604800, '/');
			setcookie('cart_item_qty['.$product_id.']', $quantity, time() + 604800, '/');
			
			if($attributes) {			
				$attribute = '';
				foreach($attributes as $row) {
					$attribute .= $row.',';
				}
				$attribute .= ',';
				
				$attribute = str_replace(',,','',$attribute);
				setcookie('cart_item_att['.$product_id.']', $attribute, time() + 604800, '/');
			}
		}
	}
	
	public function listCartItems($items = array()) {
		
		$product = new Model_Products();
		
		$cart_items = array();
		foreach($items as $product_id) {			
			$cart_items[] = $product->ListProducts($product_id);
		}
		return $cart_items;
		
	}
	
	public function emptyCart() {
	
		$items = isset($_COOKIE['cart_items']) ? $_COOKIE['cart_items'] : array();
		foreach ($items as $row => $val) {
			setcookie("cart_items[".$row."]", '', time() - 1209600, "/");
		}
		
		$cart_item_qty = isset($_COOKIE['cart_item_qty']) ? $_COOKIE['cart_item_qty'] : array();
		foreach ($cart_item_qty as $row => $val) {
			setcookie("cart_item_qty[".$row."]", '', time() - 1209600, "/");
			setcookie("cart_item_att[".$row."]", '', time() - 1209600, "/");
		}
		
		return;
	}
	
	/***
	*
	*
	Array
	(
		[PHPSESSID] => t3rp5geaeubn0i3m3u1m2q1tc2
		[cart_items] => Array
			(
				[0520220617] => 3 // 3 and 4 are the product ids
				[0520221252] => 4
			)

		[cart_item_qty] => Array
			(
				[3] => 2  // 3 and 4 are the product ids
				[4] => 1
			)

	)
	***/
	public function removefromCart($product_id) {
		$items = isset($_COOKIE['cart_items']) ? $_COOKIE['cart_items'] : array();
		foreach ($items as $row => $val) {
			if($val == $product_id) {
				setcookie("cart_items[".$row."]", '', time() - 1209600, "/");
				setcookie("cart_item_qty[".$val."]", '', time() - 1209600, "/");
				setcookie("cart_item_att[".$val."]", '', time() - 1209600, "/");
			}	
		}
		return;		
	}
	
	
	
	public function shippingTable($state=null) {
	
		$condition = '';
		if($state) {
			$condition = " State = '".$state."' " ;	
		}
		$data = $this->getData('Shipping', $condition);
		
		$shipping_table = array();
		foreach ($data as $row) {
			$shipping_table[$row['ID']] = $row;
		}
		
		return $shipping_table;
	}	
	
	
}