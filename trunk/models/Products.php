<?php

class Model_Products extends Model_Generic {

	public function ListProducts($product_id=null, $category_id=null, $brand_id=null,$forcebuild=null, $limit=8) {
	
		$where = "";
		if($product_id) {
			$where = " where t2.ID = ".$product_id;
		}
		
		if($category_id) {
			if(!is_numeric($category_id) ) {
				if ( preg_match('/^[0-9]*-[0-9]*-[0-9]*$/', $category_id) === 1) { #found TreeID
					$where = " where t7.TreeID = '".$category_id."'";
				} else {
					$where = " where t3.Name = '".$category_id."'";
				}
			} else {
				$where = " where t7.CategoryID = '$category_id' ";
			}
		}

		if($brand_id) {
			$where = " where t4.ID = ".$brand_id;
			if(!is_numeric($brand_id) ) {
				$tmp1 = str_replace(' ', '-', strtolower($brand_id) ); //remove spaces
				$tmp2 = str_replace("'", "", $tmp1 ); // remove quotes @todo preg_replace all special 
			
				$where = " where t4.URLSegment = '".$tmp2."'";
			}			
		}			
	
		$group_by = "";
		
		$join_product_images = "";
		$select_filename = "";
		
		#if($product_id) {
		
		#	$join_product_images = "";
		#	$select_filename = "";
		#} else {		
			$select_filename = " , t6.Filename ";
			$join_product_images = "left join ProductImages t6 on t6.ProductID = t2.ID";
			//$where .= " and t6.isPrimary = 1 ";
			$group_by =  " group by t2.ID";
		#}
		
		$sql = "select t1.Quantity, t1.Minimum, t2.ID ProductID, t2.*,  t3.ID CategoryID, t3.Name CatName, t4.ID BrandID, t4.Name BrandName, t5.Currency, t5.Price, t5.WholePrice, t5.DiscPrice, t5.DiscRate, t5.isDefault 
		".$select_filename."
		from Store t1 
		join Products t2 on t1.ProductID = t2.ID 
		left join ProductCategory t7 on t1.ProductID = t7.ProductID
		left join Category t3 on t7.CategoryID = t3.ID
		join Brands t4 on t1.BrandID = t4.ID 
		left join Prices t5 on t1.ProductID = t5.ProductID 
		";
		
		$sql .= $join_product_images;
		$sql .= $where ;
		$sql .= $group_by ;
		$sql .= " order by t2.Created Desc";
		
		$limit_sql = " limit ".$limit;

		
		if($limit == 0 ) {
			$limit_sql = "";
		}		
		$sql .= $limit_sql;

		//echo $sql; exit;
		
		$data = $this->oldSkul($sql);
		$images = $this->getData("ProductImages");
		
		if($category_id || $brand_id) {
			return $data;
		}
		
		if(count($data) > 0 && ($product_id > 0) )   {
			return $data[0];
		}
		
		$manifest = new Model_Manifest();
		$manifest_data = $manifest->Load("ListProducts",$data, $forcebuild); //forcebuild is set to 1 for 
					
		return $manifest_data  ;
		
	}
	
	

	public function ListProductByURL($url) {
		$product = $this->getData("Products","URLSegment='$url'");
		
		if($product) {
			return $this->ListProducts($product[0]['ID']);
		}
	}
	
	/***
	*
	* @todo delete corresponding ProductImage
	**/
	public function RemoveProduct($product_id,$confirmation) {
		
		if($confirmation == 1) {
			$this->oldSkul("delete from Products where ID=".$product_id, false);
			$this->oldSkul("delete from Store where ProductID=".$product_id, false);
			$this->oldSkul("delete from Prices where ProductID=".$product_id, false);
			$this->oldSkul("delete from ProductImages where ProductID=".$product_id, false);
		}
	
	}
	
	public function DisplayProductImages($ProductID) {
		return $this->getData("ProductImages","ProductID=".$ProductID," isPrimary desc ","");
	}
	
	public function DisplayProductAttributes($ProductID) {
		return $this->getData("ProductAttributes","ProductID=".$ProductID,"","");
	}

	public function GetProductTreeIDs ($ProductID) {
		return $this->oldSkul("select distinct TreeID from ProductCategory where ProductID=".$ProductID);
	}
	
	public function DisplayProductCategories($ProductID) {
		//return $this->getData("ProductCategory","ProductID=".$ProductID,"","");
		#return $this->oldSkul("select t1.*, t2.Name from ProductCategory t1 join Category t2 on t2.ID = t1.CategoryID where ProductID=".$ProductID);
		return $this->oldSkul("select distinct t1.ProductID, t1.CategoryID, t2.Name from ProductCategory t1 join Category t2 on t2.ID = t1.CategoryID where ProductID=".$ProductID);
	}	
	
	public function Search($string) {
		$string = strip_tags(trim($string));
		if(strlen($string) > 3) {		
			return $this->oldSkul("select Products.*, ProductImages.Filename from Products left join ProductImages on Products.ID = ProductImages.ProductID  where 
			Name like '%".$string."%' or Name like '".$string."%' or
			Description like '%".$string."%' or Description like '".$string."%' or
			Code like '%".$string."%' 
			group by Products.ID ");
		}
		return array();
	}
	
	// Used in transactions
	public function UpdateInventory($product_id,$quantity, $refund=null,$refund_val=null) {
		
		$this->oldSkul("update Store set Quantity = (Quantity - $quantity) where ProductID=".$product_id,false);
	}
	
	public function CheckInventory($data) {
		
		$updated_data = array();
		foreach($data as $row => $val) {
		
			if($val == 0) {
				$shop = new Model_Shop();
				$shop->removefromCart($row);
			}
			else {
				$check = $this->getData("Store"," ProductId =".$row);
				if($check[0]['Quantity'] < $val) {
					$updated_data[$row] = 1 ;
					setcookie("cart_item_qty[".$row."]", 1, time() + 3600, "/") ;
				} else {
					$updated_data[$row] = $val;
					setcookie("cart_item_qty[".$row."]", $val, time() + 3600, "/");
				}			
			}	
		}
		
		return $updated_data;
	}
	
	public function bestSeller($category_id=null,$exclude=array(),$limit=8) {
		#$raw = $this->oldSkul("select count(ProductID) c, ProductID from Transactions group by ProductID order by c desc limit $limit ");
                $raw = $this->oldSkul("select ProductID from best_sellers order by `order` limit $limit ");
		
		$best_sellers = array();
		if($raw) {
			foreach ($raw as $row) {
				$best_sellers[] = $this->ListProducts($row['ProductID']);
			}
		}
		
		return $best_sellers;
	}
	
	public function featuredProducts($category_id=null,$exclude=array(),$limit=8) {
		$raw = $this->oldSkul("select * from Products where isFeatured = 1 limit $limit ");
		
		$best_sellers = array();
		if($raw) {
			foreach ($raw as $row) {
				$best_sellers[] = $this->ListProducts($row['ID']);
			}
		}
		
		return $best_sellers;
	}	
	
	

}
