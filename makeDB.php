<?php

$pass = explode("\n", file_get_contents('./N05-14_GML/PW.txt'));
$pdo = new PDO('mysql:dbname=location;host=localhost:charset=utf8', $pass[0], $pass[1], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);

$pdo->beginTransaction();

try {
	$pdo->exec('DROP TABLE IF EXISTS section;');

	$query = "CREATE TABLE IF NOT EXISTS section(
		id INTEGER NOT NULL,
		rint INTEGER NOT NULL COMMENT '種別。新幹線が1、在来線が2など',
		lin TEXT NOT NULL COMMENT '名前',
		opc TEXT NOT NULL COMMENT '会社名',
		rfid INTEGER NOT NULL COMMENT '文字列ID',
		time INTEGER NOT NULL COMMENT '線路開業年',
		begin INTEGER NOT NULL COMMENT '開業年',
		end INTEGER NOT NULL COMMENT '廃止年',
		PRIMARY KEY(id)
	) ENGINE = innoDB DEFAULT CHARSET=utf8;";
	$pdo->exec($query);

	
	$pdo->exec('DROP TABLE IF EXISTS station;');

	$query = "CREATE TABLE IF NOT EXISTS station(
		id INTEGER NOT NULL,
		stn TEXT NOT NULL COMMENT '駅名',
		sectionid INTEGER NOT NULL COMMENT '路線id',
		rfid INTEGER NOT NULL COMMENT '文字列ID',
		time INTEGER NOT NULL COMMENT '駅開業年',
		begin INTEGER NOT NULL COMMENT '開業年',
		end INTEGER NOT NULL COMMENT '駅廃止年',
		pos GEOMETRY NOT NULL COMMENT '座標',
		PRIMARY KEY(id)
	) ENGINE = INNODB DEFAULT CHARSET=utf8;";
	$pdo->exec($query);



	$json = file_get_contents('./N05-14_GML/N05-14.json');
	$contents = json_decode($json, true);
	$sth = $pdo->prepare('INSERT INTO section (`id`, `rint`, `lin`, `opc`, `rfid`, `time`, `begin`, `end`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
	foreach ($contents["ksj_RailroadSection2"] as $v) {
		$sth->bindValue(1, (int) substr($v["@attributes"]["gml_id"], 3), PDO::PARAM_INT);
		$sth->bindValue(2, (string) $v["ksj_int"], PDO::PARAM_STR);
		$sth->bindValue(3, (string) $v["ksj_lin"], PDO::PARAM_STR);
		$sth->bindValue(4, (string) $v["ksj_opc"], PDO::PARAM_STR);
		$sth->bindValue(5, (int) substr($v["ksj_rfid"], 5), PDO::PARAM_INT);
		$sth->bindValue(6, (int) $v["ksj_usb"]["gml_TimeInstant"]["gml_timePosition"], PDO::PARAM_INT);
		$sth->bindValue(7, (int) $v["ksj_exp"]["gml_TimePeriod"]["gml_beginPosition"], PDO::PARAM_INT);
		$sth->bindValue(8, (int) $v["ksj_exp"]["gml_TimePeriod"]["gml_endPosition"], PDO::PARAM_INT);
		$sth->execute();
		echo "\rsection:".sprintf("%04d", substr($v["@attributes"]["gml_id"], 3)).'/'.sizeof($contents["ksj_RailroadSection2"]);
	}
	echo PHP_EOL;

	$a=[];
	foreach ($contents["gml_Point"] as $v) {
		$a[$v["@attributes"]["gml_id"]]=$v["gml_pos"];
	}
	$sth = $pdo->prepare('INSERT INTO station (`id`, `stn`, `sectionid`, `rfid`, `time`, `begin`, `end`, `pos`) VALUES (:id, :stn, :sectionid, :rfid, :time, :begin, :end, POINT(:north, :east))');
	foreach ($contents["ksj_Station2"] as $v) {
		$num["id"] = (int) substr($v["@attributes"]["gml_id"],3);
		$sth->bindParam(':id', $num["id"], PDO::PARAM_INT);
		$str["stn"] = (string) $v["ksj_stn"];
		$sth->bindParam(':stn', $str["stn"], PDO::PARAM_STR);
		$num["rfid"] = (int) substr($v["ksj_rfid"],5,5);
		$sth->bindParam(':sectionid', $num["rfid"], PDO::PARAM_INT);
		$str["rfid"] = (string) $v["ksj_rfid"];
		$sth->bindParam(':rfid', $str["rfid"], PDO::PARAM_STR);
		$num["time"] = (int) $v["ksj_usb"]["gml_TimeInstant"]["gml_timePosition"];
		$sth->bindParam(':time', $num["time"], PDO::PARAM_INT);
		$num["begin"] = (int) $v["ksj_exp"]["gml_TimePeriod"]["gml_beginPosition"];
		$sth->bindParam(':begin', $num["begin"] , PDO::PARAM_INT);
		$num["end"] = (int) $v["ksj_exp"]["gml_TimePeriod"]["gml_endPosition"];
		$sth->bindParam(':end', $num["end"], PDO::PARAM_INT);
		$point = explode(' ', $a[substr($v["ksj_loc"]["@attributes"]["xlink_href"], 1)]);
		$point[0] = (string)(float) $point[0];
		$point[1] = (string)(float) $point[1];
		$sth->bindParam(':north', $point[0], PDO::PARAM_STR);
		$sth->bindParam(':east', $point[1], PDO::PARAM_STR);
		$sth->execute();
		echo "\rstation:".sprintf("%04d", substr($v["@attributes"]["gml_id"], 3)).'/'.sizeof($contents["ksj_Station2"]);
	}
	echo PHP_EOL;
	$pdo->commit();
} catch(PDOException $e) {
	echo "Error:". $e->getMessage(). PHP_EOL;
	$pdo->rollback();
}
