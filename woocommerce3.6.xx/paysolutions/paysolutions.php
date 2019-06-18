<?php
/*
Plugin Name: Paysolutions Payment Gateway
Description: Online Payment Service Provider, The First of Thailand.
Author: Paysolutions
Author URI: https://www.paysolutions.asia/
 */

add_action('plugins_loaded', 'paysolutions_init', 0);
add_action('admin_head', 'paysolutions_backorder_font_icon');
add_action('init', 'paysolutions_register_awaiting_payment_order_status');
add_filter('wc_order_statuses', 'paysolutions_add_awaiting_payment_to_order_statuses');

register_activation_hook(__FILE__, 'paysolutions_register_activation_hook');
register_deactivation_hook(__FILE__, 'paysolutions_register_deactivation_hook');

function paysolutions_register_activation_hook()
{

    global $woocommerce;

    $args = array(
        'post_type' => 'shop_order',
        'post_status' => 'wc-pending',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_payment_method',
                'value' => 'paysolutions',
                'compare' => 'LIKE',
            ),
        ),
    );

    $loop = new WP_Query($args);

    try {
        while ($loop->have_posts()): $loop->the_post();
            $order = new WC_Order($loop->post->ID);
            $order->update_status('awaiting-payment');
        endwhile;
    } catch (Exception $e) {

    }
}

function paysolutions_register_deactivation_hook()
{

    global $woocommerce;

    $args = array(
        'post_type' => 'shop_order',
        'post_status' => 'wc-awaiting-payment',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_payment_method',
                'value' => 'paysolutions',
                'compare' => 'LIKE',
            ),
        ),
    );

    $loop = new WP_Query($args);

    try {
        while ($loop->have_posts()): $loop->the_post();
            $order = new WC_Order($loop->post->ID);
            $order->update_status('pending');
        endwhile;
    } catch (Exception $e) {

    }
}

/* This function is set the paysolutions icon in admin panel */
function paysolutions_backorder_font_icon()
{
    echo '<style>
            .widefat .column-order_status mark.awaiting-payment:after{
                font-family:WooCommerce;
                speak:none;
                font-weight:400;
                font-variant:normal;
                text-transform:none;
                line-height:1;
                -webkit-font-smoothing:antialiased;
                margin:0;
                text-indent:0;
                position:absolute;
                top:0;
                left:0;
                width:100%;
                height:100%;
                text-align:center;
            }
            .widefat .column-order_status mark.awaiting-payment:after{
                content:"\e012";
                color:#0496c9;
            }
  </style>';
}

/* This function is used to add custom order status into post */
function paysolutions_register_awaiting_payment_order_status()
{

    register_post_status('wc-awaiting-payment', array(
        'label' => 'Awaiting Payment',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Awaiting Payment <span class="count">(%s)</span>', 'Awaiting Payment <span class="count">(%s)</span>'),
    ));
}

// Add to list of WC Order statuses
function paysolutions_add_awaiting_payment_to_order_statuses($order_statuses)
{

    $new_order_statuses = array();

    // add new order status after processing
    foreach ($order_statuses as $key => $status) {

        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-awaiting-payment'] = 'Awaiting Payment';
        }
    }

    return $new_order_statuses;
}

