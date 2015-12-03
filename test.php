<?php
$json = file_get_contents('./N05-14_GML/N05-14.json');
$contents = json_decode($json, true);


$b=[];
foreach ($contents["gml_Curve"] as $v) {
	$b[substr($v["@attributes"]["gml_id"], 3)] = explode("\n", ($v["gml_segments"]["gml_LineStringSegment"]["gml_posList"]));
	foreach ($b[substr($v["@attributes"]["gml_id"], 3)] as &$c) {
		$c=explode(' ', trim($c));
	}
}
var_dump($b);
