<?php

include('config.inc.php');
require_once('class.gscontact.php');

$objContact = new GS_CONTACT($strApiKey, $strApiUrl);
$objContact->setUsernamePassword($strUsername, $strPassword);
$objContact->dispatchRequest();