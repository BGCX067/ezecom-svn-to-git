<?php

//Collection of static and basic functions

class Model_Transaction extends Model_Generic {

	public function Add($data,$user_details) {
		
		$Product = new Model_Products();

		if(!$data['Product']) {
			//return error
		}
		
		//$transid = $data['excheckout']; //@todo validate this		
		$transid = 	$this->randomizer();	//@todo validate this	
		$UserID = $data['UserID'];
		$Email = $data['Email'];	
		$Firstname = $data['Firstname'];
		$Lastname = $data['Lastname'];
		$Phone = $data['Phone'];
		$ShippingAddress = $data['ShippingAddress'];
		$DropShipAddress = $data['DropShipAddress'];
		$ShippingFee = ($data['ShippingFee'] > 0) ?  $data['ShippingFee'] : 0;
		$PaymentMethod = $data['PaymentMethod'];
		$Attribute = isset($data['Attribute']) ? $data['Attribute'] : '' ;
		$StoreCreditsUsed = isset($data['StoreCreditsUsed']) ? $data['StoreCreditsUsed'] : 0 ;
		
	
		$errors = array();		
		$transaction_data = array();
		
		
		$pricing = ($user_details['Type'] == 'Reseller') ? 'WholePrice' : 'Price';
		
		// we do this to check if quanity is >= 6
		foreach ($data['Product'] as $row => $val) {
			$tmp_ttl_qty[] = $val;
		}
		$ttl_qty = array_sum($tmp_ttl_qty);
	
		#update TransactionSequenceCount
                $this->oldSkul("update SiteUsers set TransactionSequenceCount = TransactionSequenceCount + 1 where ID = $UserID", false);
				
                $TransactionSequenceCount = $this->db->fetchAll("select TransactionSequenceCount from SiteUsers where ID = $UserID");
				
				
                if (isset($TransactionSequenceCount[0]['TransactionSequenceCount'])) {
					if ( $TransactionSequenceCount[0]['TransactionSequenceCount'] == '0' ) {
							$NumberOfOrder = '';
					} else if ( $TransactionSequenceCount[0]['TransactionSequenceCount'] == '1' ) {
							$NumberOfOrder = $TransactionSequenceCount[0]['TransactionSequenceCount'] . 'st Order';
					} else if ( $TransactionSequenceCount[0]['TransactionSequenceCount'] == '2' ) {
							$NumberOfOrder = $TransactionSequenceCount[0]['TransactionSequenceCount'] . 'nd Order';
					} else if ( $TransactionSequenceCount[0]['TransactionSequenceCount'] == '3' ) {
							$NumberOfOrder = $TransactionSequenceCount[0]['TransactionSequenceCount'] . 'rd Order';
					} else {
							$NumberOfOrder = $TransactionSequenceCount[0]['TransactionSequenceCount'] . 'th Order';
					}
                    $transaction_data['NumberOfOrder'] = $NumberOfOrder;
                }
		
		foreach ($data['Product'] as $row => $val) {
			$ProductDetails = $Product->ListProducts($row);
		
			$transaction_data['ID'] = $transid;
			$transaction_data['Created'] = date('Y-m-d H:i:s');
			$transaction_data['LastEdited'] = date('Y-m-d H:i:s');
			$transaction_data['ProductID'] = $row;
			$transaction_data['Quantity'] = $val;
			$transaction_data['Attribute'] = $Attribute;
			
			
			if($ttl_qty >= 6) { //$ttl_qty
				$pricing = 'WholePrice'; // override pricing as well
				$Price = $ProductDetails['WholePrice'];
			} else {
				$Price = $ProductDetails[$pricing];
			}
			
			$SubTotal = $Price * $val;
			
			$tmp_TransactionTotal[] = $SubTotal;
			//@todo move to later part, update after write instead
			$TransactionTotal = array_sum($tmp_TransactionTotal) + $ShippingFee;
			$transaction_data['TransactionTotal'] = $TransactionTotal; //@todo validate calculation
			
			$transaction_data['SubTotal'] = $SubTotal;
			
			$transaction_data['QuantityTotal'] = $ttl_qty;
			
			$transaction_data['Pricing'] = $pricing;
			
			
			$transaction_data['UserID'] = $UserID;
			$transaction_data['Firstname'] = $Firstname;
			$transaction_data['Lastname'] = $Lastname;
			$transaction_data['Email'] = $Email;
			
			$transaction_data['Region'] = $data['Region'];
			$transaction_data['ShippingAddress'] = $ShippingAddress;
			$transaction_data['DropShipAddress'] = $DropShipAddress;
			$transaction_data['ShippingFee'] = $ShippingFee;
			
			$transaction_data['Phone'] = $Phone;
			
			$transaction_data['PaymentDue'] = $data['PaymentDue'];
			$transaction_data['SpecialInstructions'] = $data['SpecialInstructions'];
			$transaction_data['StoreCreditsUsed'] = $StoreCreditsUsed;
			
			$transaction_data['PaymentMethod'] = $PaymentMethod;
			$transaction_data['Status'] = 1; // 1 - pending; 2 - processing , 3 - shipped
			$transaction_data['isNew'] = 1;
			
			$this->oldSkul("update Store set Quantity = (Quantity - $val) where ProductID=".$row,false);
			
			//set flag that qualifies as a reseller
			if($ttl_qty >= 6) {
				$this->oldSkul("update SiteUsers set ResellerQualify = 1 where ID = $UserID", false);
			}
			
			$this->insertData("Transactions", $transaction_data);			
			
		}
		
			//write transaction total after for loop
			$TransactionTotal = (array_sum($tmp_TransactionTotal) + $ShippingFee) - $StoreCreditsUsed;
			
			//print_r ( $TransactionTotal); exit;
			$this->oldSkul("update Transactions set TransactionTotal = '".$TransactionTotal."' where ID=".$transid, false);
		
		
	
		#update StoreCredits
		if ($StoreCreditsUsed > 0) {
			$this->oldSkul("update SiteUsers set StoreCredits = StoreCredits - $StoreCreditsUsed where ID = $UserID", false);
			$AdvanceDepositUsageHistoryData = array();
			$AdvanceDepositUsageHistoryData['EventDate'] = date('Y-m-d H:i:s');
			$AdvanceDepositUsageHistoryData['Event'] = 'Payment Transaction';
			$AdvanceDepositUsageHistoryData['Amount'] = ($StoreCreditsUsed * -1);
			$AdvanceDepositUsageHistoryData['TransactionID'] = $transid;
			$AdvanceDepositUsageHistoryData['SiteUserID'] = $UserID;
			$this->insertData('AdvanceDepositUsageHistory', $AdvanceDepositUsageHistoryData);
		}
	
		//@todo the email needs to be looped and sent separately to recipients		
		$mail_data = $this->getTransaction($transid,$Email);
		
		$mailer = new Model_Mailer();
		$mailer->sendBuyItemConfirmation($mail_data);
		
		return $transid;
		
		
	}
	
