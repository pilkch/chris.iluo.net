<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

class browser{

  var $Name = "Unknown";
  var $Version = "Unknown";
  var $Platform = "Unknown";
  var $UserAgent = "Not reported";
  var $AOL = false;

  function __construct($agent)
  {
    // initialize properties
    $bd['platform'] = "Unknown";
    $bd['browser'] = "Unknown";
    $bd['version'] = "Unknown";
    $this->UserAgent = $agent;

    // find operating system
        if (eregi("win", $agent))
        $bd['platform'] = "Windows";
    elseif (eregi("mac", $agent))
      $bd['platform'] = "MacIntosh";
    elseif (eregi("linux", $agent))
      $bd['platform'] = "Linux";
    elseif (eregi("OS/2", $agent))
      $bd['platform'] = "OS/2";
    elseif (eregi("BeOS", $agent))
      $bd['platform'] = "BeOS";

    // test for Opera
        if (eregi("opera",$agent)){
      $val = stristr($agent, "opera");
      if (eregi("/", $val)){
        $val = explode("/",$val);
        $bd['browser'] = $val[0];
        $val = explode(" ",$val[1]);
        $bd['version'] = $val[0];
      }else{
        $val = explode(" ",stristr($val,"opera"));
        $bd['browser'] = $val[0];
        $bd['version'] = $val[1];
      }

    // test for WebTV
        }elseif(eregi("webtv",$agent)){
      $val = explode("/",stristr($agent,"webtv"));
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];

    // test for MS Internet Explorer version 1
        }elseif(eregi("microsoft internet explorer", $agent)){
      $bd['browser'] = "MSIE";
      $bd['version'] = "1.0";
      $var = stristr($agent, "/");
      if (ereg("308|425|426|474|0b1", $var)){
        $bd['version'] = "1.5";
      }

    // test for NetPositive
        }elseif(eregi("NetPositive", $agent)){
      $val = explode("/",stristr($agent,"NetPositive"));
      $bd['platform'] = "BeOS";
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];

    // test for MS Internet Explorer
        }elseif(eregi("msie",$agent) && !eregi("opera",$agent)){
      $val = explode(" ",stristr($agent,"msie"));
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];

    // test for MS Pocket Internet Explorer
        }elseif(eregi("mspie",$agent) || eregi('pocket', $agent)){
      $val = explode(" ",stristr($agent,"mspie"));
      $bd['browser'] = "MSPIE";
      $bd['platform'] = "WindowsCE";
      if (eregi("mspie", $agent))
        $bd['version'] = $val[1];
      else {
        $val = explode("/",$agent);
        $bd['version'] = $val[1];
      }

    // test for Galeon
        }elseif(eregi("galeon",$agent)){
      $val = explode(" ",stristr($agent,"galeon"));
      $val = explode("/",$val[0]);
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];

    // test for Konqueror
        }elseif(eregi("Konqueror",$agent)){
      $val = explode(" ",stristr($agent,"Konqueror"));
      $val = explode("/",$val[0]);
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];

    // test for iCab
        }elseif(eregi("icab",$agent)){
      $val = explode(" ",stristr($agent,"icab"));
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];

    // test for OmniWeb
        }elseif(eregi("omniweb",$agent)){
      $val = explode("/",stristr($agent,"omniweb"));
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];

    // test for Phoenix
        }elseif(eregi("Phoenix", $agent)){
      $bd['browser'] = "Phoenix";
      $val = explode("/", stristr($agent,"Phoenix/"));
      $bd['version'] = $val[1];

    // test for Firebird
        }elseif(eregi("firebird", $agent)){
      $bd['browser']="Firebird";
      $val = stristr($agent, "Firebird");
      $val = explode("/",$val);
      $bd['version'] = $val[1];

    // test for Firefox
        }elseif(eregi("Firefox", $agent)){
      $bd['browser']="Firefox";
      $val = stristr($agent, "Firefox");
      $val = explode("/",$val);
      $bd['version'] = $val[1];

    // test for Mozilla Alpha/Beta Versions
        }elseif(eregi("mozilla",$agent) &&
                eregi("rv:[0-9].[0-9][a-b]",$agent) && !eregi("netscape",$agent)){
                  $bd['browser'] = "Mozilla";
                  $val = explode(" ",stristr($agent,"rv:"));
                  eregi("rv:[0-9].[0-9][a-b]",$agent,$val);
                  $bd['version'] = str_replace("rv:","",$val[0]);

    // test for Mozilla Stable Versions
                }elseif(eregi("mozilla",$agent) &&
                        eregi("rv:[0-9]\.[0-9]",$agent) && !eregi("netscape",$agent)){
                          $bd['browser'] = "Mozilla";
                          $val = explode(" ",stristr($agent,"rv:"));
                          eregi("rv:[0-9]\.[0-9]\.[0-9]",$agent,$val);
                          $bd['version'] = str_replace("rv:","",$val[0]);

    // test for Lynx & Amaya
                        }elseif(eregi("libwww", $agent)){
      if (eregi("amaya", $agent)){
        $val = explode("/",stristr($agent,"amaya"));
        $bd['browser'] = "Amaya";
        $val = explode(" ", $val[1]);
        $bd['version'] = $val[0];
      } else {
        $val = explode("/",$agent);
        $bd['browser'] = "Lynx";
        $bd['version'] = $val[1];
      }

    // test for Safari
                        }elseif(eregi("safari", $agent)){
      $bd['browser'] = "Safari";
      $bd['version'] = "";

    // remaining two tests are for Netscape
                        }elseif(eregi("netscape",$agent)){
      $val = explode(" ",stristr($agent,"netscape"));
      $val = explode("/",$val[0]);
      $bd['browser'] = $val[0];
      $bd['version'] = $val[1];
                        }elseif(eregi("mozilla",$agent) && !eregi("rv:[0-9]\.[0-9]\.[0-9]",$agent)){
                          $val = explode(" ",stristr($agent,"mozilla"));
                          $val = explode("/",$val[0]);
                          $bd['browser'] = "Netscape";
                          $bd['version'] = $val[1];
                        }

    // clean up extraneous garbage that may be in the name
        $bd['browser'] = ereg_replace("[^a-z,A-Z]", "", $bd['browser']);
    // clean up extraneous garbage that may be in the version
        $bd['version'] = ereg_replace("[^0-9,.,a-z,A-Z]", "", $bd['version']);

    // check for AOL
        if (eregi("AOL", $agent)){
      $var = stristr($agent, "AOL");
      $var = explode(" ", $var);
      $bd['aol'] = ereg_replace("[^0-9,.,a-z,A-Z]", "", $var[1]);
        }

    // finally assign our properties
        $this->Name = $bd['browser'];
    $this->Version = $bd['version'];
    $this->Platform = $bd['platform'];
    $this->AOL = $bd['aol'];
  }
}

  $util=new cUtil();
    $util->SetTheme();

    $theme = new cTheme($util);
    $theme->header("chris.iluo.net", true);
      $theme->menu(true);

    // We provide a footer here instead of using the theme footer
?>
    <p id="conservatory">
      Website and Content Copyright &copy;2006 <a href="mailto:chris.pilkington@gmail.com">Christopher</a> <a href="mailto:chris.pilkington@gmail.com">Pilkington</a><br /><br />
      <a href="http://validator.w3.org/check?uri=referer"><img src="images/valid-xhtml11-blue.png" alt="Valid XHTML 1.1" height="31" width="88" /></a>
    </p>
<?PHP

    $theme->footerEnd();

  $util->Delete();
?>
