<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Twitter Admin</title>

<link rel="stylesheet" type="text/css" href="css/reset.css" />

</head>

<body>
<?php

//print getcwd()."<br>";
//chdir("../");
//print getcwd()."<br>";

//TODO myDBは、汎用性が高いので、他に移すべき
require_once('../cron/get_tweet/myDB.php');

$db = connectDB();


$order='DESC';


$newer = $_GET['newer'];
$older = $_GET['older'];
$user = $_GET['user'];
$contain = $_GET['contain'];


if(isset($user))
{
	$condition = connectCondition($condition,"from_user = '$user'");
}
if(isset($contain))
{
	$condition = connectCondition($condition,"text LIKE '%$contain%'");
}
if(isset($newer))
{
	$condition = connectCondition($condition,"id > $newer");
	$order='ASC';
}
else if(isset($older))
{
	$condition = connectCondition($condition,"id < $older");
}

function connectCondition($original_str,$add_str)
{
	if($original_str==='') 	return "WHERE ".$add_str;
	else					return $original_str." AND ".$add_str;
}


// 2011.7.1 added by mio 外部ファイルなしで動くように
$result = mysql_query("SELECT * FROM ".TWEET_TABLE) or die("select1 ".mysql_error());

// 2011.7.1 edited by mio PEARナシで動くように
$array = array();
while ($row = mysql_fetch_array($result))
{
	array_push($array,$row);
	//var_dump( $row );
}



// 結果なし
if( count($array) <= 0 ) 
{
	echo '<div id="noresults"><span>no results...</span></div>';
	$noresults = true;
}
else
{
	// 並べ替え
	if(isset($newer)) $array = array_reverse($array);
	
	
	echo "<table>";
	
	// 表示
	for($i=0;$i<count($array);$i++)
	{
		$row = $array[$i];
		$noresults = false;
		
		// 時刻表示を日本時間に
		date_default_timezone_set('Asia/Tokyo');
		$created_at = date("Y/m/d H:i:s" , strtotime($row['created_at']));
		
		$id = $row['id'];
		$source = $row['source'];
		// 2011.7.1 mio ereg_replaceをpreg_replaceに変更。（eregは推奨されないという理由でエラーが出た@heteml）
		$text = preg_replace("/(https?|ftp)(:\/\/[[:alnum:]\+\$\;\?\.%,!#~*\/:@&=_-]+)/", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>" , $row['text']);
		$user = $row['from_user'];
		$image = '<img src="'.$row['profile_image_url'].'" width="48" height="48" alt="'.$user.'" />';
		
		
		echo '<tr id="'.$id.'">';
		echo '<td class="t_thumb"><a href="http://twitter.com/'.$user.'" target="_blank">'.$image.'</a></td>';
		echo '<td class="t_texts">';
		echo '<a class="t_account" href="http://twitter.com/'.$user.'" target="_blank">'.$user.'</a>
				<span class="time" data="{time'.$created_at.'}">'.$created_at.'</span>';
		echo '<p class="tweet">'.$text.'</p>';
		echo '</td>';
		
		// ↓ここのあたりで、管理系のボタン作成
		echo "<td>管理btn</td>";
		echo "</tr>\n\n";
		
		
		if($newestID==0 || ($newestID+0 <= $id+0)) $newestID = $id;
		if($oldestID==0 || ($id+0 <= $oldestID+0)) $oldestID = $id;
	}
	
	echo "</table>";
}

disconnectDB($db);


?>
</body>
</html>