	public function AddMemberTransaction($data,$user_details) {

		$transid = $data['memcheckout']; //@todo validate this  //excheckout
		$UserID = $user_details['ID'];	
		$Email = $user_details['SiteEmail'];	
		$Firstname = $user_details['Firstname'];
		$Lastname = $user_details['Lastname'];
		$Phone = isset($user_details['Phone1']) ?  $user_details['Phone1'] : $data['Phone1'];
		$ShippingAddress = isset($data['ShippingAddress']) ? $data['ShippingAddress'] : $user_details['Address'].' '.$user_details['City'].' '.$user_details['Country'].' '.$user_details['Zip'] ;
		$PaymentMethod = $data['PaymentMethod'];

		//process overrieds
		$products = $data['Product'];
		$purchase = array();
		$purchase_qty = array();
		
		foreach ($products as $id => $qty) {
			$purchase_qty[] = $qty;
		}
		$Pricing = (array_sum($purchase_qty) >= 6 ) ? 'WholePrice' : 'Price' ;
		
		
		foreach ($products as $id => $qty) {
			$product = new Model_Products;
			$product_details = $product->ListProducts($id);
			$purchase[] = $product_details[$Pricing] * $qty;
			
		}
		
		$Total = array_sum($purchase);
		
		//$Products = $data['Product'];
		$ShippingFee = ($Total < 1000) ? $data['ShippingFee'] : 0;
		
		$Total = array_sum($purchase) +  $ShippingFee;
		$Total_qty = array_sum($purchase_qty);
				
		$member_data = array('excheckout'=>$transid,
			'UserID' => $UserID,
			'Email' => $Email,
			'Firstname' => $Firstname,
			'Lastname' => $Lastname,
			'Phone' => $Phone,
			'ShippingAddress' => $ShippingAddress,
			'DropShipAddress' => $data['DropShipAddress'],
			'Region' => $data['State'],
			'ShippingFee' => $ShippingFee,
			'PaymentMethod' => $PaymentMethod,
			'Product' => $products,
			'Total' => $Total,
			'PaymentDue' => $data['PaymentDue'],
			'SpecialInstructions' => $data['SpecialInstructions'],
			'StoreCreditsUsed' => $data['StoreCreditsUsed'],
		);
		
		$process = $this->Add($member_data,$user_details);
	
		return $process;
	}
	
