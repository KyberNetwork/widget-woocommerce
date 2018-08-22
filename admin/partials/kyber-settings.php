<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return apply_filters( 'wc_kyber_settings',
    array(
        'enabled' => array(
            'title' => __( 'Enable/Disable', 'woocommerce-gateway-kyber' ),
            'label' => __( 'Enable Kyber', 'woocommerce-gateway-kyber' ),
            'type' => 'checkbox',
            'description' => '',
            'default' => 'no',
        ),
        'title' => array(
            'title' => __( 'Title', 'woocommerce-gateway-kyber' ),
            'type' => 'text',
            'description' => '',
            'default' => __( 'Coins, Tokens', 'woocommerce-gateway-kyber' ),
            'desc_tip' => true,
        ),
        'description' => array(
            'title' => __( 'Description', 'woocommerce-gateway-kyber' ),
            'type'  => 'text',
            'description' => __('This is description for kyber payment method', 'woocommerce-gateway-kyber'),
            'default' => __( 'Pay with your coins, tokens supported by Kyber',  'woocommmerce-gateway-kyber'),
            'desc_tip' => true,
        ),
        'receive_addr' => array(
            'title' => __( 'Receive Address', 'woocommerce-gateway-kyber' ),
            'type' => 'text',
            'description' => 'Address to receive coins, token payment',
        ),
        'receive_token_symbol' => array(
            'title' => __( 'Receive token symbol', 'woocommerce-gateway-kyber' ),
            'type' => 'text',
            'description' => __('Token you would like to receive by payment', 'woocommerce-gateway-kyber'),
        ),
        'network' => array(
            'title' => __( 'Network', 'woocommerce-gateway-kyber' ),
            'type' => 'select',
            'description' => __( 'Select network which you want payment to run on, Ropsten for test and Mainnet for produdction', 'woocomerce-gateway-kyber' ),
            'default' => 'ropsten',
            'desc_tip' => false,
            'options' => array(
                'ropsten' => __( 'Ropsten', 'woocomerce-gateway-kyber' ),
                'mainnet' => __( 'Mainnet', 'woocomerce-gateway-kyber' )
            ),
        ),
        'network_node_endpoint' => array(
            'title' => __( 'Network node endpoint', 'woocomerce-gateway-kyber' ),
            'type' => 'text',
            'description' => __( 'Node endpoint to check transaction status. If you do not have one, create using <a href="https://infura.io/" target="__blank">Infura</a>' )
        )
    )
);