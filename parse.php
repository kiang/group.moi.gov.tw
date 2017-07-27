<?php

$tmpPath = __DIR__ . '/tmp/pages';
if(!file_exists($tmpPath)) {
  mkdir($tmpPath, 0777, true);
}

$context = stream_context_create(array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
));

$baseUrl = 'https://group.moi.gov.tw/sgms/admin/sogp_main_search!list.action?category=&groupAddress=&groupCity=&groupName=&groupPermitDateb=&groupPermitDatee=&groupPermitNo=&groupTown=&groupZip=&gtype=&name=&pager.orderBy=createDate&pager.orderType=desc&pager.pageSize=1000&searchValue=&status=3&tel=undefined&xlevel=&pager.pageNumber=';

$fh = false;

for($i = 1; $i <= 13; $i++) {
  $url = $baseUrl . $i;
  $tmpFile = $tmpPath . '/' . $i;
  if(!file_exists($tmpFile)) {
    file_put_contents($tmpFile, file_get_contents($url, false, $context));
  }
  $page = file_get_contents($tmpFile);
  $lines = explode('</table>', $page);
  foreach($lines AS $line) {
    $cols = explode('</span>', $line);
    if(count($cols) === 17) {
      foreach($cols AS $k => $v) {
        $cols[$k] = trim(strip_tags($v));
      }
      $data = array(
        '團體名稱' => $cols[1],
        '團體類型' => $cols[3],
        '成立日期' => $cols[5],
        '理事長' => $cols[7],
        '社團狀態' => $cols[9],
        '會址電話' => $cols[11],
        '核准立案字號' => $cols[13],
        '會址' => $cols[15],
      );
      if(false === $fh) {
        $fh = fopen(__DIR__ . '/list.csv', 'w');
        fputcsv($fh, array_keys($data));
      }
      fputcsv($fh, $data);
    }
  }
}
