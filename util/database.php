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

      $this->db = mysqli_connect($server,$username,$password);
      @mysqli_select_db($this->db, $database) or die($this->Error("Unable to select database $database"));
      $this->open = true;
    }

    function Query($query)
    {
      if ($this->open && $query) return mysqli_query($this->db, $query);

      return 0;
    }

    function GetLastInsertID()
    {
      return mysqli_insert_id($this->db);
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
        $numrows = mysqli_num_rows($result) or $numrows = 0;
        return $numrows;
      }

      //$this->Information("GetRows(\"$result\")");
      return 0;
    }

    function GetFields($table)
    {
      if ($table) return $this->Query("SHOW COLUMNS from $table");

      return null;
    }

    function CloseDB()
    {
      if ($this->open) {
        // TODO: Close the connection here?  Set it to null?
      }

      $this->open = false;
    }

    function Information($errstr = "")
    {
      print "cDB->Information($errstr);<br>\n";
      mysqli_error($this->db);
    }

    function Error($errstr = "")
    {
      echo "cDB->Error(" . $errstr . ");<br>\nmysql_error()=\"" . mysqli_error($this->db) . "\"";
    }
  };
?>
