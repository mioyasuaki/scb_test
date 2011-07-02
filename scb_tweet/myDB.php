<?php


//////////////////////////
//						//
// DATABESE FUNCTIONS	//
//						//
//////////////////////////


//////////////////////////
// 
// Configuration

define('USERNAME',    	'_supercoolbiz_wp');
define('PASSWORD',    	'sakurasaku');
define('HOST',    		'mysql91.heteml.jp');
define('DATABASE',    	'_supercoolbiz_wp');

define('TWEET_TABLE',    'TweetTable');

//////////////////////////


/*
require_once('DB.php');
require_once('MDB2.php');
*/








//////////////////////////
// 
// Database Common Func

// DB接続
function connectDB(){
	
	$username = USERNAME;
	$password = PASSWORD;
	$host     = HOST;
	$database = DATABASE;
	
	/*
	// PEARを使う場合
$db = MDB2::factory("mysql://$username:$password@$host/$database");
	
	
	if (DB::isError( $db )) {
		die($db->getMessage());
	}
	
	// phpMyAdmin内でのみ文字化けしてしまう対策
	$db->query("SET NAMES utf8");

	return $db;
*/
	
	//MySQL に接続する。
	if( !$db = mysql_connect( $host, $username, $password) ){
		print "MYSQL への接続に失敗しました。";
		exit;
	}
	
	//使用するＤＢを選択する。
	mysql_select_db( $database, $db );
	
	mysql_query("SET NAMES utf8");

	return $db;
	
	
}

/*

// DB切断
function disconnectDB($db)
{
	$db->disconnect();
}
*/

function disconnectDB($db)
{
	mysql_close($db);
}







//////////////////////////
// 
// Database Set Func



// Tweetの情報をDBへ登録
function setTwitCache($db, $value)
{
	// [1] 受け取ったデータからDB登録用のデータを抽出&形成して変数に格納
	echo("hogehogeho</br>");
	echo($value);
	// from API
	$id							= $value["id_str"];// edited by mio 2011.1.6		idからid_strに変更
	$created_at					= $value["created_at"];
	$from_user 					= $value["from_user"];
	$from_user_id				= $value["from_user_id"];
	$profile_image_url			= $value["profile_image_url"];
	$text						= $value["text"];
	$to_user_id					= $value["to_user_id"];
	$to_user					= $value["to_user"];
	$iso_language_code 			= $value["iso_language_code"];
	$source						= $value["source"];
	$in_reply_to_status_id 		= $value["in_reply_to_status_id"];	// searchAPIに含まれない
	$in_reply_to_user_id 		= $value["in_reply_to_user_id"];		// searchAPIに含まれない
	$metadata_result_type		= $value["metadata"]["result_type"];	
	$metadata_recent_retweets	= $value["metadata"]["recent_retweets"];
	$geo_coordinates_ido		= $value["geo"]["coordinates"][0];
	$geo_coordinates_keido		= $value["geo"]["coordinates"][1];
	
	
	// others
	
	// created_atを日付け時間型に変換
	date_default_timezone_set('GMT'); // 時刻表示はtwitterに合わせてGMT時間に
	$created_at_date = date("Y-m-d H:i:s" , strtotime($created_at));
	
	
	
	// どの質問IDか調べる（複数ある場合は最新のものにする）
	$qids = getQIDs($text);
	rsort($qids);
	$qid = $qids[0];
	//echo "~".$qid."~<br />";
	// 登録データ名の対応表 ---------------------------
	
	$fieldnames = array(
					//	 (DBフィールド名,				[1]で作った変数名)
					
					// from API
					array('id',							$id),
					array('created_at',					$created_at),
					array('from_user',					$from_user),
					array('from_user_id',				$from_user_id),
					array('profile_image_url',			$profile_image_url),
					array('text',						$text),
					//array('to_user_id',					$to_user_id),
					//array('to_user',					$to_user),
					array('iso_language_code',			$iso_language_code),
					array('source',						$source),
					//array('in_reply_to_status_id',		$in_reply_to_status_id),
					//array('in_reply_to_user_id',		$in_reply_to_user_id),
					//array('metadata_result_type',		$metadata_result_type),
					//array('metadata_recent_retweets',	$metadata_recent_retweets),
					// others
					array('geo_coordinates_ido',$geo_coordinates_ido),
					array('geo_coordinates_keido',$geo_coordinates_keido),
					array('created_at_date',			$created_at_date),
					array('article_id',						$qid)
					
					);
	
	// SQL文の作成 ------------------------------------
	
	$sql = replaceSQL( TWEET_TABLE, $fieldnames);
	
	//echo $sql."<br /><br />";
	
	// -------------------------------------------------
	
	/*
$result = $db->query($sql);
	
	if (DB::isError( $result )) 
	{
		die($result->getMessage());
	}
*/
	
	$result = mysql_query($sql,$db)or die("error1".mysql_error());
	
	//sendMail("test",var_dump(DB::isError( $result )));
	//
	//	status_updateに値がなかったらcreated_at_dateの値を入れておく
	//
	
	// SQL文の作成 ------------------------------------
/*
	
	$sql = "UPDATE ".TWEET_TABLE." 
			SET status_update=created_at_date 
			WHERE status_update = '0000-00-00 00:00:00';";
	
	// -------------------------------------------------
	
	$result = mysql_query($sql,$db)or die("error2".mysql_error());
*/
	
/*
	if (DB::isError( $result )) 
	{
		die($result->getMessage());
	}
*/
	
}


// added by mio

function replaceSQL($table_name, $fieldnames){
	
	$sql = "REPLACE INTO ".$table_name."(";
										 
	for($i=0;$i<count($fieldnames);$i++)
	{
		if($i!=(count($fieldnames)-1))
		{
			$sql .= $fieldnames[$i][0].",";
		}
		else
		{
			$sql .= $fieldnames[$i][0].") VALUES (";
		}
	}
	for($i=0;$i<count($fieldnames);$i++)
	{
		if($i!=(count($fieldnames)-1))
		{
			// 'などをエスケープ
			$sql .= "'".mysql_real_escape_string($fieldnames[$i][1])."',";
		}
		else
		{
			// 'などをエスケープ
			$sql .= "'".mysql_real_escape_string($fieldnames[$i][1])."');";
		}
	}
	
	return $sql;
}




// 文字列の中から「#MAMAsQ01」のようなタグを取得
function getQIDs($_text)
{
	$sorce_text = $_text;
	$results = array();
	while( mb_ereg("#MAMAsQ([[:digit:]]{2})",$sorce_text, $matches) )
	{
		array_push($results,$matches[1]);
		$sorce_text = str_replace($matches[0],"", $sorce_text);
	}
	return $results;
}





//////////////////////////
// 
// Debug Func


// メールを送る
function sendMail(  $subject="DebugMail", 
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

?>