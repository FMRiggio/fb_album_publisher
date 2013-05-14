<?php
require 'confFB.php';
require_once 'facebook.php';

$connDb = mysql_connect(ConfFB::host, ConfFB::username, ConfFB::password);
if (!$connDb) {
    die('Could not connect: ' . mysql_error());
}

try {

	$facebook = new Facebook(array(
		'appId'  => ConfFB::appIdKey,
		'secret' => ConfFB::appSecret,
		'cookie' => true,
		'domain' => ConfFB::appCallbackUrl
	));
	
	$facebook->setFileUploadSupport(true);
	
	$session = $facebook->getSession();
	
	$me = null;
	$notHavePermission = null;
	
	if ($session) {	
		try {		
			$uid 		 	=	$facebook->getUser();
			$me 		 	=	$facebook->api('/me');	
			$arrPermissions =   $facebook->api('/me/permissions');					
		} catch (FacebookApiException $e) {		
			
		}
	}

	// Controllo se esistono i permessi
	if (isset($session) && isset($arrPermissions)) {	
		if( !(array_key_exists('publish_stream', $arrPermissions['data'][0]) && array_key_exists('offline_access', $arrPermissions['data'][0])) ) {
			$notHavePermission = true;
			die("Non avete i permessi");
		}
	}

	$ok = false;

	if ($me && !$notHavePermission) {

		$record = array();
		$errors = array();
		$postSent = false;
		

		// se è stato inviato il post salvo i dati e mando alla thankyou page
		if (isset($_POST['invio'])) {
			$ok = true;
			
			$data = date('d/m/Y');
			
			//Create an album
			$album_details = array(
					'message'=> 'The Good2know Wall',
					'name'=> 'The Good2know Wall ' . $data
			);
			$create_album = $facebook->api('/me/albums', 'post', $album_details);
			  
			//Get album ID of the album you've just created
			$album_uid = $create_album['id'];
			  			
			
			$sql = "SELECT * FROM photos WHERE pubb_fb = false";
			$result = mysql_query($sql, $connDb);
			
			while($row = mysql_fetch_assoc($result)) {
				//Upload a photo to album of ID
				$photo_details = array(
					'message'=> 'Photo #' . $row['id_photo']
				);
				;
				$extension = substr($row['link'], -4);
				if ($extension == 'jpeg') {
					$extension = '.jpg';
				}
				$file = "";
				
				if (file_exists(ConfFb::pathToPhoto . $row['id_photo'] . '_resize' . $extension)) {
					$file = ConfFb::pathToPhoto . $row['id_photo'] . '_resize' . $extension;
				} else {
					if (file_exists(ConfFb::pathToPhoto . $row['id_photo'] . $extension)) {
						$file = ConfFb::pathToPhoto . $row['id_photo'] . $extension;
					}
				}
				
				if ($file != "") {
					$photo_details['image'] = realpath($file);
					$upload_photo = $facebook->api('/'.$album_uid.'/photos', 'post', $photo_details);
					if ($upload_photo) {
						$sql = "UPDATE photos set pubb_fb = true WHERE id_photo = " . $row['id_photo'];
						$result = mysql_query($sql, $connDb);
					}
				}
			}

		}
		
	} else {
	 	// url di login e logout dipendenti dallo stato dell'utente
		$loginUrl = $facebook->getLoginUrl(
								array(	'req_perms' => ConfFB::permissions, 
										'next' 		=> 'http://apps.facebook.com/goodtoknow-photospub/'
								)
							);					
	}
	

	if (isset($ok) && $ok) {
		include("thankyou.php");	
	} else {	
		include 'header.php';
		if ($me) {
			include 'form.php';
		}
		include 'footer.php';	
	}	
	
} catch(Exception $e) {
	die($e->getError());
}
?>