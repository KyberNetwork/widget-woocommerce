<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 
 * @link developer.kyber.network
 * @since 0.0.1
 * 
 * @package Woo_Kyber_Payment
 * @subpackage Woo_Kyber_Payment/includes
 */

class WC_Kyber_Payment_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'kyber';
        $this->method_title = __( 'Kyber', 'woocommerce-gateway-kyber' );
        $this->method_description = sprintf( __('Kyber allow user to pay by using tokens') );
        $this->order_button_text = __( 'Proceed to Kyber', 'woocommerce-gateway-kyber' );
        $this->has_fields = true;
        $this->supports = array(
            'products',
            'refunds',
            'tokenization',
            'add_payment_method'
        );

        $this->init_form_fields();

        $this->init_settings();

        $this->title = $this->get_option( 'title' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->description = $this->get_option( 'description' );
    }

    function init_form_fields() {
        $this->form_fields = require( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/kyber-settings.php' );
    }
}