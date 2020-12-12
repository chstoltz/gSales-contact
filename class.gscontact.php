<?php

class GS_CONTACT{

	protected $strApiKey;
	protected $strApiUrl;
	
	protected $strUsername;
	protected $strPassword;
	
	protected $strErrorMessage;
    
        public function __construct($strApiKey, $strApiUrl){
		$this->strApiKey = $strApiKey;
		$this->strApiUrl = $strApiUrl;
		session_start();
	}
	
	public function setUsernamePassword($strUsername, $strPassword){
		if (strlen(trim($strUsername)) < 4 ) throw new Exception('Benutzername muss mindestens 4 Zeichen lang sein');
		if (strlen(trim($strPassword)) < 4 ) throw new Exception('Passwort muss mindestens 4 Zeichen lang sein');
		$this->strUsername = $strUsername;
		$this->strPassword = $strPassword;
	}
	
	public function dispatchRequest(){

		$strRequest = $_GET['p'];

		if ($strRequest != 'login' && $strRequest != 'list' && $strRequest != 'details' && $strRequest != 'logout') $strRequest = 'login';
		if ($strRequest == 'logout') $this->actionLogout();
		if (false == $this->isUserAuthenticated()) $strRequest = 'login';
		if ($strRequest == 'login' && $this->isUserAuthenticated()) $strRequest = 'list';
		if ($strRequest == 'details' && isset($_GET['id']) == false) $strRequest = 'list';
		
		switch ($strRequest) {
		    case 'list':
		    	$this->actionOverview();
		        break;
		    case 'details':
		        $this->actionDetails($_GET['id']);
		        break;
		    default:
		    	$this->actionLogin();
		        break;
		}
	}


	////////////////////////////////////////////////
	// controller

	protected function actionLogin(){
		if (isset($_POST['user'])){
			if ($_POST['user'] == $this->strUsername && $_POST['pass'] == $this->strPassword){
				$_SESSION['gscontact'] = 1;
				header('Location:index.php');
			} else {
				$this->setError('Login fehlgeschlagen!');
			}
		}
		$this->viewShowLogin();
	}
	
	protected function actionLogout(){
		unset($_SESSION['gscontact']);
	}
	
	protected function actionOverview(){
		$strSearchstring='';
		if (isset($_POST['searchfor'])) $strSearchstring = trim($_POST['searchfor']);
		$arrCustomers = $this->modelGetCustomersList($strSearchstring);
		$this->viewShowOverview($arrCustomers);
	}
	
	protected function actionDetails($intCustomerId){
		$arrCustomer = $this->modelGetCustomerDetails($intCustomerId);
		$this->viewShowDetails($arrCustomer);
	}
	
	
	////////////////////////////////////////////////
	// helper	
	
	
	protected function isUserAuthenticated(){
		if (isset($_SESSION['gscontact'])) return true;
		return false;
	}
	
	protected function setError($strMessage){
		$this->strErrorMessage = $strMessage;
	}
	
	protected function getError(){
		return $this->strErrorMessage;
	}
	
	
	////////////////////////////////////////////////
	// model

	
	protected function modelGetCustomersList($strSearchString=''){
		ini_set("soap.wsdl_cache_enabled", "0");
		$objClient = new soapclient($this->strApiUrl); 
		if ($strSearchString != '') $arrSOAPFilter[] = array('field'=>'company', 'operator'=>'like', 'value'=>$strSearchString);
	        
		$arrSOAPSort = array('field'=>'company', 'direction'=>'asc');
		$customers = $objClient->getCustomers($this->strApiKey, $arrSOAPFilter,$arrSOAPSort,999999,0);
		if ($customers['status']->code == 0){
			foreach ((array)$customers['result'] as $key => $value){
				$arrSort[$value->id] = strtolower($value->companylabel);
				$arrReturnTmp[$value->id] = $value->companylabel;
			}
			if (is_array($arrSort)) asort($arrSort);
			foreach ((array)$arrSort as $key => $value) $arrReturn[$key] = $arrReturnTmp[$key];
			return $arrReturn;
		}
		$this->setError($customers['status']->message);
		return false;
	}
	
