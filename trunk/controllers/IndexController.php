<?php

class IndexController extends Zend_Controller_Action {
    
	public function init() {
		parent::init();
		
		$site = new Model_Site();
		$page = new Model_Page();
		
		$categories = new Model_Categories();
		$products = new Model_Products();
		
		
		
		$all_menus = $site->AllMenus();
		foreach($all_menus as $menu_id => $menu ) {
			$menu_key = 'menu_'.$menu_id;
			$this->view->$menu_key = $menu;
		}
		$this->view->category_menu =  $site->categoryMenu();
		
		$categoryTree = $categories->listCategoryTree();
		$this->view->categoryTree = $categoryTree;
		
		$this->view->widget_blurb = $site->widget_blurb(); //@todo to be removed soon
		$this->view->categories = $page->displayCategories(null,12);
		$this->view->Brands = $site->ListBrands();
		
		$this->view->best_sellers = $products->bestSeller();
		
		$this->view->featured_products = $products->featuredProducts();
    }
    	
	public function indexAction() {
		$this->_helper->layout()->setLayout('redesign-2014-index');	
		$site = new Model_Site();
		
		$banners = new Model_Banners();
		
		$home_page = $site->getData("Pages","PageType='Home'");
		
		$home_content = '';
		if($home_page) {
			$home_content = $home_page[0]; 
		}
		
		$this->view->banners = $banners->listBannersByTag();
		$this->view->home_content = $home_content;
	}
	
	public function brandsAction() {
		
		$products = new Model_Products;
		$site = new Model_Site;
		
		$brand_id = $this->_getParam('bid');
		
		$this->view->items = $products->ListProducts("","",$brand_id); //where 2 is the category id
	}

 	public function categoryAction() {
		$this->_helper->layout()->setLayout('redesign-2014-page');
		
		$products = new Model_Products;
		$site = new Model_Site;
		$categories = new Model_Categories;
		
		
		
		$category_id = $this->_getParam('cid');
		
		//build category tree sidebar items
		$explode_category_id = explode("-",$category_id);
		$parent_category_name = $categories->getParentCategory($explode_category_id[0].'-0-0');
		$subParents = $categories->getSubParents($explode_category_id[0].'-0-0'); //getSubParents
		
		foreach ($subParents as $subparent_key => $subparent_value) 
		{
			$subparent_value['thirdLevel'] = $categories->getCategoryChildren($explode_category_id[0].'-0-0', $subparent_value['SubParentID']);
			$category_tree[$subparent_key] = $subparent_value;
		}
		$this->view->category_tree = $category_tree;		
		
		if($explode_category_id[1] != '0')
		{
			$this->view->is_child_category = 1;
		}
		
		$this->view->active_category = $explode_category_id[0].'-0-0';
		
		//$parent_category_name = 'category '.strtolower($parent_category_name[0]['Name']); //used as body class
		$parent_category_name = strtolower($parent_category_name[0]['Name']); //used as body class
		$this->view->parent_category_name = $parent_category_name;				
		$this->view->site_title = $parent_category_name;
		
		$this->view->pagetype = 'category';
		
		$items = ($explode_category_id[1] != '0') ? $this->paginator($products->ListProducts("",$category_id, null, null, 0) )  : $products->ListProducts("",$category_id); //where 2 is the category id
		
		
		$this->view->items = $items;
		
		
		$banners = new Model_Banners();
		$this->view->banners = $banners->listBannersByTag($parent_category_name); 
		
		$this->render('brands');
	} 

	public function displayAction() {
	
		$this->_helper->layout()->setLayout('page');
	
		$products = new Model_Products;
		
		$product_id = $this->_getParam('pid');
		$this->view->data = $products->ListProducts($product_id);
		$this->view->images = $products->DisplayProductImages($product_id);
		
		$this->view->product_categories = $products->DisplayProductCategories($product_id);

		$this->render('product'); 
	}
	
	public function producturlAction() {
	
		$this->_helper->layout()->setLayout('page');
	
		$products = new Model_Products;
		
		$product_id = $this->_getParam('pid');
		$this->view->data = $products->ListProducts($product_id);
		$this->view->images = $products->DisplayProductImages($product_id);
		
		$this->view->product_categories = $products->DisplayProductCategories($product_id);

		$this->render('product'); 
	}	
	