	public function updateStatus($data) {
	
		$transaction_id = isset($data['TransactionID']) ? $data['TransactionID'] : '';
		if(!$transaction_id) {
			return false;
		}
		
		$shipping_code = isset($data['ShippingCode']) ? $data['ShippingCode'] : '';
		
		
	
		$payment_status =  (isset($data['PaymentStatus'])) ? $data['PaymentStatus'] : '';
		if($payment_status) {
			$update_payment_status = " , PaymentStatus = ".$payment_status;
		}
		
		$status_id = isset($data['Status']) ? $data['Status'] : 0;
		
		if($status_id == 3){ //shipped
			$update_payment_status = " , PaymentStatus =  2 ";
		}
		
		$sql = "update Transactions set Status = $status_id, 
			LastEdited='".date('Y-m-d H:i:s')."' ,
			isNew = 0,
			Remarks = '".$data['Remarks']."',
			ShippingCode = '$shipping_code'
			$update_payment_status	
			where ID = '".$transaction_id."'";
		
		$this->oldSkul($sql,false);
			
		if($status_id == 3){ //shipped
			//send email 
		}
	}	
	
	public function ListTransaction($product_id=null,$status_id=null) {
		
		$data = array();
		if(strlen($product_id) > 0) {
			$where = "where t1.ID='".$product_id."'";
			$data = $this->oldSkul("select t1.*, t2.Name ProductName, t2.Code Code, t3.Price, a.Status as OrderStatus 
			from Transactions t1 
			join Products t2 on t2.ID  = t1.ProductID 
			join Prices t3 on t3.ProductID = t1.ProductID 
			left join OrderStatus a on t1.Status = a.OrderStatusID
			$where");
		}
		else {
			$data = $this->oldSkul("select distinct(t1.ID), t1.* ,
			t2.SiteUsername,
			t3.PaymentDate, 
			a.Status as OrderStatus
			from Transactions t1 
			join SiteUsers t2 on t2.ID = t1.UserID
			left join Payments t3 on t3.TransactionID = t1.ID
			left join OrderStatus a on t1.Status = a.OrderStatusID
			group by t1.ID
			order by t1.Created desc");
		}
				
		return $data;
	}
	
	public function getUserTransaction($transaction_id,$email=null) {
		$transaction_id = strtolower(strip_tags(trim($transaction_id)));
		$email = strtolower(strip_tags(trim($email)));
		
		$where = "where t1.ID='".$transaction_id."' ";
		if($email) {
			$where .= "and t1.Email = '".$email. "'";
		}
		$sql = "select t1.*, t2.Name ProductName, t2.Code Code, 
		t3.Price, t3.DiscPrice, t3.WholePrice
		from Transactions t1 
		join Products t2 on t2.ID  = t1.ProductID 
		join Prices t3 on t3.ProductID = t1.ProductID 
		$where";
		
		$data = $this->oldSkul($sql);	
		
		return $data;
	}
	
	public function getTransaction($transaction_id,$email=null) {
		$transaction_id = strtolower(strip_tags(trim($transaction_id)));
		$email = strtolower(strip_tags(trim($email)));
		
		$where = "where t1.ID='".$transaction_id."' ";
		
		$qty_check = $this->oldSkul("select sum(Quantity) Total_Quantity from Transactions t1 $where" );
		$total_quantity = $qty_check[0]['Total_Quantity'];
		
		//$price = ($total_quantity >= 6) ? ' t3.DiscPrice Price ' : ' t3.Price Price';
		
		if($email) {
			$where .= "and t1.Email = '".$email. "'";
		}
		
		$sql = "select t1.*, t2.Name ProductName, t2.Code Code, t4.*, 
		t3.Price, t3.WholePrice, t3.DiscPrice , t6.Filename, a.Status as OrderStatus
		from Transactions t1 
		join Products t2 on t2.ID  = t1.ProductID 
		join Prices t3 on t3.ProductID = t1.ProductID 
		left join Payments t4 on t4.TransactionID = t1.ID
		left join ProductImages t6 on t6.ProductID = t2.ID
		left join OrderStatus a on t1.Status = a.OrderStatusID
		$where
		group by t2.ID
		";
		
		$data = $this->oldSkul($sql);	
		
		return $data;
	}
	
	public function payTransaction($transaction_id,$data) {
	
		$sql = "insert into Payments values ('".$transaction_id."', '".$data['PaymentDate']."','".$data['PaymentAmount']."', '".strip_tags($data['PaymentDetails'])."',". $this->db->quote($data['receiptFile']) .")";
		$this->oldSkul($sql,false) ;
		
		$sqlPaymentAmount = '';
		if ($data['PaymentMethod'] == 5) {
			if (is_numeric($data['PaymentAmount'])) {
				$sqlPaymentAmount = " , StoreCreditsUsed = StoreCreditsUsed + " . str_replace(',','',$data['PaymentAmount']);

				$this->oldSkul("update SiteUsers set StoreCredits = StoreCredits - ". str_replace(',','',$data['PaymentAmount']) ." where ID = " . $data['UserID'], false);
				$AdvanceDepositUsageHistoryData = array();
				$AdvanceDepositUsageHistoryData['EventDate'] = date('Y-m-d H:i:s');
				$AdvanceDepositUsageHistoryData['Event'] = 'Payment Transaction';
				$AdvanceDepositUsageHistoryData['Amount'] = (str_replace(',','',$data['PaymentAmount']) * -1);
				$AdvanceDepositUsageHistoryData['TransactionID'] = $transaction_id;
				$AdvanceDepositUsageHistoryData['SiteUserID'] = $data['UserID'];
				$this->insertData('AdvanceDepositUsageHistory', $AdvanceDepositUsageHistoryData);
			}
		}
		$sql = "update Transactions set PaymentMethod = '".$data['PaymentMethod']."' $sqlPaymentAmount where ID = '".$transaction_id."'";
		#$sql = "update Transactions set PaymentMethod = '".$data['PaymentMethod']."'  where ID = '".$transaction_id."'";
		$this->oldSkul($sql,false) ;
				
		$data = array('TransactionID'=>$transaction_id, 'Status' => 2, 'PaymentStatus' => 1  ); // 2 = in process, 1 =  marked as payment sent		 
				
		$this->updateStatus($data); 
		
		
		
		$PaymentMethod = '';
		switch ($data['PaymentMethod']){			
			case 1:
				$PaymentMethod = 'GCASH';
				break;
			case 2:
				$PaymentMethod = 'BDO';
				break;			
			case 3:
				$PaymentMethod = 'BPI';
				break;
			case 4:
				$PaymentMethod = 'LBC';
				break;
			case 5:
				$PaymentMethod = 'AD';
				break;			
			
			default:
				$PaymentMethod = '';
				break;
		}			
		
		
		$mailer = new Model_Mailer();		
		$subject = strtoupper($transaction_id).' has been Marked As Paid';
		
		$message = '<h3>Transaction '.strtoupper($transaction_id).' has been Marked as Paid.</h3>' ;
		$message .= '<table>';
		$message .= '<tr>';
		$message .= '<td>Payment Method</td>';
		$message .= '<td>'.$PaymentMethod.'</td>';
		$message .= '</tr>';
		$message .= '<tr>';
		$message .= '<td>Payment Date</td>';
		$message .= '<td>'.$data['PaymentDate'].'</td>';
		$message .= '</tr>'	;
		$message .= '<tr>';
		$message .= '<td>Amount</td>';
		$message .= '<td>'.$data['PaymentAmount'].'</td>';
		$message .= '</tr>'	;		
		$message .= '<tr>';
		$message .= '<td>Payment Details</td>';
		$message .= '<td>'.$data['PaymentDetails'].'</td>';
		$message .= '</tr>'	;		
		$message .= '</table>';
		
		$mailer->Mailer('marketing.eazyfashion@hotmail.com',$subject,$message,$headers=null);
	}
	
	
	public function getUserTransactions($user_id) {
		$data = $this->oldSkul("select distinct(t1.ID) TransactionID, t1.Created, t1.TransactionTotal, t1.Status, t1.PaymentStatus, t1.PaymentDue , t1.ShippingCode, a.Status as OrderStatus
		from Transactions t1
		left join OrderStatus a on t1.Status = a.OrderStatusID
		where UserID=".$user_id." order by PaymentStatus asc, Created desc " );
		return $data;
	}
	
	
	public function transactionStatus() {
		$data = $this->oldSkul("select OrderStatusID, Status from OrderStatus");
		$status = array();
		foreach ($data as $row) {
			$status[$row['OrderStatusID']] = $row['Status'];
		}		
		return $status;
	}

	public function randomizer() {
		//returns 6 character alpha-numeric
		//$n = rand(10e16, 10e2);
		//return base_convert($n, 10, 36);
		
		return rand(100000,999999);
		
	}


}
