<?php 
require_once('cron/iplusdDB.php');

$num = 27; // 表示件数


$db = connectDB();

$newer = $_GET['newer'];
$older = $_GET['older'];
$user = $_GET['user'];
$contain = $_GET['contain'];

/*
$user_filter = '';
if(isset($user)) $user_filter = "AND from_user = '".$user."'";

if( (empty($newer) && empty($older)) || (isset($newer) && isset($older)) )
{
	$startID = getNewestIDinDB($db);
	
	$user_filter = '';
	if(isset($user)) $user_filter = "WHERE from_user = '".$user."'";
	
	$result = getTweets($db,$num,$user_filter,"DESC");
}
else if(isset($newer))
{
	$result = getTweets($db,$num,"WHERE id > $newer ".$user_filter,"ASC");
}
else if(isset($older))
{
	$result = getTweets($db,$num,"WHERE id < $older AND text LIKE '%#iplusd_saigai%'".$user_filter,"DESC");
}
else
{
	die('<span style="color:red;">wrong query...</span><br />');
}
*/
$condition = '';
$order='DESC';


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
//echo "--------------".$condition;
$result = getTweets($db,$num,$condition,$order);

// $condition文を連結する
function connectCondition($original_str,$add_str)
{
	if($original_str==='') 	return "WHERE ".$add_str;
	else					return $original_str." AND ".$add_str;
}

$newestID = 0;
$oldestID = 0;

//echo '<span style="display:none;">debug:'.$newestID.'</span>';
// 一旦配列に格納
$array = array();
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) 
{
	array_push($array,$row);
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
		$text = ereg_replace("(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>" , $row['text']);
		$user = $row['from_user'];
		$image = '<img src="'.$row['profile_image_url'].'" width="48" height="48" alt="'.$user.'" />';
		
		/*
		echo '<li class="clf" id="'.$id.'">';
		echo '<div class="t_thumb"><a href="http://twitter.com/'.$user.'" target="_blank">'.$image.'</a></div>';
		echo '<div class="t_content">';
		echo '<p class="tweet"><a class="t_account" href="http://twitter.com/'.$user.'" target="_blank">'.$user.'</a> '.$text.'</p>';
		echo '<p class="tweet_caption"><a rel="bookmark" href="http://twitter.com/'.$user.'/status/'.$id.'" target="_blank"><span class="time" data="{time:'.$created_at.'}">'.$created_at.'</span></a> via '.htmlspecialchars_decode($source).'</p>';
		echo '</div>';
		echo '</li>';
		*/
		
		echo '<li class="tweet_unit clf" id="'.$id.'">';
		echo '<div class="t_thumb"><a href="http://twitter.com/'.$user.'" target="_blank">'.$image.'</a></div>';
		echo '<div class="t_texts">';
		echo '<a class="t_account" href="http://twitter.com/'.$user.'" target="_blank">'.$user.'</a>　<span class="time" data="{time'.$created_at.'}">'.$created_at.'</span>';
		echo '<p class="tweet">'.$text.'</p>';
		echo '</div>';
		echo '</li>';
		
		
		
		
		if($newestID==0 || ($newestID+0 <= $id+0)) $newestID = $id;
		if($oldestID==0 || ($id+0 <= $oldestID+0)) $oldestID = $id;
	}
}

disconnectDB($db);

?>