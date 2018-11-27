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
]);

$tx = '0xf513db1b7de61ba88afecd5a9a228c983b5b0b6b48bbb56f859bcd60dafc245d';
$data = $monitor->checkStatus($tx);

```
Currently, the following options are supported.

|     Field               |   Value    |      Detail                                                        |
|-------------------------|------------|--------------------------------------------------------------------|
|     node                |     String |    URL of node                                                     |
|     network             |     String |    Ethereum network                                                |
|     blockConfirm        |     Number |    Number of block confirmation                                    |
|     txLostTimeout       |     Number |    Time until declare a tx is lost                                 |
|     intervalRefetchTx   |     Number |    Tx will be re-check after the time until data gotten            |

Default values:
```
  'node' => 'https://ropsten.infura.io',
  'network' => 'ropsten',
  'blockConfirm' => 7,
  'txLostTimeout' => 15, // minutes
  'intervalRefetchTx' => 5, // seconds
```

#### Response data

```php
[
  "status" => "SUCCESS",  // "FAIL" , "LOST"
  "from" => [
    "address" => "0xeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee",
    "decimal" => 18,
    "symbol" => "ETH",
    "amount" => "0.00162",
  ],
  "to" => [
    "address" => "0x4e470dc7321e84ca96fcaedd0c8abcebbaeb68c6",
    "decimal"=> 18,
    "symbol" => "KNC",
    "amount" => "0.6",
  ],
  "sentAddress" => "0x8d61ab7571b117644a52240456df66ef846cd999",
  "receivedAddress" => "0x63b42a7662538a1da732488c252433313396eade",
  "timestamp" => 1539243951,
  "type" => "pay",  // "transfer" , "trade"
]
```
