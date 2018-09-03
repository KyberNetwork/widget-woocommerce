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
            'title' => __( 'Receive Address*', 'woocommerce-gateway-kyber' ),
            'type' => 'text',
            'description' => 'Address to receive coins, token payment',
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
        'receive_token_symbol' => array(
            'title' => __( 'Receive token symbol', 'woocommerce-gateway-kyber' ),
            'type' => 'select',
            'description' => __('Token you would like to receive by payment', 'woocommerce-gateway-kyber'),
            'default' => '',
            'options' => $this->get_list_token_supported(),
        ),
        'mode' => array(
            'title' => __( 'Payment mode', 'woocommerce-gateway-kyber' ),
            'type' => 'select',
            'description' => __( 'This is mode for display Kyber widget style, tab will load Kyber widget in new tab, iframe will load Kyber widget inside order received page.' ),
            'default' => 'iframe',
            'descp_ip' => false,
            'options' => array(
                'iframe' => 'iframe',
                'tab' => 'tab',
                'popup' => 'popup'
            ),
        ),
        'network_node_endpoint' => array(
            'title' => __( 'Network node endpoint', 'woocommerce-gateway-kyber' ),
            'type' => 'text',
            'description' => __( 'Node endpoint to check transaction status. If you do not have one, create using <a href="https://infura.io/" target="__blank">Infura</a>' )
        ),
        'block_confirmation' => array(
            'title' => __( 'Block Confirmation', 'woocommerce-gateway-kyber' ),
            'type' => 'number',
            'description' => __( 'Number of block confirmation for confirm tx as success' ),
            'default' => '10',
        ),
        'commission_id' => array(
            'title' => __( 'Commission ID', 'woocommerce-gateway-kyber' ),
            'type' => 'text',
            'description' => __( 'Your Ethereum wallet to get commission of the fees for the transaction. Your wallet must be whitelisted by KyberNetwork (the permissionless registration will be available soon) in order to get the commission, otherwise it will be ignored.', 'woocommerce-gateway-kyber' )
        )
    )
);