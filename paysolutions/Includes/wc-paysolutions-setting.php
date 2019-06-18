<?php

return array(
    'enabled' => array(
        'title' => __('Enable/Disable', 'woo_paysolutions'),
        'type' => 'checkbox',
        'label' => __('Enable paysolutions', 'woo_paysolutions'),
        'default' => 'no',
        'description' => ''
    ),
    'title' => array(
        'title' => __('Title', 'woo_paysolutions'),
        'type' => 'text',
        'default' => __('Paysolutions Credit / Debit Card', 'woo_paysolutions'),
        'description' => __('This controls the title which the user sees during checkout.', 'woo_paysolutions'),
        'desc_tip' => true
    ),
    'description' => array(
        'title' => __('Description', 'woo_paysolutions'),
        'type' => 'textarea',
        'default' => __('Pay Securely by Credit card, Debit card,Installment,Internet banking,', 'woo_paysolutions'),
        'description' => __('This controls the description which the user sees during checkout.', 'woo_paysolutions'),
        'desc_tip' => true
    ),
    'wc_paysolutions_api_details' => array(
        'title'       => __( 'API credentials', 'woocommerce' ),
        'type'        => 'title',
        'description' => '',
    ),
    'key_id' => array(
        'title' => __('Merchant ID', 'woo_paysolutions'),
        'type' => 'text',
        'description' => __('Provided to Merchant by PAYSOLUTIONS team'),
        'desc_tip' => true
    ),
    'wc_paysolutions_default_lang' => array(
        'title' => __('Select Language', 'woo_paysolutions'),
        'type' => 'select',
        'label' => __('Select Language.', 'woo_paysolutions'),
        'default' => 'test',
        'description' => __('PAYSOLUTIONS currently supports 6 languages'),
        'desc_tip' => true,
        'class'        => 'wc-enhanced-select',
        'options' => array(
            'en' => 'English',
            'th' => 'Thailand',
        ),
    ),

);
?> 