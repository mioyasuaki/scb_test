<?php

sendmail("テスト","ssss");

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

?>