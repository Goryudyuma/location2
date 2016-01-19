<?php
if (is_numeric($_REQUEST['x']) && 26.0 < $_REQUEST['x'] && $_REQUEST['x'] < 46.0 && 
	is_numeric($_REQUEST['y']) && 127.0 < $_REQUEST['y'] && $_REQUEST['y'] < 146.0 && 
	is_numeric($_REQUEST['year']) && 1950 <= $_REQUEST['year'] && $_REQUEST['year'] <= 2100) {
 	$pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
	$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
// distanceは https://gist.github.com/ugwis/195ba542689e541d191c から変形
	$sth = $pdo->prepare('
	SELECT 
		c.id, 
		section.lin AS linename, 
		section.rfid as sectionid, 
		section.opc AS opc, 
		TRUNCATE(SQRT(POW(ABS(:north/180*PI() - ST_X(c.pos)/180*PI())*(6378137.0*(0.9933056200098024/POW(SQRT(1 - POW( 0.08181919084296535*SIN( (:north/180*PI() + ST_X(c.pos)/180*PI())/2), 2)), 3))), 2) + POW(ABS(:east/180*PI() - ST_Y(c.pos)/180*PI())*( 6378137.0/SQRT(1 - POW( 0.08181919084296535*SIN( (:north/180*PI() + ST_X(c.pos)/180*PI())/2), 2)))*COS( (:north/180*PI() + ST_X(c.pos)/180*PI())/2), 2)), 0) AS distance,
		ST_X(c.pos) AS north, 
		ST_Y(c.pos) AS east 
	FROM 
		(SELECT * FROM curve WHERE intersects(pos, buffer(point(:north , :east), :length))) AS c 
	INNER join 
		(SELECT DISTINCT id, lin, opc, rfid FROM section WHERE begin <= :year AND :year <= end ) AS section 
	ON 
		section.id = c.sectionid 
	ORDER BY 
		distance 
	LIMIT 
		20;
	');
	$sth->bindParam(':north', $var['north'], PDO::PARAM_STR);
	$sth->bindParam(':east', $var['east'], PDO::PARAM_STR);
	$sth->bindParam(':length', $var['length'], PDO::PARAM_STR);
	$sth->bindParam(':year', $var['year'], PDO::PARAM_STR);
	$var['north'] = (string)(float) $_REQUEST['x'];
	$var['east'] = (string)(float) $_REQUEST['y'];
	$var['year'] = (int) $_REQUEST['year'];

	for ($len=0.01; $len < 1000 ; $len*=10) {
		$var['length'] = (string)(float) $len;
		$sth->execute();
		$result=$sth->fetchAll(PDO::FETCH_CLASS);
		if (sizeof($result)>=20) {
			foreach ($result as &$v) {
				$v=json_encode($v,JSON_UNESCAPED_UNICODE);
			}
			echo '[' . implode(',', $result) . ']';
			break;
		}
	}
}
