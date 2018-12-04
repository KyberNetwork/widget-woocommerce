<?php

namespace ETH;
require_once 'Helper.php';
require_once 'Database.php';

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Exception;

class Monitor{

  protected $node = 'https://ropsten.infura.io';
  protected $blockConfirm = 7;
  protected $txLostTimeout = 15; // minutes
  protected $intervalRefetchTx = 5; // sec
  protected $currentBlock = null;
  protected $eth = null;
  protected $network = 'ropsten';
  protected $config = null;
  protected $useDatabase = false;
  protected $checkPaymentValid = false;
  protected $receivedAddress = null;
  protected $amount = null;
  protected $receivedToken = null;
  protected $useIntervalLoop = true;

  public function __construct($params = []){
    if($params['node']) $this->node = $params['node'];
    if($params['network']) $this->network = $params['network'];
    if($params['blockConfirm'] && $params['blockConfirm'] > 0) 
      $this->blockConfirm = $params['blockConfirm'];
    if($params['txLostTimeout']) $this->txLostTimeout = $params['txLostTimeout'];
    if($params['intervalRefetchTx']) 
      $this->intervalRefetchTx = $params['intervalRefetchTx'];
    if($params['useDatabase']){
      $this->useDatabase = $params['useDatabase'];
      if($params['servername'] && $params['username'] && $params['password'] && $params['db']){
        connectDB($params['servername'], $params['username'], $params['password'], $params['db']);
      }else{
        die('Cannot connect database. Please check again!');
      }
    }
    $this->checkPaymentValid = $params['checkPaymentValid'] ? true : false;
    if($params['receivedAddress']) $this->receivedAddress = $params['receivedAddress'];
    if($params['amount']) $this->amount = $params['amount'];
    if($params['receivedToken']) $this->receivedToken = $params['receivedToken'];
    $this->useIntervalLoop = $params['useIntervalLoop'] ? true : false;
    $this->config = readConfig($this->network);

    $web3 = new Web3(new HttpProvider(new HttpRequestManager($this->node, 5)));
    $this->eth = $web3->eth;
    $this->utils = $web3->utils;
  }

  public function checkStatus($tx){
    $txLost = false;
    $this->txData = [
      'txReceipt' => null,
      'tx' => null,
    ];
    if($this->useDatabase){
      insertDB([
        'hash' => $tx, 
        'status' => 'PENDING',
      ]);
    }
    if($tx){
      $starttime = time();
      while(true){
        if(!$this->txData['txReceipt']){
          $this->eth->getTransactionReceipt($tx, function ($err, $txReceipt) {
            if ($err) {
              logDebug($err->getMessage());
              sleep($this->intervalRefetchTx);
            }
            if ($txReceipt) {
              $this->txData['txReceipt'] = $txReceipt;
            }
          });
        }
        if(!$this->txData['tx']){
          $this->eth->getTransactionByHash($tx, function ($err, $txHash) {
            if ($err) logDebug($err->getMessage());
            if ($txHash) {
              $this->txData['tx'] = $txHash;
            }
          });
        }
        if($this->txData['txReceipt'] && $this->txData['tx']){
          break;
        }else{
          if($this->useDatabase){
            $pendingTx = getPendingTx($tx)[0];
            $starttime = strtotime($pendingTx['created_at']);
          }
          $endtime = time(true);
          $timediff = ($endtime - $starttime) / 60;
          if($timediff > $this->txLostTimeout){
            $txLost = true;
            break;
          }
        }

        if($this->useDatabase || !$this->useIntervalLoop) return [ 'status' => 'PENDING' ];
      }
    }
    if($txLost){
      if($this->useDatabase){
        updateDB([
          'hash' => $tx, 
          'status' => 'LOST',
        ]);
      }
      return [ 'status' => 'LOST' ];
    }else{
      return $this->handleData($tx);
    }
  }

