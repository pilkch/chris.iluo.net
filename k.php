<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();

    $timestamp = date("ymdHis");
    $past = $util->SubtractSecondsFromDate($timestamp, 10);

    $result = $util->db->query("UPDATE keyboard SET keyboard_datetime='$timestamp' WHERE keyboard_id=1;");

  $util->Delete();
?>