	protected function modelGetCustomerDetails($intCustomerId){
		ini_set("soap.wsdl_cache_enabled", "0");
		$objCLient = new soapclient($this->strApiUrl); 
		$customer = $objCLient->getCustomer($this->strApiKey, $intCustomerId);
			if ($customer['status']->code == 0){
			return $customer['result'];
		}
		$this->setError($customer['status']->message);
		return false;		
	}

	
	////////////////////////////////////////////////
	// views
	
	
	protected function viewShowLogin($strError=''){
		$this->templateHeader();
		$this->templateLogin($strError);
		$this->templateFooter(false);
	}
	
	protected function viewShowOverview($arrCustomers){
		$this->templateHeader();
		$this->templateOverview($arrCustomers);
		$this->templateFooter();
	}
	
	protected function viewShowDetails($arrCustomer){
		$this->templateHeader();
		$this->templateDetails($arrCustomer);
		$this->templateFooter();
	}
		
	
	////////////////////////////////////////////////
	// templates
	
	
	protected function templateHeader(){
		?>
		<!DOCTYPE html>
		<html lang="de">
		<head>
			
			<meta content="yes" name="apple-mobile-web-app-capable" />
			<link rel="shortcut icon" href="/favicon.ico" />
			<link rel="icon" type="image/png" href="/favicon.png" sizes="32x32" />
			<link rel="icon" type="image/png" href="/favicon.png" sizes="96x96" />
			<link rel="apple-touch-icon" sizes="180x180" href="/img/touch-icon-iphone-retina.png" />
			<link rel="apple-touch-icon" sizes="167x167" href="/img/touch-icon-ipad-retina.png" />
			<link rel="manifest" href="manifest.webmanifest" />
			<link href="/img/splashscreens/iphone5_splash.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/iphone6_splash.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/iphoneplus_splash.png" media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/iphonex_splash.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/iphonexr_splash.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/iphonexsmax_splash.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/ipad_splash.png" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/ipadpro1_splash.png" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/ipadpro3_splash.png" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
			<link href="/img/splashscreens/ipadpro2_splash.png" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
			<meta content="index,follow" name="robots" />
			<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<link href="css/w3.css" rel="stylesheet" media="screen" type="text/css" />
			<link href="css/all.min.css" rel="stylesheet" media="screen" type="text/css" />
			<link href="css/skk.css" rel="stylesheet" media="screen" type="text/css" />
			<title>SKK</title>
		</head>
			
		<?php
	}
	
	protected function templateBlockError(){
		if ($this->getError() != ''){
			echo '<div class="w3-center w3-red">'.$this->getError().'</div><br />';
			echo '</ul>';
		}
	}
	
	protected function templateFooter($booShowLogout=true){
		echo '<div class="w3-bottom"><div class="w3-bar w3-blue">';
			if ($booShowLogout) echo '<div class="w3-bar-item"><a href="index.php?p=logout"><i class="fas fa-sign-out-alt"></i></a></div>';
	        echo '<div class="w3-bar-item w3-right"><strong><i>SKK</i> <i class="far fa-copyright"></i> <a href="/impressum/">christoph stoltz</a></strong></div>';
		echo '</div></div>';
		echo '</body>';
		echo '</html>';
	}
	
	protected function templateLogin(){
		?>
		<body>
                <div class="w3-top">
                <div class="w3-bar w3-blue">
                        <div class="w3-bar-item"><strong><i>SKK</i> Login</strong></div>
			<div class="w3-bar-item w3-right"><a href="#" onclick="window.location.reload(false);"><i class="fas fa-redo-alt"></i></a></div>
		</div>
		</div>
		<div class="w3-content a1">
                <div class="w3-container w3-content w3-padding-64 a2">
			<?php $this->templateBlockError(); ?>
		
			<form action="index.php" method="POST">
				<label><strong>Benutzername:</strong></label>
				<input class="w3-input" type="text" name="user" value="<?php echo $_POST['user'] ?>" /><br />
				<label><strong>Passwort:</strong></label>
				<input class="w3-input" type="password" name="pass" /><br />
				<button class="w3-btn w3-blue">Einloggen</button>
			</form>
		</div>	
		</div>	
		<?php
	}
	