	public function loginAction() {
	
		$this->_helper->layout()->setLayout('redesign-2014');
		
		$users = new Model_User();		
		$authsession = new Zend_Session_Namespace('authsession');

		
		
		$this->view->referer = (isset($_GET['ref'])) ? $_GET['ref'] : '';
		
		$validate = '';
		
		if($_POST) {
			$user = isset($_POST['user']) ? $_POST['user'] : '';
			$pass = isset( $_POST['pass'] ) ? md5($_POST['pass']) : ''; 
			
			$validate = $users->validate($user,$pass);	
			
		}
		$referer = (isset($_POST['referer'])) ? $_POST['referer'] : 'profile';
		

		if( isset($authsession->logged_admin ) ){			
			$this->_redirect('/admin/settings/');
		} 
		elseif(isset($authsession->logged_user)){
			$this->_redirect('/'.$referer);
		}		
		$this->view->loggedUser = $validate;			
		
	}
	
	public function logoutAction() {
		$this->_helper->layout()->setLayout('page');
	
		$authsession = new Zend_Session_Namespace('authsession');
		
		if(isset($authsession->logged_user) || isset($authsession->logged_admin)){
			unset($authsession->logged_user);	
			unset($authsession->logged_admin);	
		}
		
		//clear the cart
		$shop = new Model_Shop();
		$shop->emptyCart();
		
		$this->render('login');	
	}
	
	public function resetAction() {
		$this->_helper->layout()->setLayout('page');
		$email = $this->_getParam('UserName');
		
		if($_POST) {
			$reset = $this->_getParam('ResetCode');
			if($reset  && $email) {
				
				$generic = new Model_Generic();
				$check = $generic->oldSkul("select * from SiteUsers where SiteEmail = '".$email."'  and ResetCode= '".$reset."' ");
				if($check){
					$new_password = Model_Transaction::randomizer();
					$generic->oldSkul("update SiteUsers set SitePassword = md5('".$new_password."'), ResetCode = '' where SiteEmail='".$email."' ",false);
					$this->view->new_password = $new_password;
				}
			}
			$this->view->msg = 'A confirmation link has been sent to your email address before you can reset your password.';
			$reset_code =  base64_encode( Model_Transaction::randomizer() );
			
			$mailer = new Model_Mailer();
			$mailer->sendResetPasswordCode($email,$reset_code);
		}
		
		if($this->_getParam('msg')) {
				$this->view->msg = 'Enter your reset code below';
		}
		
		//echo Model_Transaction::randomizer();
		
	}
	

	public function adminAction() {		
		$this->_helper->layout()->setLayout('admin');		
	}
	
	public function buyAction() {
	
		if($_POST) {
			
			$shop = new Model_Shop();
			
			$product_id = isset($_POST['pid']) ? $_POST['pid'] : 0;
			$quantity = isset($_POST['Quantity']) ? $_POST['Quantity'] : 0;
			
			$attributes = array();
			foreach ($_POST as $key => $val) {
				if( strpos($key, 'Attribute_') !== false ) {
					$attributes[str_replace("Attribute_","",$key)] =  $val;
				}
			}
			
			$shop->addtoCart($product_id,$quantity,$attributes);
		}
		$this->_redirect('/index/cart');
	}
	
