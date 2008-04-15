<?php
/**
 *  A convenience class to make it easy to write RSS 2.0 classes
 * 
 *  A convenience class to make it easy to write RSS classes, patched to not
 *  longer print directly but to gather everything in $GLOBALS['rss-print'].
 *  some other patches to allow things we want :-)
 *
 *  @package sf-active
 *  @subpackage syndication
 *  @author Edd Dumbill <edd+rsswriter@usefulinc.com>
 *  @edited-by lotus <lotus@indymedia.org>
 *
 *  2.0 changes based on http://www.engadget.com/common/videos/pt/rss.xml
 */

/**
 *  A convenience class to make it easy to write RSS 2.0 classes
 *
 *  A convenience class to make it easy to write RSS 2.0 classes, patched to not
 *  longer print directly but to gather everything in $GLOBALS['rss-print'].
 *  some other patches to allow things we want :-)
 *
 *  @package    sf-active
 *  @subpackage syndication
 *  @author     Edd Dumbill <edd+rsswriter@usefulinc.com>
 *  @edited-by  lotus <lotus@indymedia.org>
 */

class RSSWriter
 {
  // A convenience class to make it easy to write RSS classes
  // Edd Dumbill <mailto:edd+rsswriter@usefulinc.com>
  // Revision 1.1  2001/05/17 18:17:46  edmundd
  // Start of a convenience library to help RSS1.0 creation

  #no vars are defined for this class

  /**
  *  Class constructor.
  *  @param    string  $uri
  *  @param    string  $title     The title of yur feed.
  *  @param    string  $description    description of your feed.
  *  @param    string  $about
  *  @param    array   $meta
  */
  function RSSWriter($uri, $title, $description, $about, $meta=array())
  {
    //Constructor
    $this->chaninfo=array();
    $this->website=$uri;
    $this->chaninfo["link"]=$uri;
    $this->chaninfo["description"]=$description;
    $this->chaninfo["title"]=$title;
    $this->items=array();
    $this->modules=array("dc" => "http://purl.org/dc/elements/1.1/", 'itunes' => 'http://www.itunes.com/dtds/podcast-1.0.dtd', 'media' => 'http://search.yahoo.com/mrss/');
    $this->channelURI=$about;
    foreach ($meta as $key => $value) {
      $this->chaninfo[$key]=$value;
    }

    $this->useModule("content", "http://purl.org/rss/1.0/modules/content/");
  }

  /**
  *  sets additional modules ("dc" is build in)
  *  @param    string  $prefix name of the module
  *  @param    string  $uri    the url declaring the module.
  */
  function useModule($prefix, $uri)
  {
    $this->modules[$prefix]=$uri;
  }

  /**
  *  sets the image of your feed.
  *  @param    string  $imgURI  url of your image
  *  @param    string  $imgAlt  alt. message of your image
  *  @param    int      $imgWidth
  *  @param    int      $imgHeight
  */
  function setImage($imgURI, $imgAlt, $imgWidth=88, $imgHeight=31)
  {
    $this->image=array(
      "uri" => $imgURI, "title" => $imgAlt, "width" => $imgWidth,
      "height" => $imgHeight);
  }

  /**
  *  adds an item to your feed.
  *  @param    string  $uri    url of the item
  *  @param    string  $title  title of the item
  *  @param    array   $meta   all other data of your item (like dc: stuff and so)
  */
  function addItem($uri, $title, $meta=array()) {
    $item=array("uri" => $uri, "link" => $uri, 
      "title" => $this->deTag($title));
    foreach ($meta as $key => $value) {
      if ($key == "description" || $key == "dc:description") {
        $value=$this->deTag($value);
      }
      $item[$key]=$value;
    }
    $this->items[]=$item;
  }

  /**
  *  renders all information into a string containing your feed.
  */
  function generateFeed()
  {
    $this->preamble();
    $this->channelinfo();

  //lotus - these aren't present in the sample feed i'm looking at
  //  $this->image();

    $this->items();
    $this->postamble();
    return $this->return_value;
  }

  /**
  *  strips tags
  *  @param    string  $in
  */
  function deTag($in)
  {
    while(ereg('<[^>]+>', $in)) {
      $in=ereg_replace('<[^>]+>', '', $in);
    }
    return $in;
  }

  /**
  *  renders the head of your feed.
  */
  function preamble()
  {
    $this->return_value.= '<?xml version="1.0" ?>
  <rss version="2.0" 
  ';
    foreach ($this->modules as $prefix => $uri) {
      $this->return_value.= "         xmlns:${prefix}=\"${uri}\"\n";
    }
    $this->return_value.= ">\n\n<channel>";
  }

  /**
  *  renders the <channel> part of your feed
  *  lotus - not used for 2.0 feed
  */
  function channelinfo() {

    $this->return_value.= '';
    $i=$this->chaninfo;
    foreach (array("title", "link", "dc:source", "description", "dc:language", "dc:publisher", 'language', 
      "dc:creator", "dc:rights", 'ttl', 'itunes:author', 'itunes:subtitle', 'itunes:explicit' ) as $f) {
      if (isset($i[$f])) {
        $this->return_value.= "    <${f}>" . htmlspecialchars($i[$f], ENT_NOQUOTES, 'UTF-8') . "</${f}>\n";
      }
          }
          foreach (array('itunes:category') as $f) {
      if (isset($i[$f])) {
                      $this->return_value.= "    <${f} text=\"" . htmlspecialchars($i[$f], ENT_NOQUOTES, 'UTF-8') . "\" />\n";
      }
    }
  }
  
  /**
  *  renders the image part.
  *  lotus - not used for 2.0 feed
  */
  function image() {
    
    if (isset($this->image)) {
    $this->return_value.= "  <image rdf:about=\"" . htmlspecialchars($this->image["uri"], ENT_NOQUOTES, 'UTF-8') . "\">\n";
      $this->return_value.= "     <title>" . htmlspecialchars($this->image["title"], ENT_NOQUOTES, 'UTF-8') . "</title>\n";
      $this->return_value.= "     <url>" . htmlspecialchars($this->image["uri"], ENT_NOQUOTES, 'UTF-8') . "</url>\n";
      $this->return_value.= "     <link>" . htmlspecialchars($this->website, ENT_NOQUOTES, 'UTF-8') . "</link>\n";
      if ($this->chaninfo["description"]){
      $this->return_value.= "     <dc:description>" . htmlspecialchars($this->chaninfo["description"], ENT_NOQUOTES, 'UTF-8') .
        "</dc:description>\n";
    $this->return_value.= "  </image>\n\n";
    }
      }
  }

  /**
  *  renders the bottom part.
  */
  function postamble()
  {
    $this->return_value.= '</channel></rss>';
  }

  /**
  *  renders all items.
  */
  function items()
  {
      foreach ($this->items as $item) {
        $this->return_value.= "  <item>\n";
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
      $this->return_value.= "  </item>\n\n";
    }
  }
}

