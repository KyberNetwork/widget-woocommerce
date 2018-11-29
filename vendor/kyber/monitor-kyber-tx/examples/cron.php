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

    'useDatabase' => true,
    'servername' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'db' => 'kyber_tx',
  ]);

  $monitor->recheckTxDB();