<?php

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';
use Web3\Utils;    
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

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

    protected $dai = "0x89d24a6b4ccb1b6faa2625fe562bdd9a23260359";
    protected $eth = "0xeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee";
    protected $gwei = "1000000000000000000"; // gwei const
    protected $abi = '[{"constant":false,"inputs":[{"name":"alerter","type":"address"}],"name":"removeAlerter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"enabled","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"pendingAdmin","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"getOperators","outputs":[{"name":"","type":"address[]"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"src","type":"address"},{"name":"srcAmount","type":"uint256"},{"name":"dest","type":"address"},{"name":"destAddress","type":"address"},{"name":"maxDestAmount","type":"uint256"},{"name":"minConversionRate","type":"uint256"},{"name":"walletId","type":"address"},{"name":"hint","type":"bytes"}],"name":"tradeWithHint","outputs":[{"name":"","type":"uint256"}],"payable":true,"stateMutability":"payable","type":"function"},{"constant":false,"inputs":[{"name":"token","type":"address"},{"name":"srcAmount","type":"uint256"},{"name":"minConversionRate","type":"uint256"}],"name":"swapTokenToEther","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"token","type":"address"},{"name":"amount","type":"uint256"},{"name":"sendTo","type":"address"}],"name":"withdrawToken","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"maxGasPrice","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newAlerter","type":"address"}],"name":"addAlerter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"kyberNetworkContract","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"user","type":"address"}],"name":"getUserCapInWei","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"src","type":"address"},{"name":"srcAmount","type":"uint256"},{"name":"dest","type":"address"},{"name":"minConversionRate","type":"uint256"}],"name":"swapTokenToToken","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"newAdmin","type":"address"}],"name":"transferAdmin","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"claimAdmin","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"token","type":"address"},{"name":"minConversionRate","type":"uint256"}],"name":"swapEtherToToken","outputs":[{"name":"","type":"uint256"}],"payable":true,"stateMutability":"payable","type":"function"},{"constant":false,"inputs":[{"name":"newAdmin","type":"address"}],"name":"transferAdminQuickly","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"getAlerters","outputs":[{"name":"","type":"address[]"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"src","type":"address"},{"name":"dest","type":"address"},{"name":"srcQty","type":"uint256"}],"name":"getExpectedRate","outputs":[{"name":"expectedRate","type":"uint256"},{"name":"slippageRate","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"user","type":"address"},{"name":"token","type":"address"}],"name":"getUserCapInTokenWei","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOperator","type":"address"}],"name":"addOperator","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_kyberNetworkContract","type":"address"}],"name":"setKyberNetworkContract","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"operator","type":"address"}],"name":"removeOperator","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"field","type":"bytes32"}],"name":"info","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"src","type":"address"},{"name":"srcAmount","type":"uint256"},{"name":"dest","type":"address"},{"name":"destAddress","type":"address"},{"name":"maxDestAmount","type":"uint256"},{"name":"minConversionRate","type":"uint256"},{"name":"walletId","type":"address"}],"name":"trade","outputs":[{"name":"","type":"uint256"}],"payable":true,"stateMutability":"payable","type":"function"},{"constant":false,"inputs":[{"name":"amount","type":"uint256"},{"name":"sendTo","type":"address"}],"name":"withdrawEther","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"token","type":"address"},{"name":"user","type":"address"}],"name":"getBalance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"admin","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"_admin","type":"address"}],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"trader","type":"address"},{"indexed":false,"name":"src","type":"address"},{"indexed":false,"name":"dest","type":"address"},{"indexed":false,"name":"actualSrcAmount","type":"uint256"},{"indexed":false,"name":"actualDestAmount","type":"uint256"}],"name":"ExecuteTrade","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"newNetworkContract","type":"address"},{"indexed":false,"name":"oldNetworkContract","type":"address"}],"name":"KyberNetworkSet","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"token","type":"address"},{"indexed":false,"name":"amount","type":"uint256"},{"indexed":false,"name":"sendTo","type":"address"}],"name":"TokenWithdraw","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"amount","type":"uint256"},{"indexed":false,"name":"sendTo","type":"address"}],"name":"EtherWithdraw","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"pendingAdmin","type":"address"}],"name":"TransferAdminPending","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"newAdmin","type":"address"},{"indexed":false,"name":"previousAdmin","type":"address"}],"name":"AdminClaimed","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"newAlerter","type":"address"},{"indexed":false,"name":"isAdd","type":"bool"}],"name":"AlerterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"name":"newOperator","type":"address"},{"indexed":false,"name":"isAdd","type":"bool"}],"name":"OperatorAdded","type":"event"}]';
    protected $supported_tokens = array();

    public function __construct() {
        $this->id = 'kyber';
        $this->method_title = __( 'Kyber', 'woocommerce-gateway-kyber' );
        $this->method_description = sprintf( __('Kyber allow user to pay by using tokens', 'woocommerce-gateway-kyber') );
        $this->order_button_text = __( 'Place order', 'woocommerce-gateway-kyber' );
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
        $this->network = $this->get_option( 'network' );
        $this->description = $this->get_option( 'description' );

        if ( $this->network == "ropsten" ) {
            $this->description .= sprintf( __(" TESTMODE is enabled. The payment by this method now will not be proceed.", "woocommerce-gateway-kyber") );
        }

        $timeout = 30;
        $network_endpoint = sprintf("https://%s.infura.io", $this->network);
        $provider = new HttpProvider(new HttpRequestManager($network_endpoint, $timeout));
        $this->proxy_contract =  new Contract($provider, $this->abi);

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_api_kyber_callback', array( $this, 'handle_kyber_callback' ) );
        add_action( 'woocommerce_order_details_after_order_table_items', array( $this, 'add_tx_hash_to_order' ) );
        add_action( 'woocommerce_thankyou', array( $this, 'embed_kyber_widget_button' ) );
        add_action( 'woocommerce_admin_order_totals_after_total', array( $this, 'kyber_price_filter' ) );
    }

    /**
     * Init admin settings fields
     * 
     * @since 0.0.1
     */
    public function init_form_fields() {
        $this->form_fields = require( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/kyber-settings.php' );
    }

    /**
     * Processes and saves options.
     * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
     * @return bool was anything saved?
     */
    public function process_admin_options() {
        $this->init_settings();

        $post_data = $this->get_post_data();

        foreach ( $this->get_form_fields() as $key => $field ) {
            if ( 'title' !== $this->get_field_type( $field ) ) {
                try {
                    $this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
                } catch ( Exception $e ) {
                    $this->add_error( $e->getMessage() );
                }
            }
        }

        return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ) );
    }


    /**
     * Validate title field
     * 
     * @param $key string title
     * @param $value string
     * 
     * @return $value if valid
     * 
     * @since 0.0.3
     */
    public function validate_title_field( $key, $value ) {
        $value = trim( $value );
        if ( $value == "" ) {
            $this->add_error( "title should not be empty" );
            // $this->display_errors();
        } else {
            return $value;
        }
    }

    /**
     * Validate receive address
     * 
     * @param $key string receive_addr
     * @param $value string
     * 
     * @return $value if valid else display error
     * 
     * @since 0.0.3
     */
    public function validate_receive_addr_field( $key, $value ) {
        if ( !Utils::isAddress($value) ) {
            $this->add_error( "receive address is not valid" );
            // $this->display_errors();
        } else {
            return $value;
        }
    }

    /**
     * Validate receive token symbold
     * 
     * @param $key string receive_token_symbol
     * @param $value string|null
     * @return $value if valid else display error
     * 
     * @since 0.0.3
     */
    public function validate_receive_token_symbol_field( $key, $value ) {
        $support_tokens = $this->get_list_token_supported();
        if ( !in_array( $value, $support_tokens ) ) {
            $this->add_error( "receive token is not supported" );
            // $this->display_errors();
        } else {
            return $value;
        }
    }

    /**
     * Validate mode field
     * @param $key string mode
     * @param $value string
     * 
     * @return $value if valid 
     * @since 0.0.3
     */
    public function validate_mode_field( $key, $value ) {
        $modes = array("iframe", "tab", "popup");
        if ( !in_array( $value, $modes ) ) {
            $this->add_error( "Widget mode is not valid" );
            // $this->display_errors();
        } else {
            return $value;
        }
    }

    /**
     * Validate block confirmation field
     * 
     * $key block_confirmation
     * $value input value
     * 
     * return $value if valid/display error if not valid 
     * @since 0.0.3
     */
    public function validate_block_confirmation_field( $key, $value ) {
        if ( $value < 1 ) {
            $this->add_error( "block confirmation must greater than 0" );
            $this->display_errors();
        } else {
            $this->display_errors();
            return $value;
        }
    }

    /**
     * Insert Kyber logo into checkout page
     * 
     * @param null
     * @return string gateway icon
     * 
     * @since 0.0.1
     */
    public function get_icon() {
		$icons_str = '<img src="' . WC_KYBER_PLUGIN_URL . '/admin/images/kyber.svg" class="kyber-icon" alt="Kyber" />';

		return apply_filters( 'woocommerce_gateway_icon', $icons_str, $this->id );
    }

    /**
     * Get list token supported from kyber
     * 
     * @param null
     * @return array list of supported token
     * 
     * @since 0.0.1
     */
    public function get_list_token_supported() {
        $tracker_url = sprintf('https://tracker.kyber.network/api/tokens/supported?chain=%s', $this->get_option( 'network' ) );
        $response = wp_remote_get( $tracker_url );

        $response_body= $response['body'];
        $data = json_decode( $response_body );

        $result = array();
        for ( $index = 0; $index < count( $data); $index++ ) {
            $result[$data[$index]->symbol] = $data[$index]->symbol;
            $this->supported_tokens[$data[$index]->symbol] = $data[$index]->contractAddress;
        }

        return $result;
    }

    /**
     * Override process_payment from WC_Payment_Gateway class
     * 
     * @param integer order id
     * @return array redirect to place order payment 
     * 
     * @since 0.0.1 
     */
    public function process_payment( $order_id ) {
        
        global $woocommerce;
        $order = new WC_Order( $order_id );

        // check if setting is correct for payment
        $setting_ok = $this->validate_gateway_settings();
        if ( !$setting_ok ) {
            return;
        }

        // update order network is current network setting
        $order->update_meta_data( 'network', $this->get_option( 'network' ) );

        // update order receive address is current receive address
        $order->add_meta_data( 'receive_addr', $this->get_option( 'receive_addr' ), true );

        // update order receive symbol is current receive symbol
        $order->add_meta_data( 'receive_symbol', $this->get_option( 'receive_token_symbol' ), true );

        $order->save();

        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );
    }

    /**
     * Display transaction hash on order detail page
     * 
     * @param WC_Abstract_Order order
     * @return string transaction hash
     * 
     * @since 0.0.1
     */
    public function add_tx_hash_to_order( $order ) {
        $order_tx = $order->get_meta("tx");
        $network = $order->get_meta( 'network' );
        $tx_status = $order->get_meta( 'tx_status' );

        $response = '';

        if ( $order_tx != "" ) {
            $response .= sprintf("<tr class='woocommerce-table__line-item order_item' >
            <td class='woocommerce-table__product-name product-name'> 
            %s </td>
            <td class='woocommerce-table__product-total product-total order-tx-hash'>
            <a href='https://%s.etherscan.io/tx/%s' target='_blank'>%s</a>
            </td></tr>", __('Order transaction hash', 'woocommerce-gateway-kyber'), $network, $order_tx, $order_tx);
        }

        if ( $tx_status != "" ) {
            $response .= sprintf("<tr class='woocommerce-table__line-item order_item' >
            <td class='woocommerce-table__product-name product-name'> 
            %s </td>
            <td class='woocommerce-table__product-total product-total'>
            %s
            </td></tr>", __('Tx Status', 'woocommerce-gateway-kyber'), $tx_status); 
        }

        if ( $network != "" ) {
            $response .= sprintf("<tr class='woocommerce-table__line-item order_item' >
            <td class='woocommerce-table__product-name product-name'> 
            %s </td>
            <td class='woocommerce-table__product-total product-total'>
            %s
            </td></tr>", __('Network', 'woocommerce-gateway-kyber'), $network); 
        }
        echo $response;
    }

    /**
     * Check if Kyber g,ateway config is correct
     * 
     * @return bool
     * 
     * @since 0.0.1
     */
    public function validate_gateway_settings() {
        $receiveAddr = $this->get_option( 'receive_addr' );
        if ( !$receiveAddr ) {
            wc_add_notice( __('Receive address is empty. You cannot use pay by token, please contact your shop about this issue', 'woocommerce-gateway-kyber'), 'error' );
            return false;
        }

        $receiveToken = $this->get_option( 'receive_token_symbol' );
        if ( !$receiveToken ) {
            wc_add_notice( __('Receive token is not supported.', 'woocommerce-gateway-kyber'), 'error' );
            return false;
        }
       
        $network = $this->get_option( 'network' );
        if ( $network != 'ropsten' && $network != 'mainnet' ) {
            wc_add_notice( __('Network is not valid.', 'woocommerce-gateway-kyber'), 'error' );
            return false;
        }

        $mode = $this->get_option( 'mode' );
        if ( $mode != 'tab' && $mode != 'iframe' && $mode != 'popup' ) {
            wc_add_notice( __('Widget mode is not valid.', 'woocommerce-gateway-kyber'), 'error' );
            return false;
        }

        return true;
    }

    /**
     * Build Kyber widget redirect url
     *
     * @param  WC_Abstract_Order $order 
     * @return string checkout url for widget
     *  
     * @since 0.0.1
     */
    public function get_checkout_url( $order ) {
        // $version = $this->get_option( 'version' );
        $endpoint = "https://widget.kyber.network/v0.6.2/?type=pay&theme=light&paramForwarding=true&";


        $callback_url = get_site_url() . '/wc-api/kyber_callback';

        if ( !$this->validate_gateway_settings() ) {
            return;
        }

        $receiveAddr = $this->get_option( 'receive_addr' );
        $receiveToken = $this->get_option( 'receive_token_symbol' );
        $network = $order->get_meta( 'network' );
        if ( !$network ) {
            return;
        }
        $mode = $this->get_option( 'mode' );
        

        $receiveAmount = $order->get_meta( 'token_price' );
        if ( !$receiveAmount ) {
            $receiveAmount = $this->get_token_price( $order );
            error_log( print_r( sprintf("cannot get token price from order meta data, try to get from blockchain: %s", $receiveAmount), 1 ) );
        }

        $endpoint .= 'mode='. $mode .'&receiveAddr=' . $receiveAddr . '&receiveToken=' . $receiveToken . '&callback=' . $callback_url . '&receiveAmount=' . $receiveAmount;
        $endpoint .= '&network=' . $network;

        // commission id is optional
        $commissionID = $this->get_option( 'commission_id' );
        if ( $commissionID ) {
            $endpoint .= '&commissionId=' . $commissionID;
        }

        // add custom params
        $order_id = $order->get_id();

        $endpoint .= '&order_id=' . strval($order_id);

        // paymentData will be store on blockchain
        $endpoint .= '&paymentData=' . strval($order_id);

        return $endpoint;
    }

    /**
     * Handle callback from Kyber widget
     * 
     * @since 0.0.1
     * 
     */
    public function handle_kyber_callback() {
        if ( ( 'POST' !== $_SERVER['REQUEST_METHOD'] )) {
			return;
        }

        $request_body    = file_get_contents( 'php://input' );
        $request_header = $this->get_request_headers();

        if ( strpos($request_header['Content-Type'], 'application/x-www-form-urlencoded') !== false ) {
            parse_str( $request_body, $dataStr );
            $dataJSON = json_encode($dataStr);
            $data = json_decode($dataJSON);
        }

        $valid = $this->validate_callback_params($data);
        header( "Access-Control-Allow-Origin: *", true );
        if ( !$valid ) {
            status_header( 403 );
            die();
        }
        
        // get data from callback
        $order_id = $data->order_id;
        $tx = $data->tx;
        $network = $data->network;

        $order = wc_get_order( $order_id );

        // check if network from callback is match with order network
        // if not the pay is not valid - ignore
        if ( $network != $order->get_meta( 'network' ) ) {
            return;
        }

        // Reduce stock levels
        $order->reduce_order_stock();

        // Save transaction hash to order
        $order->update_meta_data("tx", $tx);
        $order->update_meta_data("network", $network);
        $order->add_meta_data("tx_status", "pending", true);
        $order->update_meta_data("payment_time", time());
        $order->save();

        // Mark as on-hold (we're awaiting cheque)
        $order->update_status('on-hold', __("Awaiting cheque payment", "woocommerce-gateway-kyber"));
    }

    /**
     * Check if transaction hash already saved in db
     * 
     * @param string tx
     * @return bool 
     * 
     * @since 0.0.1
     */
    public function transaction_exists($tx, $order_id) {
        $args = array(
            'post_type'   => 'shop_order',
            'post_status' => array('wc-cancelled','wc-on-hold', 'wc-processing', 'wc-pending-payment', 'wc-completed', 'wc-refunded', 'wc-failed'),
            'numberposts' => 5,
            'meta_key'     => 'tx',
            'meta_value'   => $tx,
        );
        $current_post = get_posts($args);

        if( $current_post && $current_post[0]->ID != $order_id ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validate callback params
     * 
     * @since 0.0.1
     */
    private function validate_callback_params( $request ) {
        $order_id = $request->order_id;
        $tx = $request->tx;
        if ( $this->transaction_exists($tx, $order_id) ) {
            return false;
        }
        return true;
    }


	/**
	 * Gets the incoming request headers. Some servers are not using
	 * Apache and "getallheaders()" will not work so we may need to
	 * build our own headers.
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function get_request_headers() {
		if ( ! function_exists( 'getallheaders' ) ) {
			$headers = array();

			foreach ( $_SERVER as $name => $value ) {
				if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
					$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
				}
			}

			return $headers;
		} else {
			return getallheaders();
		}
	}

    /**
     * 
     * Embed widget into order received page
     * 
     * @param integer order id
     * @return string widget button
     * 
     * @since 0.0.1
     */
    public function embed_kyber_widget_button( $order_id ) {
        $order = wc_get_order($order_id);
        $order_status = $order->get_status();

        if ( $order->get_payment_method() == 'kyber' && ( $order_status == "pending" || $order_status == "failed" )  ) {
            $endpoint = $this->get_checkout_url( $order );

            $widget_text = apply_filters( 'kyber_widget_text', __('Pay by tokens', 'woocommerce-gateway-kyber') );

            printf("<a href='%s'
            class='theme-emerald kyber-widget-button' name='KyberWidget - Powered by KyberNetwork' title='Pay by tokens'
            target='_blank'>%s</a>", $endpoint, $widget_text);
        }
    }

    /**
     * Get token rate from dai to receive token and return it in payment method description
     *
     * @return string token price for cart
     * @since 0.2
     */
    public function payment_fields() {
        $description = $this->description;
        if ( $description ) {
            echo $description;
        }
        $total = WC()->cart->total;
        if (!$total) {
            global $wp;
            $order_id = $wp->query_vars['order-pay'];
            $order = new WC_Order( $order_id );
            $total = $order->get_total();
        }
        $receiveToken = $this->get_option( "receive_token_symbol" );
        $receiveTokenAddress = $this->supported_tokens[$receiveToken];
        $dai = $this->supported_tokens["DAI"];

        $functionName = "getExpectedRate";
        $src_qty = 1000000000000000000;
        $rate = 0;
        $this->proxy_contract->at("0x818E6FECD516Ecc3849DAf6845e3EC868087B755")->call($functionName, $dai, $receiveTokenAddress, $src_qty, function( $err, $result ) use (&$rate) {
            if ($err != null) {
                error_log( print_r( $err->getMessage(), 1 ) );
            }
            $expectedRate =  strval($result['expectedRate']->value);
            $rate = bcdiv( $expectedRate, $this->gwei, 18); 
        });

        $token_price = $rate * $total;

        $token_price_html = sprintf('</br><p></p>
        <div class="kyber-cart-token-price">
        <strong>%s</strong>
        <span class="receive-token">%s</span>
        </div>',
        esc_html($token_price),
        esc_html($receiveToken));

        echo $token_price_html;
    }

    /**
     * Get token price for order
     * 
     * @return float token price
     * 
     * @since 0.2
     */
    public function get_token_price( $order ) {
        $receiveToken = $this->get_option( "receive_token_symbol" );
        $receiveTokenAddress = $this->supported_tokens[$receiveToken];
        $dai = $this->supported_tokens["DAI"];

        $functionName = "getExpectedRate";
        $src_qty = 1000000000000000000;
        $rate = 0;
        $this->proxy_contract->at("0x818E6FECD516Ecc3849DAf6845e3EC868087B755")->call($functionName, $dai, $receiveTokenAddress, $src_qty, 
        function( $err, $result ) use (&$rate) {
            if ($err != null) {
                error_log( print_r( sprintf("get dai rate from blockchain failed: %s", $err->getMessage()), 1 ) );
            }
            $expectedRate =  strval($result['expectedRate']->value);
            $rate = bcdiv( $expectedRate, $this->gwei, 18); 
        }); 

        $token_price = $order->get_total() * $rate;
        $order->add_meta_data( "token_price", $token_price, true );
        
        return $token_price;
    }

    public function kyber_price_filter( $order_id ) {
        error_log( print_r( sprintf("kyber price filter: %s", $order_id), 1) );
        $total_order_token_price_html = sprintf('<p>%s</p>', esc_html($order_id));
        return $total_order_token_price_html;
    }

}