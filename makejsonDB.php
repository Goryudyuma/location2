<?php

$pass = explode("\n", file_get_contents(__dir__.'/N05-14_GML/PW.txt'));

try {
	$pdo = new PDO('mysql:dbname=location;host=localhost', $pass[0], $pass[1], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);

	$pdo->beginTransaction();

/*
	$sth = $pdo->prepare('INSERT INTO json (`id`, `sectionid`, `json`) VALUES (NULL, :sectionid, :json);');

	$sth->bindParam(':sectionid', $var["sectionid"], PDO::PARAM_INT);
	$sth->bindParam(':json', $var["json"], PDO::PARAM_STR);

	foreach ($b as $k => $u) {
		$var["sectionid"] = (int) substr($k, 3);
		$var["json"] = (string) json_encode($u, JSON_UNESCAPED_UNICODE);

		$sth->execute();
		echo "\rjson:".sprintf("%04d", $var["sectionid"]).'/'.sizeof($b);
	}
	echo PHP_EOL;
 */

	$pdo->exec('DROP TABLE IF EXISTS returnjson;');

	$query = "CREATE TABLE IF NOT EXISTS returnjson(
		id INTEGER NOT NULL AUTO_INCREMENT COMMENT 'id',
		rfid INTEGER NOT NULL COMMENT '路線id',
		json JSON NOT NULL COMMENT 'json',
		PRIMARY KEY (id),
		INDEX(rfid)
	) ENGINE = INNODB DEFAULT CHARSET=utf8;";
	$pdo->exec($query);


	$sth = $pdo->prepare('SELECT DISTINCT rfid FROM section;');
	$sth->execute();

	$result = $sth->fetchAll(PDO::FETCH_CLASS);

	$sth = $pdo->prepare('SELECT json FROM json WHERE id IN (SELECT id FROM section WHERE rfid = :rfid);');
	$sth->bindParam(':rfid', $var["rfid"], PDO::PARAM_INT);

	$sth2 = $pdo->prepare('INSERT INTO returnjson (`id`, `rfid`, `json`) VALUES (NULL, :rfid, :json);');
	$sth2->bindParam(':rfid', $var["rfid"], PDO::PARAM_INT);
	$sth2->bindParam(':json', $var["json"], PDO::PARAM_STR);

	$count = 0;

	foreach ($result as $x) {
		$var["rfid"] = $x->rfid;

		$sth->execute();

		$y = $sth->fetchAll(PDO::FETCH_NUM);

		foreach ($y as $k => $v) {
			$y[$k] = $v[0];
		}
		$var["json"] = '['.implode(',',$y).']';

		$sth2->execute();

		echo "\rreturnjson:".sprintf("%04d", $count++).'/'.sizeof($result);
	}
	
	$pdo->commit();
} catch(Exception $e) {
	echo "Error:". $e->getMessage(). PHP_EOL;
	$pdo->rollback();
}
