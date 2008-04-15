<?php
/**
 *  A convenience class to make it easy to write Atom classes
 *
 *  @package sf-active
 *  @subpackage syndication
 *  @author Edd Dumbill <edd+rsswriter@usefulinc.com>
 *  @edited-by lotus <lotus@indymedia.org>
 *
 *  2.0 changes based on http://www.engadget.com/common/videos/pt/rss.xml
 */

class AtomWriter
{
  var $channel_info;

  var $items;

  var $return_value;

  function AtomWriter($channel_uri, $channel_title, $channel_description, $channel_id, $channel_author, $channel_author_email, $channel_author_uri)
  {
    $this->channel_info = array();
    $this->channel_info["title"] = $channel_title;
    $this->channel_info["description"] = $channel_description;
    $this->channel_info["link"] = $channel_uri;
    $this->channel_info["author_name"] = $channel_author;
    $this->channel_info["author_email"] = $channel_author_email;
    $this->channel_info["author_uri"] = $channel_author_uri;
    $this->channel_info["id"] = $channel_id;

    $this->items = array();

    $this->return_value = "";
  }

  //$int_date: current date in UNIX timestamp
  function get_iso_8601_date($int_date)
  {
    $date_mod = date('Y-m-d\TH:i:s', $int_date);
    $pre_timezone = date('O', $int_date);
    $time_zone = substr($pre_timezone, 0, 3) . ":" . substr($pre_timezone, 3, 2);
    $date_mod .= $time_zone;
    return $date_mod;
  }

  function deTag($in)
  {
    while(ereg('<[^>]+>', $in)) {
      $in = ereg_replace('<[^>]+>', '', $in);
    }

    return $in;
  }

  function addItem($id, $title, $uri, $datetime, $content)
  {
    $item = array("id" => $id, "title" => $this->deTag($title), "uri" => $uri, "datetime" => $datetime, "content" => $this->deTag($content));

    $this->items[] = $item;
  }

  /**
  *  renders the <channel> part of your feed
  */
  function writeChannelInfo()
  {
    $this->return_value .= "
  <title>" . $this->channel_info["title"] . "</title>
  <link rel=\"self\" href=\"" . $this->channel_info["link"] . "\"/>
  <updated>" . $this->get_iso_8601_date(time()) . "</updated>
  <author>
    <name>" . $this->channel_info["author_name"] . "</name>
    <email>" . $this->channel_info["author_email"] . "</email>
    <uri>" . $this->channel_info["author_uri"] . "</uri>
  </author>
  <id>urn:tag:" . $this->channel_info["id"] . "</id>\n";
  }

  function replaceBadCharacters($text)
  {
    /*$bad_characters = array(chr(160), chr(194));
    return str_replace($bad_characters, "", $text);*/

    $utf8 ='';
    $max = strlen($text);

    for ($i = 0; $i < $max; $i++) {

      if ($text{i} == "&") $neu = "&x26;";
      elseif ((ord($text{$i}) < 32) or (ord($text{$i}) > 127)){
        $neu = "";
        //$neu = urlencode(utf8_encode($text{$i}));
        //$neu = preg_replace('#\%(..)\%(..)\%(..)#','&#x\1;&#x\2;&#x\3;',$neu);
        //$neu = preg_replace('#\%(..)\%(..)#','&#x\1;&#x\2;',$neu);
        //$neu = preg_replace('#\%(..)#','&#x\1;',$neu);
      } else $neu = $text{$i};

      $utf8 .= $neu;
    }

    return $utf8;
  }

  function replaceAmp($text)
  {
    $text = str_replace("&nbsp;", " ", $text);
    $text = str_replace("&nbsp;", "&amp;nbsp;", $text);
    $text = str_replace("& ", "&amp;", $text);
    //$text = preg_replace("/&(?!amp;)/i", "&amp;", $text);
    //$text = preg_replace("/&amp;#(\d+);/i", "&#$1;", $text); // For numeric entities
    //$text = preg_replace("/&amp;(\w+);/i", "&$1;", $text); // For literal entities
    return $text;
  }

  function prepareString($text)
  {
    //htmlspecialchars
    return $this->replaceAmp($this->replaceBadCharacters($text));
  }

  function writeAnItem($id, $title, $uri, $datetime, $content)
  {
    $this->return_value .= "
  <entry>
    <title>" . $this->prepareString($title) . "</title>
    <link href=\"" . $uri . "\"/>
    <id>http://yoursite/article/?i=" . $id . "</id>
    <updated>" . $this->get_iso_8601_date(time() + $datetime) . "</updated>
    <summary type=\"xhtml\"><div xmlns=\"http://www.w3.org/1999/xhtml\">" . $this->prepareString($content) . "</div></summary>
  </entry>\n";
  }

  /**
  *  renders all items.
  */
  function writeItems()
  {
    foreach ($this->items as $item) {
      $this->writeAnItem($item["id"], $item["title"], $item["uri"], $item["datetime"], $item["content"]);

      /*$this->return_value.= "  <entry>\n";
      foreach ($item as $key => $value) {
        if ($key!=="uri" && $key !=="content:encoded" && $key !== "dcterms:hasPart" && $key!=='description' && $key!=='enclosure' && $key !== 'guid' && $key !== 'media:thumbnail') {
          if (is_array($value)) {
            foreach ($value as $v1) {
              $this->return_value.= "    <${key}>" . htmlspecialchars($v1, ENT_NOQUOTES, 'UTF-8') . "</${key}>\n";
            }
          }else {
            $this->return_value.= "    <${key}>" . htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8') . "</${key}>\n";
          }
        }
        if ($key == "content:encoded" or $key == 'description') {
          $this->return_value .="    <${key}><![CDATA[".$value."]]></${key}>\n";
        }
        if($key == "dcterms:hasPart" && strlen($value) > 0) {
          $this->return_value .= "    <${key} rdf:resource=\"".$value."\" />\n";
        }

        if($key == "enclosure" && strlen($value) > 0) {

          list($url, $mime, $size) = split(",", $value);
          if (strlen($url) > 0)
          {
            $this->return_value .= "   <enclosure url=\"$url\" length=\"$size\" type=\"$mime\" />\n";
          }
        }
        if ($key == 'guid') {
          $this->return_value .= '   <guid isPermaLink="true">' . $value . '</guid>' . "\n";
        }
        if ($key == 'media:thumbnail') {
          $this->return_value .= '   <media:thumbnail url="'. $value .'" />'."\n";
        }
      }
      $this->return_value.= "  </entry>\n\n";*/
    }
  }

  function writeHeader()
  {
    $this->return_value = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $this->return_value .= "<feed xmlns=\"http://www.w3.org/2005/Atom\">\n";
  }

  function writeFooter()
  {
    $this->return_value .= "</feed>\n";
  }

  function generateFeed()
  {
    $this->writeHeader();

    $this->writeChannelInfo();
    $this->writeItems();

    $this->writeFooter();

    return $this->return_value;
  }
}
