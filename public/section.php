<?php
if (is_numeric($_REQUEST['id'])) {
	$pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
	$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
	$sth = $pdo->prepare("SELECT json FROM json WHERE sectionid = :id LIMIT 1;");
	$var['id'] = (int) $_REQUEST['id'];

	$sth->bindParam(':id', $var['id'], PDO::PARAM_INT);
	$sth->execute();
	$result = $sth->fetch(PDO::FETCH_ASSOC);
	echo $result['json'];
}
