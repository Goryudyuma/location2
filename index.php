<?php
$json = file_get_contents("./N05-14_GML/N05-14.json");
$contents = json_decode($json, true);
foreach ($contents["ksj_Station2"] as $k => $v) {
	if ($v["@attributes"]["gml_id"] == "stn299") {
		//      var_dump($v);
	}
}

foreach ($contents["gml_Curve"] as $k => $v) {
	if ($v["@attributes"]["gml_id"] == "cv_rrs2132") {
		var_dump(explode(" ",preg_replace_callback("/[\n][ ]{5}/",function($x){return "";},$v["gml_segments"]["gml_LineStringSegment"]["gml_posList"])));
	}
}
