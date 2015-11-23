<?php

$pass = explode("\n",file_get_contents("./N05-14_GML/PW.txt"));
$pdo = new PDO('mysql:dbname=location;host=localhost:charset=utf8',$pass[0],$pass[1]);

$pdo->beginTransaction();

try{
	$pdo->query("DROP TABLE IF EXISTS Section;");

	$query="CREATE TABLE IF NOT EXISTS Section(
		id INTEGER NOT NULL,
		RInt INTEGER NOT NULL COMMENT '種別。新幹線が1、在来線が2など',
		Lin TEXT NOT NULL COMMENT '名前',
		Opc TEXT NOT NULL COMMENT '会社名',
		Rfid INTEGER NOT NULL COMMENT '文字列ID',
		PRIMARY KEY(id)
	) ENGINE = innoDB DEFAULT CHARSET=utf8;";
	$pdo->query($query);


	$json = file_get_contents("./N05-14_GML/N05-14.json");
	$contents = json_decode($json, true);

	$sth = $pdo->prepare('INSERT INTO Section (`id`, `RInt`, `Lin`, `Opc`, `Rfid`) VALUES (?, ?, ?, ?, ?)');
	foreach ($contents["ksj_RailroadSection2"] as $v) {
		$sth->bindValue(1, (int)substr($v["@attributes"]["gml_id"],3), PDO::PARAM_INT);
		$sth->bindValue(2, (string)$v["ksj_int"], PDO::PARAM_STR);
		$sth->bindValue(3, (string)$v["ksj_lin"], PDO::PARAM_STR);
		$sth->bindValue(4, (string)$v["ksj_opc"], PDO::PARAM_STR);
		$sth->bindValue(5, (int)substr($v["ksj_rfid"],5), PDO::PARAM_INT);
		$sth->execute();
	}

	$pdo->commit();
}catch(PDOException $e){
	$pdo->rollback();
}
