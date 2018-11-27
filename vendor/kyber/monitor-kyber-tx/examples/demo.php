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

  // swap
  $tx = '0xe763ffe95d02e231f1d7450a0848b588447c8bf604953077fefc1eef369e901e';
  $tx = '0xdabc1ab1e8333125fbaa5d36a479e9ee6cefa59b49df4dad70d255344c250055';
  // transfer Token
  $tx = '0xd910078d3c2630acfdf15c0f72b09d0808639fcc5323ea6fe054e9444f90525d';
  // transfer ETH
  $tx = '0x253f8e00104738fca9869010ebf78218ae4d2c40be4d48d0b85b13a8971b4cb6';

  // Pay Token -> ETH
  $tx = '0xf513db1b7de61ba88afecd5a9a228c983b5b0b6b48bbb56f859bcd60dafc245d';
  $tx = '0x8f27370da1b79ffe3d6205ba21cc21cc09ebb80dd2a7c56800f367cc103dcebf';
  // Pay ETH -> ETH
  $tx = '0xc2a66fd9238d609b3428946f990cc64cd9aa6f34baebf336b4916fabfed9e1a6';
  // Pay ETH -> Token
  $tx = '0xe3b99c2937f37990fe9f61b2fc24ec9228061d856b6d796483ccc06dd6c443e4';
  // Pay Token -> Token
  $tx = '0x5aa30da4ed81079b8136801ee4ab1e712a73f9c1df8949236fcd8d6f0b988b62';

  var_dump($monitor->checkStatus($tx));
  die();