<!DOCTYPE html> 
<html lang="ja"> 
<head> 
<meta charset=utf-8>
<script>
//http://www.htmq.com/geolocation/

//ユーザーの現在の位置情報を取得
navigator.geolocation.getCurrentPosition(successCallback2, errorCallback);


/***** ユーザーの現在の位置情報を取得 *****/
function successCallback2(position) {
	var gl_text = '<input type=\"HIDDEN\" name=\"y\" value=\"' + position.coords.latitude +'\">';
	gl_text += '<input type=\"HIDDEN\" name=\"x\" value=\"' + position.coords.longitude  +'\">';
 	document.getElementById("locate").innerHTML=gl_text;
	document.locate.submit();
}
/***** 位置情報が取得できない場合 *****/
function errorCallback(error) {
	var err_msg = "";
	switch(error.code)
	{
	case 1:
		err_msg = "位置情報の利用が許可されていません";
		break;
	case 2:
		err_msg = "デバイスの位置が判定できません";
		break;
	case 3:
		err_msg = "タイムアウトしました";
		break;
	}
	document.getElementById("show_result").innerHTML = err_msg;
	//デバッグ用→　document.getElementById("show_result").innerHTML = error.message;
}

//window.onload =jump;
function jump(){
	document.locate.submit();
};
</script>
<title>位置情報から路線を取得するテスト。</title>
</head>
<body>
<form name="locate" action="./map.php" method="post">
<div id="locate"></div>
</form>


<p>あなたの現在位置</p>
<div id="show_result"></div>
</body> 
</html>
