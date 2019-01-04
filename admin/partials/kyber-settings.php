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
        // 'version' => array(
        //     'title' => __( 'Version', 'woocommerce-gateway-kyber' ),
        //     'type' => 'select',
        //     'decription' => __( 'Choose widget version that you want to use', 'woocommerce-gateway-kyber' ),
        //     'desc_tip' => false,
        //     'default' => 'v0.4',
        //     'options' => array(
        //         // 'v0.1' => 'v0.1',
        //         // 'v0.2' => 'v0.2',
        //         // 'v0.3' => 'v0.3',
        //         'v0.4' => 'v0.4'
        //     )
        // ),
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
            'class' => 'supported-tokens',
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
        'block_confirmation' => array(
            'title' => __( 'Block Confirmation', 'woocommerce-gateway-kyber' ),
            'type' => 'number',
            'description' => __( 'Number of block confirmation for confirm tx as success' ),
            'default' => '7',
        ),
        'commission_id' => array(
            'title' => __( 'Commission ID', 'woocommerce-gateway-kyber' ),
            'type' => 'text',
            'description' => __( 'Your Ethereum wallet to get commission of the fees for the transaction. Your wallet must be whitelisted by KyberNetwork (the permissionless registration will be available soon) in order to get the commission, otherwise it will be ignored.', 'woocommerce-gateway-kyber' )
        )
        // 'use_cron_job' => array(
        //     'title' => __( 'Use cronjob (recommended)', 'woocommerce-gateway-kyber' ),
        //     'type' => 'select',
        //     'description'=> __( 'Use cronjob to monitor transaction, you can check out how to setup cronjob <a href="https://github.com/KyberNetwork/widget-woocommerce/wiki/Transaction-monitor">here</a>', 'woocommerce-gateway-kyber' ),
        //     'options'=> array(
        //         'true' => 'Yes',
        //         'false'=> 'No'
        //     ),
        //     'default' => 'true'
        // )
    )
);