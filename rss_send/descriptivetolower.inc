<?php

function DescriptiveToLower($descriptive)
{
  //$translations = array(
   // ":" => "",
  //  "+" => "plus"
  //);

  //return strtolower(strtr($descriptive, $translations));

  return strtolower(preg_replace('/[^A-Z^a-z^0-9]+/', '_',
    preg_replace('/([a-z\d])([A-Z])/', '\1_\2', 
      preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $descriptive))));
}

?>

