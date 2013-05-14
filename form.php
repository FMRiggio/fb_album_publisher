<form method="post" action="http://www.good2know.it/photos-publisher/index.php">
	<p>Premi invio per pubblicare le foto dal wall</p>
	<input type="submit" name="invio" value="Pubblica foto" />
	<input type="hidden" name="signed_request" value="<?php echo $_REQUEST['signed_request']; ?>" />
</form>
