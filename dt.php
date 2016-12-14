<?php
/**
 * [API] FOTA.
 * 
 * FOTA(Farmware update On The Air)
 * Return FOTA settings provided for the account as JSON.
 * 
 * Requires $_GET['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */

ini_set( 'display_errors', 0 ); // エラー出力しない場合
#ini_set( 'display_errors', 1 ); // エラー出力する場合
require_once("vendor/autoload.php"); 

function mtime(){
	return substr(explode(".", (microtime(true) . ""))[1], 0, 3);
}
//echo microtime(true);exit;


#require_once("Log.php");
$logfilename = "dt.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.mtime().'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;
$logfile->log('['.__LINE__.']'.mtime().'*** STARTED 2***');
$result = "";
if(isset($_POST['sec'])&&isset($_POST['msec'])){
  $correction_time = $_POST['sec'] + ($_POST['msec']/1000); # Web から貰った補正時刻
  $original_diff = microtime(true) - $correction_time;
	$command ="sudo ./setdate_milli ".$_POST['sec']." ".$_POST['msec'];
	$logfile->log('['.__LINE__.']'.mtime().'command = '.$command);
	$result = `$command`;
  $after_diff = microtime(true) - $correction_time;
	$logfile->log('['.__LINE__.']'.mtime().'result = '.$result);

	$json['result']=$result;
  $json['original_diff']=$original_diff;
  $json['after_diff']=$after_diff;

	header("Access-Control-Allow-Origin: *");
	header('Content-Type: application/json');
	$json_str = json_encode( $json );
	error_log('$json_str = '.$json_str);
	echo $json_str;

	exit;

}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>データアップロード</title>
  <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script>
    var now = new Date().getTime();
    var sec = Math.floor(now / 1000);
    var msec = now % 1000;

    $.ajax({
        type: "POST",
        url: "dt.php",
        data: {sec: sec, msec: msec},
        dataType: "json",
      })
     .then(
       function(data, dataType){
         console.log('OK : ');
         console.log('original_diff = ' + data.original_diff)
         console.log('after_diff = ' + data.after_diff)
       },
       function(XMLHttpRequest, textStatus, errorThrown){
         console.log('Error : ' + errorThrown);
     });

  </script>
</head>
<body>
</body>
</html>
