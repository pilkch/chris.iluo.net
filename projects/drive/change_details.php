<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();


  require ('common.php');

  $common = new cCommon();

/*
POST Request:
change_details.php
post first_name="firstname" (optional)
post last_name="lastname" (optional)
post gender="gender" (optional)

result:
<result>
  <code value="not_logged_in"/> OR
  <code value="user_does_not_exist"/> OR
  <code value="details_changed"/>
</result>
*/

  // Do stuff
  ...


  // Ok, let's roll
  $doc = new DOMDocument();
  $doc->formatOutput = true;

  // Result
  $result_element = $doc->createElement("result");
  $doc->appendChild($result_element);

  // Code
  $code_element = $doc->createElement("code");
  $result_element->appendChild($code_element);

  // Result code value
  $result_code = "not_logged_in";

  if ($result == not logged in) $result_code = "not_logged_in";
  else if ($result == user_does_not_exist) $result_code = "user_does_not_exist";
  else if ($result == details_changed) $result_code = "details_changed";

  $util->CreateAndAddAttributeToXMLElement($doc, $code_element, "value", $result_code);


  // Now print out the document
  header('Content-type: text/xml; charset=utf-8');
  echo $doc->saveXML();
?>
