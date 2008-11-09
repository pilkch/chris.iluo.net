<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();


  function CreateAndAddAttributeToXMLElement($document, $node, $attribute, $value)
  {
    $attribute_node = $document->createAttribute($attribute);
    $node->appendChild($attribute_node);

    $attribute_node->appendChild($document->createTextNode($value));
  }

  $features = array(
    "Added shadows",
    "Added scenegraph"
  );

  $fixes = array(
    "Fixed redrawing problem",
    "Shooting now works properly"
  );

  $doc = new DOMDocument();
  $doc->formatOutput = true;

  $info = $doc->createElement("info");
  $doc->appendChild($info);

  $latest = $doc->createElement("latest");
  $info->appendChild($latest);

  // URL
  $url_element = $doc->createElement("url");
  $latest->appendChild($url_element);

  CreateAndAddAttributeToXMLElement($doc, $url_element, "value", "http://chris.iluo.net/drive/files/drive081123.zip");

  // Version
  $version_element = $doc->createElement("version");
  $latest->appendChild($version_element);

  CreateAndAddAttributeToXMLElement($doc, $version_element, "major", "0");
  CreateAndAddAttributeToXMLElement($doc, $version_element, "minor", "1");

  // Release date
  $url_element = $doc->createElement("released");
  $latest->appendChild($url_element);

  CreateAndAddAttributeToXMLElement($doc, $url_element, "date_time", "19980717T140855,324Z");


  // Features node
  $features_element = $doc->createElement("features");
  $latest->appendChild($features_element);

  // Features children
  foreach ($features as $feature) {
    $feature_child = $doc->createElement("feature");
    $features_element->appendChild($feature_child);

    $feature_child->appendChild($doc->createTextNode($feature));
  }


  // Fixes node
  $fixes_element = $doc->createElement("fixes");
  $latest->appendChild($fixes_element);

  // Fixes children
  foreach ($fixes as $fix) {
    $fix_child = $doc->createElement("fix");
    $fixes_element->appendChild($fix_child);

    $fix_child->appendChild($doc->createTextNode($fix));
  }

  // Now print out the document
  header('Content-type: text/xml; charset=utf-8');
  echo $doc->saveXML();

/*
<info>
   <latest>
      <url value="http://chris.iluo.net/drive/files/drive081123.zip"/>
      <version major="0" minor="1"/>
      <released datetime="19980717T140855,324Z"/>
      <description>Just a small update</description>
      <features>
        <feature>Added shadows</feature>
        <feature>Added scenegraph</feature>
      </features>
      <fixes>
         <fix>Fixed redrawing problem</fix>
         <fix>Shooting now works properly</fix>
      </fixes>
   </latest>
</info>
*/
?>