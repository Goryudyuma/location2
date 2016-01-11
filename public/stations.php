<?php
if (is_numeric($_REQUEST['x']) && 26.0 < $_REQUEST['x'] && $_REQUEST['x'] < 46.0 &&
	is_numeric($_REQUEST['y']) && 127.0 < $_REQUEST['y'] && $_REQUEST['y'] < 146.0 &&
	is_numeric($_REQUEST['year']) && 1950 <= $_REQUEST['year'] && $_REQUEST['year'] <= 2100) {
	$pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
	$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
	$sth = $pdo->prepare('SELECT stn, section.lin AS linename, sectionid, section.opc AS opc, ST_X(pos) AS X,ST_Y(pos) AS Y, ST_DISTANCE(c.pos, POINT(:north , :east)) AS distance FROM station AS c INNER JOIN (SELECT distinct rfid, lin, opc FROM section WHERE begin <= :year AND :year <= end ) AS section ON section.rfid = c.sectionid WHERE c.begin <= :year AND :year <= c.end ORDER BY distance LIMIT 20;');
	$sth->bindParam(':north', $var['north'], PDO::PARAM_STR);
	$sth->bindParam(':east', $var['east'], PDO::PARAM_STR);
	$sth->bindParam(':year', $var['year'], PDO::PARAM_INT);
	$var['north'] = (string)(float) $_REQUEST['x'];
	$var['east'] = (string)(float) $_REQUEST['y'];
	$var['year'] = (int) $_REQUEST['year'];
	$sth->execute();
	$result=$sth->fetchAll(PDO::FETCH_CLASS);
	if (sizeof($result) !== 0) {
		foreach ($result as &$v) {
			$v=json_encode($v,JSON_UNESCAPED_UNICODE);
		}
		echo '[' . implode(',', $result) . ']';
	}
	$pdo = NULL;
}
