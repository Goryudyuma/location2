<?php

$pass = explode("\n", file_get_contents(__dir__.'/N05-14_GML/PW.txt'));

try {
	$pdo = new PDO('mysql:dbname=location;host=localhost', $pass[0], $pass[1], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);

	$pdo->beginTransaction();

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
		PRIMARY KEY(id),
		INDEX(end),
		INDEX(rfid),
		INDEX(end, rfid)
	) ENGINE = INNODB DEFAULT CHARSET=utf8;";
	$pdo->exec($query);


	$pdo->exec('DROP TABLE IF EXISTS station;');

	$query = "CREATE TABLE IF NOT EXISTS station(
		id INTEGER NOT NULL,
		stn TEXT NOT NULL COMMENT '駅名',
		sectionid INTEGER NOT NULL COMMENT '路線id',
		time INTEGER NOT NULL COMMENT '駅開業年',
		begin INTEGER NOT NULL COMMENT '開業年',
		end INTEGER NOT NULL COMMENT '駅廃止年',
		pos GEOMETRY NOT NULL COMMENT '座標',
		PRIMARY KEY(id),
		INDEX(sectionid),
		INDEX(end),
		INDEX(end, sectionid),
		SPATIAL INDEX(pos)
	) ENGINE = INNODB DEFAULT CHARSET=utf8;";
	$pdo->exec($query);


	$pdo->exec('DROP TABLE IF EXISTS curve;');

	$query = "CREATE TABLE IF NOT EXISTS curve(
		id INTEGER NOT NULL AUTO_INCREMENT COMMENT 'id',
		sectionid INTEGER NOT NULL COMMENT '路線id',
		pos GEOMETRY NOT NULL COMMENT '座標',
		PRIMARY KEY (id),
		INDEX(sectionid),
		SPATIAL INDEX (pos)	
	) ENGINE = INNODB DEFAULT CHARSET=utf8;";
	$pdo->exec($query);


	$pdo->exec('DROP TABLE IF EXISTS json;');

	$query = "CREATE TABLE IF NOT EXISTS json(
		id INTEGER NOT NULL AUTO_INCREMENT COMMENT 'id',
		sectionid INTEGER NOT NULL COMMENT '路線id',
		json JSON NOT NULL COMMENT 'json',
		PRIMARY KEY (id),
		INDEX(sectionid)
	) ENGINE = INNODB DEFAULT CHARSET=utf8;";
	$pdo->exec($query);


	$json = file_get_contents('./N05-14_GML/N05-14.json');
	$contents = json_decode($json, true);
	$sth = $pdo->prepare('INSERT INTO section (`id`, `rint`, `lin`, `opc`, `rfid`, `time`, `begin`, `end`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
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

	$sth = $pdo->prepare('INSERT INTO station (`id`, `stn`, `sectionid`, `time`, `begin`, `end`, `pos`) VALUES (:id, :stn, :sectionid, :time, :begin, :end, POINT(:north, :east));');

	$sth->bindParam(':id', $var["id"], PDO::PARAM_INT);
	$sth->bindParam(':stn', $var["stn"], PDO::PARAM_STR);
	$sth->bindParam(':sectionid', $var["rfid"], PDO::PARAM_INT);
	$sth->bindParam(':time', $var["time"], PDO::PARAM_INT);
	$sth->bindParam(':begin', $var["begin"] , PDO::PARAM_INT);
	$sth->bindParam(':end', $var["end"], PDO::PARAM_INT);
	$sth->bindParam(':north', $var["north"], PDO::PARAM_STR);
	$sth->bindParam(':east', $var["east"], PDO::PARAM_STR);

	foreach ($contents["ksj_Station2"] as $v) {
		$var["id"] = (int) substr($v["@attributes"]["gml_id"],3);
		$var["stn"] = (string) $v["ksj_stn"];
		$var["rfid"] = (int) substr($v["ksj_rfid"],5,5);
		$var["time"] = (int) $v["ksj_usb"]["gml_TimeInstant"]["gml_timePosition"];
		$var["begin"] = (int) $v["ksj_exp"]["gml_TimePeriod"]["gml_beginPosition"];
		$var["end"] = (int) $v["ksj_exp"]["gml_TimePeriod"]["gml_endPosition"];
		$point = explode(' ', $a[substr($v["ksj_loc"]["@attributes"]["xlink_href"], 1)]);
		$var["north"] = (string)(float) $point[0];
		$var["east"] = (string)(float) $point[1];

		$sth->execute();
		echo "\rstation:".sprintf("%04d", substr($v["@attributes"]["gml_id"], 3)).'/'.sizeof($contents["ksj_Station2"]);
	}
	echo PHP_EOL;


	$b=[];
	foreach ($contents["gml_Curve"] as $v) {
		$b[substr($v["@attributes"]["gml_id"], 3)] = explode("\n", ($v["gml_segments"]["gml_LineStringSegment"]["gml_posList"]));
		foreach ($b[substr($v["@attributes"]["gml_id"], 3)] as &$c) {
			$c=explode(' ', trim($c));
		}
	}

	$sth = $pdo->prepare('INSERT INTO curve (`id`, `sectionid`, `pos`) VALUES (NULL, :sectionid, POINT(:north, :east));');

	$sth->bindParam(':sectionid', $var["sectionid"], PDO::PARAM_INT);
	$sth->bindParam(':north', $var["north"], PDO::PARAM_STR);
	$sth->bindParam(':east', $var["east"], PDO::PARAM_STR);

	foreach ($b as $k => $u) {
		$var["sectionid"] = (int) substr($k, 3);
		foreach ($u as $v) {
			if(sizeof($v)==2){
				$var["north"] = (string)(float) $v[0];
				$var["east"] = (string)(float) $v[1];

				$sth->execute();
			}
		}
		echo "\rcurve:".sprintf("%04d", $var["sectionid"]).'/'.sizeof($b);
	}
	echo PHP_EOL;


	$sth = $pdo->prepare('INSERT INTO json (`id`, `sectionid`, `json`) VALUES (NULL, :sectionid, :json);');

	$sth->bindParam(':sectionid', $var["sectionid"], PDO::PARAM_INT);
	$sth->bindParam(':json', $var["json"], PDO::PARAM_STR);

	foreach ($b as $k => $u) {
		$var["sectionid"] = (int) substr($k, 3);
		$u = array_merge(array_filter($u, function($data){return sizeof($data) == 2;}));	
		foreach($u as $x => $y){
			$u[$x][0] = (double)$u[$x][0];
			$u[$x][1] = (double)$u[$x][1];
		}
		$var["json"] = (string) json_encode($u, JSON_UNESCAPED_UNICODE);

		$sth->execute();
		echo "\rjson:".sprintf("%04d", $var["sectionid"]).'/'.sizeof($b);
	}
	echo PHP_EOL;

	$pdo->commit();
} catch(Exception $e) {
	echo "Error:". $e->getMessage(). PHP_EOL;
	$pdo->rollback();
}
