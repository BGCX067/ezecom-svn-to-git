<?php

class AdminController extends Zend_Controller_Action {

	public function init() {
		$this->isAdmin();
		parent::init();
	}

	public function indexAction() {
		$this->_redirect('/admin/settings');
	}

	public function transactionlistAction() {

		$Transaction = new Model_Transaction();

		if($this->_getParam('export') == 1) {
			$this->_helper->layout()->setLayout('export');
			$this->view->export = 1;
		}
		
		$urlParam = '';
		foreach($_POST as $postKey => $postValue) {
			if ($postValue != '') {
				$urlParam .= $postKey . '/' . $postValue . '/';
			}
		}

		if($_POST && isset($_POST['q']) ) {
			$this->_redirect('/admin/transactionsearch/'.$urlParam);
			/*
			if(isset($_POST['pm']) && $_POST['pm'] > 0  ) {
				$this->_redirect('/admin/transactionsearch/pm/'.$_POST['pm']);
			}
			if( isset($_POST['st']) && $_POST['st'] > 0  ) {
				$this->_redirect('/admin/transactionsearch/st/'.$_POST['st']);
			}
			
			$this->_redirect('/admin/transactionsearch/q/'.$_POST['q']);
			*/
		}
		
		$Transactions = $Transaction->ListTransaction();
		
		$this->paginator($Transactions);	
	}
	