	protected function templateOverview($arrCustomers){
		?>
		<body>
 		<div class="w3-top">
		<div class="w3-bar w3-blue">
		<div class="w3-bar-item w3-right"><a href="#" onclick="window.location.reload(false);"><i class="fas fa-redo-alt"></i></a></div>
		</div>
		</div>
		<div class="w3-content a1">
		<div class="w3-container w3-content w3-padding-64 a2">
		<form action="index.php?p=list" method="POST">
		<input class="w3-input w3-border" name="searchfor" placeholder="Firmennamen durchsuchen" value="<?php echo $_POST['searchfor']; ?>" type="text" />
		</form>
		<br />
		
			<?php $this->templateBlockError(); ?>		
		
			<?php
			
			echo '<ul class="w3-ul w3-border">';
			if (is_array($arrCustomers)){
				foreach ($arrCustomers as $key => $value){
					$strLetter = strtoupper(substr($value,0,1));
					if ($strLetter == '' || intval($strLetter)) $strLetter = '0-9';
					if ($strLetter != $tmpLetter) echo '<li class="w3-light-blue"><h3>'.strtoupper($strLetter).'</h3></li>';
					echo '<a href="index.php?p=details&id='.$key.'"><li class="w3-border">'.$value.'</li></a>';
					$tmpLetter = $strLetter;
				}
			}
			echo '</ul>';
			?>
		</div>		
		</div>			
		<?php
	}
	
