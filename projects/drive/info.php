<?php
  require ($_SERVER['DOCUMENT_ROOT'] . '/util/util.php');

  $util = new cUtil();

  // Now print out the document
  header('Content-type: text/xml; charset=utf-8');
  echo "<?xml version=\"1.0\"?>\n";
?>
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