	public function transactionsearchAction() {
				
			$generic = new Model_Generic();
			
			
			if($this->_getParam('export') == 1) {
				$this->_helper->layout()->setLayout('export');
				$this->view->export = 1;
			}
			
			

			$where = " 1 ";
			if($this->_getParam('q')) {
				$query = $this->_getParam('q');
				$where = " t1.Email = '$query' or t1.ID = '$query' or t2.SiteUsername = '$query' " ;
			}
			
			$filter = "";
			if($this->_getParam('pm')) {
				$filter .= " and t1.PaymentMethod = ".$this->_getParam('pm');
			}
			
			if($this->_getParam('st')) {
				$this->view->st = $this->_getParam('st');
				$filter .= " and t1.Status = ".$this->_getParam('st');
			}
		
			/*#get Time Ordered From
			$timeOrderedFrom = '';
			if ($this->_getParam('tofh') != '' && $this->_getParam('tofm') != '' && $this->_getParam('tofs') != '') {
				$timeOrderedFrom = $this->_getParam('tofh') . ':' . $this->_getParam('tofm') . ':' . $this->_getParam('tofs');
			}
			
			#get Time Ordered To
			$timeOrderedTo = '';
			if ($this->_getParam('toth') != '' && $this->_getParam('totm') != '' && $this->_getParam('tots') != '') {
				$timeOrderedTo = $this->_getParam('toth') . ':' . $this->_getParam('totm') . ':' . $this->_getParam('tots');
			}*/

			
			
			
			
			
			#get Time Paid From
			$timePaidFrom = '';
			if ($this->_getParam('tpfh') != '') {
				$timePaidFrom = $this->_getParam('tpfh') . ':00:00';
			}
			
			#get Time Paid To
			$timePaidTo = '';
			if ($this->_getParam('tpth') != '') {
				$timePaidTo = $this->_getParam('tpth') . ':00:00';
			}
			
			
			
			
			

			$this->view->errorMessage = array();
			if ($this->_getParam('DateOrderedFrom') == '' && $this->_getParam('DateOrderedTo') != '') {
				$this->view->errorMessage[] = "You forgot to enter \"Date Ordered From:\".";
			}
			if ($this->_getParam('DateOrderedFrom') != '' && $this->_getParam('DateOrderedTo') == '') {
				$this->view->errorMessage[] = "You forgot to enter \"Date Ordered To:\".";
			}
			if($this->_getParam('DateOrderedFrom') != '' && $this->_getParam('DateOrderedTo') != '' && $this->_getParam('DateOrderedFrom') > $this->_getParam('DateOrderedTo')) {
				$this->view->errorMessage[] = "Date \"Ordered From\" must be older than \"Date Ordered To\".";
			}

			if ($this->_getParam('DatePaidFrom') == '' && $this->_getParam('DatePaidTo') != '') {
				$this->view->errorMessage[] = "You forgot to enter \"Date Paid From:\".";
			}
			if ($this->_getParam('DatePaidFrom') != '' && $this->_getParam('DatePaidTo') == '') {
				$this->view->errorMessage[] = "You forgot to enter \"Date Paid To:\".";
			}
			if($this->_getParam('DatePaidFrom') != '' && $this->_getParam('DatePaidTo') != '' && $this->_getParam('DatePaidFrom') > $this->_getParam('DatePaidTo')) {
				$this->view->errorMessage[] = "Date \"Paid From\" must be older than \"Date Paid To\".";
			}

			if ($timePaidFrom == '' && $timePaidTo != '') {
				$this->view->errorMessage[] = "Please select \"Time Paid From\".";
			}
			if ($timePaidFrom != '' && $timePaidTo == '') {
				$this->view->errorMessage[] = "Please select \"Time Paid To\".";
			}

			/*if ($this->_getParam('DateOrderedFrom') != '' && $this->_getParam('DateOrderedTo') != '' &&
			$this->_getParam('DateOrderedFrom') <= $this->_getParam('DateOrderedTo')) {

				if (empty($timeOrderedFrom) || empty($timeOrderedTo)) { #exclude time
					$filter .= " and (DATE(t1.Created) >= '". $this->_getParam('DateOrderedFrom') ."' and DATE(t1.Created) <= '". $this->_getParam('DateOrderedTo') ."') ";
				} else { #include time
					$filter .= " and (t1.Created >= '". $this->_getParam('DateOrderedFrom') ." ". $timeOrderedFrom ."' and t1.Created <= '". $this->_getParam('DateOrderedTo') ." ". $timeOrderedTo ."') ";
				}
			}*/

			if ($this->_getParam('DatePaidFrom') != '' && $this->_getParam('DatePaidTo') != '' &&
			$this->_getParam('DatePaidFrom') <= $this->_getParam('DatePaidTo')) {

				if (empty($timePaidFrom) || empty($timePaidTo)) { #exclude time
					$filter .= " and (DATE(t3.PaymentDate) >= '". $this->_getParam('DatePaidFrom') ."' and DATE(t3.PaymentDate) <= '". $this->_getParam('DatePaidTo') ."') ";
				} else { #include time
					$filter .= " and (t3.PaymentDate >= '". $this->_getParam('DatePaidFrom') ." ". $timePaidFrom ."' and t3.PaymentDate <= '". $this->_getParam('DatePaidTo') ." ". $timePaidTo ."') ";
				}
			}
			
			/*if ($this->_getParam('DatePaidFrom') != '' && $this->_getParam('DatePaidTo') != '') {
				$filter .= " and (DATE(t3.PaymentDate) >= '". $this->_getParam('DatePaidFrom') ."' and DATE(t3.PaymentDate) <= '". $this->_getParam('DatePaidTo') ."') ";
			}*/
			if ($this->_getParam('DateOrderedFrom') != '' && $this->_getParam('DateOrderedTo') != '') {
				$filter .= " and (DATE(t1.Created) >= '". $this->_getParam('DateOrderedFrom') ."' and DATE(t1.Created) <= '". $this->_getParam('DateOrderedTo') ."') ";
			}

			if ($this->_getParam('DatePaidFrom') == '' && $this->_getParam('DatePaidTo') == '' && $timePaidFrom != '' && $timePaidTo != '') {
				$filter .= " and (IF(
								'$timePaidFrom' < '$timePaidTo',
								CAST(t3.PaymentDate AS TIME) BETWEEN '$timePaidFrom' AND '$timePaidTo',
								((CAST(t3.PaymentDate AS TIME) BETWEEN CAST('00:00:00' AS TIME) AND '$timePaidTo') OR 
								(CAST(t3.PaymentDate AS TIME) BETWEEN '$timePaidFrom' AND CAST('23:59:59' AS TIME)))
							)) ";
			}
	
			//$sql = "Select distinct(ID) TrackingID, Email, Created, PaymentDue, PaymentMethod, Status, 
			//TransactionTotal  from Transactions where $where $filter ";

			$sql = "select distinct(t1.ID), t1.* ,
			t2.SiteUsername,
			t3.PaymentDate , t3.PaymentAmount, t3.PaymentDetails, a.Status as OrderStatus
			from Transactions t1 
			join SiteUsers t2 on t2.ID = t1.UserID
			left join Payments t3 on t3.TransactionID = t1.ID
			left join OrderStatus a on t1.Status = a.OrderStatusID
			where ( ( $where ) $filter ) 
			group by t1.ID
			order by t1.Created desc";
			
			$data = $generic->oldSkul($sql);			
						
			$this->paginator($data);
			
			//now we dont need to use two views
			$this->render('transactionlist');
		
	}
	
	public function transactiondisplayAction() {
	
		$Transaction = new Model_Transaction();	
		$TransID =   $this->_getParam('ID') ;
			
		$this->view->status_list =	$Transaction->transactionStatus();
		
		if($_POST) {
			$Transaction->updateStatus($_POST);
			$this->_redirect('/admin/transactiondisplay/ID/'.$_POST['TransactionID']);
		} 
		
		$data = $Transaction->getTransaction($TransID);
		if(!$data) {
			$this->_redirect('/admin/transactionlist/');
		}
		
		$rawStates = $Transaction->getData("Shipping","","State asc");
		foreach($rawStates as $row) {
			$states[$row['ID']] = $row['State'];
		}
		$this->view->states = $states;
		$this->view->payment_methods = $this->paymentMethods();
		$this->view->data = $data;
		$this->render('transactiondisplay');
	}
	
	public function inquirieslistAction() {
	
		$inquiries = new Model_Inquiries();
		$inquiries_list = $inquiries->listInquiries();
		$this->paginator( $inquiries_list);
	}
	
	public function inquiriesdisplayAction() {
	
		$inquiries = new Model_Inquiries();
		$data = $inquiries->listInquiries($this->_getParam('ID'));
		
		$this->view->data = $data;
		
	}
	
	public function settingsAction() {
		$isAdmin = $this->isAdmin();
		
		$site = new Model_Site();
		$blurbs = new Model_Widgetblurb();		
		
		$this->view->pagetitles =$site->ListPageTitles();		
		$this->view->widgetblurbs = $blurbs->listBlurbs();
		
		if($_POST) {
			
			if(isset($_POST['widgetblurb'])) {
				$blurb = new Model_Widgetblurb();
								
				$data = array();
				foreach($_POST['PageID'] as $k => $v) {
					$data[$k]['PageID'] = $v;					
				}
				
				foreach($_POST['CharLimit'] as $k => $v) {
					$data[$k]['CharLimit'] = $v;
				}
				
				//update the data
				foreach ($data as $row => $val) {
					$blurb->add($val['PageID'],$val['CharLimit'], $row);
				}
								
				$this->_redirect('/admin/settings/');
			}
			
			if(isset($_POST['widgetblurbadd'])) {
				$blurb = new Model_Widgetblurb();
								
				$blurb->add($_POST['PageID'],$_POST['CharLimit']);
				$this->_redirect('/admin/settings/');
			}
		}
		
		if(isset($_GET['wbid'])) {
			$blurbs->oldSkul("delete from WidgetBlurb where ID = ".$_GET['wbid'],false);
			$this->_redirect('/admin/settings/');
		}
		
	}
	
	//start product functions
	
	public function productslistfilterAction() {
		if($_POST ) {
		
			$this->_redirect('/admin/productslist/q1/'.$_POST['q1'].'/'
			.'q2/'.$_POST['q2'].'/'
			.'q3/'.$_POST['q3'].'/'
			.'q4/'.$_POST['q4'].'/'
			.'q5/'.$_POST['q5'].'/'
			.'q6/'.$_POST['q6'].'/'
			);
		}
	
	}
	
	public function productslistAction() {
	
		$products = new Model_Products;
		
		$where = "where 1";
		
			if( $this->_getParam('q1') ) {
				$where .= " and t2.Name like '%".$this->_getParam('q1')."%' or t2.Name like '".$this->_getParam('q1')."%'" ;
			}
			
			if( $this->_getParam('q2') ) {
				$where .= " and t2.Code = '".$this->_getParam('q2')."' " ;
			}
						
			if( $this->_getParam('q3') ) {
				$where .= " and t5.WholePrice = '".$this->_getParam('q3')."' " ;
			}
			
			if( $this->_getParam('q4') ) {
				$where .= " and t5.Price = '".$this->_getParam('q4')."' " ;
			}
			
			if( $this->_getParam('q5') ) {
				$where .= " and t1.Quantity = '".$this->_getParam('q5')."' " ;
			}
			
			if( $this->_getParam('q6') ) {
				$where .= " and t7.CategoryID = '".$this->_getParam('q6')."' " ;
			}
		
		
		
		//$list = $products->ListProducts("",$where,"",1); //set to forcebuild
		
		$sql ="select t1.Quantity, t1.Minimum, t2.ID ProductID, t2.*, t3.ID CategoryID, t3.Name CatName, t4.ID BrandID, t4.Name BrandName, t5.Currency, t5.Price, t5.WholePrice, t5.DiscRate, t5.isDefault , t6.Filename , t8.Attributes
		from Store t1 join Products t2 on t1.ProductID = t2.ID 
		left join ProductCategory t7 on t1.ProductID = t7.ProductID 
		left join Category t3 on t7.CategoryID = t3.ID join Brands t4 on t1.BrandID = t4.ID 
		left join Prices t5 on t1.ProductID = t5.ProductID 
		left join ProductImages t6 on t6.ProductID = t2.ID 
		left join ProductAttributes t8 on t8.ProductID = t2.ID
		".$where." 
		group by t2.ID order by t2.Created Desc";
		//echo $sql;exit;
		$list = $products->oldSkul($sql);
		
		if(count($list) > 0) {		
			$paginator = Zend_Paginator::factory($list);
			$this->paginator($list);	
		}
		else {
			$this->view->nodata = true;
		}
		
		$this->view->categories = $this->categories();
		
		if($this->_getParam('export') == 1) {
			$this->_helper->layout()->setLayout('export');
			$this->view->export = 1;
			$this->render('productslistexport');
		}
	}

	public function bestsellersAction() {

		$this->view->errorMessage = '';
		$BestSellers = new Model_Bestsellers();

		if ($this->_getParam('add_best_seller') != '') {

			$bestSellerItem = $BestSellers->getData('best_sellers','ProductID=' . $this->_getParam('add_best_seller'),null,1);
			if (empty($bestSellerItem)) {
				$order = $BestSellers->selectNextOrder();
				$BestSellers->insertData('best_sellers', array('ProductID' => $this->_getParam('add_best_seller'), 'order' => $order));
			} else {
				$this->view->errorMessage = 'Product that you have selected already exist in the best seller list.';
			}
		}
		
		if ($this->_getParam('delete_best_seller') != '') {
			if ($this->_getParam('delete_best_seller') == 'all') {
				$BestSellers->deleteData('best_sellers', '');
			} else {
				$BestSellers->deleteData('best_sellers', 'ProductID = ' . $this->_getParam('delete_best_seller'));
			}
		}

		if ($this->_getParam('up_best_seller') != '') {
			$bestSellerItem = $BestSellers->getData('best_sellers','ProductID=' . $this->_getParam('up_best_seller'), null, 1);
			if (!empty($bestSellerItem[0])) {
				$order = $bestSellerItem[0]['order'];
				$bestSellerItemBefore = $BestSellers->getData('best_sellers','`order` < ' . $order, '`order` desc', 1);
				if (!empty($bestSellerItemBefore[0])) {
					$BestSellers->updateData('best_sellers', array('order' => $bestSellerItemBefore[0]['order']), 'ProductID=' . $this->_getParam('up_best_seller'));
					$BestSellers->updateData('best_sellers', array('order' => $order), 'ProductID=' . $bestSellerItemBefore[0]['ProductID']);
				}
			}
		}
		
		if ($this->_getParam('down_best_seller') != '') {
			$bestSellerItem = $BestSellers->getData('best_sellers','ProductID=' . $this->_getParam('down_best_seller'),null,1);
			if (!empty($bestSellerItem[0])) {
				$order = $bestSellerItem[0]['order'];
				$bestSellerItemAfter = $BestSellers->getData('best_sellers','`order` > ' . $order,'`order` asc',1);
				if (!empty($bestSellerItemAfter[0])) {
					$BestSellers->updateData('best_sellers', array('order' => $bestSellerItemAfter[0]['order']), 'ProductID=' . $this->_getParam('down_best_seller'));
					$BestSellers->updateData('best_sellers', array('order' => $order), 'ProductID=' . $bestSellerItemAfter[0]['ProductID']);
				}
			}
		}
		
		if ($this->_getParam('default_setting') == 'true') {
			$BestSellers->setDefaultSetting();
		}

		$products = new Model_Products;
		$this->view->bestSellersList = $products->bestSeller();

		$where = "where 1";
		if($_POST ) {
			if(isset($_POST['q1'])  && !empty($_POST['q1']) ) {
				$where .= " and t2.Name like '%".$_POST['q1']."%' or t2.Name like '".$_POST['q1']."%'" ;
			}
			
			if(isset($_POST['q2'])  && !empty($_POST['q2']) ) {
				$where .= " and t2.Code = '".$_POST['q2']."' " ;
			}
			
			
			if(isset($_POST['q3'])  && !empty($_POST['q3'])) {
				$where .= " and t5.DiscPrice = '".$_POST['q3']."' " ;
			}
			
			if(isset($_POST['q4'])   && !empty($_POST['q4'])) {
				$where .= " and t5.Price = '".$_POST['q4']."' " ;
			}
			
			if(isset($_POST['q5'])   && !empty($_POST['q5'])) {
				$where .= " and t1.Quantity = '".$_POST['q5']."' " ;
			}
		}
		
		
		//$list = $products->ListProducts("",$where,"",1); //set to forcebuild
		
		$sql ="select t1.Quantity, t1.Minimum, t2.ID ProductID, t2.*, t3.ID CategoryID, t3.Name CatName, t4.ID BrandID, t4.Name BrandName, t5.Currency, t5.Price, t5.DiscPrice, t5.DiscRate, t5.isDefault , t6.Filename , t8.Attributes
		from Store t1 join Products t2 on t1.ProductID = t2.ID 
		left join ProductCategory t7 on t1.ProductID = t7.ProductID 
		left join Category t3 on t7.CategoryID = t3.ID join Brands t4 on t1.BrandID = t4.ID 
		left join Prices t5 on t1.ProductID = t5.ProductID 
		left join ProductImages t6 on t6.ProductID = t2.ID 
		left join ProductAttributes t8 on t8.ProductID = t2.ID
		".$where." 
		group by t2.ID order by t2.Created Desc";
		//echo $sql;exit;
		$list = $products->oldSkul($sql);
		
		if(count($list) > 0) {		
			$paginator = Zend_Paginator::factory($list);
			$this->paginator($list);	
		}
		else {
			$this->view->nodata = true;
		}
	}
	
	public function productsearchAction() {
		
		$query = $this->_getParam('q') ;
		
		if($query) {
			$products = new Model_Products;
			$data = $products->Search($query);
						
			$this->paginator($data);	
		}
		
	}
	

	public function productscreateAction() {

		$generic = new Model_Generic;

		$generic_call = $generic->genericForm("Products");
		$this->view->genform = $generic_call;
		
		$product_data = array();
		if($_POST) {
			foreach ($_POST as $key => $val) {
				if ( $key != 'BrandID' &&  $key != 'CategoryID' &&  $key != 'Price' && $key != 'DiscPrice' && $key != 'WholePrice' &&  $key != 'DiscRate' && $key != 'Quantity'  &$key != 'Minimum') {
					$product_data[$key] = $val;
				}
			}

			//1. Create Product 
			
			$product_data['URLSegment'] = Model_Page::urlFriendly($_POST['Name']);
			
			if(isset($_POST['ProductID'])) {
				//update
				$product_id = $_POST['ProductID'];
				unset($product_data['ProductID']);
				$generic->updateData("Products",$product_data,"ID=".$product_id);
			} else {
				
				$product_id = $generic->insertData("Products",$product_data);
			}
			
			//2. Create Price Relations
			$price_data = array();
			$price_data['Created'] = $_POST['Created'];
			$price_data['LastEdited'] = $_POST['LastEdited'];
			$price_data['ProductID'] = $product_id;
			$price_data['Currency'] = 'USD'; // we'll set this to USD for now
			$price_data['Price'] = $_POST['Price'];
			$price_data['WholePrice'] = $_POST['WholePrice'];
			$price_data['DiscPrice'] = $_POST['DiscPrice'];
			$price_data['DiscRate'] = $_POST['DiscRate'];
			
			if(isset($_POST['ProductID'])) {
				$generic->oldSkul("delete from Prices where ProductID =".$product_id, false);
				$price = $generic->insertData("Prices",$price_data);
			} else {
				$price = $generic->insertData("Prices",$price_data);
			}					
									
			//3. Create Store Data
			$store_data = array();
			$store_data['ProductID'] = $product_id;
			//$store_data['CategoryID'] = 0;
			$store_data['BrandID'] = $_POST['BrandID'];
			$store_data['Quantity'] = $_POST['Quantity'];
			$store_data['Minimum'] = $_POST['Minimum'];

			if(isset($_POST['ProductID'])) {
				$generic->oldSkul("delete from Store where ProductID =".$product_id, false);
				$store = $generic->insertData("Store",$store_data);
			} else {
				$store = $generic->insertData("Store",$store_data);
			}
			
			//4. Create Category Relations
			//See productscategoriesAction
			
			$this->_redirect('/admin/productsdisplay/pid/'.$product_id.'/?sysmsg=success');
		}
		$this->view->data = array();
		$this->view->Brands = $generic->getData("Brands","","Name");
		$this->view->Categories = $generic->getData("Category","","Name asc");	
		
		
	}
	
	public function productsdisplayAction() {
	
		//$this->_helper->layout()->setLayout('admin');
		
		$products = new Model_Products;
		$generic = new Model_Generic;
		$categories = new Model_Categories;
		
		$product_id = $this->_getParam('pid');
		
		$this->view->data = $products->ListProducts($product_id);
		$this->view->images = $products->DisplayProductImages($product_id);
		$this->view->product_attributes = $products->DisplayProductAttributes($product_id);
		$this->view->product_categories = $products->GetProductTreeIDs($product_id);
		
		$this->view->Brands = $generic->getData("Brands");
		#$this->view->Categories = $generic->getData("Category","","Name asc");

		$this->view->category_tree = $categories->listCategoryTree();
		$this->view->orphan_categories = $categories->listOrphanCategories();

		$this->render('productscreate');
	}

	public function productsattributesAction() {
		$products = new Model_Products;
		$product_id = $this->_getParam('ProductID');
		
		if ( isset($_POST['BtnSubmitProductAttribute']) ) {
			if (strtolower($_POST['BtnSubmitProductAttribute']) == 'add attribute') {
				$data = array();
				$data['ProductID'] = $_POST['ProductID'];
				$data['Created'] = date('Y-m-d H:i:s');
				$data['LastEdited'] = date('Y-m-d H:i:s');
				
				$attribute_name = strtoupper(trim($_POST['Name']));
				$data['Name'] = $attribute_name;
				
				$attributes = strtoupper(trim($_POST['Attributes']));
				$data['Attributes'] = $attributes;
				
				$products->oldSkul("delete from ProductAttributes where ProductID=".$product_id." and Name='".$attribute_name."'",false);			
				$products->insertData('ProductAttributes',$data);
				
				$this->_redirect('/admin/productsdisplay/pid/'.$product_id);
			}
			if (strtolower($_POST['BtnSubmitProductAttribute']) == 'update attribute') {
				$data = array();
				if (isset($_POST['Name'])) {
					$data[] = ' Name='. $products->db->quote(strtoupper(trim($_POST['Name']))) .' ';
				}
				if (isset($_POST['Attributes'])) {
					$data[] = ' Attributes='. $products->db->quote(strtoupper(trim($_POST['Attributes']))) .' ';
				}
				$data[] = ' LastEdited="'. date('Y-m-d H:i:s') .'" ';
				if (isset($_POST['ProductAttributeID'])) {
					$products->oldSkul("update ProductAttributes set " . implode(',', $data) . " where ID = " . $_POST['ProductAttributeID'], false);
				}
				$this->_redirect('/admin/productsdisplay/pid/'.$product_id);
			}
		}

		//delete attribute
		//@todo create security token
		//$todo convert into a method
		if (isset($_GET['delete']) && $_GET['paid'] && $_GET['pid']) {
			if ( $_GET['delete'] == 1 && $_GET['paid']) {
				$products->oldSkul("delete from ProductAttributes where ID= ".$_GET['paid'],false);	
			}
			$this->_redirect('/admin/productsdisplay/pid/'.$_GET['pid']);
		}
	}
	
	public function getproductattributeAction() {
		$generic = new Model_Generic;
		$productAttribute = $generic->db->fetchAll("select ID, ProductID, Name, Attributes from ProductAttributes where ID = " . $_GET['id']);
		echo implode('$3P@R@T0R', $productAttribute[0]);
		exit();
	}

	public function productscategoriesAction() {
		$products = new Model_Products;
		$product_id = $this->_getParam('ProductID');
	
		if($_POST) {
			$data = $_POST;
			$data['Created'] = date('Y-m-d H:i:s');
			$data['LastEdited'] = date('Y-m-d H:i:s');
			
			$products->oldSkul("delete from ProductCategory where ProductID=".$product_id,false);	
	
			unset($data['CategoryID']);
			foreach ($_POST['CategoryID'] as $category) {
			
				$parent_child = explode('-', $category);
				$ParentID = isset($parent_child[0]) ? $parent_child[0] : '0';
				$SubParentID = isset($parent_child[1]) ? $parent_child[1] : '0';
				$ChildID = isset($parent_child[2]) ? $parent_child[2] : '0';

				if ($ChildID != '0') {
					$data['CategoryID']	= $ChildID;
				} else if ($SubParentID != '0') {
					$data['CategoryID']	= $SubParentID;
				} else {
					$data['CategoryID']	= $ParentID;
				}
				$data['TreeID']	= $category;

				$products->insertData('ProductCategory',$data);

			}
			
			$this->_redirect('/admin/productsdisplay/pid/'.$product_id);
		}
	}
	
	private function saveUploadedFile($filesize, $tmpFileName, $destinationFile, $ProductID){
		$returnVal = 0;
		if ($filesize <= 2097152) {

			if(move_uploaded_file($tmpFileName, $destinationFile)) {
			
				$nufile = date('YmdHis').'_'.uniqid().'.jpg';
				rename($destinationFile, UPLOADS_PATH . '/' . $nufile);

				$fin_file =  $ProductID . '_' . $nufile;

				$image = new Model_Image();
				$image->load(UPLOADS_PATH . '/' . $nufile);
				//$image->resize(640, 480);
				//$image->resizeToHeight(800);
				$image->save(UPLOADS_PATH . '/' . $fin_file);
				
				unlink(UPLOADS_PATH . '/' . $nufile); //delete file
				
				//create db record
				$generic = new Model_Generic();
				$generic->insertData("ProductImages",array("ProductID"=>$ProductID,"Filename"=>$fin_file));
				
				//create thumbs dir if not exist
				if (!is_dir(UPLOADS_PATH . '/thumbs')) {
					mkdir(UPLOADS_PATH . '/thumbs');
				}

				//create thumb image in thumbs dir using Model_Image
				$image = new Model_Image();
				$image->load(UPLOADS_PATH . '/' . $fin_file);

				$image_source_w = $image->getWidth();
				$image_source_h = $image->getHeight();
				if ($image_source_w > $image_source_h) {
					$image->resizeToHeight(118);
					$image->save(UPLOADS_PATH . '/thumbs_tmp/th_' . $fin_file);
				} else {
					$image->resizeToWidth(118);
					$image->save(UPLOADS_PATH . '/thumbs_tmp/th_' . $fin_file);
				}
				$image->destroyImage();

				$image->load(UPLOADS_PATH . '/thumbs_tmp/th_' . $fin_file);
				$image_source_w = $image->getWidth();
				$image_source_h = $image->getHeight();
				
				$finalImage = new Model_Image();
				$finalImage->load(UPLOADS_PATH . '/thumbs_tmp/WH118PX.JPG');

				if ($image_source_w > $image_source_h) {
					imagecopy ( $finalImage->image , $image->image , 0 , 0 , floor(($image_source_w - 118) / 2) , 0 , 118 , 118 );
				} else {
					imagecopy ( $finalImage->image , $image->image , 0 , 0 , 0 , floor(($image_source_h - 118) / 2) , 118 , 118 );
				}
				$finalImage->save(UPLOADS_PATH . '/thumbs/th_' . $fin_file);
				$image->destroyImage();
				$finalImage->destroyImage();

				unlink(UPLOADS_PATH . '/thumbs_tmp/th_' . $fin_file); //delete file

				$returnVal = 1;
			}
		}
		return $returnVal;
	}

	public function uploadimageAction()	{
	
		$upload_dir = UPLOADS_PATH;		
		if ($_POST ) {			
			if(empty($_FILES['file']['name'][0])) {			
				$this->_redirect('/admin/productsdisplay/pid/'.$_POST['ProductID']);
			}
			if (is_array($_FILES['file']['name'])) {
				
				$countFiles = count($_FILES['file']['name']);
				$allFailed = true;
				for ( $n = 0; $n < $countFiles; $n++ ) {
					$target_path = $upload_dir . basename( $_FILES['file']['name'][$n]);
					$uploadResult = $this->saveUploadedFile($_FILES['file']['size'][$n], $_FILES['file']['tmp_name'][$n], $target_path, $_POST['ProductID']);
					
					if ( $uploadResult ) {
						$allFailed = false;
					}
				}
				if ($allFailed) {
					throw new Zend_Controller_Action_Exception('Something went wrong while uploading files', 500);					
				} else {
					$this->_redirect('/admin/productsdisplay/pid/'.$_POST['ProductID']);
				}
			} /*** for single file uploading
			else {

				$target_path = $upload_dir . basename( $_FILES['file']['name']);
				$uploadResult = $this->saveUploadedFile($_FILES['file']['size'], $_FILES['file']['tmp_name'], $target_path, $this->user_id, $_POST, $gal);
				if (!$uploadResult) {
					echo 'Something went wrong while uploading '. $_FILES['file']['name'] .'.';					
				} else {
					//$this->_redirect('/gallery/view/outlet_id/'. $_POST['outlet_id'] .'/date1/'.$_POST['date1'].'/date2/'.$_POST['date2']);
				}
			} ***/
		} 
		$this->_redirect('/admin/productslist/');
		
	}
	
	public function primaryimageAction()	{
		if($_POST) {
			$generic = new Model_Generic();
			$generic->oldSkul('update ProductImages set isPrimary = 0 where ProductID='.$_POST['ProductID'],false);
			$generic->oldSkul('update ProductImages set isPrimary = 1 where ID='.$_POST['ProductImageID'],false);
			$this->_redirect('/admin/productsdisplay/pid/'.$_POST['ProductID']);
		}
	
	}
	
	public function productsdeleteAction() {
		$products = new Model_Products;
		$product_id = $this->_getParam('pid');
		$confirmation = $this->_getParam('conf');

		$products->RemoveProduct($product_id, $confirmation);

		$this->_redirect('/admin/productslist/');
	
	}

	public function deleteimageAction() {
		
		$generic = new Model_Generic();
		
		if($_POST) {
			$generic->deleteData("ProductImages","ID=".$_POST['ProductImageID']);
			unlink(UPLOADS_PATH . '/' . $_POST['Filename']); //delete file
			unlink(UPLOADS_PATH . '/thumbs/th_' . $_POST['Filename']); //delete file
			
			$this->_redirect('/admin/productsdisplay/pid/'.$_POST['ProductID']);
		}
		$this->_redirect('/admin/productslist/');
	}
		
	//end product functions	

	public function categoriesAction() {

		$Categories = new Model_Categories();

		if ($this->getRequest()->isXmlHttpRequest()) {

			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);

			if (isset($_POST['CategoryTree'])) {

				$this->view->category_tree = $Categories->listCategoryTree();
				$this->view->orphan_categories = $Categories->listOrphanCategories();
				$asdf = $this->renderScript('admin/ajax/category_tree.phtml');

			}

			if (isset($_POST['Submit'])) {

				if ( isset($_POST['CategoryID']) && !empty($_POST['CategoryID']) ) { #Update

					$strUpdateCategoryName = "Update Category set Name = ". $Categories->db->quote($_POST['CategoryName']) ." where ID = {$_POST['CategoryID']} ";
					$sqlUpdateCategoryName = $Categories->db->query($strUpdateCategoryName);
					if ($sqlUpdateCategoryName) {
						echo json_encode(array('true', $_POST['CategoryName'] . ' updated.'));
					} else {
						echo json_encode(array('false', 'Something went wrong. Please Reset and try again.'));
					}

					#if ($Categories->updateCategory($_POST)) {
					#	echo $_POST['CategoryName'] . " updated!";
					#} else {
					#	echo "There was a problem processing your request. Please RESET and try again.";
					#}

				} else { #Create New
					$succeed = $Categories->createCategory($_POST);

					$allCats = array();
					$all_categories = $Categories->getData('Category','',' CustomOrder ASC, Name ASC ');
					foreach ($all_categories as $cat) {
						$allCats[] = array($cat['ID'], $cat['Name']);
					}

					$succeed[] = $allCats;
					echo json_encode($succeed);
				}
				exit();

			}
			
			if (isset($_POST['DeleteCategory'])) {

				$TreeID = isset($_POST['TreeID']) ? $_POST['TreeID'] : '0-0-0';
				$parent_child = explode('-', $TreeID);
				$ParentID = isset($parent_child[0]) ? $parent_child[0] : 0;
				$SubParentID = isset($parent_child[1]) ? $parent_child[1] : 0;
				$ChildID = isset($parent_child[2]) ? $parent_child[2] : 0;

				$sqlDelete = false;

				if ($ParentID != 0 && $SubParentID == 0 && $ChildID == 0) { #parent
					$sqlDelete = $Categories->db->query("delete from ParentChildCategories where ParentID = $ParentID");
				} else if ($ParentID != 0 && $SubParentID != 0 && $ChildID == 0) { #subparent
					$sqlDelete = $Categories->db->query("delete from ParentChildCategories where ParentID = $ParentID and SubParentID = $SubParentID");
				} else if ($ParentID != 0 && $SubParentID != 0 && $ChildID != 0) { #child
					$sqlDelete = $Categories->db->query("delete from ParentChildCategories where ParentID = $ParentID and SubParentID = $SubParentID and ChildID = $ChildID");
				} else { #delete orphan category
					$rootCat = 0;
					if ($ChildID != 0) {
						$rootCat = $ChildID;
					} else if ($SubParentID != 0) {
						$rootCat = $SubParentID;
					} else if ($ParentID != 0) {
						$rootCat = $ParentID;
					}
					$sqlDelete = $Categories->db->query("delete from Category where ID = $rootCat");
				}

				$retVal = array();
				$retVal['msg'] = $sqlDelete ? "Category deleted!" : "Something went wrong. Please Reset and try again.";
				$retVal['allCats'] = array();
				$all_categories = $Categories->getData('Category','',' CustomOrder ASC, Name ASC ');
				foreach ($all_categories as $cat) {
					$retVal['allCats'][] = array($cat['ID'], $cat['Name']);
				}
				echo json_encode($retVal);
				exit();

			}

		} else {
			$this->view->all_categories = $Categories->getData('Category','',' CustomOrder ASC, Name ASC ');
		}
	}


	public function categoriesbackupAction() {
		
		$categories = new Model_Categories();
		
		if($this->_getParam('edit')) {
			$category_id = $this->_getParam('edit');
			$category  = $categories->listCategories($category_id);
			$this->view->data = $category[0];
			
			$parents = $categories->listParentCategories($category_id);		
			$this->view->parentcategories = $parents ;
			
			if($_POST) {
				$categories->update($_POST['ID'],$_POST);
				$this->_redirect('/admin/categories/edit/'.$_POST['ID']);
			}
		}
		
		if($this->_getParam('delete')) {
			$category_id = $this->_getParam('delete');
			$category  = $categories->delete($category_id);
			$this->_redirect('/admin/categories/');
		}
		
		//create
		if($_POST) {
			$new_category = $categories->create($_POST);
			$this->_redirect('/admin/categories/');
		}
		
		if(!$this->_getParam('edit')) {
			$parents = $categories->listParentCategories();		
			$this->view->parentcategories = $parents ;
		}
		
		$this->view->category_tree = $categories->listCategoryTree();

		$this->view->orphan_categories = $categories->listOrphanCategories();

		#$categories_list = $categories->listCategories();
		#$this->paginator($categories_list);
		
		
	}
	
	public function bannersAction() { 
		$Banners = new Model_Banners();
		
		//read and edit
		if($this->_getParam('edit')) {
			$banner_id = $this->_getParam('edit');
			$this->view->data = $Banners->listBanners($banner_id);
			
			if($_POST) {
				$Banners->update($_POST['ID'],array(
					'Title'=>$_POST['Title'],
					'URLSegment'=>$_POST['URLSegment'],
					'CustomOrder'=>$_POST['CustomOrder'] ,
					'CategoryID'=>$_POST['CategoryID'] ,
					'Tags'=>$_POST['Tags'] 
					)
				);
				$this->_redirect('/admin/banners/edit/'.$_POST['ID']);
			}	
			//$generic = new Model_Generic;
			$this->view->categories = $this->categories();
			
			$this->render('banners-edit');
		}
		
		
		if ($_POST ) {
			//create
 			if(empty($_FILES['file']['name'][0])) {			
				$this->_redirect('/admin/banners/');
			}
			if (is_array($_FILES['file']['name'])) {				
				$process_upload = $Banners->create($_FILES,$_POST['Title'],$_POST['URLSegment'] );		
				$this->view->uploaded_files = $process_upload;
			} 
			
		}
		
		
		
		if($this->_getParam('delete')) {
			$banner_id = $this->_getParam('delete');
			$this->view->data = $Banners->delete($banner_id);
			$this->_redirect('/admin/banners');
		}
		
		
		$banners_list = $Banners->listBanners();
		$this->paginator($banners_list);
				
	}	
	
	private function isAdmin() {
		$this->_helper->layout()->setLayout('admin2');
		$users = new Model_User();
		$authsession = new Zend_Session_Namespace('authsession');

		if( !isset($authsession->logged_admin) ){			
			$this->_redirect('/index/login/');
		}
	}
	
	public function paginator($list) {
	
		$paginator = Zend_Paginator::factory($list);
		$curPage=$this->_getParam('page',1);
		$paginator->setItemCountPerPage(20);
		$paginator->setCurrentPageNumber($curPage);
		$this->view->list = $paginator;	
	}
	
	public function userlistAction() {
		$generic = new Model_Generic;
		$users =  $generic->getData("SiteUsers","SiteUsername != 'admin' ","SiteEmail");
		
		$this->paginator($users);
	}
	
	public function adminlistAction() {
		$generic = new Model_Generic;
		$users =  $generic->getData("SiteUsers","SiteUsername != 'admin' and anAdmin = 1","SiteEmail");
		
		$this->paginator($users);
		
		if($_POST) 
		{
			$this->admincreate($_POST);
		}
		
		
	}
	
	private function admincreate($data) {
		if($data['email'] && $data['user'] && $data['password1'] ==  ($data['password2']) )
		{
			//@todo validate users and user inpput
		
			$generic = new Model_Generic;
			$generic->oldSkul("insert into SiteUsers (Created, LastEdited, SiteUsername, SiteEmail, SitePassword, anAdmin) values (now(), now(), '".$data['user']."','".$data['email']."', '".md5($data['password1'])."',1) ", false);
		}
		$this->_redirect("/admin/adminlist");
	}

	public function	admindeleteAction() {
		$generic = new Model_Generic;
		$user_id = $this->_getParam('ID');
		
		if($user_id)
		{
			$generic->oldSkul("delete from SiteUsers where ID = ".$user_id,false);
		}
		//@todo display message confirmation
		$this->_redirect("/admin/adminlist");
	}
	
	public function usersearchAction() {
		
		$query = $this->_getParam('q') ;
		$generic = new Model_Generic();

			$where = " 1 ";
			if($this->_getParam('user')) {
				$query = $this->_getParam('user');
				$where = " SiteEmail like '$query%' or SiteUsername like '$query%' " ;
			}
			
			if($this->_getParam('email')) {
				$query = $this->_getParam('email');
				$where = " SiteEmail like '$query%' or SiteUsername like '$query%' " ;
			}			
			
			$filter = "";
			if($this->_getParam('fname')) {
				$filter .= " and Firstname like '".$this->_getParam('fname')."%' ";
			}
			
			if($this->_getParam('lname')) {
				$filter .= " and Firstname = ".$this->_getParam('lname');
			}
			
			if($this->_getParam('at')) {
				$filter .= " and Type = '".$this->_getParam('at')."' ";
			}
			
			$sql = "Select * from SiteUsers where $where and anAdmin !=1 $filter ";	
			
			$data = $generic->oldSkul($sql);
			$this->paginator($data);	
			
			$this->render("userlist");
		
	}	
	
	public function userdisplayAction() {
		$user = new Model_User;
		$generic = new Model_Generic;
		
		if($_POST) {
			$advdep = str_replace(array('-','+'), '', $_POST['StoreCredits']);
			unset($_POST['StoreCredits']);
			if (is_numeric($advdep) && $advdep != 0) {
				$advdep = (isset($_POST['selAD']) && $_POST['selAD'] == 'add') ? $advdep : $advdep * -1;
				$AdvanceDepositUsageHistoryData = array();
				$AdvanceDepositUsageHistoryData['EventDate'] = date('Y-m-d H:i:s');
				$AdvanceDepositUsageHistoryData['Event'] = 'Advance Deposit';
				$AdvanceDepositUsageHistoryData['Amount'] = $advdep;
				$AdvanceDepositUsageHistoryData['TransactionID'] = 'N/A';
				$AdvanceDepositUsageHistoryData['SiteUserID'] = $_POST['UserID'];
				$generic->insertData('AdvanceDepositUsageHistory', $AdvanceDepositUsageHistoryData);
				
				$_POST['StoreCredits'] = $_POST['StoreCreditsBeforeSubmit'] + $advdep;
			}
			unset($_POST['StoreCreditsBeforeSubmit']);
			unset($_POST['selAD']);

			$user->updateUserInfo($_POST);
			$user_id = $_POST['UserID'];
			$this->_redirect("/admin/userdisplay/ID/".$user_id.'/?sysmsg=success');
		}
		
		
		if($this->_getParam('ID')) {			
			
			$user_id = isset($_POST['UserID']) ? $_POST['UserID'] : $this->_getParam('ID');
			
			$data = $user->oldSkul("select * from SiteUsers left join ResellerApplications on ResellerApplications.SiteUserID = SiteUsers.ID where SiteUsers.ID=".$user_id." and anAdmin != 1");
			
			$this->view->data = isset($data[0]) ? $data[0] : 0;
			$this->view->advanceDepositHistory = $generic->oldSkul("select * from AdvanceDepositUsageHistory where SiteUserID=".$user_id." order by EventDate");
			
		}
		
		$states = $user->getData('Shipping','','State');
		$states_fin = array();	
		foreach ($states as $row) {
			$states_fin[$row['ID']] = $row['State'];
		}
		
		
		$this->view->States = $states_fin;
	}
	
	
	public function productsalesfilterAction() {
		if($_POST){
			$this->_redirect('/admin/productsales/q1/'.$this->_getParam('q1').'/q2/'.$this->_getParam('q2').'/q3/'.$this->_getParam('q3').'/q4/'.$this->_getParam('q4'));
		}
		
	}	
	
	/***
	@todo convert to zend params see product list
	otherwise paging will not work
	***/
	public function productsalesAction() {
		$generic = new Model_Generic;
		
		
		if($this->_getParam('export') == 1) {
			$this->_helper->layout()->setLayout('export');
			$this->view->export = 1;
		}
		
		$limit = " limit 20";
		
		$where = " where 1";
		$having = "";
				
		if($this->_getParam('q1'))  {
			$where = " where t2.Name like '".$this->_getParam('q1')."%'  ";
			$where .= " or t2.Name like '%".$this->_getParam('q1')."%'  ";
		}
		
		if($this->_getParam('q2'))  {
			$where = " where t2.Code like '".$this->_getParam('q2')."%'  ";
		}
		
		if($this->_getParam('q3'))  {
			$having = " having QuantitySold = '".$this->_getParam('q3')."'  ";
		}
		
		if($this->_getParam('q4'))  {
			$where = " where t3.CategoryID = '".$this->_getParam('q4')."'  ";
		}
		
		
		$sql = " select distinct(t1.ProductID) ID, t2.Name ProductName, t2.Code, sum(t1.Quantity) QuantitySold, t4.Name CategoryName, t5.Quantity PresentInventory, t5.Minimum MinimumQuantity, 
		t6.Filename
		from Transactions t1  
		join Products t2 on t2.ID = t1.ProductID  
		left join ProductCategory t3 on t3.ProductID = t2.ID 
		join Category t4 on t4.ID = t3.CategoryID 
		join Store t5 on t5.ProductID = t2.ID
		left join ProductImages t6 on t6.ProductID = t2.ID
		$where
		group by t1.ProductID 
		$having
		order by t1.Created ".$limit;
		
		$data = $generic->oldSkul($sql);
		
		$this->paginator($data);
		
		$this->view->categories = $this->categories();
		
	}
	
	public function productsquickeditAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		if($_POST) {
			//echo json_encode($_POST);
			
			//"pid":"2482","code":"SH037","name":"COMFY CAT AND DOG MISMATCH ECONOMIC SHOES","qty":"4984","minqty":"4500","price":"399","dscprice":"299"
			$generic = new Model_Generic();
			$generic->oldSkul("update Products set Name = '".$_POST['name']."', Code='".$_POST['code']."' where ID = ".$_POST['pid'], false);
			
			$generic->oldSkul("update Store set Quantity = '".$_POST['qty']."', Minimum='".$_POST['minqty']."' where ProductID = ".$_POST['pid'], false);
			
			$generic->oldSkul("update Prices set Price = '".$_POST['price']."', WholePrice='".$_POST['whlprice']."' where ProductID = ".$_POST['pid'], false);
			
			$r = array('result'=>'success');
		}else {
			$r = array('result'=>'failed');
		}
		
		echo json_encode($r); //should be echo
	}
	
	
	public function paymentMethods() {		
		$methods =  array(1=>'GCASH',2=>'BDO DEPOSIT',3=>'BPI DEPOSIT',4=>'LBC',5=>'ADV DEPOSIT') ;		
		return $methods;
	}
	

	public function reportsAction() {
		
		$generic = new Model_Generic;
		
		$type = $this->_getParam('type');
		
		$data = "";
		if($type == "ubr")
		{
			$data = $generic->oldSkul("select count(distinct(Referrer)) count, Referrer from SiteUsers 
			where Referrer != ''
			group by Referrer");
		}

		if($type == "prmi")
		{
		
			//echo '<pre>'; print_r($_GET);
			/***
			Array
(
    [q1] => 
    [q2] => 
    [qltogt] => >
    [Quantity] => 100
    [mltogt] => 
    [Minimum] => 
)
			***/
			//exit;
			
			$where = " where t5.Quantity <= `Minimum` ";
			
			if(isset($_GET['q1'])  )
			{
				if(!empty($_GET['q1']) )
				{
				$where = " where t2.Name like '".  $_GET['q1']."%' ";
				}
			}
			
			if(isset($_GET['q2'])  )
			{
				if(!empty($_GET['q2']) )
				{
				$where = " where t2.Code = '".  $_GET['q2']."' ";
				}
			}
			
			
			if(isset($_GET['qltogt']) &&  isset($_GET['Quantity']) )
			{
				if(!empty($_GET['qltogt'])  && !empty($_GET['Quantity']) )
				{
				$where = " where t5.Quantity ".  $_GET['qltogt']." ".$_GET['Quantity'];
				}
			}
		
			if(isset($_GET['mltogt']) &&  isset($_GET['Minimum']) )
			{
				if(!empty($_GET['mltogt'])  && !empty($_GET['Minimum']) )
				{
					$where = " where t5.Minimum ".  $_GET['mltogt']." ".$_GET['Minimum'];
				}
			}
			
			//echo $where; 
		
			$sql="select distinct(t1.ProductID) ID, t2.Name ProductName, t2.Code, t1.Quantity QuantitySold, t4.Name CategoryName, t5.Quantity PresentInventory, t5.Minimum MinimumQuantity, 
		t6.Filename
		from Transactions t1  
		join Products t2 on t2.ID = t1.ProductID  
		left join ProductCategory t3 on t3.ProductID = t2.ID 
		join Category t4 on t4.ID = t3.CategoryID 
		join Store t5 on t5.ProductID = t2.ID
		left join ProductImages t6 on t6.ProductID = t2.ID
		$where
		group by t1.ProductID order by t1.Created";
		
			$data = $generic->oldSkul($sql);
		}
		
		$this->paginator($data);
		$this->render("reports".$type);
	}

	
	private function categories() {
		$generic = new Model_Generic;
		return $generic->getData('Category','','Name');
	}

	
	
	
}	