<?php
if (is_numeric($_REQUEST['x'])&&26.0<$_REQUEST['x']&&$_REQUEST['x']<46.0&&is_numeric($_REQUEST['y'])&&127.0<$_REQUEST['y']&&$_REQUEST['y']<146.0) {
	$pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
	$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
	$sth = $pdo->prepare("SELECT section.lin AS linename, sectionid, section.opc AS opc, ST_DISTANCE(c.pos,point(:north , :east)) AS dist, sectionid FROM (SELECT * FROM curve WHERE intersects(pos, buffer(point(:north , :east), :length))) AS c INNER join (SELECT DISTINCT id, lin, opc FROM section WHERE end = 9999) AS section ON section.id = c.sectionid GROUP BY sectionid ORDER BY dist LIMIT 20;");
	$sth->bindParam(':north', $var["north"], PDO::PARAM_STR);
	$sth->bindParam(':east', $var["east"], PDO::PARAM_STR);
	$sth->bindParam(':length', $var["length"], PDO::PARAM_STR);
	$var["north"] = (string)(float) $_REQUEST['x'];
	$var["east"] = (string)(float) $_REQUEST['y'];

	for ($len=0.01; ; $len*=10) {
		$var["length"] = (string)(float) $len;
		$sth->execute();
		$result=$sth->fetchAll(PDO::FETCH_CLASS);
		if (sizeof($result)>=1) {
			foreach ($result as &$v) {
				$v=json_encode($v,JSON_UNESCAPED_UNICODE);
			}
			echo '[' . implode(',', $result) . ']';
			break;
		}
	}
}
