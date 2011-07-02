<?php
/*

よく使う便利な関数

*/


// Return size in Mb
function GetRealSize($file) {
	clearstatcache();
	$INT = 4294967295;//2147483647+2147483647+1;
	$size = filesize($file);
	$fp = fopen($file, 'r');
	fseek($fp, 0, SEEK_END);
	if (ftell($fp)==0) $size += $INT;
	fclose($file);
	if ($size<0) $size += $INT;
	return ceil($size/1024/1024);
}

// バイト数を渡すと小数点$precision位までで、丁度よい表記に変換してくれる関数
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
  
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
  
    $bytes /= pow(1024, $pow);
  
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/***************************************************************************************//**
 * パスから絶対URLを作成
 *
 * @param string $path パス
 * @param int $default_port デフォルトのポート（そのポートである場合にはURLに含めない）
 * @return string URL
 *///****************************************************************************************
function path_to_url($path, $default_port = 80){
	//ドキュメントルートのパスとURLの作成
	$document_root_url = $_SERVER['SCRIPT_NAME'];
	$document_root_path = $_SERVER['SCRIPT_FILENAME'];
	while(basename($document_root_url) === basename($document_root_path)){
		$document_root_url = dirname($document_root_url);
		$document_root_path = dirname($document_root_path);
	}
	if($document_root_path === '/')  $document_root_path = '';
	if($document_root_url === '/') $document_root_url = '';

	$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off')? 'https': 'http';
	$port = ($_SERVER['SERVER_PORT'] && $_SERVER['SERVER_PORT'] != $default_port)? ':'.$_SERVER['SERVER_PORT']: '';
	$document_root_url = $protocol.'://'.$_SERVER['SERVER_NAME'].$port.$document_root_url;

	//絶対パスの取得 (realpath関数ではファイルが存在しない場合や、シンボリックリンクである場合にうまくいかない)
	$absolute_path = realpath($path);
	if(!$absolute_path)
		return false;
	if(substr($absolute_path, -1) !== '/' && substr($path, -1) === '/')
		$absolute_path .= '/';

	//パスを置換して返す
	$url = str_replace($document_root_path, $document_root_url, $absolute_path);
	if($absolute_path === $url)
		return false;
	return $url;
}

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