<?php

class WC_PAYSOLUTIONS_Constant{
    const WC_PAYSOLUTIONS_DEFAULT_LANG = 'en_GB';
}

class WC_paysolutions_currency{


    public function get_currency_code(){

        return array(
        "THB" => array ("Num" => "764","exponent" => "2","paysolutions_code" => "00"),
        "USD" => array ("Num" => "840","exponent" => "2","paysolutions_code" => "01"),
        "JPY" => array ("Num" => "392","exponent" => "0","paysolutions_code" => "02"),
        "SGD" => array ("Num" => "702","exponent" => "2","paysolutions_code" => "03"),
        "HKD" => array ("Num" => "344","exponent" => "2","paysolutions_code" => "04"),
        "EUR" => array ("Num" => "978","exponent" => "2","paysolutions_code" => "05"),
        "GBP" => array ("Num" => "826","exponent" => "2","paysolutions_code" => "06"),
        "AUD" => array ("Num" => "036","exponent" => "2","paysolutions_code" => "07"),
        "CHF" => array ("Num" => "756","exponent" => "2","paysolutions_code" => "08"),

        );
    }


}

?>