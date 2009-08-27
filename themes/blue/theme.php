<?PHP
  include_once "style.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . "/util/login.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . "/util/translate.php";


  define("icon_add",    "add.png");
  define("icon_delete", "delete.png");
  define("icon_edit",   "edit.png");
  define("icon_priority_raise", "priority_raise.png");
  define("icon_priority_lower", "priority_lower.png");


  class cTheme
  {
    var $page;
    var $home;
    var $dir;
    var $img;

    var $filename;
    var $name;
    var $description;

    var $author_firstname;
    var $author_lastname;
    var $author_email;

    var $loggedin;

    var $link;
    var $foreground;
    var $background;

    var $width; //this is the user's screen width (prefered at least)
    var $height; //this is the user's screen height (prefered at least)

    var $tabs_content; //how far in the main text should be tabbed

    var $menuwidth;
    var $menuimageheight;

    var $mainwidth;
    var $mainimageheight;

    var $gap;

    var $db;
    var $util;

    function cTheme($db, $util, $showlogin=true,$loggedin="",$name="",$type="")
    {
      $this->db=$db;
      $this->util=$util;
      $this->filename="blue";
      $this->name="Blue";
      $this->description = "Just your basic blue skin";

      $this->author_firstname = "Christopher";
      $this->author_lastname = "Pilkington";
      $this->author_email = "chris.pilkington@gmail.com";

      $this->loggedin="";

      $this->link="#666699";
      $this->foreground = "#000000";
      $this->background = "#eaeaea";

      $this->home="http://chris.iluo.net"; //$_SERVER['REDIRECT_SITE_HTMLROOT'];
      $this->dir=$this->home . "/themes/" . $this->filename;
      $this->img=$this->dir . "/images";
      $this->page=$PHP_SELF;
      $this->showlogin=$showlogin;

      $this->width=990;
      $this->height=768;

      $this->menuwidth=116;
      $this->menuleftimagewidth=16;
      $this->menuimagewidth=132;
      $this->menuimageheight=16;
      $this->menurightimagewidth=16;

      $this->mainwidth=850;
      $this->mainleftimagewidth=16;
      $this->mainimagewidth=820;
      $this->mainimageheight=16;
      $this->mainrightimagewidth=16;

      $this->gap=200;

      $this->tabs_content="\t\t\t\t\t\t";
    }

    function header($title = "dev.iluo.net")
    {
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="php, programming, chris, christopher, pilkington, christopher pilkington, chris pilkington, iluo, dev, development, c, getfree, c++, opengl, java" />
    <meta name="description" content="Chris Pilkington Programming: C, C++, Java, OpenGL and PHP.  " />
    <title><?PHP echo $title; ?> </title>
    <link rel="icon" type="image/ico" href="<?PHP echo $this->img; ?>/icon.ico" />
    <link rel="shortcut icon" type="images/ico" href="<?PHP echo $this->img; ?>/icon.ico" />

    <link rel="alternate" type="application/atom+xml" title="Atom" href="http://chris.iluo.net/blog/feed/atom" />

    <link rel="openid.server" href="http://www.livejournal.com/openid/server.bml" />
    <link rel="openid.delegate" href="http://cgpilk.livejournal.com/" />

    <!--<link rel="home" title="Home" href="/" />
    <link rel="contents" title="Site Map" href="/site/" />
    <link rel="help" title="Technical Support" href="/support/" />
     <link href='/go.bml?journal=cpp&amp;itemid=130666&amp;dir=prev' rel='Previous' />
    <link href='/go.bml?journal=cpp&amp;itemid=130666&amp;dir=next' rel='Next' />-->

<?PHP
    embedstyle($this->showlogin, $this->width, $this->height);
?>
  </head>
  <body>

      <div><img src="<?PHP echo $this->img; ?>/header.gif" alt="header" style="padding: 6px 6px 6px 6px; margin: 6px 6px 6px 6px; border: 6px 6px 6px 6px;" width="99%" height="75px" /></div>


    <!-- START EVERYTHING BELOW TITLE BANNER -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr valign="top">
<?PHP
    }

    function form_submitButton($text, $action, $icon)
    {
      return "<input type=\"image\" name=\"$text\" alt=\"$text\" title=\"$text\" src=\"http://chris.iluo.net/images/action/" . $icon . "\" onclick=\"this.disabled=1; this.form.submit(); return false;\" width=\"16\" height=\"16\">";
    }

    function form_action($form, $submit_text, $page, $action, $icon)
    {
      return  '<form name="form' . $form . '" method="post" action="http://chris.iluo.net/' . $page . '?action=' . $action . '">
              ' . $this->form_submitButton($submit_text,  $action, $icon) . '</form>';
    }

    function menu_write_section($title, $text)
    {
?>
          <div class="box">
            <div class="menuimg" style="background-image: url('<?PHP echo $this->img; ?>/menu.gif')">
              <h1><?PHP echo $title ?></h1>
            </div>

            <ul class="menu">
              <li>
                <?PHP echo $text . "\n"; ?>
              </li>
            </ul>
          </div>
<?PHP
    }

    function menu_write_first($title, $text)
    {
?>
        <!-- START MENU -->
        <td id="menu_header">
<?PHP
          $this->menu_write_section($title, $text);
    }

    function menu_write_last($title, $text)
    {
          $this->menu_write_section($title, $text);
?>
        </td>
<?PHP
    }

    function menu($login, $type = "")
    {
      $this->menu_write_first("Main Menu", "<a href=\"" . $this->home . "\">Home</a><a href=\"http://chris.iluo.net/blog/feed/atom\"><img src=\"" . $this->home . "/images/rss.png\" alt=\"RSS Feed\"></a><br />
          <a href=\"" . $this->home . "/blog/\">Blog</a><br />
          <a href=\"http://sourceforge.net/users/pilkch/\">sourceforge.net</a><br />
          <a href=\"http://www.last.fm/user/cgpilk\">last.fm</a><br />
          <!--<a href=\"" . $this->home . "/about.php\">About Me</a><br />
          <a href=\"" . $this->home . "/contact.php\">Contact Me</a><br />-->");

      $this->menu_write_section("Projects", "<a href=\"http://chrisiluonet.svn.sourceforge.net/viewvc/chrisiluonet/chris.iluo.net/\">Website Source</a><br />
          <a href=\"" . $this->home . "/dropbox\">Dropbox</a><br />
          <a href=\"" . $this->home . "/beautify\">Code Beautifier</a><br />
          <a href=\"" . $this->home . "/todo\">TODO</a><br />
          <a href=\"" . $this->home . "/rss\">RSS2RSS</a><br />
          <!--<a href=\"" . $this->home . "/misc.php\">Misc Projects</a><br />
          <a href=\"" . $this->home . "/download.php\">Downloads</a><br />-->");

      /*$this->menu_write_section("Reference", "<a href=\"http://chris.iluo.net/blog/\">Journal</a><br />
        <a href=\"" . $this->home . "/reference/tutorials/tutorials.php\">Tutorials</a><br />
        <a href=\"" . $this->home . "/reference/freecode/freecode.php\">Free Code</a><br />
        <a href=\"" . $this->home . "/reference/forums/forums.php\">Forums</a><br />");*/

      $this->menu_write_section("Links", "<a href=\"http://www.iluo.net/\">
          <img alt=\"Iluo\" src=\"" . $this->home . "/images/link/iluo.png\" />
          </a><br />
          <a href=\"http://www.opera.com/\">
          <img alt=\"Opera\" src=\"" . $this->home . "/images/link/opera.png\" />
          </a><br />
          <a href=\"http://www.simonstenhouse.net/\">
          <img alt=\"Simon Stenhouse\" src=\"" . $this->home . "/images/link/sten.png\" />
          </a><br />");

      /*<a href=\"http://www.sf.net/\">
        <img alt=\"SourceForge\" src=\"" . $this->home . "/images/link/sf.png\" />
      </a><br />
      <a href=\"http://www.php.org/\">
        <img alt=\"PHP\" src=\"" . $this->home . "/images/link/php.png\" />
      </a><br />
      <a href=\"http://www.mysql.org/\">
        <img alt=\"MYSQL\" src=\"" . $this->home . "/images/link/mysql.png\" />
      </a><br />
      <a href=\"http://digi.times.lv/\">
        <img alt=\"DiGi\" src=\"" . $this->home . "/images/link/digi.png\" />
      </a><br />");*/

      $this->menu_write_section("Server", date("d/m/y") .
        "<br />\n" . $this->util->GetTimef() .
        "<br />\n<br />\n" . $this->util->StringUsersOnline());


      //we have a login and wish to display it
      if($this->showlogin && $login)
      {
        $this->menu_write_section("User", $login);
      }
      else
      {
        $this->menu_write_section("User", $this->util->GetIP() .
          "<br />\n" . $this->util->GetHost() .
          "<br />\n" . $this->util->GetReferer());
      }

      $this->menu_write_last("Theme", "Skin: " . $this->name . "<br />\n" .
        "Author: <a href=\"mailto:" . $this->author_email . "\">" . $this->author_firstname . "</a> <a href=\"mailto:" .
        $this->author_email . "\">"  . $this->author_lastname . "</a><br />\n" .
        "Description: " . $this->description . "<br />");
    }

    function error($str="")
    {
?>
        <P class="err" align="left">
          <B>Error</B>
        </P>
        <P class="errentry" align="left">
          <?PHP echo $str; ?>
        </P>
<?PHP
    }

    function main_begin()
    {
?>
        <!-- START MAIN SECTION -->
        <td id="main" rowspan="2">
<?PHP
    }

    function entry_begin($title="")
    {
?>
          <div class="box">
<?PHP
      if($title)
      {
?>
            <div class="mainimg" style="background-image: url('<?PHP echo $this->img; ?>/main.gif')">
              <h1><?PHP echo $title . "\n"; ?></h1>
            </div>
<?PHP
      }
?>
            <div class="main">
<?PHP
    }

    function entry_end()
    {
?>
            </div>
          </div>
<?PHP
    }

    function paragraph()
    {
?>
            </div>
            <div class="main">
<?PHP
    }

    //batched article with heading
    function article_heading($title, $content)
    {
      $this->entry_begin($title);
        print translate($content, $this->tabs_content);
      $this->entry_end();
    }

    //batched article without heading
    function article($content)
    {
      $this->begin_entry();
        print translate($content, $this->tabs_content);
      $this->end_entry();
    }


    //unbatched article functions
    function article_begin($title="")
    {
      $this->entry_begin($title);
    }
    function article_addline($content)
    {
      print translate($content, $this->tabs_content) . "\n";
    }
    function article_addline_raw($content)
    {
      print $content . "\n";
    }
    function article_end()
    {
      $this->entry_end();
    }


    function journalentry($content, $id,$timestamp,$project,$version,$author,$title)
    {
      $content=translate($content, $this->tabs_content);

      //$this->entry_begin($id . " " . $title);
      $this->entry_begin($title);

      $out_timestamp="";
      $out_timestamp.=$timestamp[2] . $timestamp[3];
      $out_timestamp.="/" . $timestamp[5] . $timestamp[6];
      $out_timestamp.="/" . $timestamp[8] . $timestamp[9];
      $out_timestamp.=" " . $timestamp[11] . $timestamp[12];
      $out_timestamp.=":" . $timestamp[14] . $timestamp[15];
      $out_timestamp.=":" . $timestamp[17] . $timestamp[18];
?>
                    Submitted: <?PHP echo $out_timestamp; ?> Project: <?PHP echo $project; ?> Version: <?PHP echo $version; ?> Author: <?PHP echo $author; ?><br /><br />
                    <?PHP echo $content; ?>
<?PHP
      $this->entry_end();
    }

    function journal($journal_project)
    {
      $first=0;
      $perpage=-1;

      if($journal_project!="")
        $where="journal_project=\"$journal_project\"";

      if($first<=0)
        $first=0;

      if(!$perpage)
        $perpage=-1;

      $result=$this->db->Select("journal", "count(journal_id)", $where);
      $entries=mysql_result($result,0);//$this->db->Query("select count(journal_id) from journal"),0);

      if($perpage>0)
      {
        echo $first . "-" . $perpage;
        $result=$this->db->Select("journal", "", $where, " ORDER BY `journal_timestamp` DESC LIMIT $first, $perpage");
      }
      else
      {
        $result=$this->db->Select("journal", "", $where, " ORDER BY `journal_timestamp` DESC LIMIT $first, 1000");
      }

      if($result)
        $num=$this->db->GetRows($result);
      else
        $this->error("Result returned \"$result\"");

      if($num)
      {
        $i=0;
        while ($i < $num)
        {
          $id=mysql_result($result,$i,"journal_id");
          $timestamp=mysql_result($result,$i,"journal_timestamp");
          $project=mysql_result($result,$i,"journal_project");
          $version=mysql_result($result,$i,"journal_version");
          $author=mysql_result($result,$i,"journal_author");
          $title=mysql_result($result,$i,"journal_title");
          $content=mysql_result($result,$i,"journal_content");

          if($title=="")
          $title="&lt;NO TITLE&gt;";

          $this->journalentry($content, $id,$timestamp,$project,$version,$author,$title);

          ++$i;
        }

        if($perpage>0)
        {
          $perpage=2;
          $s=$perpage . ' entries per page<br />';

          for($i=0;$i<$first;$i=$i+$perpage)
            $s=$s . '<a href="' . $PHP_SELF . '?first=' . $i  . '&perpage=' . $perpage  . '">&lt;</a> ';

          $s=$s . '<a href="' . $PHP_SELF . '?first=' . $first  . '&perpage=' . $perpage  . '">This</a> ';

          for($i=$first+$perpage;$i<$entries;$i=$i+$perpage)
            $s=$s . '<a href="' . $PHP_SELF . '?first=' . $i  . '&perpage=' . $perpage  . '">&gt;</a> ';

          $s=$s . '<br /><br />';

          $perpage=5;
          $s=$s . $perpage . ' entries per page<br />';
          for($i=0;$i<$first;$i=$i+$perpage)
            $s=$s . '<a href="' . $PHP_SELF . '?first=' . $i  . '&perpage=' . $perpage  . '">&lt;</a> ';
          $s=$s . '<a href="' . $PHP_SELF . '?first=' . $first  . '&perpage=' . $perpage  . '">This</a> ';
          for($i=$first+$perpage;$i<$entries;$i=$i+$perpage)
            $s=$s . '<a href="' . $PHP_SELF . '?first=' . $i  . '&perpage=' . $perpage  . '">&gt;</a> ';
          $s=$s . '<br /><br />';

          $perpage=10;
          $s=$s . $perpage . ' entries per page<br />';
          for($i=0;$i<$first;$i=$i+$perpage)
            $s=$s . '<a href="' . $PHP_SELF . '?first=' . $i  . '&perpage=' . $perpage  . '">&lt;</a> ';
          $s=$s . '<a href="' . $PHP_SELF . '?first=' . $first  . '&perpage=' . $perpage  . '">This</a> ';
          for($i=$first+$perpage;$i<$entries;$i=$i+$perpage)
            $s=$s . '<a href="' . $PHP_SELF . '?first=' . $i  . '&perpage=' . $perpage  . '">&gt;</a> ';
          $s=$s . '<br /><br />';
          $this->article($s);
        }
      }
    }

    function main_end($t="")
    {
?>
        </td>
<?PHP
    }

    function footer()
    {
?>
      </tr>

      <!-- START FOOTER -->
      <tr>
        <td id="menu_footer" valign="bottom">
<?PHP
          $this->menu_write_section("Disclaimer", "Website and Content Copyright &copy;2006 <a href=\"mailto:chris.pilkington@gmail.com\">Christopher</a> <a href=\"mailto:chris.pilkington@gmail.com\">Pilkington</a><br /><br />" .
                                                  "<a href=\"http://validator.w3.org/check?uri=referer\"><img src=\"http://www.w3.org/Icons/valid-xhtml11-blue.png\" alt=\"Valid XHTML 1.1\" height=\"31\" width=\"88\" /></a>");
?>
        </td>
      </tr>
    </table>
  </body>
</html>
<?PHP
    }
  }
?>
