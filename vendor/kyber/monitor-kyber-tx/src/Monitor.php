<?php

namespace ETH;

require_once 'Helper.php';
require_once 'Database.php';
require_once 'Connection.php';

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Connection;

class Monitor{
  protected $node = 'https://ropsten.infura.io';
  protected $blockConfirm = 7;
  protected $txLostTimeout = 15; // minutes
  protected $intervalRefetchTx = 5; // sec
  protected $txBlockNumber = null;
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
    $this->eth = new Connection($this->network, $params['node']);
  }

  public function checkStatus($tx, $timeInit = null){
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
          $this->txData['txReceipt'] = $this->eth->getTransactionReceipt($tx);
        }
        if(!$this->txData['tx']){
          $this->txData['tx'] = $this->eth->getTransactionByHash($tx);
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

        if($this->useDatabase || !$this->useIntervalLoop) return [ 'hash' => $tx, 'status' => 'PENDING' ];
      }
    }
    if($txLost){
      if($this->useDatabase){
        updateDB([
          'hash' => $tx, 
          'status' => 'LOST',
        ]);
      }
      return [ 'hash' => $tx, 'status' => 'LOST' ];
    }else{
      return $this->handleData($tx);
    }
  }

  protected function handleData($tx){
    $currentBlock = $this->eth->getBlockByNumber('latest');

    $txReceipt = $this->txData['txReceipt'];
    $txBlockNumber = hexdec($txReceipt->blockNumber);
    $this->txBlockNumber = $txBlockNumber;
    $blockConfirmed = $currentBlock - $txBlockNumber;
    if($blockConfirmed > $this->blockConfirm){
      $status = hexdec($txReceipt->status);
      if($status){
        $from = $to = $sentAddress = $receivedAddress = null;
        if(strcasecmp($txReceipt->to, $this->config->payWrapper) == 0){
          $payData = $this->handlePay();
          $type = 'pay';
          $from = $payData['src'];
          $to = $payData['dest'];
          $sentAddress = $payData['sentAddress'];
          $receivedAddress = $payData['receivedAddress'];
          $paymentData = $payData['paymentData'];
        }elseif(strcasecmp($txReceipt->to, $this->config->network) == 0){
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
        $txBlock = $this->eth->getBlockByNumber($txBlockNumber);
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
      if($this->useDatabase || !$this->useIntervalLoop) return [ 'hash' => $tx, 'status' => 'PENDING' ];
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
    
    $eventLogParams = $this->getKyberTradeParams();
    $src = [];
    $dest = [];
    foreach($txReceipt->logs as $log){
      if($log->topics[0] == $eventLogParams['trade_topic']){
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

    $sentAddress = toAddress($readLogData[$eventLogParams['destAddress']]);
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
      if(!$decimals){
        $token = 'UNKNOWN';
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
      if(strcasecmp($log->address, $this->config->payWrapper) == 0){
        $readLogData = readTxLog($log->data);
        $paymentData = $this->eth->getPaymentData($log->data);
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

  public function recheckTxDB(){
    $pendingTxs = getPendingTx();
    foreach($pendingTxs as $tx){
      $this->checkStatus($tx['hash']);
    }
  }

  protected function checkPaymentValidFunc($receivedAddress, $amount, $receivedToken){
    if(
      $this->checkPaymentValid &&
      strcasecmp($receivedAddress,$this->receivedAddress) == 0&&
      $amount >= $this->amount &&
      $receivedToken == $this->receivedToken
    ){
      return true;
    }
    return false;
  }

  protected function getKyberTradeParams(){
    if($this->txBlockNumber > $this->config->changedKyberTradeBlock){
      return [
        'srcToken' => 0, 
        'destToken' => 1, 
        'srcAmount' => 2, 
        'destAmount' => 3, 
        'destAddress' => 4, 
        'trade_topic' => $this->config->trade_topic_2
      ];
    }
    return [
      'srcAddress' => 0,
      'srcToken' => 1,
      'srcAmount' => 2,
      'destAddress' => 3,
      'destToken' => 4,
      'destAmount' => 5,
      'trade_topic' => $this->config->trade_topic_1
    ];
  }

}