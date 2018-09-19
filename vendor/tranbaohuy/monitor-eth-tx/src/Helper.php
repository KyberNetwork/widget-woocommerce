<?php

function logDebug($data){
  if (!file_exists('logs')) {
    $x = mkdir('logs', 0777, true);
  }
  $file = 'logs/logs';
  $fh = fopen($file, 'a');
  if($fh){
    $current = file_get_contents($file);
    $data = json_encode($data);
    $current .= "$data\n";
    file_put_contents($file, $current);
  }
}

function readTxLog($logData){
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

function toAddress($hex){
  return '0x' . substr($hex, strlen($hex) - 40, strlen($hex));
}

function toRealAmount($amount, $decimal){
  return $amount / pow(10, $decimal);
}

function readConfig($network){
  try{
    $file = dirname(__FILE__, 2) . "/config/$network.json";
    if ( !file_exists($file) ) {
      throw new Exception('File not found.');
    }
    $current = file_get_contents($file);
    return json_decode($current);
  }catch(Exception $e){
    return false;
  }
}