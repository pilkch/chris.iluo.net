<?PHP
  require_once $_SERVER['DOCUMENT_ROOT'] . "/util/login.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . "/util/translate.php";
  include $_SERVER['DOCUMENT_ROOT'] . "/util/captcha.php";

  if (!function_exists("stripos"))
  {
    function stripos($str,$needle)
    {
      return strpos(strtolower($str),strtolower($needle));
    }
  }

  class cUtil
  {
    //Constants
    var $difference=17;
    var $email_from="mailmonkey@iluo.net";
    //var $email_from="pilch@dawber.dreamhost.com";
    var $email_from_name="Iluo Mail Monkey";
    var $email_to="chris.pilkington@gmail.com";

    //Variables
    var $db;
    var $ip;
    var $host;
    var $referer;
    var $request;
    var $user;
    var $query;
    var $full_query;

    function cUtil()
    {
      //Do anything here that *has* to be first to do any output
      $config_use_sessions = TRUE;
      session_start();

      $this->ProcessURI();

      $this->db=new cDB();
      $this->db->OpenDB(false);
      $this->user = new cLogin($this);
    }

    function Delete()
    {
      $this->db->CloseDB();
    }

    function ProcessURI()
    {
      $this->full_query = $this->GetRequest();

      $array = explode("/", $this->full_query); // Explode the URI using '/'.
      $num = count($array); // How many items in the array?

      $this->query="";

      for ($i = 2 ; $i < $num ; $i++)
      {
        $this->query.=$array[$i];
      }
    }

    function FormatContent($input)
    {
      $paragraph = explode("\r\n\r\n", $input);
      $num = count($paragraph);

      $output="";

      if($num)
      {
        for($i=0;$i<$num;$i++)
        {
          $line_array = explode("\r\n", $paragraph[$i]);
          $line_count = count($line_array);

          if($line_count)
          {
            $inparagraph=false;

            for($i2=0;$i2<$line_count;$i2++)
            {
              if(""!=$line_array[$i2])
              {
                if(preg_match("/^<cite>/", $line_array[$i2]) || preg_match("/^<h/", $line_array[$i2]))
                  $output.=$line_array[$i2] . "<br />\n";
                else if($i2+1<$line_count)
                {
                  if($inparagraph)
                    $output.=$line_array[$i2] . "<br />\n";
                  else
                    $output.="<p>" . $line_array[$i2] . "<br />\n";

                  $inparagraph=true;
                }
                else
                {
                  if($inparagraph)
                    $output.=$line_array[$i2] . "</p>\n";
                  else
                    $output.="<p>" . $line_array[$i2] . "</p>\n";

                  $inparagraph=false;
                }
              }
            }
          }
          else
          {
            if(FALSE===stripos($paragraph[$i], "<p>") &&
              FALSE===stripos($paragraph[$i], "<cite>") &&
              FALSE===stripos($paragraph[$i], "<h"))
                $output.="<p>" . $paragraph[$i] . "</p>\n";
            else
            {
              echo "(pre-formatted " . $paragraph[$i] . ")";
              $output.=$paragraph[$i];
            }
          }
        }

        return $output;
      }
      else if(FALSE===stripos($input, "<p>") &&
        FALSE===stripos($input, "<cite>") &&
        FALSE===stripos($input, "<h"))
          return "<p>" . $input . "</p>";

      echo "(passed through " . $input . ")";
      return $input;
    }

    function CreateUser($user, $password)
    {
      $result=$this->db->Select("user", "", "`user_login`='$user'");
      $num=$this->db->GetRows($result);
      if($num>0)
        return 1;

      $result=$this->db->query("INSERT INTO user (user_id, user_login, user_pass, user_type) " .
        "VALUES ('', '$user', '$password', 'USER');");

      if($result)
        return 0;

      //Error, no result returned, did not insert
      return 2;
    }

    function SetTheme($t="blue")
    {
      require_once $_SERVER['DOCUMENT_ROOT'] . "/themes/" . $t . "/theme.php";
    }

    function EmailAdmin($title, $message)
    {
      $message="<html><body>" . $message . "</body></html>";
      $headers  = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

      // Additional headers
      $headers .= 'From: ' . $this->email_from_name . ' <' . $this->email_from . '>' . "\r\n";

      return (true==mail($this->email_to, "iluo.net " . $title, $message, $headers));
    }

    function random()
    {
      return rand(0,100)*0.01;
    }

    function GetLocalHourf()
    {
      $temphour=date("H");
      $temphour=$temphour+$this->difference; //Time Difference between the server and administrator location

      if($temphour>24) //Wrap around to get times within 24 hour period, can be changed to 12 or 48 etc.
        $temphour-=24;

      if($temphour<10) //
        $temphour="0" . $temphour;

      return $temphour;
    }

    function GetServerHourf()
    {
      $temphour=date("H");

      if($temphour>24) //Wrap around to get times within 24 hour period, can be changed to 12 or 48 etc.
        $temphour-=24;

      if($temphour<10) //
        $temphour="0" . $temphour;

      return $temphour;
    }

    function GetTimef()
    {
      return $this->GetLocalHourf() . date(":i:s");
    }

    function GetIP()
    {
      if(isset($_SERVER['X_FORWARDED_FOR']))
        $this->ip=$_SERVER['X_FORWARDED_FOR'];
      else
        $this->ip=$_SERVER['REMOTE_ADDR'];

      return $this->ip;
    }

    function GetHost()
    {
      $this->host=gethostbyaddr($this->ip);
      return $this->host;
    }

    function GetReferer()
    {
      if(isset($_SERVER['HTTP_REFERER']))
        $this->referer=$_SERVER['HTTP_REFERER'];
      else
        $this->referer=null;

      return $this->referer;
    }

    function GetRequest()
    {
      global $REQUEST_URI;   // Define our global variables

      if(empty($REQUEST_URI))
        $this->request=$_SERVER['REQUEST_URI'];
      else
        $this->request=$REQUEST_URI;

      return $this->request;
    }

    function get_iso_8601_date($int_date) {
      //$int_date: current date in UNIX timestamp
      $date_mod = date('Y-m-d\TH:i:s', $int_date);
      $pre_timezone = date('O', $int_date);
      $time_zone = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
      $date_mod .= $time_zone;
      return $date_mod;
    }

    function SubtractSecondsFromDate($date, $seconds)
    {
      $zeros="";
      $i=0;
      while($i<strlen($date) && $date[$i]==0)
      {
        $zeros=$zeros . "0";
        $i++;
      }

      if($seconds<$date)
      {
        return $zeros . "" . ($date-$seconds);
      }

      return $date;
    }

    function GetUserOnline()
    {
      $this->GetIP();
      $this->GetHost();
      $this->GetReferer();
      $this->GetRequest();
      $timestamp = date("ymdHis");
      $past = $this->SubtractSecondsFromDate($timestamp, 200);

      $result=$this->db->Add("counter", "counter_timestamp, counter_ip, counter_host, counter_referer, counter_request", "'$timestamp', '$this->ip', '$this->host', '$this->referer', '$this->request'");

      $result=$this->db->Select("counter", "DISTINCT counter_ip", "counter_timestamp > '$past'");
      $num=$this->db->GetRows($result);

      return $num;
    }

    function StringUsersOnline()
    {
      $num=$this->GetUserOnline();

      if((int)$num==1)
        return "1 user online";

      return $num ." users online";
    }
  }
?>
