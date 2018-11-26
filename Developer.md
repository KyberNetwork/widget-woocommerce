# Developer

## Order status:

The order status are following the [Managing Orders](https://docs.woocommerce.com/document/managing-orders/) of Woocommerce. There is only difference in **On-Hold** status.

When the order is in **On-Hold** status, it will be monitor the tx-hash by [monitor-eth-tx](https://packagist.org/packages/tranbaohuy/monitor-eth-tx)

## Widget configs

Widget configs in the plugin are base on the widget configs from its [generator](https://developer.kyber.network/docs/WidgetGenerator/) and modified to fit Woocommerce model.

![screenshot-1](./assets/images/screenshot-1.png)

Those configs are define in widget as a [payment gateway settings](https://docs.woocommerce.com/document/payment-gateway-api/) in Woocommerce and define in [kyber-settings.php](kyber-settings.php)

The main logic of handling payment through widget is defined in [class-woo-kyber-payment-gateway.php](./includes/class-woo-kyber-payment-gateway.php)

## The flow of an order

Order created -> Pending payment -> On-hold(payment broadcasted - mornitoring) -> Processing (payment success) or Failed (payment failure)