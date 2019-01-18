# Monitor Kyber Transaction (Swap / Widget)

## Install
Set minimum stability to dev

```
"minimum-stability": "dev"
```

Then

```console
$ composer require kyber/monitor-kyber-tx
```

## Usage

```php
<?php 

require_once __DIR__ . '/vendor/autoload.php';
use ETH\Monitor;

$monitor = new Monitor([
  'node' => 'https://ropsten.infura.io',
  'network' => 'ropsten',
  'blockConfirm' => 7,
  'txLostTimeout' => 15, // minutes
  'intervalRefetchTx' => 10, // seconds
  'checkPaymentValid' => true,
  'receivedAddress' => '0x63b42a7662538a1da732488c252433313396eade',
  'amount' => 0.05,
  'receivedToken' => "OMG",
  'useIntervalLoop' => false
]);

$tx = '0x5388158e57fecefd3a850283f606ab58e4670c29f730f470ab7f413551c01af4';
$data = $monitor->checkStatus($tx);

```
Currently, the following options are supported.

|     Field               |   Type    |   Default                     |     Detail                                                          |
|-------------------------|-----------|-------------------------------|---------------------------------------------------------------------|
|     node                |  String   | https://ropsten.infura.io     |     URL of node                                                     |
|     network             |  String   | ropsten (mainnet, rinkerby)   |     Ethereum network                                                |
|     blockConfirm        |  Number   | 7                             |     Number of block confirmation                                    |
|     txLostTimeout       |  Number   | 15                            |     Time until declare a tx is lost                                 |
|     intervalRefetchTx   |  Number   | 5                             |     Tx will be re-check after the time until data gotten            |
|     checkPaymentValid   |  Boolean  | false                         |     Set "true" if you want to check payment data                    |
|     receivedAddress     |  String   |                               |     Wallet your want to receive after transaction                   |
|     amount              |  Number   |                               |     Amount your want to receive after transaction                   |
|     receivedToken       |  String   |                               |     Token your want to receive after transaction                    |
|     useIntervalLoop     |  Boolean  | false                         |     If you don't want to get response data until tx success         |
|     useDatabase         |  Boolean  | false                         |     Set true if you want to use database to store tx, table with name "kyber_tx" would be created. And you should config server to run cronjob to update tx status (See example below)        |
|     servername          |  String   |                               |     Database server name (only support MySql version in this version)|
|     username            |  String   |                               |     Database username                                               |
|     password            |  String   |                               |     Database password                                               |
|     db                  |  String   |                               |     Database db name                                                |

#### Response data

```php
[
  "status" => "SUCCESS",  // "FAIL" , "LOST"
  "from" => [
    "address" => "0xeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee",
    "decimal" => 18,
    "symbol" => "ETH",
    "amount" => "0.001",
  ],
  "to" => [
    "address" => "0x4e470dc7321e84ca96fcaedd0c8abcebbaeb68c6",
    "decimal"=> 18,
    "symbol" => "KNC",
    "amount" => "0.368905346628",
  ],
  "sentAddress" => "0x3cf628d49ae46b49b210f0521fbd9f82b461a9e1",
  "receivedAddress" => "0x3cf628d49ae46b49b210f0521fbd9f82b461a9e1",
  "timestamp" => 1543398038,
  "type" => "pay",  // "transfer" , "trade"

  "paymentValid" => "true",
  "paymentData" => "abc123asdasdasdassd3e423wsdfsdfsdfsdfdsfsdfsdfdsfsdfghfgh",
]
```

#### Example script to check pending transactions which stored in database.
You need to config your server to run this script via cronjob.

```php
<?php 

require_once __DIR__ . '/vendor/autoload.php';
use ETH\Monitor;

$monitor = new Monitor([
  'node' => 'https://ropsten.infura.io',
  'network' => 'ropsten',
  'useIntervalLoop' => false
]);

$monitor->recheckTxDB();

```