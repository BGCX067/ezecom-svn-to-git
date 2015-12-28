<?php

class Model_Widgets extends Model_Generic {

	//@deprecated
	function CategoryList($name) {
		$products = new Model_Products;
		$data = $products->ListProducts("",$name,"",""); 
		
		return $data;
	}



}