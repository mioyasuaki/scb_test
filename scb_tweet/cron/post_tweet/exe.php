<?php

require_once 'tweetQuestion.php';

// �j���̏����i�����j
if((date("w")!=6)&&(date("w")!=0))
{
	// �����̏����i9:00 12:00 15:00 18:00�j
	if((date("G")==8)||(date("G")==11)||(date("G")==14)||(date("G")==17))
	{
		// ���̏����i����00���`����05���j
		if( 52<=date("i") && date("i")<57 )
		{	
			// ���s
			//tweet_setsumei();
			tweet_question();
		}
	}
}


//���s����Ă��邩�m�F���郁�[���𑗐M
//require_once '../phplib/teraUtil.php';
//sendmail("THis is debug mail of issu+design", "cron_exe.php is called.");
?>
