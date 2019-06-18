<?php
require_once($_SERVER['DOCUMENT_ROOT'] .'/wp-load.php');

global $woocommerce;
$order = new WC_Order($_REQUEST["order"]);
$order->payment_complete();
