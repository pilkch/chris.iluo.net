<?php
  class cDB
  {
    var $open = false;
    var $db = "";

    function OpenDB()
    {
      $server = "mydatabasehostname";
      $username = "myusername";
      $password = "mypassword";
      $database = "mydatabase";

      $this->db = mysql_connect($server,$username,$password);
      @mysql_select_db($database) or die($this->Error("Unable to select database"));
      $this->open = true;
    }

    function Query($query)
    {
      if ($this->open && $query) return mysql_query($query, $this->db);

      return 0;
    }

    function GetLastInsertID()
    {
      return mysql_insert_id();
    }

    function Select($table, $selection = "", $where = "", $options = "")
    {
      $s = "SELECT";

      if ($selection) $s .= " $selection";
      else $s .= " *";

      $s .= " FROM $table";

      if ($where) $s .= " WHERE $where";

      if ($options) $s .= " $options";

      return $this->Query("$s");
    }

    function Remove($table, $where = "1")
    {
      return $this->Query("DELETE FROM `$table` WHERE $where");
    }

    function Add($table, $fields, $values)
    {
      return $this->Query("INSERT INTO $table ($fields) VALUES ($values)") or die($this->Error("Add"));
    }

    function GetRows($result)
    {
      if ($result) {
        $numrows = mysql_numrows($result) or $numrows = 0;
        return $numrows;
      }

      //$this->Information("GetRows(\"$result\")");
      return 0;
    }

    function GetFields($table)
    {
      if ($table) return $this->query("SHOW COLUMNS from $table");

      return null;
    }

    function CloseDB()
    {
      if ($this->open) mysql_close();

      $this->open = false;
    }

    function Information($errstr = "")
    {
      print "cDB->Information($errstr);<br>\n";
      mysql_error();
    }

    function Error($errstr = "")
    {
      echo "cDB->Error(" . $errstr . ");<br>\nmysql_error()=\"" . mysql_error() . "\"";
    }
  };
?>