  protected function handleData($tx){
    $currentBlock = null;
    while(true){
      $this->eth->getBlockByNumber('latest', false, function ($err, $block) use (&$currentBlock) {
        if ($err !== null) logDebug($err->getMessage());
        if($block){
          $currentBlock = hexdec($block->number);
        }
      });
      if($currentBlock){
        break;
      }
    }

    $txReceipt = $this->txData['txReceipt'];
    $txBlockNumber = hexdec($txReceipt->blockNumber);
    $blockConfirmed = $currentBlock - $txBlockNumber;
    if($blockConfirmed > $this->blockConfirm){
      $status = hexdec($txReceipt->status);
      if($status){
        $from = $to = $sentAddress = $receivedAddress = null;
        if(strtolower($txReceipt->to) == strtolower($this->config->payWrapper)){
          $payData = $this->handlePay();
          $type = 'pay';
          $from = $payData['src'];
          $to = $payData['dest'];
          $sentAddress = $payData['sentAddress'];
          $receivedAddress = $payData['receivedAddress'];
          $paymentData = $payData['paymentData'];
        }elseif(strtolower($txReceipt->to) == strtolower($this->config->network)){
          $tradeData = $this->handleTrade();
          $type = 'trade';
          $from = $tradeData['src'];
          $to = $tradeData['dest'];
          $sentAddress = $tradeData['sentAddress'];
          $receivedAddress = $tradeData['receivedAddress'];
        }else{
          $transferData = $this->handleTransfer();
          $from = $transferData['src'];
          $to = $transferData['dest'];
          $sentAddress = $transferData['sentAddress'];
          $receivedAddress = $transferData['receivedAddress'];
          $type = 'transfer';
        }
        $txBlock = null;
        while(true){
          $this->eth->getBlockByNumber($txBlockNumber, false, function ($err, $block) use (&$txBlock) {
            if ($err !== null) logDebug($err->getMessage());
            if($block){
              $txBlock = $block;
            }
          });
          if($txBlock){
            break;
          }
        }
        $checkPaymentValid = $this->checkPaymentValidFunc($receivedAddress, $to['amount'], $to['symbol']);
        if($this->useDatabase){
          $updateData = [
            'hash' => $tx,
            'status' => 'SUCCESS', 
            'source_amount' => $from['amount'], 
            'source_symbol' => $from['symbol'], 
            'dest_amount' => $to['amount'], 
            'dest_symbol' => $to['symbol'],
          ];
          if($checkPaymentValid){
            $updateData['payment_valid'] = 1;
          }else{
            $updateData['payment_valid'] = 0;
          }
          updateDB($updateData);
        }
        $returnData = [
          'txHash' => $tx,
          'status' => 'SUCCESS',
          'from' => $from,
          'to' => $to,
          'sentAddress' => $sentAddress,
          'receivedAddress' => $receivedAddress,
          'timestamp' => hexdec($txBlock->timestamp),
          'type' => $type,
        ];
        if($this->checkPaymentValid){
          $returnData['paymentValid'] = $checkPaymentValid ? true : false;
          $returnData['paymentData'] = $paymentData ? $paymentData : '';
        }
        return $returnData;
      }else{
        if($this->useDatabase){
          updateDB([
            'hash' => $tx, 
            'status' => 'FAIL', 
          ]);
        }
        return [ 'status' => 'FAIL' ];
      }
    }else{
      if($this->useDatabase || !$this->useIntervalLoop) return [ 'status' => 'PENDING' ];
      $this->txData = [
        'txReceipt' => null,
        'tx' => null,
      ];
      sleep($this->intervalRefetchTx);
      return $this->checkStatus($tx);
    }
  }

  protected function handleTrade(){
    $txReceipt = $this->txData['txReceipt'];
    $tx = $this->txData['tx'];
    
    $eventLogParams = [
      'srcAddress' => 0, 
      'srcToken' => 1, 
      'srcAmount' => 2, 
      'destAddress' => 3, 
      'destToken' => 4, 
      'destAmount' => 5
    ];

    $src = [];
    $dest = [];
    foreach($txReceipt->logs as $log){
      if($log->topics[0] == $this->config->trade_topic){
        $readLogData = readTxLog($log->data);
        $hexSrc = $readLogData[$eventLogParams['srcToken']];
        $hexDest = $readLogData[$eventLogParams['destToken']];
        $hexActualSrcAmount = $readLogData[$eventLogParams['srcAmount']];
        $hexActualDestAmount = $readLogData[$eventLogParams['destAmount']];

        $src['address'] = toAddress($hexSrc);
        $dest['address'] = toAddress($hexDest);

        foreach($this->config->tokens as $token) {
          if($token->address == $dest['address']){
            $dest['decimals'] = $token->decimals;
            $dest['symbol'] = $token->symbol;
            $dest['amount'] = toRealAmount(hexdec($hexActualDestAmount), $token->decimals);
            $dest['amount'] = strval($dest['amount']);
          }
          if($token->address == $src['address']){
            $src['decimals'] = $token->decimals;
            $src['symbol'] = $token->symbol;
            $src['amount'] = toRealAmount(hexdec($hexActualSrcAmount), $token->decimals);
            $src['amount'] = strval($src['amount']);
          }
          if(isset($dest['symbol']) && isset($src['symbol'])) break;
        }
        break;
      }
    }

    $sentAddress = toAddress($readLogData[$eventLogParams['srcAddress']]);
    $receivedAddress = toAddress($readLogData[$eventLogParams['destAddress']]);

    return [
      'src' => $src, 
      'dest' => $dest, 
      'sentAddress' => $sentAddress, 
      'receivedAddress' => $receivedAddress
    ];
  }

