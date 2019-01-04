<?php

require_once 'Helper.php';

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class Connection
{
  public function __construct($network, $node = null)
  {
    if(!$node){
      $node = 'https://'. $network .'.infura.io';
    }
    $web3 = new Web3(new HttpProvider(new HttpRequestManager($node, 5)));
    $this->eth = $web3->eth;
    $this->utils = $web3->utils;
  }

  public function getTransactionReceipt($tx)
  {
    $receipt = null;
    $this->eth->getTransactionReceipt($tx, function ($err, $txReceipt) use (&$receipt) {
      if ($err) {
        logDebug($err->getMessage());
        sleep($this->intervalRefetchTx);
      }
      $receipt = $txReceipt;
    });
    return $receipt;
  }

  public function getTransactionByHash($tx)
  {
    $txData = null;
    $this->eth->getTransactionByHash($tx, function ($err, $txHash) use (&$txData) {
      if ($err){
        logDebug($err->getMessage());
        sleep($this->intervalRefetchTx);
      }
      $txData = $txHash;
    });
    return $txData;
  }

  public function getPaymentData($logData){
    $readLogData = readTxLog($logData);
    $paymentData = '';
    foreach($readLogData as $key => $value){
      if($key < 4) continue;
      $paymentData = $paymentData . $this->utils->hexToBin($value);
    }
    return $paymentData;
  }

  public function getBlockByNumber($blockNumber)
  {
    $data = null;
    $this->eth->getBlockByNumber($blockNumber, false, function ($err, $block) use (&$data) {
      if ($err !== null) logDebug($err->getMessage());
      if($block){
        $data = hexdec($block->number);
      }
    });
    return $data;
  }
}