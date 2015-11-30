<?php

if ($_POST['x']==""||$_POST['y']=="") {
	header('Location: http://063.jp:8888');
	exit;
}
$pass = explode("\n", file_get_contents('./N05-14_GML/PW.txt'));
$pdo = new PDO('mysql:dbname=location;host=localhost;charset=utf8', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
$sth = $pdo->prepare("SELECT stn ,section.lin AS linename, GLength(ST_GeomFromText(CONCAT(:geo , X(pos), ' ', Y(pos), ')')))*111.319 AS distance FROM station INNER JOIN (SELECT DISTINCT rfid, lin FROM section WHERE end = 9999) AS section ON section.rfid = sectionid WHERE end = 9999 ORDER BY distance LIMIT 20; ");
$var["north"] = (string)(float) $_POST['y'];
$var["east"] = (string)(float) $_POST['x'];
$sth->execute(["geo" => "LineString(".$var['north'].' '.$var['east'].","]);
?>

<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset=utf-8>
<title>位置情報から路線を取得するテスト。</title>

</head>

<body>
<table>
<tr>
<th>駅名</th>
<th>線名</th>
<th>距離</th>
</tr>
<?php
while($result = $sth->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>'.
		 '<th>'.$result['stn'].'</th>'.
		 '<th>'.$result['linename'].'</th>'.
		 '<th>'.$result['distance'].'</th>'.
		 '</tr>'.PHP_EOL;
}
?>
</table>

<div align="center"><input type="button" onclick="location.href='index.php'" value="更新" ></div>

</body>
</html>
