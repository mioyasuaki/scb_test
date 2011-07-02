<?

//header("Content-Type: text/html;charset=Shift_JIS"); 

// --------------------------------------------------
// 
// CSVファイルから１日ごとの質問を読み込んでツイートする
// 
// --------------------------------------------------

// TODO 改行ありでもOKなようにする

// ------------------------------ < config > 

// 質問内容を読み込む先のCSVファイルへのパス
$data = file_get_contents('http://mamasnote.jp/interface/modx/question/bot.js');
//$data = file_get_contents('http://mamasnote.jp/interface/modx/questiontoday-test.js');


// エラーの場合の通知先
$error_mail = "terada@karappo.net";

// ------------------------------
 
// ブラウザからのリクエスト用
//require_once($_SERVER['DOCUMENT_ROOT'].'/phplib/twitteroauth/twitteroauth.php');
// cronからのリクエスト用
require_once('phplib/twitteroauth/twitteroauth.php');

require_once('config.php');

// -------------------------------------------------------
// 関数

// メールを送る
// メールを送る
function sendmail(  $subject="DebugMail", 
					$comment="This is debug mail.", 
					$toadrs="terada@karappo.net", 
					$toname="terada", 
					$fradrs="info@karappo.net", 
					$frname="debug")
{
	mb_language("ja");
	mb_internal_encoding("utf-8");
	$frname0 = mb_encode_mimeheader($frname);
	$toname0 = mb_encode_mimeheader($toname);
	$sdmail0 = "$toname0 <$toadrs>";
	$mlhed = "From:\"$frname0\" <$fradrs>\r\n";
	$rslt = mb_send_mail($sdmail0,$subject,$comment,$mlhed);
}
/*
function sendmail($toadrs, $toname, $fradrs, $frname, $subject, $comment)
{
	mb_language("ja");
	mb_internal_encoding("sjis");
	$frname0 = mb_encode_mimeheader($frname);
	$toname0 = mb_encode_mimeheader($toname);
	$sdmail0 = "$toname0 <$toadrs>";
	$mlhed = "From:\"$frname0\" <$fradrs>\r\n";
	$rslt = mb_send_mail($sdmail0,$subject,$comment,$mlhed);
}
*/
// -------------------------------------------------------

function tweet_question()
{
	global $data;
	global $error_mail;
	
	$json_data = json_decode($data);		// jsonデータ
	
	/*
	switch(json_last_error())
	{
		case JSON_ERROR_DEPTH:
			echo ' - Maximum stack depth exceeded';
		break;
		case JSON_ERROR_CTRL_CHAR:
			echo ' - Unexpected control character found';
		break;
		case JSON_ERROR_SYNTAX:
			echo ' - Syntax error, malformed JSON';
		break;
		case JSON_ERROR_NONE:
			echo ' - No errors';
		break;
	}
	
	echo PHP_EOL;
	*/
	
	
	$today_str = date("Y/m/d");				// 今日の日付を「2010/09/01」形式で
	$q_data = $json_data->{$today_str};	// 質問文
	
	
	// 改行処理
	//$question = str_replace("\\n", "\n", $question);
	//$question = str_replace("'", "\'", $question);
	
	$text = $q_data->{"text"};
	$text = str_replace("\n", "<br />", $text );
	
	//echo $text."<br />";
	//var_dump($q_data);
	
	
	// デバッグ用　必要なくなったらここのブロックをコメントアウト
	// ------------------------------------------------------------
	/*
	$toadrs = $error_mail;
	$toname = "Mama's Note 管理者";
	$fradrs = "terada@karappo.net";
	$frname = "Mama's Note 今日の質問 Bot";
	$subject = "デバッグ";
	$comment = $today_str."  ".$question;
	sendmail($toadrs, $toname, $fradrs, $frname, $subject, $comment);
	*/
	// ------------------------------------------------------------
	
	
	
	if($text != '')
	{
		// 質問文が見つかったらツイートする
		
		tweet($text);
		
		$toadrs = $error_mail;
		$toname = "Mama's Note 管理者";
		$fradrs = "terada@karappo.net";
		$frname = "Mama's Note 今日の質問 Bot";
		$subject = "【母子手帳】質問を投稿しました";
		$comment = $today_str.'分の質問を投稿しました。\n'+$text;
		sendmail($subject,$comment);
	}
	else
	{
		// 質問文が見つからなかったので、エラーメールを送る
		$toadrs = $error_mail;
		$toname = "Mama's Note 管理者";
		$fradrs = "terada@karappo.net";
		$frname = "Mama's Note 今日の質問 Bot";
		$subject = "【母子手帳】エラーのお知らせ";
		$comment = $today_str.'分の質問が見つからなかったのでツイートできませんでした。';
		sendmail($subject,$comment);
	}
}

function tweet_setsumei()
{
	// 説明の本文はmodxから読込
	tweet(file_get_contents("http://mamasnote.jp/interface/modx/question/description.txt"));
}


function tweet($text)
{
	//　グローバル変数を読み込む
	global $access_token;
	
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	
	$text = mb_convert_encoding($text , "UTF-8", "auto");
	
	// 途中１０個はさまないと同じ内容のツイートがはじかれてしまう問題の対策
	// エラーが帰ってきたら括弧と記号の部分を入れ替えてもう一度試みる
	// プレースホルダー: {left-kakko} {right-kakko} {kigou}
	$leftKakkos 	= array("【","〔","《","『","“","〈");
	$rightKakkos 	= array("】","〕","》","』","”","〉");
	$kigous			= array("＊","※","★","☆","◎","＃");
	$index=0;
	$content='';
	$var_text='';
	do 
	{
		$var_text = str_replace('{left-kakko}',$leftKakkos[$index], $text);
		$var_text = str_replace('{right-kakko}',$rightKakkos[$index], $var_text);
		$var_text = str_replace('{kigou}',$kigous[$index], $var_text);
		$content = $connection->post('statuses/update', array('status' => $var_text));
		$index++;
	} 
	while (($content->error == "Status is a duplicate.")&&($index<count($leftKakkos)));
}

?>