<?php

  include('config.inc.php');

  if(isset($_GET['token']) AND $_GET['token'] == $token) {
      
    $id = $_GET['id'];
    $comment = $_POST['comment'];
      
    $arrayCreateComment = array('sub'=>'subcustomer', 'recordid'=>$id, 'comment'=>$comment);
    $client = new soapclient($strApiUrl);
    $client->CreateComment($strApiKey,$arrayCreateComment);
      
    header("Location: index.php?p=details&id=$id"); 
      
    } else {
	    
      echo 'Unberechtigter Zugriff!';
	    
    }

?>

