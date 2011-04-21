<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();

    $timestamp = date("ymdHis");
    $past = $util->SubtractSecondsFromDate($timestamp, 200);

    $result = $util->db->Select("keyboard", "DISTINCT keyboard_id", "keyboard_datetime > '$past'");
    $num = $util->db->GetRows($result);
    if ($num == 1) echo "1";
    else echo "0";

  $util->Delete();
?>
