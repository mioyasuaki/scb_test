<?php

/*

TwitterAPIから関係するツイートを取得してDBへ登録

*/

require_once($_SERVER['DOCUMENT_ROOT'].'/phplib/twitteroauth/twitteroauth.php');	// exe.phpをブラウザがから直接たたいて実行する時
//require_once('phplib/twitteroauth/twitteroauth.php');								// cronから実行する時
require_once 'myDB.php';
require_once 'config.php';

// --------------------------------------------------------------------------

// メインの処理

// --------------------------------------------------------------------------

// for debug
$debug = false;
exe();

function exe()
{
	// debug
	//print_head();
	
	//　グローバル変数を読み込む
	global $access_token;
	
	
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	
	
	// twitter API リクエストを作成 ---------------------------------
	
	// TODO:「Queries are limited 140 URL encoded characters.」とあるので、urlの数が増えた時大丈夫か検証
	
	
	/*
	メモ
	# : %23
	@ : to%3 (@ではじまる)
	and: +
	   : %40  (@を含む)
	or : +OR+
	*/
	
	
	// ①ORで検索する条件
	/*
	$targets = array(	'from:AccessMWC',
						'to:AccessMWC',
						'@AccessMWC',
						'#accessMWC',
						'#future',
						'#MobileWish');
	*/
	$targets = array('#MobileWish');
	
	
	// ②静的ページ
	$staticURIs = array('http://access.s106.coreserver.jp/');
	
	// ③動的ページ
	// TODO:modxから動的に読み込んできて追加するURI
	$dynamicURIs = array();
	
	// ④元のURL（②＋③）
	$longURIs = array_merge($staticURIs, $dynamicURIs);
	
	// ⑤短縮URL（④をBitly化）※Bitlyでエラーが良く起こるのでその都度取得はやめる
	//$BitlyURIs 	= getShortenURIs($longURIs, 'Bitly');
	// ⑥短縮URL（④をTinyURL化）
	//$TinyURLURIs= getShortenURIs($longURIs, 'TinyURL');
	
	// 全条件のマージ（①＋④＋⑤＋⑥）
	//$conditions = doEncode(array_merge($targets, $longURIs, $BitlyURIs, $TinyURLURIs));
	$conditions = doEncode(array_merge($targets, $longURIs));
	
	
	
	// リクエスト文作成 ----------------------------------------------
	
	// ↓result_type=recentつけておかないと、2010.4から仕組みが変わるらしい
	$req = "http://search.twitter.com/search.json?result_type=recent&rpp=100&q=";
	
	
	$noConditions = true;
	
	for($i = 0; $i < sizeof($conditions); $i++)
	{
		if($conditions[$i] != '') 
		{
			//短縮URLサービスなどの不調でコンディションがない場合を考慮
			$noConditions = false;
			$req .= $conditions[$i]."+OR+";
		}
	}
	
	// さいごにくっついた「+OR+」を取る
	if(!$noConditions) $req = substr_replace($req, "", -4,strlen($req));
	
	//echo $req."<br />";
	
	$result = reqAPI($req);
	//var_dump($result);
	
	
	// データ保存 ----------------------------------------------------
	
	if(!$debug)
	{
		$db = connectDB();
		
		foreach( $result as $value )
		{
			setTwitCache($db, $value);
			
			if(! $_REQUEST["isFlash"])
			{
				// ブラウザで直接アクセスした場合は、内容を表示 （Flashからのときは、表示はしない）
				echoTweetData($value);
			}
		}
		
		if($_REQUEST["isFlash"]){
			echo "connection=success";
		}
		
		disconnectDB($db);
	}
	else
	{
		/*
		foreach( $result as $value )
		{
			echoTweetData($value);
		}
		*/
	}
}

function  echoTweetData( $value )
{
	//echo $value->id."<br />";
	echo $value->id_str."<br />";
	echo $value->created_at."<br />";
	echo $value->from_user."<br />";
	echo '<img src="'.$value->profile_image_url.'" /><br />';
	echo $value->text."<br />";
	echo "<br />";
}


// --------------------------------------------------------------------------
//
// 関数
//
// --------------------------------------------------------------------------

// 参考：http://d.hatena.ne.jp/hirokan55/20100429/p1

function reqAPI($request_str) 
{
	$ch = curl_init($request_str);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	curl_close($ch);
	$ret = array();
	if (!$res) {
		fputs(STDERR , "Failed to get response\n");
		return $ret;
	}
	$json = json_decode($res);
	if (!$json) {
		fputs(STDERR , "Failed to get json\n");
		return $ret;
	}
	//print $res;
	return $json->results;
}

function reqAPIXML($request_str) 
{
	$ch = curl_init($request_str);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	//echo $res;
	curl_close($ch);
	$ret = array();
	if (!$res) {
		fputs(STDERR , "Failed to get response\n");
		return $ret;
	}
}


// 配列の中身を全てエンコード
function doEncode($array)
{
	foreach( $array as $key=>$string )
	{
		$array[$key] = urlencode($string);
	}
	return $array;
}

// 短縮URL
function getShortenURIs($rawURIs, $serviceName)
{
	require_once('Services/ShortURL.php'); 
	
	$URIArray = array();
	
	// Bitlyはアカウントが必要
	if( $serviceName == 'Bitly' )
	{
		Services_ShortURL::setServiceOptions('Bitly', array( 
			'login'=>'iplusd', 
			'apiKey'=>'R_f9fe67070dfa38f2881423b244c143f8' 
		)); 
	}
	
	$api = Services_ShortURL::factory($serviceName);
	$shorten = "";
	foreach ($rawURIs as $uri)
	{
		array_push($URIArray, $api->shorten($uri));
	}
	
	return $URIArray;
}

// 短縮URL（１つだけ取得）
function getShortenURI($rawURI, $serviceName)
{
	require_once('Services/ShortURL.php'); 
	
	// Bitlyはアカウントが必要
	if( $serviceName == 'Bitly' )
	{
		Services_ShortURL::setServiceOptions('Bitly', array( 
			'login'=>'iplusd', 
			'apiKey'=>'R_f9fe67070dfa38f2881423b244c143f8' 
		)); 
	}
	
	$api = Services_ShortURL::factory($serviceName);
	
	$shorten = $api->shorten($rawURI);
	
	return $shorten;
}

function print_head()
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml">';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	echo '<title>デバッグ用ヘッダー</title>';
	echo '</head>';
}

?>