	public function cartAction() {
	
		$this->_helper->layout()->setLayout('redesign-2014');
		$this->view->cart_page = 1; //@todo replace iwth pagetype
		$this->view->pagetype = 'cart';
		
		$shop = new Model_Shop();
		$product = new Model_Products();
		$generic = new Model_Generic();
		
		$login_info = $this->isLoggedIn();
		$this->view->loginInfo = $login_info;		
		
		$cookie_items = isset($_COOKIE['cart_items']) ? $_COOKIE['cart_items'] : array() ;			
		$items = $shop->listCartItems($cookie_items);		
				
		foreach ($items as $row) {
			$images[$row['ProductID']] =  $product->DisplayProductImages($row['ProductID']);
		}		
		
		if(isset($images)) {
			$this->view->images = $images;
		}
		
		$this->view->itemcount = count($items);
		$this->view->items = $items;
		
		if($_POST) {
			// Empty the Basket
			if(isset($_POST['emptycart'])) {
				$shop->emptyCart();
				$this->_redirect('/cart/');
			}
			// Refresh Cart
			if(isset($_POST['refreshcart'])) {
				
				$products = new Model_Products();				
				$updated_quantities = $products->CheckInventory($_POST['Quantity']);//validation for item quantities
				
				//$this->view->updated_quantities = $updated_quantities;					
				$this->_redirect('/cart/'); //this is done to refresh the cart items
			}
			// Checkout
			if(isset($_POST['checkout'])) {
				
				$this->view->showCheckOutForm = 1;
				//$this->view->updated_quantities = $_POST['Quantity'] ;
				if(isset($_POST['Quantity'])) {
					$products = new Model_Products();
					$products->CheckInventory($_POST['Quantity']);
					$this->view->updated_quantities = $_POST['Quantity'] ;
				}				

				// Member Checkout	
				if($this->isLoggedIn() ) {
					$user_details = $this->isLoggedIn();
					$this->view->isLoggedIn = 1; 
					$this->view->User = $user_details;
					$this->view->states = $shop->shippingTable();
					$this->view->shipping_rate = $generic->getData("Shipping","","State asc");
					
					if(isset($_POST['StoreCredits'])) {
						$this->view->store_credits_to_use = $_POST['StoreCredits'];
						if($_POST['StoreCredits'] > $user_details['StoreCredits']) {
							$this->view->StoreCreditError = 'Error!  You have entered more than your available store credits.  ';
							$this->view->store_credits_to_use = 0;
						}
					
					}

					// Member Transaction
					if(isset($_POST['memcheckout'])) {
						//echo '<pre>'; print_r($_POST);exit;
						$state = isset($_POST['State']) ? $_POST['State'] : 0;
						
						$shipping_details = $generic->getData("Shipping","ID=".$state);						
						//$shipping_fee = isset($shipping_details[0]['Fee']) ? $shipping_details[0]['Fee'] : 0;
												
						
						//unset($_POST['ShippingFee']);
						
						$data = $_POST;
						$data['ShippingFee'] = $_POST['ShippingFee'];
						
						$Transaction = new Model_Transaction();
						$process = $Transaction->AddMemberTransaction($data,$user_details);

						if($process) {
							$shop->emptyCart();
							$trackingid = $process;
							
							$this->_redirect('/thankyou/?esig1234='.$user_details['SiteEmail'].'&trackingID='.$trackingid);
						}				
					}
					else {
						$this->render('cartuser');
					}
				}
				
				// Create Transaction 
				if(isset($_POST['excheckout'])) {
					 
					$Transaction = new Model_Transaction();
					$process = $Transaction->Add($_POST);
					
					if($process) {
						$shop->emptyCart();
						//$this->view->trackingid = $_POST['excheckout'];
						$this->view->trackingid = $process;
						$this->view->email = $_POST['Email'];
						$this->render('confirm');
					}  //else {
						//$this->render('confirm'); //@todo why is this here?
					//}
				}		
				
			}			
		}
	}
	
	public function thankyouAction() {
		$this->_helper->layout()->setLayout('redesign-2014');
		
		$TransID =   $this->_getParam('trackingID') ;
		$Email =   $this->_getParam('esig1234') ; //honeypot
				
		if($TransID && $Email) {
			$this->view->trackingid = $TransID;
			$this->view->email = $Email;
		}		
	}
	
	public function cartremoveAction() {
		if($this->_getParam('rm')) {
			$product_id = $this->_getParam('rm');
			$shop = new Model_Shop();
			$shop->removefromCart($product_id);
			
			
		}
		$this->_redirect('/cart/');
	}
	
	//function to display logins and other static content and the like
	public function staticsAction() {
		
		$this->_helper->layout()->setLayout('static');
		//user
		$authsession = new Zend_Session_Namespace('authsession');		

		//cart
		$shop = new Model_Shop();		
		$tmp_items = isset($_COOKIE['cart_items']) ? $_COOKIE['cart_items'] : array() ;		
		$listCartItems = $shop->listCartItems($tmp_items);
		
		$items = array();
		$item_amount_total = 0;
		if($listCartItems> 0) {
			foreach ($listCartItems as $item) {
				$cart_item_qty = isset($_COOKIE['cart_item_qty']) && isset($_COOKIE['cart_item_qty'][$item['ProductID']]) ? $_COOKIE['cart_item_qty'][$item['ProductID']] : 1 ;
				
				if($cart_item_qty >= 6) {
					$item_total = $item['DiscPrice'] * $cart_item_qty ;
				} else {
					$item_total = $item['Price'] * $cart_item_qty ;
				}
				$items[] = $item_total;
			}
			$item_amount_total = number_format(array_sum($items),2);
		}
		
		$item_count = (isset($items) )? count($items) : '0';
				
		
		//final data
		$user = isset($authsession->logged_user) ? $authsession->logged_user : 'Login';
		$user = isset($authsession->logged_admin) ? $authsession->logged_admin : $user;
		
		$statics = array( //"logged_admin"=> $authsession->logged_admin,
			"logged_user"=>$user,
			"cart_item_count"=>$item_count,
			"cart_items"=>$items,
			"cart_total" => $item_amount_total,
			"moolah"=>1,
			);
		$this->view->data = json_encode($statics);
	}
	
