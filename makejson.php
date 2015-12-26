<?php
$str = __dir__."/N05-14_GML/N05-14.xml";
$content = file_get_contents($str);

//XMLの名前空間、今回は邪魔なので置換して消す
$content = preg_replace_callback('/[:]/', function($x){return "_";}, $content);
$xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml,JSON_UNESCAPED_UNICODE);

//jsonにして格納
file_put_contents("./N05-14_GML/N05-14.json",$json);
