<?php
date_default_timezone_set('Asia/Tokyo');

//チャンネルアクセストークン
$channelAccessToken = getenv('channelAccessToken');

//ユーザーからのメッセージ取得
$inputData = file_get_contents('php://input');

//受信したJSON文字列をデコードします
$jsonObj = json_decode($inputData);

//Webhook Eventのタイプを取得
$eventType = $jsonObj->{"events"}[0]->{"type"};

$weatherToken = getenv('weatherApiToken');

//OpenWhetherMapからお天気情報を取得します
$weather = "";
$urlContents = file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Tokyo&appid=".$weatherToken);
$weatherArray = json_decode($urlContents, true);    //連想配列の場合は第2引数へtrueを指定
//print_r($weatherArray);
$weather = $weatherArray['weather'][0]['main'];
$tempMax = $weatherArray['main']['temp_max'] - 273.15;
$tempMin = $weatherArray['main']['temp_min'] - 273.15;

// お天気の判断
switch ($weather) {
	case 'Thunderstorm':
		$code = '10003A';
		$weatherToJp = "雷";
		break;
	case 'Drizzle':
		$weatherToJp = "小雨";
		break;
	case 'Rain':
		$code = '1000AA';
		$weatherToJp = '雨';
		break;
	case 'Snow':
		$code = '1000AB';
		$weatherToJp = '雪';
		break;
	case 'Clear':
		$code = '1000A9';
		$weatherToJp = '晴れ';
		break;
	case 'Fog':
	case 'Clouds':
		$code = '1000AC';
		$weatherToJp = "曇り";
		break;
	default:
	  $weatherToJp = '分からないです';
		break;
}

// 16進エンコードされたバイナリ文字列をデコード
$bin = hex2bin(str_repeat('0', 8 - strlen($code)) . $code);
// UTF8へエンコード
$emoticon =  mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');

switch ($eventType) {
	//友達追加時
	case 'follow':
	$messageText = "友達になってくれてありがとう！
こちらは大空お天気です！

「お天気」と呼びかけてくれると今のお天気をお知らせします！";
		break;

	//お天気の返事
	case 'message':
	//メッセージ取得
	$validMessage = $jsonObj->{"events"}[0]->{"message"}->{"text"};

	//メッセージにお天気が含まれていた場合
	if(strpos($validMessage,"お天気") !== false){
		// 返答用の文字列形成
		$date = new DateTime('now');
		$NowDateTime = $date->format('H時i分');

		$messageText = "時刻は${NowDateTime}!
	今日のお空はどんな空〜?
	大空お天気の時間です!

	今日の都心部は${weatherToJp}${emoticon}!
	最高気温は${tempMax}度、最低気温は${tempMin}度です!

	それでは通勤通学気をつけて、行ってらっしゃ〜い";
	}
		break;

	default:
		exit("イベントが対応していません");
		break;

}

//メッセージタイプ取得
//ここで、受信したメッセージがテキストか画像かなどを判別できます
$messageType = $jsonObj->{"events"}[0]->{"message"}->{"type"};

//ReplyToken取得
//受信したイベントに対して返信を行うために必要になります
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

$response_format_text = [
	"type" => "text",
	"text" => $messageText
];

$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format_text]
];

//後は、Reply message用のURLに対して HTTP requestを行うのみです
$ch = curl_init("https://api.line.me/v2/bot/message/reply");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $channelAccessToken
    ));

$result = curl_exec($ch);
curl_close($ch);

?>