	public function searchAction() {
	
		$this->_helper->layout()->setLayout('redesign-2014');
	
		$products = new Model_Products;
		$string = strip_tags(trim($this->_getParam('qu')));
		
		$data = $products->Search($string); //sanitation is also done on the model
		
		$this->view->querystring = $string;
		
		if(isset($string) && !empty($data)){
			$this->view->resultscount = count($data);
			$this->view->resultsfound = 1;
			$this->view->data = $data;
		} elseif ( isset($string) && empty($data)) {
			$this->view->noresults = 1;			
		}
				
	}
	
	public function signupAction() {
	
		$this->_helper->layout()->setLayout('redesign-2014');
	
		//redirect logged in users
		if ($this->isLoggedIn() ) {
			$this->_redirect('/profile/');	
		}
	
		$this->view->msg = '';
		$this->view->success = 0;
		
		$generic = new Model_Generic();
		
		$this->view->states = $generic->getData("Shipping","","State asc");
		
		if($_POST) {
			$user = new Model_User();
			$new_user = $user->registerUser($_POST);
			
			if(!is_array($new_user) && $new_user > 0) {
				$this->view->success = 1;
				$this->view->msg = 'Thank you for signing up. You can now <a href="/login/">login</a> as '.$_POST['SiteUsername'];
			}
			else {
				foreach ($_POST as $field => $val) {
					$this->view->$field = $val;
				}
				$this->view->msg = $new_user['msg'];
			}
		}
	} 
	
	public function profileAction() {
		$this->_helper->layout()->setLayout('page');
		
		if(!$this->isLoggedIn()) {
			$this->_redirect('/login/');
		}

		$authsession = new Zend_Session_Namespace('authsession');
		$logged_user = isset($authsession->logged_user) ? $authsession->logged_user : '';
				
		$user = new Model_User();
		$user_data = $user->details($logged_user);
		
		$this->view->SiteUsername = $user_data['SiteUsername'];
		$this->view->SiteEmail = $user_data['SiteEmail'];
		$this->view->account_type = $user_data['Type'];
		
		$this->view->states = $user->getData("Shipping","","State asc");
		
		unset($user_data['SitePassword'],$user_data['anAdmin'],$user_data['Type'],$user_data['SiteEmail']);

		if($_POST) {
			$user->updateUserInfo($_POST);
			$this->_redirect('/profile/');
		}
		
		$msg = isset($_GET['msg']) ?  $_GET['msg'] : '';
		$this->view->msg = $msg;		
		$this->view->data = $user_data;	
		
		$reseller_application_data = $user->getData("ResellerApplications","SiteUserID=".$user_data['ID']);		
		$reseller_application = ($reseller_application_data) ? $reseller_application_data[0] : '';
		 
		$this->view->reseller_application = $reseller_application;
	}
	
	public function myordersAction() {
		$this->_helper->layout()->setLayout('page');
		$user = $this->isLoggedIn();
		
		if (!$user) {
			$this->_redirect('/login/');
		}
		
		$transactions = new Model_Transaction();
		$this->view->data = $transactions->getUserTransactions($user['ID']);
		
		
	}

	public function paynowAction() {
		
		$this->_helper->layout()->setLayout('page');
		$user = $this->isLoggedIn();		
		if (!$user) {
			$this->_redirect('/login/');
		}
		
		$transaction_id = ($this->_getParam('TransactionID')) ? $this->_getParam('TransactionID') : 0;
		$transactions = new Model_Transaction();
			
		if ($transaction_id) {
			$data = $transactions->getUserTransaction($transaction_id,$user['SiteEmail']);
		} else {
			$this->_redirect('/myorders/');
		}
				
		if(!$data) {
			$this->_redirect('/myorders/');
		}
		
		$products = new Model_Products;
		$products_data = array();
		foreach ($data as $row) {
			$products_data[$row['ProductID']] = $products->ListProducts($row['ProductID']);
		}
		//echo '<pre>'; print_r($products_data); exit;
		$this->view->products_data = $products_data;
		
		
		$payment_exists = $transactions->getData("Payments t1","t1.TransactionID=".$transaction_id,"","","Transactions t2 on t2.ID = t1.TransactionID");
		if($payment_exists) {
			$this->view->payment_exists = 1;
			$this->view->payment_data = $payment_exists;
		}			
		
		$rawStates = $transactions->getData("Shipping","","State asc");
		foreach($rawStates as $row) {
			$states[$row['ID']] = $row['State'];
		}
		$this->view->states = $states;
		
		$this->view->payment_methods = $this->paymentMethods();
		$this->view->data = $data;
		$this->view->transaction_id = $transaction_id;
		$this->view->paynow_page = 1;
	}

