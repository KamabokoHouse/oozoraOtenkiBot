<?php

//チャンネルアクセストークン
$channelAccessToken = 'チャンネルアクセストークン';

//ユーザーからのメッセージ取得
$inputData = file_get_contents('php://input');

//受信したJSON文字列をデコードします
$jsonObj = json_decode($inputData);

//Webhook Eventのタイプを取得
$eventType = $jsonObj->{"events"}[0]->{"type"};

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

	//メッセージタイプがtextの場合の処理
	if ($messageType == 'text') {

		//メッセージテキスト取得
		//ここで、相手から送られてきたメッセージそのものを取得できます
		$messageText = $jsonObj->{"events"}[0]->{"message"}->{"text"};

		//返答準備1
		//単純にテキストで返す場合です
		//よくあるオウム返しでは、text に $messageText を入れればOKです
		$response_format_text = [
			"type" => "text",
			"text" => $messageText
		];

		//返答準備2
		//先程取得したトークンとともに、返答する準備です
		$post_data = [
			"replyToken" => $replyToken,
			"messages" => [$response_format_text]
		];
	}
	//上記以外のメッセージタイプ
	//画像やスタンプなどの場合です
	else {

		//返答準備1
		$response_format_text = [
			"type" => "text",
			"text" => "メッセージ以外は受け取りません！"
		];

		//返答準備2
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
