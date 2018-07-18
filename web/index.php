<?php

//チャンネルアクセストークン
$channelAccessToken = '64OkySA3W34Ej2vPE7j1hdbYldQvnhSBDzfoXoB+B9iGi+xSqsGdBz81tLQinTM2Fg8xbTkUMTg1jnaiqKKo1ez0VdTZTbITqxSQahZEteOzvStBRgakAM7ZTk+tNipbCQQWln2+6x3txgqWzg+mlgdB04t89/1O/w1cDnyilFU=';

//ユーザーからのメッセージ取得
$inputData = file_get_contents('php://input');

//受信したJSON文字列をデコードします
$jsonObj = json_decode($inputData);

//Webhook Eventのタイプを取得
$eventType = $jsonObj->{"events"}[0]->{"type"};

// TODO:お天気取得API
$weather = "";
$urlContents = file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Tokyo"."&appid=3d60d8b0905b2e9436feb5f451d25319");
$weatherArray = json_decode($urlContents, true);    //連想配列の場合は第2引数へtrueを指定
//print_r($weatherArray);
$weather = $weatherArray['weather'][0]['main'];
$tempMax = $weatherArray['weather'][0]['temp_max'] - 273.15;
$tempMin = $weatherArray['weather'][0]['temp_min'] - 273.15;

switch ($weather) {
	case 'Thunderstorm':
		$weatherToJp = "雷";
		break;
	case 'Drizzle':
		$weatherToJp = "小雨";
		break;
	case 'Rain':
		$weatherToJp = '雨';
		break;
	case 'Snow':
		$weatherToJp = '雪';
		break;
	case 'Clear':
		$weatherToJp = '晴れ'
		break;
	case 'Clouds':
		$weatherToJp = '晴れ'
		break;
	default:
	  $weatherToJp = '分からないです'
		break;
}

// TODO: 返答用の文字列形成
	$date = new DateTime('now');
	$NowDateTime = date->format('H時i分')

	//$messageText = '時刻は$NowDateTime!今日のお空はどんな空〜? 大空お天気の時間です! 今日の都心部は$weatherToJp! 最高気温は$tempMax度、最低気温は$tempMin度です! それでは通勤通学気をつけて、行ってらっしゃ〜い';

	$messageText == 'temp'
//メッセージイベントだった場合です
//テキスト、画像、スタンプなどの場合「message」になります
//他に、follow postback beacon などがあります
if ($eventType == 'message') {

	//メッセージタイプ取得
	//ここで、受信したメッセージがテキストか画像かなどを判別できます
	$messageType = $jsonObj->{"events"}[0]->{"message"}->{"type"};

	//ReplyToken取得
	//受信したイベントに対して返信を行うために必要になります
	$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

	//メッセージにお天気が含まれていた場合
		$response_format_text = [
			"type" => "text",
			"text" => $messageText

		$post_data = [
			"replyToken" => $replyToken,
			"messages" => [$response_format_text]
		];
	}

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
