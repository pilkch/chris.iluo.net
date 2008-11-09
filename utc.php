<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();

  echo $util->GetUTCTimeNowISO8601();
?>
