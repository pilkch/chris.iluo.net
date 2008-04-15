<html>
  <head>
    <title>
      Administration Page
    </title>
  </head>
  <body>
<?php
  require_once("util.php");

  $db=new cDB();
  $db->OpenDB(false);

  function StringUsersOnline()
  {
    $this->GetIP();
    $this->GetHost();
    $this->GetReferer();
    $timestamp = date("ymdHis");
    $past = $timestamp - 300;

    $result=$db->Add("counter", "counter_timestamp, counter_ip, counter_host, counter_referer", "'$timestamp', '$this->ip', '$this->host', '$this->referer'");

    $result = $db->Select("counter", "DISTINCT counter_ip", "counter_timestamp > '$past'");
    $num = $db->GetRows($result);

    if($num == 1) return "1 user online";
    else return $num ." user online";
  }
?>
    <h3>
      mode=<?PHP echo $mode; ?><br />
      q=<?PHP echo $q; ?><br />
    </h3>
    <p>
<?PHP
      if($mode == "add")
      {
        $table = "journal";

        /*
        $journal_content
        $journal_id
        $journal_timestamp
        $journal_project
        $journal_version
        $journal_author
        $journal_title
        */
      }
      else if($mode == "query")
      {
?>
      <table border="1">
<?PHP
        $fields = $db->GetFields("counter");//$table);

?>
        <tr>
<?PHP
        $i=0;
        while ($line = mysql_fetch_array($fields, MYSQL_ASSOC))
        {
          $i=0;
          foreach ($line as $col_value)
          {
            if($i == 0) echo "<td>" . $col_value . "</td>";
            $i++;
          }
        }
?>
        </tr>
<?PHP

        $result = $db->Query($q);
        while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
        {
?>
        <tr>
<?PHP
          foreach ($line as $col_value)
          {
?>
          <td>
<?PHP
            if ($col_value) echo "$col_value\n";
            else echo "&nbsp;\n";
?>
          </td>
<?PHP
          }
?>
        </tr>
<?PHP
        }
?>
      </table>
<?PHP
      }
      else
      {
?>
      <h3>Journal Entry</h3>
      <form method="GET" action="">
        <fieldset>
          <input type="hidden" name="mode" value="add">
          Date: <input type="text" name="year" size="2" maxlength="2"><input type="text" name="month" size="2" maxlength="2"><input type="text" name="day" size="2" maxlength="2">
          Time: <input type="text" name="hour" size="2" maxlength="2"><input type="text" name="minute" size="2" maxlength="2"><input type="text" name="second" size="2" maxlength="2"><br />
          Project: <input type="text" name="journal_project" size="16"> Version: <input type="text" name="journal_version" size="5" maxlength="5"><br />
          <br>
          Title: <input type="text" name="journal_title" size="32" maxlength="32"><br />
          Content: <br />
          <textarea name="journal_content" cols="60" rows="10"></textarea><br />
          <br />
          Password: <input type="password" name="password" size="16"><br />
          <input type="submit" name="action" value="Submit">
        </fieldset>
      </form>
<?PHP
      }
?>
    </p>
<?PHP
  $db->CloseDB();
?>
  </body>
</html>
