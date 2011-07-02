<?php

/*

表示

*/


require_once 'myDB.php';

// DBから読み込んで表示
function showTweets()
{
	$db = connectDB();
	
	//$result = getTwitCache($db,"WHERE text LIKE '%http://%'");
	$result = getTwitCache($db);
	
	// 各行のデータを順に取得し行がなくなるまで続ける
	while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) 
	{
		// 時刻表示を日本時間に
		date_default_timezone_set('Asia/Tokyo');
		$created_at = date("Y-m-d H:i:s" , strtotime($row['created_at']));
		
		//URL置換
		$text = ereg_replace("(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>" , $row['text']);
		$image = '<img src="'.$row['profile_image_url'].'" />';
		
		echo $row['id']."<br />";
		echo $created_at."<br />";
		echo $row['from_user']."<br />";
		echo $image."<br />";
		echo $text."<br />";
		echo "<br />";
		
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>[issue plus design] cache test</title>
</head>

<body>

<?php
echo showTweets();
?>

</body>
</html>