function paysolutions_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    //Included required files.
    require_once dirname(__FILE__) . '/Includes/wc-paysolutions-constant.php';
    require_once dirname(__FILE__) . '/Includes/wc-paysolutions-request-helper.php';
    require_once dirname(__FILE__) . '/Includes/wc-paysolutions-validation-helper.php';

    //Gateway class
    class WC_Gateway_paysolutions extends WC_Payment_Gateway
    {
        //Make __construct()
        public function __construct()
        {

            $this->id = 'paysolutions'; // ID for WC to associate the gateway values
            $this->method_title = 'Paysolutions'; // Gateway Title as seen in Admin Dashboad
            $this->method_description = 'Paysolutions - Redefining Payments, Simplifying Lives'; // Gateway Description as seen in Admin Dashboad
            $this->has_fields = false; // Inform WC if any fileds have to be displayed to the visitor in Frontend

            $this->init_form_fields(); // defines your settings to WC
            $this->init_settings(); // loads the Gateway settings into variables for WC

            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->url = 'https://www.thaiepay.com/epaylink/payment.aspx';
            $this->service_provider = array_key_exists('service_provider', $this->settings) ? $this->settings['service_provider'] : "";
            $this->msg['message'] = '';
            $this->msg['class'] = '';

            add_action('woocommerce_receipt_paysolutions', array(&$this, 'receipt_page'));
            add_action('woocommerce_checkout_update_order_meta', array(&$this, 'wc_paysolutions_custom_checkout_field_update_order_meta'));

            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options')); //update for woocommerce >2.0
            } else {
                add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options')); // WC-1.6.6
            }

        } //END-__construct

        public function validate_key_id_field($key, $value)
        {
            if (empty($value)) {
                WC_Admin_Settings::add_error(esc_html__('Please Enter Merchant Id', 'woo_paysolutions'));
                return $value;
            }
            return $value;
        }

        public function validate_wc_paysolutions_fixed_description_field($key, $value)
        {
            if (strlen($value) > 250) {
                WC_Admin_Settings::add_error(esc_html__('Fixed description field value should be less than 250 charactors.', 'woo_paysolutions'));
                return $value;
            }
            return $value;
        }

        public function wc_paysolutions_get_setting()
        {
            return $this->settings;
        }

        /* Get the plugin response URL */
        public function wc_paysolutions_response_url($order_Id)
        {
            $order = new WC_Order($order_Id);

            $redirect_url = $this->get_return_url($order);

            return $redirect_url;
        }

        /* Initiate Form Fields in the Admin Backend */
        function init_form_fields()
        {
            $this->form_fields = include dirname(__FILE__) . '/Includes/wc-paysolutions-setting.php';
        }

        public function admin_options()
        {
            echo '<h3>' . esc_html__('Paysolutions', 'woo_paysolutions') . '</h3>';
            echo '<p>' . esc_html__('Paysolutions provides a wide range of payment. you just save your account detail in it and enjoy shopping just in one click on Paysolutions', 'woo_paysolutions') . '</p>';
            echo '<table class="form-table">';
            // Generate the HTML For the settings form.
            $this->generate_settings_html();
            echo '</table>';
        }

        /* Receipt Page */
        function receipt_page($order)
        {
            echo $this->generate_paysolutions_form($order);
        }

        /* Generate button link */
        function generate_paysolutions_form($order_id)
        {

            global $woocommerce;
            $order = new WC_Order($order_id);
            $redirect_url = $this->get_return_url($order);

            if (strcasecmp($this->service_provider, 'money') == 0) {
                $service_provider = '';
            } else {
                $service_provider = 'PAYSOLUTIONS';
            }

            if (is_user_logged_in()) { // Customer is loggedin.
                $loggedin_user_data = wp_get_current_user();
                $cust_email = $loggedin_user_data->data->user_email;
            } else {
                $cust_email = $order->data['billing']['email']; //Guest customer.
            }

            $fixed_description = $this->settings['wc_paysolutions_fixed_description'];

            if ($fixed_description == '') {
                $product_name = "";
                foreach ($order->get_items() as $item) {
                    $product_name .= $item['name'] . ', ';
                }

                $product_name = (strlen($product_name) > 0) ? substr($product_name, 0, strlen($product_name) - 2) : "";
                $product_name .= '.';
                $product_name = filter_var($product_name, FILTER_SANITIZE_STRING);
                $product_name = mb_strimwidth($product_name, 0, 255, '...');

            } else {
                $product_name = mb_strimwidth($fixed_description, 0, 255, "");
            }

            $default_lang = WC_PAYSOLUTIONS_Constant::WC_PAYSOLUTIONS_DEFAULT_LANG;
            $selected_lang = sanitize_text_field($this->paysolutions_setting_values['wc_paysolutions_default_lang']);
            $default_lang = !empty($selected_lang) ? $selected_lang : $default_lang;

            $funpaysolutions_args = array(
                'payment_description' => $product_name,
                'order_id' => $order_id,
                'invoice_no' => $order_id,
                'amount' => $order->order_total,
                'customer_email' => sanitize_email($cust_email),
                'default_lang' => strtoupper($default_lang),
            );

            $objWC_PAYSOLUTIONS_Validation_Helper = new WC_PAYSOLUTIONS_Validation_Helper();
            $isValid = $objWC_PAYSOLUTIONS_Validation_Helper->wc_paysolutions_is_valid_merchant_request($funpaysolutions_args);

            if (!$isValid) {
                foreach ($objWC_PAYSOLUTIONS_Validation_Helper->wc_paysolutions_error as $key => $value) {
                    echo esc_html__($value, 'woo_paysolutions');
                }

                echo '</br>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp<a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . esc_html__('Cancel order &amp; restore cart', 'woo_paysolutions') . '</a>';
                return;
            } else {

                echo '<p><strong>' . esc_html__('Thank you for your order.', 'woo_paysolutions') . '</strong> <br/>' . esc_html__('The payment page will open if you click on button "Pay With PAYSOLUTIONS".', 'woo_paysolutions') . '</p>';
            }

            $objwc_paysolutions_construct_request = new wc_paysolutions_construct_request_helper();
            $wc_paysolutions_form_field = $objwc_paysolutions_construct_request->wc_paysolutions_construct_request(is_user_logged_in(), $funpaysolutions_args);

            $strHtml = '';
            $strHtml .= '<form action="' . esc_url($this->url) . '" method="post" id="paysolutions_payment_form">';
            $strHtml .= $wc_paysolutions_form_field;
            $strHtml .= '<input type="submit" class="button-alt" id="submit_paysolutions_payment_form" value="' . esc_html__('Pay With PAYSOLUTIONS', 'woo_paysolutions') . '" />';
            $strHtml .= '&nbsp;&nbsp;&nbsp;&nbsp<a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' . esc_html__('Cancel order &amp; restore cart', 'woo_paysolutions') . '</a>';
            $strHtml .= '</form>';

            return $strHtml;
        }

        //Process the payment and return the result
        function process_payment($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);

            if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) { // For WC 2.1.0
                $checkout_payment_url = $order->get_checkout_payment_url(true);
            } else {
                $checkout_payment_url = get_permalink(get_option('woocommerce_pay_page_id'));
            }

            return array('result' => 'success', 'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, $checkout_payment_url)));
        }

        public function thanku_page()
        {}

    } //END-class

    //Add the Gateway to WooCommerce
    function woocommerce_add_gateway_paysolutions_gateway($methods)
    {
        $methods[] = 'WC_Gateway_paysolutions';
        return $methods;
    } //END-wc_add_gateway

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_paysolutions_gateway');

} //END-init

//Virasat Solutions 'Settings' link on plugin page
add_filter('plugin_action_links', 'paysolutions_add_action_plugin', 10, 5);
function paysolutions_add_action_plugin($actions, $plugin_file)
{
    static $plugin;

    if (!isset($plugin)) {
        $plugin = plugin_basename(__FILE__);
    }

    if ($plugin == $plugin_file) {

        $settings = array(
            'settings' => '<a href="admin.php?page=wc-settings&tab=checkout&section=WC_Gateway_paysolutions">' . esc_html__('Settings', 'woo_paysolutions') . '</a>',
        );

        $actions = array_merge($settings, $actions);
    }

    return $actions;
} //END-settings_add_action_link
