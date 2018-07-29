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

// TODO:お天気取得API
$weather = "";
$urlContents = file_get_contents("http://api.openweathermap.org/data/2.5/weather?q=Tokyo&appid=".$weatherToken);
$weatherArray = json_decode($urlContents, true);    //連想配列の場合は第2引数へtrueを指定
//print_r($weatherArray);
$weather = $weatherArray['weather'][0]['main'];
$tempMax = $weatherArray['main']['temp_max'] - 273.15;
$tempMin = $weatherArray['main']['temp_min'] - 273.15;

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
		$weatherToJp = '晴れ';
		break;
	case 'Clouds':
		$weatherToJp = '曇り';
		break;
	default:
	  $weatherToJp = '分からないです';
		break;
}

// TODO: 返答用の文字列形成
	$date = new DateTime('now');
	$NowDateTime = $date->format('H時i分');

	$messageText = "時刻は${NowDateTime}!
今日のお空はどんな空〜?
大空お天気の時間です!

今日の都心部は${weatherToJp}!
最高気温は${tempMax}度、最低気温は${tempMin}度です!

それでは通勤通学気をつけて、行ってらっしゃ〜い";

//メッセージイベントだった場合です
//テキスト、画像、スタンプなどの場合「message」になります
//他に、follow postback beacon などがあります
$validMessage = $jsonObj->{"events"}[0]->{"message"}->{"text"};

if ($eventType == 'message') {
		//メッセージにお天気が含まれていた場合
		if(strpos($validMessage,'お天気')){
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
		}
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
