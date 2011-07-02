<?php

require_once 'tweetQuestion.php';

// 曜日の条件（平日）
if((date("w")!=6)&&(date("w")!=0))
{
	// 時刻の条件（9:00 12:00 15:00 18:00）
	if((date("G")==8)||(date("G")==11)||(date("G")==14)||(date("G")==17))
	{
		// 分の条件（○時00分～○時05分）
		if( 52<=date("i") && date("i")<57 )
		{	
			// 実行
			//tweet_setsumei();
			tweet_question();
		}
	}
}


//実行されているか確認するメールを送信
//require_once '../phplib/teraUtil.php';
//sendmail("THis is debug mail of issu+design", "cron_exe.php is called.");
?>
