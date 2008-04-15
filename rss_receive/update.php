<?php

  require_once($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  // Make sure SimplePie is included. You may need to change this to match the location of simplepie.inc.
  require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_receive/simplepie/simplepie.inc');


  function AddArticle($util, $feed_id, $rss_item)
  {
    //echo "AddArticle<br/>";
    // Get id, we pass true to get a unique hash
    $hash = mysql_real_escape_string($rss_item->get_id(true));
    $title = mysql_real_escape_string($rss_item->get_title());
    $url = mysql_real_escape_string($rss_item->get_permalink());
    $description = mysql_real_escape_string($rss_item->get_description());
    $author = mysql_real_escape_string($rss_item->get_author() . " " . $rss_item->get_contributor());
    $date = $rss_item->get_date("ymdHis");//'j F Y | g:i a');
    echo "Adding Article now=" . date("ymdHis") . " date=" . $date . "<br/>\n";
    $content = utf8_encode(utf8_decode(mysql_real_escape_string($rss_item->get_content())));
    $get_category = mysql_real_escape_string($rss_item->get_category());

    /*echo "hash=$hash<br/>" .
      "title=$title<br/>" .
      "url=$url<br/>" .
      "description=$description<br/>" .
      "date=$date<br/>" .
      "content=$content<br/>" .
      "author=$author<br/><br/>";*/

    // Just go ahead and insert, relying on the fact that hash is unique so we can't add doubles
    $util->db->Query("INSERT INTO rss_article (feed_id, hash, title, url, description, author, date, content, category) VALUES" .
      "('$feed_id', '$hash', '$title', '$url', '$description', '$author', '$date', '$content', '$category')");
  }

  function ForEachFeed($util, $feed_id, $url)
  {
    //echo "ForEachFeed<br/>";
    $rss_in = new SimplePie($url);

    //$i = 0;

    // Loop through all of the items in the feed, and $rss_item will represents the current item in the loop.
    foreach ($rss_in->get_items() as $rss_item) {
      AddArticle($util, $feed_id, $rss_item);
      //$i++;
    }

    //echo "ForEachFeed printed " . $i . " items, returning<br/>";
  }

  function ForEachChannel($util, $channel_id)
  {
    //echo "ForEachChannel<br/>";
    $result = $util->db->Select("rss_feed", "", "`channel_id`='$channel_id'");
    $num = $util->db->GetRows($result);

    for ($i = 0; $i < $num; $i++) {
      $feed_id = mysql_result($result, $i, "id");
      $url = mysql_result($result, $i, "url");

      ForEachFeed($util, $feed_id, $url);
    }
  }


  $util=new cUtil();

  {
    $result = $util->db->Select("rss_channel");
    $num = $util->db->GetRows($result);

    for ($i = 0; $i < $num; $i++) {
      $channel_id = mysql_result($result, $i, "id");
      ForEachChannel($util, $channel_id);
    }
  }

    //$sql = 'SELECT * FROM `rss_channel`';
    //$sql = 'SELECT * FROM `rss_feed`';

    // We'll process this feed with all of the default options.


    /*$rss_out = new RSSWriter($rss_in->get_permalink(), $rss_in->get_title(), $rss_in->get_description(), "ABOUT");
    $rss_out->useModule("content", "http://purl.org/rss/1.0/modules/content/");

    // Here, we'll loop through all of the items in the feed, and $item represents the current item in the loop.
    foreach ($rss_in->get_items() as $item) {
      $rss_out->addItem($item->get_permalink(), $item->get_title(),
          array("description" => $item->get_description(),
              "content:encoded" => $item->get_description(),
              "dc:creator" => $item->get_author(),
              "dc:date" => $item->get_date('j F Y | g:i a'),
              "dc:subject" => $item->get_category()));
    }*/

  /*if ("add_top"==$action)
  {
    $theme->article_addline("add_top " . $util->query);
    $todo_content = $_REQUEST['content'];

    if($todo_content != "")
    {
      // Find out if we have any other entries with priority 0, if we do, increment the whole table
      $result = $util->db->Select("todo", "", "", " ORDER BY `todo_priority` ASC");
      $num = $util->db->GetRows($result);

      // Increment all priorities
      if (($num > 0) && (mysql_result($result, 0, "todo_priority") < 1))
        $util->db->Query("UPDATE todo SET todo_priority=todo_priority + 1 WHERE 1");

      $result = $util->db->query("INSERT INTO todo (todo_id, todo_content, todo_priority) " .
        "VALUES ('', '$todo_content', '0');");
    }
    else
      $theme->article_addline("No Content Entered.  ");
  }*/

  $util->Delete();

?>
