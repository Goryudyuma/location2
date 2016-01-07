<?php
if (is_numeric($_REQUEST['id']) && is_numeric($_REQUEST['year'])) {
        $pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
        $pdo = new PDO('mysql:dbname=location;host=localhost;', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
        $sth = $pdo->prepare('SELECT json FROM json INNER JOIN (SELECT id FROM section WHERE rfid = :id AND begin <= :year AND :year <= end) AS section ON section.id = sectionid;');
        $sth->bindParam(':id', $var['i'], PDO::PARAM_INT);
		$sth->bindParam(':year', $var['year']);
        $var['i'] = (int) $_REQUEST['id'];
		$var['year'] = (int) $_REQUEST['year'];

        $sth->execute();
        $return = '{"type": "FeatureCollection", "features": [{"type": "Feature", "properties":{}, "geometry": {"type": "MultiLineString", "coordinates": ['.implode(',',$sth->fetchAll(PDO::FETCH_COLUMN)).']}}]}';
		echo $return;
        $pdo=null;
}
