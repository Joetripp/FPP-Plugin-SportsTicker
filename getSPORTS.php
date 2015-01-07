#!/usr/bin/php
<?
error_reporting(0);

$pluginName ="SportsTicker";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once "SPORTS.inc.php";


$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = "/tmp/FPP.MessageQueue";

if(file_exists($messageQueuePluginPath."functions.inc.php"))
	{
		include $messageQueuePluginPath."functions.inc.php";
		$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

	} else {
		logEntry("Message Queue Plugin not installed, some features will be disabled");
	}	


//if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
//	$queueMessages = getNewPluginMessages("SMS");
//	print_r($queueMessages);
//} else {
//	logEntry("MessageQueue plugin is not enabled/installed");
//}	


$SPORTS=ReadSettingFromFile("SPORTS",$pluginName);

$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));

//echo "ENABLED: ".$ENABLED."\n";
if($ENABLED != "on" && $ENABLED != "1") {
	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");

	exit(0);
}

$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));

$SPORTS_READ = explode(",",$SPORTS);

//echo "Incoming sports reading: \n";
//print_r($SPORTS_READ);
//print_r($SPORTS_DATA_ARRAY);

$messageText="";


for($i=0;$i<=count($SPORTS_READ)-1;$i++) {
	//echo "Retrieving data for: ".$SPORTS_READ[$i]."\n";
	
	if( search_in_array($SPORTS_READ[$i],$SPORTS_DATA_ARRAY) > 0) {
		
		//echo $SPORTS_READ[$i]. " is in Sports data array\n";
			
		//fetch the information
		$sportsScores = file_get_contents($SPORTS_DATA_ARRAY[$i][1]);
		
		


	$new = str_replace('&',"|",$sportsScores);
	//$new = str_replace('&',"|",$output);

	$stats = explode('&',$sportsScores);

//print_r($stats);

	foreach($stats as $item) {

		$split = explode("=",$item);

		$left = $split[0];
		$right = (string)urldecode($split[1]);


		if(substr($left,0,10) == strtolower($SPORTS_READ[$i]."_s_left")) {

		//echo $right."<br/>";
	//echo $split[0]." --- ".$right."<br/>";

		if(substr($right,0,1) == "^") {
			$right = substr($right,1);
		}

		if(trim($right) !="") {
			$messageText .= $right." | ";
		}
	}
	}
	
	//there gets some ^ in the output.. erase them!
	$messageText = preg_replace('/\^/', '', $messageText);
	
	if(trim($messageText) == "" ) {
		$messageLine = $SPORTS_READ[$i]." - No Scores Availble";
	} else {
	
		$messageLine = $SPORTS_READ[$i]. " ".$SEPARATOR." ".$messageText;
	}
	addNewMessage($messageLine,$pluginName,$pluginData=$SPORTS_READ[$i]);
	$messageText="";
	$messageLine="";
	}
	
}

function search_in_array($value, $arr){

	$num = 0;
	for ($i = 0; $i < count($arr); ) {
		if($arr[$i][0] == $value) {
			$num++;
		}
		$i++;
	}
	return $num ;
}
?>