  protected function handleTransfer(){
    $txReceipt = $this->txData['txReceipt'];
    $tx = $this->txData['tx'];
    $value = hexdec($tx->value);
    $token = 'ETH';
    $sentAddress = $tx->from;
    if($value > 0){
      $decimals = $this->config->tokens->$token->decimals;
      $amount = strval(toRealAmount($value, $decimals));
      $receivedAddress = $tx->to;
    }else{
      foreach($this->config->tokens as $t) {
        if($t->address == $tx->to){
          $token = $t->symbol;
          $decimals = $t->decimals;
          break;
        }
      }
      $readLogData = readTxLog($tx->input);
      $receivedAddress = toAddress($readLogData[0]);
      $amount = strval(toRealAmount(hexdec($readLogData[1]), $decimals));
    }
    return [
      'src' => [
        'symbol' => $token,
        'amount' => $amount,
      ], 
      'dest' => [
        'symbol' => $token,
        'amount' => $amount,
      ], 
      'sentAddress' => $sentAddress, 
      'receivedAddress' => $receivedAddress
    ];
  }

  protected function handlePay(){
    $txReceipt = $this->txData['txReceipt'];
    $tx = $this->txData['tx'];
    $readInputData = readTxLog($tx->input);
    $src = [];
    $dest = [];

    $sentAddress = $tx->from;
    $receivedAddress = toAddress($readInputData[3]);
    $paymentData = null;

    foreach($txReceipt->logs as $log){
      if($log->address == $this->config->payWrapper){
        $readLogData = readTxLog($log->data);
        $paymentData = $this->getPaymentData($log->data);
        $hexSrc = $readInputData[0];
        $hexDest = $readInputData[2];
        $hexActualSrcAmount = $readInputData[1];
        $hexActualDestAmount = $readLogData[1];

        $src['address'] = toAddress($hexSrc);
        $dest['address'] = toAddress($hexDest);

        foreach($this->config->tokens as $token) {
          if($token->address == $dest['address']){
            $dest['decimals'] = $token->decimals;
            $dest['symbol'] = $token->symbol;
            $dest['amount'] = toRealAmount(hexdec($hexActualDestAmount), $token->decimals);
            $dest['amount'] = strval($dest['amount']);
          }
          if($token->address == $src['address']){
            $src['decimals'] = $token->decimals;
            $src['symbol'] = $token->symbol;
            $src['amount'] = toRealAmount(hexdec($hexActualSrcAmount), $token->decimals);
            $src['amount'] = strval($src['amount']);
          }
          if(isset($dest['symbol']) && isset($src['symbol'])) break;
        }
        break;
      }
    }
    
    return [
      'src' => $src, 
      'dest' => $dest, 
      'sentAddress' => $sentAddress, 
      'receivedAddress' => $receivedAddress,
      'paymentData' => $paymentData
    ];
  }

  protected function getPaymentData($logData){
    $readLogData = readTxLog($logData);
    $paymentData = '';
    foreach($readLogData as $key => $value){
      if($key < 4) continue;
      $paymentData = $paymentData . $this->utils->hexToBin($value);
    }
    return $paymentData;
  }

  public function recheckTxDB(){
    $pendingTxs = getPendingTx();
    foreach($pendingTxs as $tx){
      $this->checkStatus($tx['hash']);
    }
  }

  protected function checkPaymentValidFunc($receivedAddress, $amount, $receivedToken){
    if(
      $this->checkPaymentValid &&
      strtolower($receivedAddress) == strtolower($this->receivedAddress) &&
      $amount >= $this->amount &&
      $receivedToken == $this->receivedToken
    ){
      return true;
    }
    return false;
  }
}