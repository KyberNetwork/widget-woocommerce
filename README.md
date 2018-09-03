# Introduction
Kyber Payment Gateway for WooCommerce 

![screenshot](https://github.com/KyberNetwork/widget-woocommerce/blob/master/assets/images/screenshot-2.png)

# Requirements

- PHP version 5.6+
- WordPress 3.8+
- WooCommerce 3.0+

# Installation

1. Download the plugin [zip file](https://github.com/halink0803/woo-kyber-payment/releases)
2. Unzip to your wordpress folder /wp-content/plugins
3. Activate plugin /wp-admin/plugins.php

# Usage

After activate, you will find plugin settings under WordPress Dashboard/WooCommerce/Payment. Enable payment gateway will add an option to pay by tokens to your checkout page.

# Config

Plugin settings include:

- **Title**
  Title will be display as a payment option for user in checkout pages.


- **Description**
  Description will be display under the payment option, describe what payment gateway is for.


- **Receive Address**
  This is the wallet address where you want to receive the payment. This address is required, without this address payment will not be processed.

- **Receive Token Symbol**
  This is token symbol (currency) which you want to receive from payment. This token is only in list accept to swap by [Kyber Network](https://kyber.network/swap/eth_knc).

- **Network**
  This is Ethereum network where you want the payment gateway runs on. There are 2 options, Ropsten for test and Mainnet for your production.

- **Mode**
  This is mode for display the [Kyber Widget](kyber widget url). There are 3 options available.

- **Network node endpoint**
  This options is for tracking tx status