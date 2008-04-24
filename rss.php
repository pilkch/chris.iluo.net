<?php

  require_once($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');
  //require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_send/rss_writer.inc');
  require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_send/atom_writer.inc');

  define("LIMIT_PER_FEED", "30");

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


  function CreateRSSIconLink($url)
  {
    return "<a href=\"" . $url . "\"><img src=\"http://chris.iluo.net/images/rss.png\" alt=\"RSS Feed\"></a>";
  }


  function ForEachChannelWritingToConfigurationPage($util, $theme, $channel_id)
  {
    if ($bUserIsOwner)
      $theme->article_addline("<table border=\"1\">" .
        "<tr><td><strong>Priority</strong></td><td><strong>Content</strong></td><td><strong>Status</strong></td><td><strong>Action</strong></td></tr>");
    else
      $theme->article_addline("<table border=\"1\">" .
        "<tr><td><strong>Feed</strong></td><td><strong>URL</strong></td><td><strong>URL</strong></td></tr>");

      //echo "ForEachChannel<br/>";
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


  $util=new cUtil();

    if ($util->query) {
      $query = $util->full_query;

      $query = strtolower(rawurldecode($query));

      list($rss_folder, $rss_folder2, $user_login, $channel) = explode("/", $query);

      $isRequestingXML = false;

      if ($channel) {
        // "user/channel.xml"
        $array = explode(".", $channel);

        if (count($array) > 1) {
          $channel = $array[0];
          $isRequestingXML = true;
        }
      } else {
        // "user.xml"
        $array = explode(".", $user_login);

        if (count($array) > 1) {
          $user_login = $array[0];
          $isRequestingXML = true;
        }
      }

      if ($isRequestingXML) {
        // Ok, we have a valid user and potentially channel, get the items to generate a feed
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

            $theme->article_addline("<table><tr><td><h2><a href=\"http://chris.iluo.net/rss/" . $user_login . "\">" . $user_login . "</a></h2></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . ".xml") . "</td></tr></table>");

              // Now select all of the user's channels and add them to the feed
              if ($channel) {
                $theme->article_addline("<table><tr><td><h3><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel . "\">" . $channel . "</a></h3></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel . ".xml") . "</td></tr></table>");
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
              }

              if ($channel) $bIsChannel = true;
              else $bIsChannel = false;

              for ($i = 0; $i < $num; $i++) {
                if (!$channel) {
                  // User mode
                  $channel_title = mysql_result($result, $i, "title");
                  $channel_titleshort = mysql_result($result, $i, "title_short");
                  $theme->article_addline("<table><tr><td><h3><a href=\"http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . "\">" . $channel_title . "</a></h3></td><td>" . CreateRSSIconLink("http://chris.iluo.net/rss/" . $user_login . "/" . $channel_titleshort . ".xml") . "</td></tr></table>");
                } else {
                  // Channel mode
                  $channel_id = mysql_result($result, $i, "id");
                  ForEachChannelWritingToConfigurationPage($util, $theme, $channel_id);
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
