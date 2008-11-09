<?PHP
  class cCar
  {
    //Variables
    var $id;
    //var $brand;
    //var $colour;

    function cCar()
    {
      $this->id = 0;
    }

    function Delete()
    {
    }
  }

  class cPart
  {
    //Variables
    var $id;
    var $car_id;

    function cCar()
    {
      $this->id = 0;
      $this->car_id = 0;
    }

    function Delete()
    {
    }
  }

  class cCommon
  {
    //Constants
    //var $difference=17;

    //Variables
    //var $db;

    function cCommon()
    {
      //Do anything here that *has* to be first to do any output
      //$config_use_sessions = TRUE;
      //session_start();

      //$this->ProcessURI();

      //$this->db=new cDB();
      //$this->db->OpenDB(false);
      //$this->user = new cLogin($this);
    }

    function Delete()
    {
      //$this->db->CloseDB();
    }

    function CreateAndAddCarToXMLElement($util, $document, $node, $car)
    {
      $car_element = $document->createElement("car");
      $node->appendChild($car_element);

      $util->CreateAndAddAttributeToXMLElement($document, $car_element, "id", $car->id);
    }

    function CreateAndAddPartToXMLElement($util, $document, $node, $part)
    {
      $part_element = $document->createElement("part");
      $node->appendChild($part_element);

      $util->CreateAndAddAttributeToXMLElement($document, $part_element, "id", $part->id);
      $util->CreateAndAddAttributeToXMLElement($document, $part_element, "car_id", $part->car_id);
    }
  }
?>
