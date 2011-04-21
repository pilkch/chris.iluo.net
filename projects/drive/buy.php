<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();


  require ('common.php');

  $common = new cCommon();

/*
GET Request:
buy.xml
car list
part list, indexes via id into the car list
new, secondhand and auction do not list any cars again, they instead list indexes via id into the car list

<buy>
  <cars>
    <car id="afsadfasdfasdf">

    </car>
    ... cars
  </cars>

  <parts>
    <part id="afsadfasdf6434" car_id="afsadfasdfasdf">

    </part>
    ... parts
  </parts>

  <new>
    <car car_id="afsadfasdfasdf">
      <price value="14248"/>
    </car>
    ... cars
  </new>

  <secondhand>
    <car car_id="afsadfasdfasdf">
      <price value="14248"/>
    </car>
    ... cars
  </secondhand>

  <auction>
    <cars>
      <car car_id="afsadfasdfasdf">
        <ending datetime="19980717T140855,324Z"/>
        <starting datetime="19980717T140855,324Z"/>
      </car>
      ... cars
    </cars>
    <bids>
      <bid car_id="afsadfasdfasdf" player_id="34j9asfdjkls343" player_name="name of player1" value="10232">
      <bid car_id="afsadfasdfasdf" player_id="34j9asfdjklsdfj" player_name="name of player2" value="10500">
      ... bids
    </bids>
    <history>
      <car id="auctioned_afsadfasdfasdf">
        <ending datetime="19980717T140855,324Z"/>
        <starting datetime="19980717T140855,324Z"/>
      </car>
    </history>
  </auction>
</buy>
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


  // Ok, let's roll
  $doc = new DOMDocument();
  $doc->formatOutput = true;

  $buy = $doc->createElement("buy");
  $doc->appendChild($buy);


  // Cars node
  $cars_element = $doc->createElement("cars");
  $buy->appendChild($cars_element);

  // Cars children
  foreach ($cars as $car) {
    $common->CreateAndAddCarToXMLElement($util, $doc, $cars_element, $car);
  }

  // Parts node
  $parts_element = $doc->createElement("parts");
  $buy->appendChild($parts_element);

  // Parts children
  foreach ($parts as $part) {
    $common->CreateAndAddPartToXMLElement($util, $doc, $parts_element, $part);
  }


  // New
  $new_element = $doc->createElement("new");
  $buy->appendChild($new_element);

  // Second hand
  $secondhand_element = $doc->createElement("secondhand");
  $buy->appendChild($secondhand_element);

  // Auction
  $auction_element = $doc->createElement("auction");
  $buy->appendChild($auction_element);


  // Now print out the document
  header('Content-type: text/xml; charset=utf-8');
  echo $doc->saveXML();
?>