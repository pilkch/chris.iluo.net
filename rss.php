<?php

  require_once($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');
  //require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_send/rss_writer.inc');
  require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_send/atom_writer.inc');

  define("LIMIT_PER_FEED", "30");

  function CreateLowerCaseSafeDirectory($raw)
  {
    //TODO: Remove all invalid characters such as '?', '!', '.', etc.
    return strtolower($raw);
  }

  function CreateURNForIDFromDatabaseID($bIsChannel, $id)
  {
    // Make sure that they don't clash because we can have a user_id of 1 and a channel_id of 1 for example
    if ($bIsChannel == true) $id_unique = "CHANNEL" . $id;
    else $id_unique = "USER" . $id;

    // Now actually create the uri
    return "chris.iluo.net," . $id_unique;
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
      $datetime = mysql_result($result, $i, "date");
      $content = mysql_result($result, $i, "content");
      $get_category = mysql_result($result, $i, "category");

      $rss_out->addItem($hash, $title, $uri, $datetime, $content);
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
    echo '<table>
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
        <table>';
  }

  function PrintAddFeedForm($theme, $user_login, $channel_title_short)
  {
    $add_action = "add_feed";
    echo "<h3>Add Feed</h3>\n";
    echo '<table>
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
          <td>' . $theme->form_submitButton("Add Channel", $add_action, icon_add) . '

          </td>
        </tr>
        </form>
        <table>';
  }

  function CreateRSSIconLink($url)
  {
    return "<a href=\"" . $url . "\"><img src=\"http://chris.iluo.net/images/rss.png\" alt=\"RSS Feed\"></a>";
  }


  function ForEachChannelWritingToConfigurationPage($util, $theme, $channel_id)
  {
    //echo "ForEachChannel<br/>";
    if ($bUserIsOwner)
      $theme->article_addline("<table border=\"1\">" .
        "<tr><td><strong>Priority</strong></td><td><strong>Content</strong></td><td><strong>Status</strong></td><td><strong>Action</strong></td></tr>");
    else
      $theme->article_addline("<table border=\"1\">" .
        "<tr><td><strong>Feed</strong></td><td><strong>Short Title</strong></td><td><strong>URL</strong></td></tr>");

    $result = $util->db->Select("rss_feed", "", "`channel_id`='$channel_id'");
    $num = $util->db->GetRows($result);

    for ($i = 0; $i < $num; $i++) {
      $feed_id = mysql_result($result, $i, "id");
      $feed_title = mysql_result($result, $i, "title");
      $feed_titleshort = mysql_result($result, $i, "title_short");
      $feed_url = mysql_result($result, $i, "url");
      //if ($bUserIsOwner)
      //else
          $theme->article_addline("<tr><td>" . $feed_title . "</td><td>" . $feed_titleshort . "</td><td><a href=\"" . $feed_url . "\">" . $feed_url . "</a>" . CreateRSSIconLink($feed_url) . "</td></tr>");
    }

    mysql_free_result($result);

    $theme->article_addline("</table>");
  }


  $util = new cUtil();

    if ($util->query) {
      $query = $util->full_query;

      $query = strtolower(rawurldecode($query));

      $isRequestingXML = false;
      $isRequestingTXT = false;
      $isAddingChannel = false;
      $isAddingFeed = false;

      list($domain_name, $rss_folder, $user_login, $channel) = explode("/", $query);

      if ($channel) {
        // "user/channel.xml"
        $array = explode(".", $channel);

        if (count($array) > 1) {
          $channel = $array[0];
          $isRequestingXML = ($array[1] == "xml");
          $isRequestingTXT = ($array[1] == "txt");
        }
      } else {
        // "user.xml"
        $array = explode(".", $user_login);

        if (count($array) > 1) {
          $user_login = $array[0];
          $isRequestingXML = ($array[1] == "xml");
          $isRequestingTXT = ($array[1] == "txt");
        } else {
          // "user?action="
          $array = explode("?", $user_login);

          if (count($array) > 1) {
            $user_login = $array[0];
            list($action_dummy, $action) = explode("=", $array[1]);
            if ($action == "add_channel") $isAddingChannel = true;
            else if ($action == "add_feed") $isAddingFeed = true;
          }
        }
      }

      if ($isRequestingXML || $isRequestingTXT) {
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
        $bUserIsOwner = ($util->user->user == $user_login);

        $util->SetTheme();

        $theme = new cTheme($util->db, $util, true, $util->user->loggedin, $util->user->login, $util->user->type);

        $theme->header("RSS2RSS");

          $theme->menu($util->user->loginForm());

          $theme->main_begin();

            $theme->article_begin("RSS2RSS");

            if ($bUserIsOwner && $isAddingChannel && $user_login) {

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

                // Find our user id
                $result = $util->db->Select("rss_channel", "", "`title_short`='$channel'");
                $num = $util->db->GetRows($result);

                if ($num == 1) {
                  $feed_channel_id = mysql_result($result, 0, "id");
                  mysql_free_result($result);

                  $feed_title = $_REQUEST['title'];
                  $feed_title_short = CreateLowerCaseSafeDirectory($_REQUEST['title_short']);
                  $feed_url = $_REQUEST['url'];

                  if (($feed_title != "") && ($feed_title_short != "") && ($feed_url != "")) {
                    $result = $util->db->query("INSERT INTO rss_feed (id, channel_id, title, title_short, url) " .
                        "VALUES ('', '$feed_channel_id', '$feed_title', '$feed_title_short', '$feed_url');");
                  }
                }
              }

              // Now continue as usual
              {
                $theme->article_addline("<table><tr><td><h2><a href=\"http://chris.iluo.net/rss/" . $user_login . "\">" . $user_login . "</a></h2></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . ".xml") . "</td><td><a href=\"http://chris.iluo.net/rss/" . $user_login . ".txt\">Export</a></td></tr></table>");

                // Now select all of the user's channels and add them to the feed
                if ($channel) {
                  $theme->article_addline("<table><tr><td><h3><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel . "\">" . $channel . "</a></h3></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel . ".xml") . "</td><td><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel . ".txt\">Export</a></td></tr></table>");
                  $result = $util->db->Select("rss_channel", "", "`title_short`='$channel'");
                } else {
                  // Ok, we have a valid user, get the user_id for collecting the user's channel
                  $result = $util->db->Select("user", "", "`user_login`='$user_login'");
                  $num = $util->db->GetRows($result);
                  if ($num == 1) {
                    $user_id = mysql_result($result, 0, "user_id");
                    $result = $util->db->Select("rss_channel", "", "`user_id`='$user_id'");
                  }
                }
                $num = $util->db->GetRows($result);

                if ($channel && ($num > 0)) {
                  $id = mysql_result($result, 0, "id");
                  $title = mysql_result($result, 0, "title");
                  $description = mysql_result($result, 0, "description");
                  $channel_title_short = mysql_result($result, $i, "title_short");
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
                  if (!$channel) {
                    // User mode
                    $channel_title = mysql_result($result, $i, "title");
                    $channel_titleshort = mysql_result($result, $i, "title_short");
                    $channel_description = mysql_result($result, $i, "description");
                    $theme->article_addline("<table><tr><td><h3><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . "\">" . $channel_title . "</a></h3></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . ".xml") . "</td><td><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . ".txt\">Export</a></td></tr></table><p>" . $channel_description . "</p>");
                  } else {
                    // Channel mode
                    $channel_id = mysql_result($result, $i, "id");
                    ForEachChannelWritingToConfigurationPage($util, $theme, $channel_id);
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
            $result = $util->db->Select("user");
            $num = $util->db->GetRows($result);

            // Print out all of the users
            for ($i = 0; $i < $num; $i++) {
              $user_login = mysql_result($result, $i, "user_login");
              $user_loginshort = $user_login;
              $theme->article_addline("<table><tr><td><h2><a href=\"http://chris.iluo.net/rss/" . $user_loginshort . "\">" . $user_login . "</a></h2></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_loginshort . ".xml") . "</td></tr></table>");
            }

          $theme->article_end();

        $theme->main_end();
      $theme->footer();
    }

  $util->Delete();

?>
