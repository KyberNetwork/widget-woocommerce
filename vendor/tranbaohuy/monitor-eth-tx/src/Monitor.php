<?php

namespace ETH;
require 'Helper.php';

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Exception;

class Monitor{

  protected $node = 'https://mainnet.infura.io';
  protected $blockConfirm = 30;
  protected $txLostTimeout = 15; // minutes
  protected $intervalRefetchTx = 5; // sec
  protected $currentBlock = null;
  protected $eth = null;
  protected $network = null;
  protected $config = null;
  protected $txData = [
    'txReceipt' => null,
    'tx' => null,
  ];

  public function __construct($params = []){
    if(isset($params['node'])) 
      $this->node = $params['node'];
    if(isset($params['network'])) 
      $this->network = $params['network'];
    if(isset($params['blockConfirm'])) 
      $this->blockConfirm = $params['blockConfirm'];
    if(isset($params['txLostTimeout'])) 
      $this->txLostTimeout = $params['txLostTimeout'];
    if(isset($params['intervalRefetchTx'])) 
      $this->intervalRefetchTx = $params['intervalRefetchTx'];
    $this->config = readConfig($this->network);
    $this->eth = (new Web3(new HttpProvider(new HttpRequestManager($this->node, 5))))->eth;
  }

  public function checkStatus($tx){
    $this->tx = $tx;
    $txLost = false;
    if($tx){
      $starttime = microtime(true);
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
          $endtime = microtime(true);
          $timediff = ($endtime - $starttime) / pow(10,6) / 60;
          if($timediff > $this->txLostTimeout){
            $txLost = true;
            break;
          }
        }
      }
    }
    return $this->handleData($tx, $txLost);
  }

  protected function handleData($tx, $txLost = false){
    if($txLost){
      return [ 'status' => 'LOST' ];
    }
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
        if(strtolower($txReceipt->to) == strtolower($this->config->network)){
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
        return [
          'status' => 'SUCCESS',
          'from' => $from,
          'to' => $to,
          'sentAddress' => $sentAddress,
          'receivedAddress' => $receivedAddress,
          'timestamp' => hexdec($txBlock->timestamp),
          'type' => $type,
        ];
      }else{
        return [ 'status' => 'FAIL' ];
      }
    }else{
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

    $recivedAddress = null;
    $src = [];
    $dest = [];
    foreach($txReceipt->logs as $log){
      if($log->topics[0] == $this->config->trade_topic){
        $readLogData = readTxLog($log->data);
        $hexSrc = $readLogData[0];
        $hexDest = $readLogData[1];
        $hexActualSrcAmount = $readLogData[2];
        $hexActualDestAmount = $readLogData[3];

        $src['address'] = toAddress($hexSrc);
        $dest['address'] = toAddress($hexDest);

        foreach($this->config->tokens as $token) {
          if($token->address == $dest['address']){
            $dest['decimal'] = $token->decimal;
            $dest['symbol'] = $token->symbol;
            $dest['amount'] = toRealAmount(hexdec($hexActualDestAmount), $token->decimal);
            $dest['amount'] = strval($dest['amount']);
          }
          if($token->address == $src['address']){
            $src['decimal'] = $token->decimal;
            $src['symbol'] = $token->symbol;
            $src['amount'] = toRealAmount(hexdec($hexActualSrcAmount), $token->decimal);
            $src['amount'] = strval($src['amount']);
          }
          if(isset($dest['symbol']) && isset($src['symbol'])) break;
        }
        $hexSentAddress = $log->topics[1];
        $sentAddress = '0x' . substr($hexSentAddress, strlen($hexSentAddress) - 40, strlen($hexSentAddress));
        break;
      }
    }

    $readInputData = readTxLog($tx->input);
    $receivedAddress = toAddress($readInputData[3]);

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
      $decimal = $this->config->tokens->$token->decimal;
      $amount = strval(toRealAmount($value, $decimal));
      $receivedAddress = $tx->to;
    }else{
      foreach($this->config->tokens as $t) {
        if($t->address == $tx->to){
          $token = $t->symbol;
          $decimal = $t->decimal;
          break;
        }
      }
      $readLogData = readTxLog($tx->input);
      $receivedAddress = toAddress($readLogData[0]);
      $amount = strval(toRealAmount(hexdec($readLogData[1]), $decimal));
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

}