	public function receiptimageAction() {

		$authsession = new Zend_Session_Namespace('authsession');
		if (!isset($authsession->logged_admin)) {
			$user = $this->isLoggedIn();
			if (!$user) {
				$this->_redirect('/login/');
			}
		}

		if ($this->_getParam('file') != '') {
			$file = $this->_getParam('thumbs') == 'yes' ? UPLOADS_PATH . 'receipts/thumbs/th_' . $this->_getParam('file') : UPLOADS_PATH . 'receipts/' . $this->_getParam('file');
			if (file_exists($file)) {
				header('Content-Type: image/jpeg');
				readfile($file);
			} else {
				header("HTTP/1.0 404 Not Found");
			}
		} else {
			header("HTTP/1.0 404 Not Found");
		}
		exit();
	}

	public function completepaymentAction() {
	
		$user = $this->isLoggedIn();
		if (!$user) {
			$this->_redirect('/login/');
		}
	
		$transaction_id = $this->_getParam('TransactionID');
		if ($transaction_id) {
			$transactions = new Model_Transaction();

			if(!empty($_FILES) && $_FILES["freceipt"]["error"] == 0) {
				
				if (!is_dir(UPLOADS_PATH . 'receipts')) {
					mkdir(UPLOADS_PATH . 'receipts');
					if (!is_dir(UPLOADS_PATH . 'receipts/thumbs')) {
						mkdir(UPLOADS_PATH . 'receipts/thumbs');
					}
				}

				$arrImgType = explode('/', $_FILES["freceipt"]["type"]);
				$imgType = isset($arrImgType[1]) && !empty($arrImgType[1]) ? $arrImgType[1] : 'jpg';
				$md5FileName = md5($_FILES["freceipt"]["name"]) . uniqid() . '.' . $imgType;

				if (file_exists( UPLOADS_PATH . 'receipts/' . $md5FileName ) ) {
					unlink(UPLOADS_PATH . 'receipts/' . $md5FileName);
				}
				if ( file_exists( UPLOADS_PATH . 'receipts/thumbs/th_' . $md5FileName ) ) {
					unlink(UPLOADS_PATH . 'receipts/thumbs/th_' . $md5FileName);
				}

				if ( move_uploaded_file( $_FILES["freceipt"]["tmp_name"], UPLOADS_PATH . 'receipts/' . $md5FileName ) ) {
					$image = new Model_Image();
					$image->load( UPLOADS_PATH . 'receipts/' . $md5FileName );
					$image->resizeToWidth(130);
					$image->save( UPLOADS_PATH . 'receipts/thumbs/th_' . $md5FileName );
				}
			}

			$_POST['receiptFile'] = $md5FileName;	
			$_POST['UserID'] = $user['ID'];
	
			$PaymentTime = isset($_POST['PaymentTimeHour']) && isset($_POST['PaymentTimeMinute']) ? $_POST['PaymentTimeHour'] . ':' . $_POST['PaymentTimeMinute'] . ':00' : '00:00:00';
			$_POST['PaymentDate'] = $_POST['PaymentDate'] . ' ' . $PaymentTime;
	
			$data = $transactions->payTransaction($transaction_id,$_POST);
		}
		$this->_redirect('/index/myorders');	
	}
	
	public function passwordchangeAction() {
	
		if($_POST) {
			$generic = new Model_Generic();
			$sql = "select * from SiteUsers where SitePassword='".md5($_POST['OldPassword'])."' and SiteEmail='".$_POST['UserEmail']."'";
			$user_data = $generic->oldSkul($sql);
			if($user_data) {
				//print_r($_POST);
				//echo strlen($_POST['NewPassword1']); exit;
				if ( strlen($_POST['NewPassword1']) > 7 ) {
					if( ($_POST['NewPassword1'] == $_POST['NewPassword2'])) {
						
						$generic->oldSkul("update SiteUsers set SitePassword='".md5($_POST['NewPassword1'])."' where SiteEmail='".$_POST['UserEmail']."'",false);
						$this->_redirect('/profile/?msg=password changed');	
					} else {
						$this->_redirect("/profile/?msg=error updating password");
					}
				}				
			} else {
				$this->_redirect("/profile/?msg=error updating password!");
			}
		}
		$this->_redirect('/profile/');	
	}		

