<?php
if (is_numeric($_REQUEST['x'])&&is_numeric($_REQUEST['y'])) {
	$pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
	$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
	$sth = $pdo->prepare("SELECT stn ,section.lin AS linename, section.opc AS opc, X(pos) AS X,Y(pos) AS Y, GLength(ST_GeomFromText(CONCAT(:geo , X(pos), ' ', Y(pos), ')')))*111.319 AS distance FROM station INNER JOIN (SELECT DISTINCT rfid, lin, opc FROM section WHERE end = 9999) AS section ON section.rfid = sectionid WHERE end = 9999 ORDER BY distance LIMIT 20; ");
	$var["north"] = (string)(float) $_REQUEST['x'];
	$var["east"] = (string)(float) $_REQUEST['y'];
	$sth->execute(["geo" => "LineString(".$var['north'].' '.$var['east'].","]);
	$result=$sth->fetchAll(PDO::FETCH_CLASS);
	foreach ($result as &$v) {
		$v=json_encode($v,JSON_UNESCAPED_UNICODE);
	}
	echo '[' . implode(',', $result) . ']';
}
