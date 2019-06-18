<?php

class wc_paysolutions_construct_request_helper extends WC_Payment_Gateway
{
    private $paysolutions_setting_values;

    function __construct() { 
        $objWC_Gateway_paysolutions = new WC_Gateway_paysolutions();
        $this->paysolutions_setting_values = $objWC_Gateway_paysolutions->wc_paysolutions_get_setting();
    }

    private $wc_paysolutions_form_fields = array(
        "merchantid" => "",
        "refno" => "",
        "customeremail" => "",
        "productdetail" => "",
        "total" => "",
        "lang" => "",
        "cc" => "",
        "postbackurl" => "",
        "returnurl" => ""
    );

    public function wc_paysolutions_construct_request($isloggedin,$paymentBody)
    {
        $this->wc_paysolutions_create_common_form_field($paymentBody);

        $strHtml = "";
        foreach ($this->wc_paysolutions_form_fields as $key => $value) {
            if (!empty($value)) {
                $strHtml .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
            }        
        }
        return $strHtml;    
    }
    function wc_paysolutions_create_common_form_field($paymentBody){

        $objWC_Gateway_paysolutions = new WC_Gateway_paysolutions();
        $redirect_url   = $objWC_Gateway_paysolutions->wc_paysolutions_response_url($paymentBody['order_id']);
        $post_back_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"."/wp-content/plugins/paysolutions/postback.php?order=" . $paymentBody['order_id'];
        $merchant_id    = isset($this->paysolutions_setting_values['key_id']) ? sanitize_text_field($this->paysolutions_setting_values['key_id']) : "";
        $default_lang  = WC_PAYSOLUTIONS_Constant::WC_PAYSOLUTIONS_DEFAULT_LANG;
        $selected_lang = sanitize_text_field($this->paysolutions_setting_values['wc_paysolutions_default_lang']);
        $default_lang  = !empty($selected_lang) ? $selected_lang : $default_lang;
        $currency       = sanitize_text_field($this->wc_paysolutions_get_store_currency_code());
        $payment_description = $paymentBody['payment_description'];
        $order_id       = $paymentBody['order_id'];
        $amount         = $paymentBody['amount'];
        $customer_email = $paymentBody['customer_email'];
        $this->wc_paysolutions_form_fields["merchantid"] = $merchant_id;
        $this->wc_paysolutions_form_fields["refno"] = $order_id;
        $this->wc_paysolutions_form_fields["customeremail"] = $customer_email;
        $this->wc_paysolutions_form_fields["productdetail"] = $payment_description;
        $this->wc_paysolutions_form_fields["total"] = $amount;
        $this->wc_paysolutions_form_fields["cc"] = $currency;
        $this->wc_paysolutions_form_fields["lang"] = strtoupper($default_lang);
        $this->wc_paysolutions_form_fields["returnurl"] = $redirect_url;
        $this->wc_paysolutions_form_fields["postbackurl"] = $post_back_url;
    }


    function wc_paysolutions_get_store_currency_code(){
        $objWC_paysolutions_currency = new WC_paysolutions_currency();
        $currenyCode = get_option('woocommerce_currency');
        if(!isset($currenyCode))
            return "";

        foreach ($objWC_paysolutions_currency->get_currency_code() as $key => $value) {
            if($key === $currenyCode){
                return  $value['paysolutions_code'];
            }
        }
		 return "";
    }

}

