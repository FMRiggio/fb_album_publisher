<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>The Good2know Wall Publishing</title>
	<?php
	// se non sono loggato faccio il redirect alla pagina di login
	if (!$me || $notHavePermission == true) {
		echo '<script type="text/javascript">';
		echo 'top.location.href="' . $loginUrl .'"';
		echo '</script>';
		exit;
	}
	?>

</head>
<body>
