<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();

/*
request:
login.php
post user="user"
post password="password"

result:
<career>
  <name value="Chris"/>
  <gender value="male"/>
  <day value="3">
  <money value="10000"/>
  <cars>
    ...
  </cars>
  <achievements>
    <achievement id="beginner_cup"/>
    <achievement id="spring_cup"/>
  </achievements>
</career>
*/

  $achievements = array(
    "Added shadows",
    "Added scenegraph"
  );

  $achievements = array(
    "beginner_cup",
    "spring_cup"
  );

  $doc = new DOMDocument();
  $doc->formatOutput = true;

  $career = $doc->createElement("career");
  $doc->appendChild($career);

  // Name
  $name_element = $doc->createElement("name");
  $career->appendChild($name_element);

  $util->CreateAndAddAttributeToXMLElement($doc, $name_element, "value", "Chris");

  // Gender
  $gender_element = $doc->createElement("gender");
  $career->appendChild($gender_element);

  $util->CreateAndAddAttributeToXMLElement($doc, $gender_element, "value", "male");

  // Day
  $day_element = $doc->createElement("day");
  $career->appendChild($day_element);

  $util->CreateAndAddAttributeToXMLElement($doc, $day_element, "value", "3");

  // Money
  $money_element = $doc->createElement("money");
  $career->appendChild($money_element);

  $util->CreateAndAddAttributeToXMLElement($doc, $money_element, "value", "10000");


  // Achievements node
  $achievements_element = $doc->createElement("achievements");
  $career->appendChild($achievements_element);

  // Achievements children
  foreach ($achievements as $achievement) {
    $achievement_child = $doc->createElement("achievement");
    $achievements_element->appendChild($achievement_child);

    $achievement_child->appendChild($doc->createTextNode($achievement));
  }


  // Now print out the document
  header('Content-type: text/xml; charset=utf-8');
  echo $doc->saveXML();
?>