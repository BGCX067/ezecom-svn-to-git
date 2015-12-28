<?php

class Model_Mailer {

	/***public function Mailer($recipient,$subject=null,$message,$headers=null) {
		//$to      = 'masterje@gmail.com,marketing.eazyfashion@hotmail.com,'.$recipient;
		$to      = 'masterje@gmail.com,'.$recipient;
		$subject = '[EazyFashion] '.$subject;
		$headers = 'From: noreply@eazyfashion.com' . "\r\n" .
			'Reply-To: noreply@eazyfashion.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";	

		mail($to, $subject, $message, $headers);
	}
	***/
	
	public function Mailer($recipient,$subject=null,$message,$headers=null) {
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
		$mail->Subject    = $subject;
			
		//$mail->AddAddress($recipient, "EazyFashion Customer");
		$mail->AddAddress("masterje@gmail.com", "EazyFashion Admin");
		//$mail->AddAddress("marketing.eazyfashion@hotmail.com", "EazyFashion Admin");
			
		$body = $message;
		$mail->MsgHTML($body);
		
		if(!$mail->Send()) 
		{
			return "Mailer Error: " . $mail->ErrorInfo;
		}
		else 
		{
			return  "Message sent!";
		}
	}		
	
	public function sendBuyItemConfirmation($data) {
		
		$subject = 'Order Received '.strtoupper($data[0]['ID']);

		$message = '';		
		$message .= '<h3>Hello '.$data[0]['Firstname'].' '.$data[0]['Lastname'].'</h3>';
		$message .= '<p>Thank you for your purchase.  We have received your order.</p>';
		
		$message .= '<h3>Order Summary</h3>';
$message .= 
'<table rules="all" style="border-color: #665; background: #EBE8E9" cellpadding="10">
<tbody>';

$message .= '<tr>
		<td>Name</td>
		<td>'.strtoupper($data[0]['Firstname']).' '.strtoupper($data[0]['Lastname']).'</td>
		</tr>';
$message .= '<tr>
		<td>Transaction Code</td>
		<td><strong>'.strtoupper($data[0]['ID']).'</strong></td>	
		</tr>';	
$message .= '<tr>
		<td>Shipping Address</td>
		<td>'.$data[0]['ShippingAddress'].'</td>
		</tr>';	
$message .= '<tr>
		<td>Special Instructions</td>
		<td>'.$data[0]['SpecialInstructions'].'</td>
		</tr>';	
$message .= '<tr>
		<td>Payment Date</td>
		<td>'.$data[0]['PaymentDue'].'</td>
		</tr>';	
$message .= '<tr>
		<td>Status</td>
		<td>'.$data[0]['Status'].'</td>
		</tr>';			
$message .= '<tr>
		<td>Shipping Fee</td>
		<td> <strong>PHP '.number_format($data[0]['ShippingFee'],2).'</strong></td>
		</tr>';
$message .= '<tr>
		<td>Transaction Total</td>
		<td> <strong>PHP '.number_format($data[0]['TransactionTotal'],2).'</strong></td>
		</tr>';	
		
$message .= '</tbody>
</table>';	
		
		$message .= '<h3>Item Details</h3>';
		$message .= 
'<table rules="all" style="border-color: #665; background: #EBE8E9" cellpadding="10">
<thead>
	<tr>
		<th >Item</th>
		<th >Item Code</th>
		<th >Price</th>
		<th >Quantity</th>		
		<th >TOTAL</th>
	</tr>
</thead>
<tbody>';

	
foreach ($data as $row) {
	$message .= '<tr>
		<td>'.strtoupper(strip_tags($row['ProductName'])).'</td>
		<td>'.strtoupper($row['Code']).'</td>
		<td> PHP '.number_format($row['Price'],2).'</td>
		<td>'.$row['Quantity'].'</td>		
		<td> PHP '.number_format($row['SubTotal'],2).'</td>
	</tr>';
}
	$message .='<tr>
		<td  colspan="3">&nbsp;</td>
		<td>Store Credit Used</td>
		<td><strong> PHP '.number_format($data[0]['StoreCreditsUsed'],2).'</strong></td>
	</tr>';
	$message .='<tr>
		<td  colspan="3">&nbsp;</td>
		<td>Shipping</td>
		<td><strong> PHP '.number_format($data[0]['ShippingFee'],2).'</strong></td>
	</tr>';
	$message .='<tr>
		<td  colspan="3">&nbsp;</td>
		<td>TOTAL</td>
		<td><strong> PHP '.number_format($row['TransactionTotal'],2).'</strong></td>
	</tr>
</tbody>
</table>

<p>&nbsp;</p>';	



$message .= nl2br('To reach EazyFashion Customer Service, call (02) 522 4029 or  0917-7572999
For more information on automatic payments, go to the EazyFashion website and click Help in the upper right corner. Then type "billing agreements" in the search box.
Please do not reply to this email. This mailbox is not monitored and you will not receive a response. For assistance, log in to your EazyFashion account and click Help in the top right corner of any EazyFashion page.
You can choose to receive emails in plain text instead of HTML emails. To change your Notifications preferences, log in to your EazyFashion account, go to your Profile, and click My settings.');	
		
		$this->Mailer($data[0]['Email'],$subject,$message,null);
	}
	
	

	public function sendResetPasswordCode($email,$code) {
		$subject = '[EazyFashion] Reset Password';
		$headers = 'From: noreply@eazyfashion.com' . "\r\n" .
			'Reply-To: noreply@eazyfashion.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";	
		
		$generic = new Model_Generic();
		$generic->oldSkul("update SiteUsers set ResetCode = '".$code."' where SiteEmail = '".$email."'  ",false);
		
		
		$message = "Your reset code is <strong>".$code."</strong>";
		
		//$this->Mailer($email,$subject,$message,$headers);
	
	}

}