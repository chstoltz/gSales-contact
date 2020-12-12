<?php

  include('config.inc.php');

  if(isset($_GET['token']) AND $_GET['token'] == $token) {

    $id = $_GET['id'];
    $datei = $_GET['doc'];
      
    $filename = $gsalesDocRoot.$id.'-'.$datei;
      
    $mime = mime_content_type($filename);
    
    header('Content-type: '. $mime);
    header('Content-Disposition: attachment; filename="' .$filename .'"');
    readfile($filename);
    exit;

  } else {

    echo 'Unberechtigter Zugriff!';
 
  }

?>