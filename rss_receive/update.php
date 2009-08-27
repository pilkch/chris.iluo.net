<?php

  // This php file is not meant for human consumption.
  // It is only meant for running from a cron job every 30 minutes or so.

  // For some reason when running from a cron job this has not been set
  $_SERVER['DOCUMENT_ROOT'] = "/home/pilch/chris.iluo.net";

  require_once($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  // Make sure SimplePie is included. You may need to change this to match the location of simplepie.inc.
  require_once($_SERVER['DOCUMENT_ROOT'] . '/rss_receive/simplepie/simplepie.inc');


  // An arbitrary date in the past before feeds were invented/in common use,
  // anything before this date we consider to be an incorrect date and we use the current date and time instead
  define("min_date", 20020315205903);


  function StripHTMLEntities($str)
  {
    return preg_replace("/&#?[a-z0-9]+;/i", "", $str);
  }

  // http://klaus.e175.net/code/latin_utf8.phps
  // http://www.wizcity.com/Computers/Characters/CommonUTF8.php
  // http://www.w3.org/TR/xhtml1/dtds.html#a_dtd_Latin-1_characters

  function ascii_to_entities($str)
  {
    $count = 1;
    $out = '';
    $temp = array();

    for ($i = 0, $s = strlen($str); $i < $s; $i++) {
      $ordinal = ord($str[$i]);

      if ($ordinal < 128) $out .= $str[$i];
      else {
        if (count($temp) == 0) $count = ($ordinal < 224) ? 2 : 3;

        $temp[] = $ordinal;

        if (count($temp) == $count) {
          $number = ($count == 3) ? (($temp['0'] % 16) * 4096) + (($temp['1'] % 64) * 64) + ($temp['2'] % 64) : (($temp['0'] % 32) * 64) + ($temp['1'] % 64);

          $out .= '&#'.$number.';';
          $count = 1;
          $temp = array();
        }
      }
    }

    return $out;
  }

  function ConvertHTMLEntities($str)
  {
    $from = array(
      "\x85", "\x92", "\x96", "\x97", "&#151;", "&mdash;", "&ndash;", "&ldquo;", "&rdquo;", "&rsquo;", "&lsquo;",
      "&copy;", "&plusmn;", "&rarr;", "&auml;", "&pound;", "&euro;", "&trade;", "&iacute;", "&dagger;", "&b;"
    );
    $to = array(
      "...", "'", "", " ", "-", "-", "-", "'", "'", "'", "'",
      "&#0169;", "&#177;", "-", "au", "&#163;", "&#8364;", "", "", "", ""
    );
    return ascii_to_entities(str_replace($from, $to, $str));
  }

  function EscapeURI($uri)
  {
      return str_replace(" ", "%20", $uri);
  }

  function AddArticle($util, $feed_id, $rss_item)
  {
    //echo "AddArticle<br/>";
    // Get id, we pass true to get a unique hash
    $hash = mysql_real_escape_string($rss_item->get_id(true));
    $title = StripHTMLEntities(mysql_real_escape_string($rss_item->get_title()));
    $url = EscapeURI(mysql_real_escape_string($rss_item->get_permalink()));
    $description = mysql_real_escape_string($rss_item->get_description());
    $author = mysql_real_escape_string($rss_item->get_author());
    if ($author == "") $author = mysql_real_escape_string($rss_item->get_contributor());
    if ($author == "") $author = $url;
    $date = $rss_item->get_date("ymdHis");
    if ($date < min_date) $date = date("ymdHis");
    $content = utf8_encode(utf8_decode(mysql_real_escape_string(ConvertHTMLEntities($rss_item->get_content()))));
    $category = $rss_item->get_category();
    //if (isset($category)) $category = mysql_real_escape_string($category[0]);

    /*echo "Adding Article now=" . date("ymdHis") . " date=" . $date . "<br/>" .
      "hash=$hash<br/>" .
      "title=$title<br/>" .
      "url=$url<br/>" .
      "description=$description<br/>" .
      "author=$author<br/>" .
      "date=$date<br/>" .
      "content=$content<br/>" .
      "category=$category<br/><br/>";*/

    // Blacklist of spam articles, just return if we match one, we don't want to add it to the database
    if ($title == "Chronic File") return;

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
