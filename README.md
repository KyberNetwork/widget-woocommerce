<h1 align="center">Kyber Widget WooCommerce</h1>
<p align="center" style="text-align: center;">
<a href="https://packagist.org/packages/kyber-network/widget-woocommerce">
  <img src="https://poser.pugx.org/kyber-network/widget-woocommerce/version" alt="Latest Stable Version" />
</a>
<a href="https://packagist.org/packages/kyber-network/widget-woocommerce">
  <img src="https://poser.pugx.org/kyber-network/widget-woocommerce/downloads" alt="Total Downloads" />
</a>
<a href="//packagist.org/packages/kyber-network/widget-woocommerce">
  <img src="https://poser.pugx.org/kyber-network/widget-woocommerce/v/unstable" alt="Latest Unstable Version" />
</a>
<a href="./LICENSE">
  <img src="https://img.shields.io/badge/license-MIT-green.svg" alt="MIT licensed" />
</a>
<a href="https://t.me/KyberDeveloper">
  <img src="https://img.shields.io/badge/chat%20on-telegram-brightgreen.svg" alt="Social" />
</a>
</p>

# Introduction

Kyber Payment Gateway for WooCommerce 

![screenshot](https://github.com/KyberNetwork/widget-woocommerce/blob/master/assets/images/screenshot-2.png)

# Requirements

- PHP version 5.6+
- WordPress 3.8+
- WooCommerce 3.0+

# Installation

## Install from source code

1. Clone code repo to your /wp-content/plugins/

```shell
git clone https://github.com/KyberNetwork/widget-woocommerce
```

2. Install required components

```shell
composer install
```

3. Activate plugin


## Install from zip file

1. Download the plugin [zip file](https://github.com/KyberNetwork/widget-woocommerce/releases)
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
  This is mode for display the [Kyber Widget](https://developer.kyber.network/docs/WidgetOverview). There are 3 options available.

- **Network node endpoint**
  This options is for tracking tx status

- **Block Confirmation**
  Number of block confirmation for confirm tx as success

- **Commission ID**
  Registered ETH address that is part of the [fee sharing program](https://developer.kyber.network/docs/FeeSharingGuide)

## Sample Walkthrough

You can find a sample walkthough on how to use the plugin (with screenshot) [here](./sample.md)

## Developer documentation and contribution guidelines

If you are a developer and want to contribute to the repository, please check out the [developer docs](./developer.md) and [contribution guidelines](./CONTRIBUTING.MD)
  
## License

[The MIT License (MIT)](LICENSE)
