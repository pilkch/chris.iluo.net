<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();


  require ('common.php');

  $common = new cCommon();
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
  <parts>
    ...
  </parts>
  <achievements>
    <achievement id="beginner_cup"/>
    <achievement id="spring_cup"/>
  </achievements>
</career>
*/

  // Cars
  $car_a = new cCar();
  $car_a->id = 12345;

  $car_b = new cCar();
  $car_b->id = "abcdefghi";

  $cars = array();
  $cars[] = $car_a;
  $cars[] = $car_b;


  // Parts (Both connected to cars and spare parts)
  $part_a = new cPart();
  $part_a->id = 12346;
  $part_a->car_id = 12345;

  $part_b = new cPart();
  $part_b->id = "abcdefghj";
  $part_b->car_id = "abcdefghi";

  $parts = array();
  $parts[] = $part_a;
  $parts[] = $part_b;


  // Achievements
  $achievements = array();
  $achievements[] = "beginner_cup";
  $achievements[] = "spring_cup";


  // Ok, let's roll
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


  // Cars node
  $cars_element = $doc->createElement("cars");
  $career->appendChild($cars_element);

  // Cars children
  foreach ($cars as $car) {
    $common->CreateAndAddCarToXMLElement($util, $doc, $cars_element, $car);
  }

  // Parts node
  $parts_element = $doc->createElement("parts");
  $career->appendChild($parts_element);

  // Parts children
  foreach ($parts as $part) {
    $common->CreateAndAddPartToXMLElement($util, $doc, $parts_element, $part);
  }


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