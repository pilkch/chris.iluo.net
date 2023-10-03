<?PHP
  require_once $_SERVER['DOCUMENT_ROOT'] . "/util/translate.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . "/util/database.php";

  if (!function_exists("stripos"))
  {
    function stripos($str, $needle)
    {
      return strpos(strtolower($str), strtolower($needle));
    }
  }

  class cUtil
  {
    //Constants
    var $difference = 17;
    var $email_from = "mailmonkey@iluo.net";
    //var $email_from = "pilch@dawber.dreamhost.com";
    var $email_from_name = "Iluo Mail Monkey";
    var $email_to = "chris.pilkington@gmail.com";

    //Variables
    var $db;
    var $ip;
    var $host;
    var $referer;
    var $request;
    var $query;
    var $full_query;
    var $user_agent;

    function __construct()
    {
      //Do anything here that *has* to be first to do any output
      $config_use_sessions = TRUE;
      session_start();

      $this->ProcessURI();

      $this->db = new cDB();
      $this->db->OpenDB(false);
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

      $this->query = "";

      for ($i = 2; $i < $num; $i++) {
        $this->query .= $array[$i];
      }
    }

    function FormatContent($input)
    {
      $paragraph = explode("\r\n\r\n", $input);
      $num = count($paragraph);

      $output = "";

      if ($num) {
        for ($i = 0; $i < $num; $i++) {
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

      return (true == mail($this->email_to, "iluo.net " . $title, $message, $headers));
    }

    function random()
    {
      return rand(0, 100) * 0.01;
    }

    function GetLocalHourf()
    {
      $temphour = date("H");
      $temphour = $temphour + $this->difference; //Time Difference between the server and administrator location

      //Wrap around to get times within 24 hour period, can be changed to 12 or 48 etc.
      if ($temphour >= 24) $temphour -= 24;
      if ($temphour < 10) $temphour = "0" . $temphour;

      return $temphour;
    }

    function GetServerHourf()
    {
      $temphour = date("H");

      //Wrap around to get times within 24 hour period, can be changed to 12 or 48 etc.
      if ($temphour >= 24) $temphour -= 24;
      if ($temphour < 10) $temphour="0" . $temphour;

      return $temphour;
    }

    function GetTimef()
    {
      return $this->GetLocalHourf() . date(":i:s");
    }

    // Returns  20081106T150942,013Z
    // Refer to http://en.wikipedia.org/wiki/ISO_8601
    // Refer to http://au2.php.net/date
    function GetUTCTimeNowISO8601()
    {
      return gmdate("Ymd\THis,u\Z");
    }

    // Returns  2008-11-06T15:09:42,013Z
    // Refer to http://en.wikipedia.org/wiki/ISO_8601
    // Refer to http://au2.php.net/date
    function GetUTCTimeNowISO8601f()
    {
      return gmdate("Y-m-d\TH:i:s,u\Z");
    }

    function GetIP()
    {
      if (isset($_SERVER['X_FORWARDED_FOR'])) {
        $this->ip = $_SERVER['X_FORWARDED_FOR'];
      } else {
        $this->ip = $_SERVER['REMOTE_ADDR'];
      }

      return $this->ip;
    }

    function GetHost()
    {
      $this->host = gethostbyaddr($this->ip);
      return $this->host;
    }

    function GetReferer()
    {
      if (isset($_SERVER['HTTP_REFERER'])) $this->referer = $_SERVER['HTTP_REFERER'];
      else $this->referer = null;

      return $this->referer;
    }

    function GetRequest()
    {
      global $REQUEST_URI;   // Define our global variables

      if (empty($REQUEST_URI)) $this->request = $_SERVER['REQUEST_URI'];
      else $this->request = $REQUEST_URI;

      return $this->request;
    }

    function GetUserAgent()
    {
      global $HTTP_USER_AGENT;   // Define our global variables

      if (empty($HTTP_USER_AGENT)) $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
      else $this->user_agent = $HTTP_USER_AGENT;

      return $this->user_agent;
    }

    function get_iso_8601_date($int_date)
    {
      //$int_date: current date in UNIX timestamp
      $date_mod = date('Y-m-d\TH:i:s', $int_date);
      $pre_timezone = date('O', $int_date);
      $time_zone = substr($pre_timezone, 0, 3) . ":" . substr($pre_timezone, 3, 2);
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

    function ParseUserAgentString($user_agent, &$browser_name, &$os_name)
    {
      /*
      // Browser Detection
      OS    Version
      MacOS 10.5
      MacOS 10.4
      Windows XP
      Fedora 8.0

      Browser Version
      Firefox 3.0b5
      Firefox 2.0
      Opera 9.01a
      Internet Explorer 7.0

      Resolution
      Width Height
      1024 768
      1280 1024

      "Ubuntu" -> "Linux"
      "Fedora" -> "Linux"
      "Mac OS" -> "MacOS"
      "MacIntel" -> "MacOS"

      http://phpsniff.cvs.sourceforge.net/phpsniff/phpsniff/phpSniff.class.php?revision=1.22&view=markup

      http://apptools.com/phptools/download.php?filename=browser.php

      Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_2; en-us) AppleWebKit/525.13 (KHTML, like Gecko) Version/3.1 Safari/525.13
      OS: MacOS 10.5
      Browser: Safari
      Version: 3.1

      Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9b5) Gecko/2008032619 Firefox/3.0b5
      OS: MacOS 10.5
      Browser: Firefox
      Version: 3.0b5

      Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv 11.0) like Gecko
      OS: Windows 7
      Browser: Internet Explorer
      Version: 10

      Mozilla/5.0 (Linux; U; Android 2.3.2; en-sa; HTC_DesireHD_A9191 Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
      OS: Android 2.3.2
      Browser: Firefox
      Version: 4.0
      */

      $browser_name = 'Unknown';
      $os_name = 'Unknown';

      // Browser
      $browsers = array(
          'Internet Explorer' => '(MSIE)|(Trident)',
          'Opera' => 'Opera',
          'Firefox' => '(Firebird)|(Firefox)',
          'Galeon' => 'Galeon',
          'Mozilla' => 'Gecko',
          'Safari' => 'Safari',
          'MyIE' => 'MyIE',
          'Lynx' => 'Lynx',
          'Netscape' => '(Mozilla)|(Netscape)|(Netscape6)',
          'Konqueror' => 'Konqueror',
          'SearchBot' => '(nuhk)|(Googlebot)|(Baiduspider)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)|(Sphere Scout)(Spider)|(Bot)',
      );

      foreach($browsers as $browser=>$pattern) {
          if (preg_match("/$pattern/i", $user_agent)) {
            $browser_name = $browser;
            break;
          }
      }


      // Operating System
      $operatingsystems = array(
          'Android' => 'Android',
          'iOS' => '(iPad)|(iPhone)',
          'Windows Phone' => 'Windows Phone',

          'Mac OS X' => 'Mac OS X',
          'Windows' => 'Windows',
          'Linux' => '(Fedora)|(Linux)|(Ubuntu)',
          'Unix' => 'Unix',
      );

      foreach($operatingsystems as $operatingsystem=>$pattern) {
          if (preg_match("/$pattern/i", $user_agent)) {
            $os_name = $operatingsystem;
            break;
          }
      }
    }

    function AddCounterEntry($ip, $host, $referer, $request, $user_agent)
    {
      $this->ParseUserAgentString($user_agent, $browser_name, $os_name);

      $timestamp = date("ymdHis");

      $this->db->Add("counter", "counter_timestamp, counter_ip, counter_host, counter_referer, counter_request, counter_user_agent_string, counter_os, counter_os_version, counter_browser, counter_browser_version", "'$timestamp', '$ip', '$host', '$referer', '$request', '$user_agent', '$os_name', '0', '$browser_name', '0'");
    }

    function GetUserOnline()
    {
      $this->GetIP();
      $this->GetHost();
      $this->GetReferer();
      $this->GetRequest();
      $this->GetUserAgent();
      $timestamp = date("ymdHis");
      $past = $this->SubtractSecondsFromDate($timestamp, 200);

      // Add the entry
      $this->AddCounterEntry($this->ip, $this->host, $this->referer, $this->request, $this->user_agent);

      // Now count the total number of users
      $result = $this->db->Select("counter", "DISTINCT counter_ip", "counter_timestamp > '$past'");
      $num = $this->db->GetRows($result);

      return $num;
    }

    function StringUsersOnline()
    {
      $num = $this->GetUserOnline();

      if ((int)$num == 1) return "1 user online";

      return $num ." users online";
    }


    function CreateAndAddAttributeToXMLElement($document, $node, $attribute, $value)
    {
      $attribute_node = $document->createAttribute($attribute);
      $node->appendChild($attribute_node);

      $attribute_node->appendChild($document->createTextNode($value));
    }

    function JSONDecode($json, $assoc = FALSE)
    {
      // http://au2.php.net/manual/en/function.json-decode.php#95782
      $json = str_replace(array("\n","\r"), "", $json);
      $json = preg_replace('/([{,])(\s*)([^"]+?)\s*:/','$1"$3":', $json);
      return json_decode($json, $assoc);
    }
  }
?>
