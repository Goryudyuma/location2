<?php
if (is_numeric($_REQUEST['x'])&&is_numeric($_REQUEST['y'])) {
	$pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
	$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
	$sth = $pdo->prepare("SELECT id, stn, section.lin AS linename, sectionid, section.opc AS opc, ST_X(pos) AS X,ST_Y(pos) AS Y, ST_DISTANCE(c.pos, POINT(:north , :east)) AS distance FROM station AS c INNER JOIN (SELECT distinct rfid, lin, opc FROM section WHERE end = 9999) AS section ON section.rfid = c.sectionid WHERE c.end = 9999 ORDER BY distance LIMIT 20;");
	$var["north"] = (string)(float) $_REQUEST['x'];
	$var["east"] = (string)(float) $_REQUEST['y'];
	$sth->bindParam(':north', $var["north"], PDO::PARAM_STR);
	$sth->bindParam(':east', $var["east"], PDO::PARAM_STR);
	$sth->execute();
#	var_dump($var);
	$result=$sth->fetchAll(PDO::FETCH_CLASS);
	foreach ($result as &$v) {
		$v=json_encode($v,JSON_UNESCAPED_UNICODE);
	}
	echo '[' . implode(',', $result) . ']';
	$pdo = NULL;
}
