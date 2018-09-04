<?php

namespace ETH;

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
    $this->config = $this->readConfig();
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
              $this->logDebug($err->getMessage());
              sleep($this->intervalRefetchTx);
            }
            if ($txReceipt) {
              $this->txData['txReceipt'] = $txReceipt;
            }
          });
        }
        if(!$this->txData['tx']){
          $this->eth->getTransactionByHash($tx, function ($err, $txHash) {
            if ($err) $this->logDebug($err->getMessage());
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
    while(true){
      $this->eth->getBlockByNumber('latest', false, function ($err, $block) {
        if ($err !== null) $this->logDebug($err->getMessage());
        if($block){
          $this->currentBlock = $block;
        }
      });
      if($this->currentBlock){
        break;
      }
    }

    $txReceipt = $this->txData['txReceipt'];
    $currentBlock = hexdec($this->currentBlock->number);
    $txBlock = hexdec($txReceipt->blockNumber);
    $blockConfirmed = $currentBlock - $txBlock;

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
        return [
          'status' => 'SUCCESS',
          'from' => $from,
          'to' => $to,
          'sentAddress' => $sentAddress,
          'receivedAddress' => $receivedAddress,
          'type' => $type
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
        $readLogData = $this->readTxLog($log->data);
        $hexSrc = $readLogData[0];
        $hexDest = $readLogData[1];
        $hexActualSrcAmount = $readLogData[2];
        $hexActualDestAmount = $readLogData[3];

        $src['address'] = $this->toAddress($hexSrc);
        $dest['address'] = $this->toAddress($hexDest);

        foreach($this->config->tokens as $token) {
          if($token->address == $dest['address']){
            $dest['decimal'] = $token->decimal;
            $dest['symbol'] = $token->symbol;
            $dest['amount'] = $this->toRealAmount(hexdec($hexActualDestAmount), $token->decimal);
            $dest['amount'] = strval($dest['amount']);
          }
          if($token->address == $src['address']){
            $src['decimal'] = $token->decimal;
            $src['symbol'] = $token->symbol;
            $src['amount'] = $this->toRealAmount(hexdec($hexActualSrcAmount), $token->decimal);
            $src['amount'] = strval($src['amount']);
          }
          if(isset($dest['symbol']) && isset($src['symbol'])) break;
        }
        $hexSentAddress = $log->topics[1];
        $sentAddress = '0x' . substr($hexSentAddress, strlen($hexSentAddress) - 40, strlen($hexSentAddress));
        break;
      }
    }

    $readInputData = $this->readTxLog($tx->input);
    $receivedAddress = $this->toAddress($readInputData[3]);

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
      $amount = strval($this->toRealAmount($value, $decimal));
      $receivedAddress = $tx->to;
    }else{
      foreach($this->config->tokens as $t) {
        if($t->address == $tx->to){
          $token = $t->symbol;
          $decimal = $t->decimal;
          break;
        }
      }
      $readLogData = $this->readTxLog($tx->input);
      $receivedAddress = $this->toAddress($readLogData[0]);
      $amount = strval($this->toRealAmount($readLogData[1], $decimal));
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

  protected function readTxLog($logData){
    $hexLength = 64;
    $preData = strlen($logData) - (floor(strlen($logData)/$hexLength)) * 64;
    $data = substr($logData, $preData , strlen($logData));
    $data_length = strlen($data);
    $argsCount = $data_length/$hexLength;
    $args = [];

    for($i = 0; $i < $argsCount; $i++){
      array_push($args, substr($data, $i * $hexLength, $hexLength));
    }
    return $args;
  }

  public function logDebug($data){
    $file = 'logs';
    $fh = fopen($file, 'a') or die("Can't create file");
    $current = file_get_contents($file);
    $data = json_encode($data);
    $current .= "$data\n";
    file_put_contents($file, $current);
  }

  public function toAddress($hex){
    return '0x' . substr($hex, strlen($hex) - 40, strlen($hex));
  }

  public function toRealAmount($amount, $decimal){
    return $amount / pow(10, $decimal);
  }

  public function readConfig(){
    try{
      $file = dirname(__FILE__, 2) . "/config/$this->network.json";
      if ( !file_exists($file) ) {
        throw new Exception('File not found.');
      }
      $current = file_get_contents($file);
      return json_decode($current);
    }catch(Exception $e){
      return false;
    }
  }

}