	protected function templateDetails($arrDetails){
		$strCompanyLabel = $arrDetails->companylabel;
		?>
		<body>
		<div class="w3-top">
		<div class="w3-bar w3-blue">
			<div class="w3-bar-item"><a href="index.php?p=list"><i class="fas fa-angle-double-left"></i></a></div>
			<div class="w3-bar-item w3-right"><a href="#" onclick="window.location.reload(false);"><i class="fas fa-redo-alt"></i></a></div>
		</div></div>
		<div class="w3-content a1">
                 <div class="w3-container w3-content w3-padding-64 a2">
			<div><h2><?php echo $strCompanyLabel ?></h2></div>
		
		
		<?php
			
			$this->templateBlockError();
			
			if (trim($arrDetails->title.$arrDetails->firstname.$arrDetails->lastname) != ''){
				echo '<h3>Ansprechpartner</h3>';
				echo '<ul class="w3-ul w3-border">';
					echo '<li>'.trim($arrDetails->title .' '. $arrDetails->firstname .' '.$arrDetails->lastname).'</li>';
				echo '</ul>';
			}
			
			if (trim($arrDetails->cellular.$arrDetails->email.$arrDetails->phone.$arrDetails->fax.$arrDetails->homepage) != ''){
				echo '<h3>Kontaktdaten</h3>';
				echo '<ul class="w3-ul w3-border">';
					if (trim($arrDetails->cellular) != '') echo '<li><strong>Mobil</strong> <a href="tel:'.$arrDetails->cellular.'">'.$arrDetails->cellular.'</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="sms:'.$arrDetails->cellular.'">SMS</a></li>';
					if (trim($arrDetails->email) != '')echo '<li><strong>E-Mail</strong> <a href="mailto:'.$arrDetails->email.'">'.$arrDetails->email.'</a></li>';
					if (trim($arrDetails->phone) != '')echo '<li><strong>Telefon</strong> <a href="tel:'.$arrDetails->phone.'">'.$arrDetails->phone.'</a></li>';
					if (trim($arrDetails->fax) != '')echo '<li><strong>Fax</strong> '.$arrDetails->fax.'</li>';
					if (trim($arrDetails->homepage) != '')echo '<li><strong>Web</strong> <a href="'.$arrDetails->homepage.'">'.$arrDetails->homepage.'</a></li>';
				echo '</ul>';
			}			
			
			if (trim($arrDetails->company.$arrDetails->address.$arrDetails->zip.$arrDetails->city) != ''){
				echo '<h3>Anschrift</h3>';
				echo '<ul class="w3-ul w3-border">';
					echo '<li>';
						if (trim($arrDetails->company) != '') echo $arrDetails->company.'<br />';
						
						$strAddressString = trim ($arrDetails->address. ' ' .$arrDetails->zip . ' ' . $arrDetails->city);
						
						if ($strAddressString != ''){
							echo '<a href="https://maps.apple.com/?address='.urlencode($strAddressString).'">';
								if (trim($arrDetails->address) != '')  echo $arrDetails->address . '<br />';
								echo trim($arrDetails->zip . ' ' . $arrDetails->city);
							echo '</a>';
						}
						
					echo '</li>';
				echo '</ul>';
			}
	                
	                global $token;
	                global $strApiKey;
	                global $strApiUrl;

	                $client = new soapclient($strApiUrl);
	    
	                $arrResultComments = $client->getComments($strApiKey, 'subcustomer', $arrDetails->id);
      	                $arrComments = json_decode(json_encode($arrResultComments), true);

	                if(!empty($arrComments['result'])) {
	                echo '<h3>Kommentare</h3>';
	                echo '<ul class="w3-ul w3-border">';
	    
	                foreach($arrComments['result'] AS $result) {

                           $created = $result['created'];
			   $comment = $result['comment'];

                           echo '<li>';
                           echo '<strong>'.$created.':</strong> '.$comment;
                           echo '</li>';
                        }
	                echo '</ul>';
			}
	                echo '<h3>Kommentar hinzufügen</h3>';
	                echo '<form method="post" action="comment.php?id='.$arrDetails->id.'&token='.$token.'">';
	                echo '<input class="w3-input w3-border" type="text" name="comment" />';
			echo '</form>';

	                $arrFilterDocuments[] = array('field'=>'customers_id', 'operator'=>'is', 'value'=>$arrDetails->id);
	                $arrSortDocuments = array('field'=>'id', 'direction'=>'asc');
	                $arrResultDocuments = $client->getCustomerDocuments($strApiKey, $arrFilterDocuments, $arrSortDocuments,30,0);
	                $arrDocuments = json_decode(json_encode($arrResultDocuments), true);
	    
	                if(!empty($arrDocuments['result'])) {
	                echo '<h3>Dokumente</h3>';
	                echo '<ul class="w3-ul w3-border">';
	    
	                foreach($arrDocuments['result'] AS $result) {
			    
			    $id = $result['id'];
			    $filename = $result['original_filename'];
			    $title = $result['title'];
			    $description = $result['description'];
			    $endung = explode('.', $filename);
			    $endung = strtolower(end($endung));
			    
			    switch ($endung) {
			     case 'pdf':
	                        $icon = 'file-pdf';
	                        break;
			     case 'doc':
	                        $icon = 'file-word';
	                        break;
			     case 'docx':
	                        $icon = 'file-word';
	                        break;
			     case 'png':
	                        $icon = 'file-image';
	                        break;
			     case 'jpg':
	                        $icon = 'file-image';
	                        break;
			     case 'jpeg':
	                        $icon = 'file-image';
	                        break;
			     case 'xls':
	                        $icon = 'file-excel';
	                        break;
			     case 'xlsx':
	                        $icon = 'file-excel';
	                        break;
			     case 'zip':
	                        $icon = 'file-archive';
	                        break;
			     case 'rar':
	                        $icon = 'file-archive';
	                        break;
			     case 'm4a':
	                        $icon = 'file-audio';
	                        break;
			     case 'wav':
	                        $icon = 'file-audio';
	                        break;
			     case 'mp3':
	                        $icon = 'file-audio';
	                        break;
			     case 'xml':
	                        $icon = 'file-code';
	                        break;
			     case 'ini':
	                        $icon = 'file-code';
	                        break;
			     case 'txt':
	                        $icon = 'file-alt';
	                        break;
			     case 'odt':
	                        $icon = 'file-word';
	                        break;
			     case 'ods':
	                        $icon = 'file-excel';
	                        break;
			     case 'rtf':
	                        $icon = 'file-word';
	                        break;
			     case 'avi':
	                        $icon = 'file-video';
	                        break;
			     case 'mpg':
	                        $icon = 'file-video';
	                        break;
			     case 'mpeg':
	                        $icon = 'file-video';
	                        break;
			     case 'mp4':
	                        $icon = 'file-video';
	                        break;
			     case 'm4v':
	                        $icon = 'file-video';
	                        break;
			     default:
	                        $icon = 'file';
	                        break;
			    }
			    
			    
			    if(strlen($filename) > 30) {
				
				$dateiname = substr($filename, 0, 24) . '[...].' . $endung;
				
			    } else {
				
				$dateiname = $filename;
				
			    }
			    
			    echo '<a href="dl.php?id='.$id.'&doc='.$filename.'&token='.$token.'" target="_blank"><li class="w3-border"><i class="fas fa-'.$icon.'"></i> '.$dateiname.'</li></a>';
			    
			}
			echo '</ul>';
			}
			?>
			
		</div>		
		</div>
		<?php		
	}
	
}