<?php
if (is_numeric($_REQUEST['id'])) {
        $pass = explode("\n", file_get_contents('/var/www/src/PW.txt'));
        $pdo = new PDO('mysql:dbname=location;host=localhost;', $pass[2], $pass[3], [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
        $sth = $pdo->prepare("SELECT json FROM returnjson WHERE rfid = :id ;");
        $sth->bindParam(':id', $var["id"], PDO::PARAM_INT);
        $var["id"] = (int) $_REQUEST['id'];

        $sth->execute();
        echo $sth->fetchAll(PDO::FETCH_COLUMN)[0];
        $pdo=null;
}
