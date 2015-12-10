<?php
if (is_numeric($_REQUEST['id'])) {
	$pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
	$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
	$sth = $pdo->prepare("SELECT json FROM json WHERE sectionid IN (SELECT id FROM section WHERE rfid = :id) ;");
	$var['id'] = (int) $_REQUEST['id'];

	$sth->bindParam(':id', $var['id'], PDO::PARAM_INT);
	$sth->execute();
	$result = $sth->fetchAll(PDO::FETCH_COLUMN);
	echo json_encode($result);
}
