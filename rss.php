<?php

  require_once($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');
  //require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_send/rss_writer.inc');
  require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_send/atom_writer.inc');

  define("LIMIT_PER_FEED", "30");

  function CreateLowerCaseSafeDirectory($raw)
  {
    // Remove all invalid characters such as '?', '!', '.', etc.
    // Translate: ' ' -> '_', ':' -> '', '+' -> 'plus'
    $badchr = array("?", "!", ".", " ", ":", "+");
    $goodchr = array("", "", "", "_", "", "plus");
    return str_replace($badchr, $goodchr, strtolower($raw));
  }

  function CreateURNForIDFromDatabaseID($bIsChannel, $id)
  {
    // Make sure that they don't clash because we can have a user_id of 1 and a channel_id of 1 for example
    if ($bIsChannel == true) $id_unique = "CHANNEL" . $id;
    else $id_unique = "USER" . $id;

    // Now actually create the uri
    return "chris.iluo.net," . $id_unique;
  }

  function DeleteFeed($util, $feed_id)
  {
    echo "DeleteFeed id=$feed_id";
    // Remove articles
    $util->db->Remove("rss_article", "feed_id = '$feed_id'");

    // Remove feed
    $util->db->Remove("rss_feed", "id = '$feed_id'");
  }

  function DeleteChannel($util, $channel_id)
  {
    echo "DeleteChannel id=$channel_id";
    $result = $util->db->Select("rss_feed", "", "`channel_id`='$channel_id'");
    $num = $util->db->GetRows($result);

    for ($i = 0; $i < $num; $i++) {
      $feed_id = mysql_result($result, $i, "id");

      // Remove articles
      $util->db->Remove("rss_article", "feed_id = '$feed_id'");
    }

    mysql_free_result($result);

    // Remove feeds
    $util->db->Remove("rss_feed", "channel_id = '$channel_id'");

    // Remove channel
    $util->db->Remove("rss_channel", "id = '$channel_id'");
  }

  function ForEachFeed($util, $rss_out, $feed_id)
  {
    //echo "ForEachFeed<br/>";
    $result = $util->db->Select("rss_article", "", "`feed_id`='$feed_id'", " ORDER BY `date` DESC LIMIT " . LIMIT_PER_FEED);
    $num = $util->db->GetRows($result);

    for ($i = 0; $i < $num; $i++) {
      $hash = mysql_result($result, $i, "hash");
      $title = mysql_result($result, $i, "title");
      $uri = mysql_result($result, $i, "url");
      $description = mysql_result($result, $i, "description");
      $author = mysql_result($result, $i, "author");
      $author_email = "AUTHOR_EMAIL@email.com";
      $datetime = mysql_result($result, $i, "date");
      $content = mysql_result($result, $i, "content");
      $get_category = mysql_result($result, $i, "category");

      $rss_out->addItem($hash, $title, $uri, $author, $author_email, $datetime, $content);
    }

    mysql_free_result($result);
  }

  function ForEachChannel($util, $rss_out, $channel_id)
  {
    //echo "ForEachChannel<br/>";
    $result = $util->db->Select("rss_feed", "", "`channel_id`='$channel_id'");
    $num = $util->db->GetRows($result);

    for ($i = 0; $i < $num; $i++) {
      $feed_id = mysql_result($result, $i, "id");
      ForEachFeed($util, $rss_out, $feed_id);
    }

    mysql_free_result($result);
  }


  function PrintAddChannelForm($theme, $user_login)
  {
    $add_action = "add_channel";
    echo "<h3>Add Channel</h3>\n";
    echo '
        <table>
          <form name="' . $add_action . '" method="post" action="http://chris.iluo.net/rss/' . $user_login . '?action=' . $add_action . '">
          <tr>
            <td>Title</td>
            <td>Title Short</td>
            <td>Description</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><input text cols="80" id="title" name="title" style="width:100%"/></td>
            <td><input text cols="80" id="title_short" name="title_short" style="width:100%"/></td>
            <td><input text cols="80" id="description" name="description" style="width:100%"/></td>
            <td>' . $theme->form_submitButton("Add Channel", $add_action, icon_add) . '

            </td>
          </tr>
          </form>
        </table>';
  }

  function PrintAddFeedForm($theme, $user_login, $channel_title_short)
  {
    $add_action = "add_feed";
    echo "<h3>Add Feed</h3>\n";
    echo '
        <table>
          <form name="' . $add_action . '" method="post" action="http://chris.iluo.net/rss/' . $user_login . '/' . $channel_title_short . '?action=' . $add_action . '">
          <tr>
            <td>Title</td>
            <td>Title Short</td>
            <td>URL</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><input text cols="80" id="title" name="title" style="width:100%"/></td>
            <td><input text cols="80" id="title_short" name="title_short" style="width:100%"/></td>
            <td><input text cols="80" id="url" name="url" style="width:100%"/></td>
            <td>' . $theme->form_submitButton("Add Feed", $add_action, icon_add) . '

            </td>
          </tr>
          </form>
        </table>';
  }

  function CreateRSSIconLink($url)
  {
    return "<a href=\"" . $url . "\"><img src=\"http://chris.iluo.net/images/rss.png\" alt=\"RSS Feed\"></a>";
  }

  function CreateOPMLIconLink($url)
  {
    return "<a href=\"" . $url . "\"><img src=\"http://chris.iluo.net/images/opml.png\" alt=\"OPML Feed\"></a>";
  }

  function ForEachChannelWritingToConfigurationPage($util, $theme, $bUserIsOwner, $user_login, $channel_titleshort, $channel_id)
  {
    //echo "ForEachChannelWritingToConfigurationPage<br/>";
    if ($bUserIsOwner)
      $theme->article_addline("<table border=\"1\">" .
        "<tr><td><strong>Priority</strong></td><td><strong>Content</strong></td><td><strong>Status</strong></td><td><strong>Action</strong></td></tr>");
    else
      $theme->article_addline("<table border=\"1\">" .
        "<tr><td><strong>Feed</strong></td><td><strong>Short Title</strong></td><td><strong>URL</strong></td></tr>");

    $result = $util->db->Select("rss_feed", "", "`channel_id`='$channel_id'", " ORDER BY `title` ASC");
    $num = $util->db->GetRows($result);

    for ($i = 0; $i < $num; $i++) {
      $feed_id = mysql_result($result, $i, "id");
      $feed_title = mysql_result($result, $i, "title");
      $feed_titleshort = mysql_result($result, $i, "title_short");
      $feed_url = mysql_result($result, $i, "url");

      $output_line = "";
      //if ($bUserIsOwner)
      //else
          $output_line .= "<td>" . $feed_title . "</td><td>" . $feed_titleshort . "</td><td><a href=\"" . $feed_url . "\">" . $feed_url . "</a>" . CreateRSSIconLink($feed_url) . "</td>";

      if ($bUserIsOwner) $output_line .= "<td>" . $theme->form_action("remove_feed$feed_id", "Remove", "rss/" . $user_login . "/" . $channel_titleshort, "remove_feed&id=$feed_id", icon_delete) . "</td>";

      $theme->article_addline("<tr>" . $output_line . "</tr>");
    }

    mysql_free_result($result);

    $theme->article_addline("</table>");
  }


  $util = new cUtil();

    if ($util->query) {
      $query = $util->full_query;

      $query = strtolower(rawurldecode($query));

      $isRequestingXML = false;
      $isRequestingOPML = false;
      $isRequestingTXT = false;
      $isAddingChannel = false;
      $isAddingFeed = false;
      $isRemovingFeed = false;
      $isRemovingChannel = false;

      list($domain_name, $rss_folder, $user_login, $channel) = explode("/", $query);

      if ($channel) {
        // "user/channel.xml"
        $array = explode(".", $channel);

        if (count($array) > 1) {
          $channel = $array[0];
          $isRequestingXML = ($array[1] == "xml");
          $isRequestingTXT = ($array[1] == "txt");
          $isRequestingOPML = ($array[1] == "opml");
        } else {
            // "user/channel?action="
            $array = explode("?", $channel);

            if (count($array) > 1) {
              $channel = $array[0];

              $pairs = explode("&", $array[1]);
              $n = count($pairs);
              //echo "channel pairs=";
              //for ($i = 0; $i < $n; $i++) echo "([" . $i . "]=" . $pairs[$i] . ")";
              //echo "<br />\n";
              if ($n > 0) {
                list($action, $parameter) = explode("=", $pairs[0]);
                //echo "action=" . $action . ", parameter=" . $parameter . "<br />\n";
                if ($parameter == "add_feed") $isAddingFeed = true;
                else if (($parameter == "remove_feed") && ($n > 1)) {
                  list($id_dummy, $feed_id) = explode("=", $pairs[1]);
                  $isRemovingFeed = true;
                  //echo "pairs[0]=" . $pairs[0] . "pairs[1]=" . $pairs[1];
                }
              }
            }
        }
      } else {
        // "user.xml"
        $array = explode(".", $user_login);

        if (count($array) > 1) {
          $user_login = $array[0];
          $isRequestingXML = ($array[1] == "xml");
          $isRequestingTXT = ($array[1] == "txt");
          $isRequestingOPML = ($array[1] == "opml");
        } else {
          // "user?action="
          $array = explode("?", $user_login);

          if (count($array) > 1) {
            $user_login = $array[0];

            $pairs = explode("&", $array[1]);
            $n = count($pairs);
            //echo "user pairs=";
            //for ($i = 0; $i < $n; $i++) echo "([" . $i . "]=" . $pairs[$i] . ")";
            //echo "<br />\n";
            if ($n > 0) {
              list($action, $parameter) = explode("=", $pairs[0]);
              //echo "action=" . $action . ", parameter=" . $parameter . "<br />\n";
              if ($parameter == "add_channel") $isAddingChannel = true;
              else if (($parameter == "remove_channel") && ($n > 1)) {
                list($id_dummy, $channel_id) = explode("=", $pairs[1]);
                $isRemovingChannel = true;
                //echo "pairs[0]=" . $pairs[0] . "pairs[1]=" . $pairs[1];
              }
            }
          }
        }
      }

      if ($isRequestingXML || $isRequestingTXT || $isRequestingOPML) {
        // Ok, we have a valid user and potentially channel
        $result = $util->db->Select("user", "", "`user_login`='$user_login'");
        $num = $util->db->GetRows($result);

        if ($num == 1) {
          $user_id = mysql_result($result, 0, "user_id");
          $id = $user_id;
          mysql_free_result($result);

          $url = "http://chris.iluo.net/rss/" . $user_login;
          $title = ucwords($user_login);

          if ($channel) {
            $url .= "/$channel";
            $title = ucwords($channel);
          }

          $description = "Description";

          // Now select all of the user's channels and add them to the feed
          if ($channel) $result = $util->db->Select("rss_channel", "", "`title_short`='$channel'");
          else $result = $util->db->Select("rss_channel", "", "`user_id`='$user_id'");
          $num = $util->db->GetRows($result);

          if ($channel && ($num > 0)) {
            $id = mysql_result($result, 0, "id");
            $title = mysql_result($result, 0, "title");
            $description = mysql_result($result, 0, "description");
          }

          $output = "";

          if ($isRequestingTXT) {
            // The simplest format, either print out a list of all the user's urls or a list of all the feed's urls
            for ($i = 0; $i < $num; $i++) {
              $channel_id = mysql_result($result, $i, "id");

              $feed_result = $util->db->Select("rss_feed", "", "`channel_id`='$channel_id'");
              $feed_num = $util->db->GetRows($feed_result);

              for ($feed_i = 0; $feed_i < $feed_num; $feed_i++) {
                $feed_url = mysql_result($feed_result, $feed_i, "url");
                $output .= $feed_url . "\n";
              }

              mysql_free_result($feed_result);
            }

            mysql_free_result($result);

            $output .= "\n";

            header('Content-type: text/plain; charset=utf-8');
          } else if ($isRequestingOPML) {
            // Either print out a list of all the user's urls or a list of all the feed's urls
            $channel_author = ucwords($user_login);
            $channel_author_email = "AUTHOR_EMAIL@email.com";

            $output .= '<?xml version="1.0" encoding="utf-8"?>
              <!-- OPML generated by chris.iluo.net on Fri, 23 Jul 2004 23:41:33 GMT -->
              <opml version="1.1">
                <head>
                  <title>' . $title . '</title>
                  <dateCreated>Fri, 02 Jan 2004 12:59:58 GMT</dateCreated>
                  <dateModified>Fri, 23 Jul 2004 23:41:32 GMT</dateModified>
                  <ownerName>' . $channel_author . '</ownerName>
                  <ownerEmail>' . $channel_author_email . '</ownerEmail>
                  <expansionState></expansionState>
                  <vertScrollState>1</vertScrollState>
                  <windowTop>20</windowTop>
                  <windowLeft>10</windowLeft>
                  <windowBottom>120</windowBottom>
                  <windowRight>147</windowRight>
                </head>
                <body>';
            $output .= "\n";

            for ($i = 0; $i < $num; $i++) {
              $channel_id = mysql_result($result, $i, "id");

              $feed_result = $util->db->Select("rss_feed", "", "`channel_id`='$channel_id'");
              $feed_num = $util->db->GetRows($feed_result);

              for ($feed_i = 0; $feed_i < $feed_num; $feed_i++) {
                $feed_url = mysql_result($feed_result, $feed_i, "url");
                $feed_title = mysql_result($feed_result, $feed_i, "title");
                $output .= "<outline text=\"" . $feed_title . "\" description=\"" . $feed_title . "\" title=\"" . $feed_title . "\" type=\"rss\" version=\"RSS2\" xmlUrl=\"" . htmlspecialchars($feed_url) . "\"/>\n";

                // Attributes not used:
                // htmlUrl="http://www.cnn.com/blog"
                // language=\"unknown\"
                // count=\"" . ((($i + 1) * $num) + $feed_i) . "\"
              }

              mysql_free_result($feed_result);
            }

            mysql_free_result($result);

            $output .= "</body>\n</opml>\n";

            $output .= "\n";

            header('Content-type: text/x-opml; charset=utf-8');
          } else if ($isRequestingXML) {
            $channel_author = ucwords($user_login);
            $channel_author_email = "AUTHOR_EMAIL@email.com";
            $channel_author_uri = $url;

            $url .= ".xml";

            if ($channel) $bIsChannel = true;
            else $bIsChannel = false;

            // Start collecting the RSS output
            $rss_out = new AtomWriter($url, $title, $description, CreateURNForIDFromDatabaseID($bIsChannel, $id),
              $channel_author, $channel_author_email, $channel_author_uri);

            for ($i = 0; $i < $num; $i++) {
              $channel_id = mysql_result($result, $i, "id");
              ForEachChannel($util, $rss_out, $channel_id);
            }

            mysql_free_result($result);


            // Output the feed
            $output = $rss_out->generateFeed();

            // RSS
            //header("Content-Type: application/rss+xml");

            // Atom
            header('Content-type: text/xml; charset=utf-8');
          }

          // Now print out the output no matter what the format
          echo $output;
        } else {
          mysql_free_result($result);
          echo "INVALID USER user_login=" . $user_login . ", channel=" . $channel . " query=" . $query . "<br/>\n";
        }
      } else {
        $bUserIsLoggedIn = $util->user->isLoggedIn();
        $bUserIsOwner = ($bUserIsLoggedIn && $util->user->user && $user_login && ($util->user->user == $user_login));
        //echo "user=" . $util->user->user . " login=" . $user_login . " loggedin=" . $bUserIsLoggedIn . " isowner=" . $bUserIsOwner . "<br />\n";

        $util->SetTheme();

        $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);

        $theme->header("RSS2RSS");

          $theme->menu($util->user->loginForm());

          $theme->main_begin();

            $theme->article_begin("RSS2RSS");

            if ($bUserIsOwner && $isAddingChannel && $user_login) {

              //echo "Adding channel<br />\n";
              // Find our user id
              $result = $util->db->Select("user", "", "`user_login`='$user_login'");
              $num = $util->db->GetRows($result);

              if ($num == 1) {
                $user_id = mysql_result($result, 0, "user_id");
                mysql_free_result($result);

                $title = $_REQUEST['title'];
                $title_short = CreateLowerCaseSafeDirectory($_REQUEST['title_short']);
                $description = $_REQUEST['description'];

                if (($title != "") && ($title_short != "") && ($description != "")) {
                  $result = $util->db->query("INSERT INTO rss_channel (id, user_id, title, title_short, description) " .
                      "VALUES ('', '$user_id', '$title', '$title_short', '$description');");
                }
              }

            } else if ($bUserIsOwner && $isAddingFeed && $channel) {

              //echo "Adding feed<br />\n";
              // Find our user id
              $result = $util->db->Select("rss_channel", "", "`title_short`='$channel'");
              $num = $util->db->GetRows($result);

              if ($num == 1) {
                //echo "Found channel<br />\n";
                $feed_channel_id = mysql_result($result, 0, "id");
                mysql_free_result($result);

                $feed_title = $_REQUEST['title'];
                $feed_title_short = CreateLowerCaseSafeDirectory($_REQUEST['title_short']);
                $feed_url = $_REQUEST['url'];

                // Require title, titleshort, url and that url starts with "http://"
                if (($feed_title != "") && ($feed_title_short != "") && ($feed_url != "") && (substr($feed_url, 0, 7) == "http://")) {
                  //echo "Inserting feed $feed_title, $feed_title_short, $feed_url<br />\n";
                  $result = $util->db->query("INSERT INTO rss_feed (id, channel_id, title, title_short, url) " .
                      "VALUES ('', '$feed_channel_id', '$feed_title', '$feed_title_short', '$feed_url');");
                }
              }
            } else if ($bUserIsOwner && $isRemovingFeed && $feed_id) DeleteFeed($util, $feed_id);
            else if ($bUserIsOwner && $isRemovingChannel && $channel_id) DeleteChannel($util, $channel_id);

              // Now continue as usual
              {
                $theme->article_addline("<table><tr><td><h2><a href=\"http://chris.iluo.net/rss/" . $user_login . "\">" . $user_login . "</a></h2></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . ".xml") . "</td><td>" . CreateOPMLIconLink("http://chris.iluo.net/rss/" . $user_login . ".opml") . "&nbsp;<a href=\"http://chris.iluo.net/rss/" . $user_login . ".txt\">txt</a></td></tr></table>");

                // Now select all of the user's channels and add them to the feed
                if ($channel) {
                  $theme->article_addline("<table><tr><td><h3><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel . "\">" . $channel . "</a></h3></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel . ".xml") . "</td><td>" . CreateOPMLIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel . ".opml") . "&nbsp;<a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel . ".txt\">txt</a></td></tr></table>");
                  $result = $util->db->Select("rss_channel", "", "`title_short`='$channel'", " ORDER BY `title` ASC");
                } else {
                  // Ok, we have a valid user, get the user_id for collecting the user's channel
                  $result = $util->db->Select("user", "", "`user_login`='$user_login'");
                  $num = $util->db->GetRows($result);
                  if ($num == 1) {
                    $user_id = mysql_result($result, 0, "user_id");
                    $result = $util->db->Select("rss_channel", "", "`user_id`='$user_id'", " ORDER BY `title` ASC");
                  }
                }
                $num = $util->db->GetRows($result);

                if ($channel && ($num > 0)) {
                  $id = mysql_result($result, 0, "id");
                  $title = mysql_result($result, 0, "title");
                  $description = mysql_result($result, 0, "description");
                  $channel_title_short = mysql_result($result, 0, "title_short");
                }

                if ($channel) {
                  $bIsChannel = true;
                  if ($bUserIsOwner) PrintAddFeedForm($theme, $user_login, $channel_title_short);
                }
                else {
                  $bIsChannel = false;

                  if ($bUserIsOwner) PrintAddChannelForm($theme, $user_login);
                }

                for ($i = 0; $i < $num; $i++) {
                  $channel_id = mysql_result($result, $i, "id");
                  $channel_title = mysql_result($result, $i, "title");
                  $channel_titleshort = mysql_result($result, $i, "title_short");
                  $channel_description = mysql_result($result, $i, "description");

                  if (!$channel) {
                    $output_line = "";

                    // User mode
                        $output_line .= "<td><h3><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . "\">" . $channel_title . "</a></h3></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . ".xml") . "</td><td>" . CreateOPMLIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . ".opml") . "&nbsp;<a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . ".txt\">txt</a></td>";

                    if ($bUserIsOwner) $output_line .= "<td>" . $theme->form_action("remove_channel$channel_id", "Remove", "rss/" . $user_login, "remove_channel&id=$channel_id", icon_delete) . "</td>";

                    $theme->article_addline("<table><tr>" . $output_line . "</tr></table><p>" . $channel_description . "</p>");
                  } else {
                    // Channel mode
                    ForEachChannelWritingToConfigurationPage($util, $theme, $bUserIsOwner, $user_login, $channel_titleshort, $channel_id);
                  }
                }
              }

            $theme->article_end();

          $theme->main_end();
        $theme->footer();
      }
    } else {
      // No request, show main sign up page
      $util->SetTheme();

      $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);

      $theme->header("RSS2RSS");

        $theme->menu($util->user->loginForm());

        $theme->main_begin();

          $theme->article_begin("RSS2RSS");

            // Now select all of the users
            $result = $util->db->Select("user", "", "", " ORDER BY `user_login` ASC");
            $num = $util->db->GetRows($result);

            // Print out all of the users
            for ($i = 0; $i < $num; $i++) {
              $user_login = mysql_result($result, $i, "user_login");
              $user_loginshort = $user_login;
              $theme->article_addline("<table><tr><td><h2><a href=\"http://chris.iluo.net/rss/" . $user_loginshort . "\">" . $user_login . "</a></h2></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_loginshort . ".xml") . "</td><td>" . CreateOPMLIconLink("http://chris.iluo.net/rss/" . $user_loginshort . ".opml") . "</td></tr></table>");
            }

          $theme->article_end();

        $theme->main_end();
      $theme->footer();
    }

  $util->Delete();

?>
