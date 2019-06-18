<?php

class WC_PAYSOLUTIONS_Validation_Helper{

	public $wc_paysolutions_error = array(
		"payment_description" 	=> "",
		"order_id" 				=> "",			
		"amount" 				=> "",
		"customer_email"		=> "",
		);

	function __construct() { }

	function wc_paysolutions_is_valid_merchant_request($parameter){

		if(empty($parameter['order_id'])){
			$this->wc_paysolutions_error['order_id'] = "Order id cannot be blank.";
		}
		if(empty($parameter['payment_description'])){
			$this->wc_paysolutions_error['payment_description'] = "Payment Description cannot be blank.";
		}
		if(empty($parameter['amount'])){
			$this->wc_paysolutions_error['amount'] = "Amount cannot be blank.";
		}
		if(!empty($parameter['order_id'])){		
			if(strlen($parameter['order_id']) > 20){
				$this->wc_paysolutions_error['order_id'] = "Order id is limited to 20 character.";
			}
		}
		if(!empty($parameter['amount'])){
			if($parameter['amount'] <= 0){
				$this->wc_paysolutions_error['amount'] = "Amount must be greater than 0.";
			}
		}
		if(!empty($parameter['amount'])){
			if(strlen($parameter['amount']) > 12){
				$this->wc_paysolutions_error['amount'] = "Amount is limited to 12 character.";
			}
			else{
				//Calculate currenycy by methods.
				if(!is_numeric($parameter['amount'])){
					$this->wc_paysolutions_error['amount'] = "Please enter amount is digit's.";
				}				
			}
		}

		if(!is_email($parameter['customer_email'])){
			$this->wc_paysolutions_error['amount'] = "Please enter valid email address.";
		}

		foreach ($this->wc_paysolutions_error as $key => $value) {
			if(!empty($value)){
				return false;
			}
		}
		return true;
	}

}

?>