	public function reselleryapplyAction() {
	
		$user = $this->isLoggedIn();
		
		if($user && $_POST) {
			$generic = new Model_Generic;
			
			$generic->oldSkul("delete from ResellerApplications where SiteUserID = ".$user['ID'],false);
			
			$Require1 = 0;
			$Require2 = 0;
			$Require3 = 0;
			if(isset($_POST['Require1'])) {
				$Require1 = 1;
			}
			if(isset($_POST['Require2'])) {
				$Require2 = 1;
			}
			if(isset($_POST['Require3'])) {
				$Require3 = 1;
			}
			
			$sql = "insert into ResellerApplications values(".$user['ID'].",".$Require1.",".$Require2.",".$Require3.")";			
			$generic->oldSkul($sql,false);			
		}
		$this->_redirect('/profile/');
	}
	
	public function trackingAction() {
	
		$this->_helper->layout()->setLayout('page');
		
		$Transaction = new Model_Transaction();	
		$TransID =   $this->_getParam('trackingID') ;
		$Email =   $this->_getParam('esig1234') ; //honeypot
		
		$data = array();
		if($TransID && $Email) {
			$data = $Transaction->getTransaction($TransID,$Email);
			$this->view->status_list =	$Transaction->transactionStatus();
			$this->view->msg = '';
			if(empty($data)) {
				$this->view->msg = "Nothing found enter a valid email address and tracking id.";
			}
		}
		
		$this->view->data = $data;
	}
		
	public function contactAction() {
		$site = new Model_Site();	
		$contact_page = $site->getData("Pages","PageType='Contact'");
				
		$contact_url = $contact_page[0]['URLSegment'];
		
		if($_POST) {			
					
			$save = $site->saveInquiry($_POST) ;
			
			if(!is_array($save)) {
				$this->_helper->layout()->setLayout('page');
				$this->view->inquirySaved = 1;			
				$this->render('thankyou');	
				//$this->_redirect($contact_url);
			} else {				
				
				if($contact_page) {					
					$contact_url = $contact_url.'?msg=Missing Fields';
				}
				else {
					$contact_url = '/';
				}				
				$this->_redirect($contact_url);
			}
		}
		
		$this->render('page');
	}
	
	private function isLoggedIn() {
	
		$users = new Model_User();		
		$authsession = new Zend_Session_Namespace('authsession');

		if(isset($authsession->logged_user) ){
			//get user information
			return $users->details($authsession->logged_user);
			//return $authsession->logged_user;
		} 
		return false;
	
	}
	
	public function paymentMethods() {		
		$methods =  array(1=>'GCASH',2=>'BDO DEPOSIT',3=>'BPI DEPOSIT',4=>'LBC',5=>'ADV DEPOSIT') ;		
		return $methods;
	}
	
	public function paginator($list) {
	
		$paginator = Zend_Paginator::factory($list);
		$curPage=$this->_getParam('page',1);
		$paginator->setItemCountPerPage(8);
		$paginator->setCurrentPageNumber($curPage);
		$this->view->list = $paginator;	
	}
	
	public function testAction() {

		$mail = new Model_PHPMailer();
		$mail->Host       = "retail.smtp.com"; // SMTP server

		//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
		// 1 = errors and messages
		// 2 = messages only
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		
		$mail->Port       = 2525;                    // set the SMTP port for the GMAIL server
		$mail->Username   = "masterje@gmail.com"; // SMTP account username
		$mail->Password   = "63cd51ef";        // SMTP account password
			
			
		$mail->SetFrom('customerservice@eazyfashion.com', 'EazyFashion');
		//$mail->AddReplyTo("name@yourdomain.com","First Last");
		$mail->Subject    = "PHPMailer Test Subject via smtp, basic with authentication";
			
		$mail->AddAddress('darker_schneider@yahoo.com', "Jay Fajardo");
			
		$body = 'test broadcast';
		$mail->MsgHTML($body);
		
		if(!$mail->Send()) 
		{
			echo "Mailer Error: " . $mail->ErrorInfo;
		}
		else 
		{
			echo "Message sent!";
		}
		exit;		
	}	
	
}
