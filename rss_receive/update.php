<?php

  // This php file is not meant for human consumption.
  // It is only meant for running from a cron job every 30 minutes or so.

  // For some reason when running from a cron job this has not been set
  $_SERVER['DOCUMENT_ROOT'] = "/home/pilch/chris.iluo.net";

  require_once($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  // Make sure SimplePie is included. You may need to change this to match the location of simplepie.inc.
  require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_receive/simplepie/simplepie.inc');

  function StripHTMLEntities($str)
  {
    return preg_replace("/&#?[a-z0-9]+;/i", "", $str);
  }

  function AddArticle($util, $feed_id, $rss_item)
  {
    //echo "AddArticle<br/>";
    // Get id, we pass true to get a unique hash
    $hash = mysql_real_escape_string($rss_item->get_id(true));
    $title = StripHTMLEntities(mysql_real_escape_string($rss_item->get_title()));
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

  $util->Delete();

?>
