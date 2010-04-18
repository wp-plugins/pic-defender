<?php
/********************
Parse file by Jwall
********************/

//if access directly
if ($_SERVER['HTTP_REFERER'] == "")  die ('You cannot access this file directly');

	function str_makerand ($minlength = 10, $maxlength = 15, $useupper = true, $usespecial = false, $usenumbers = true) {
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; 
		// Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}	
	
	function encrypt($string, $key) {
    $enc=mcrypt_cbc (MCRYPT_TripleDES, $key, $string, MCRYPT_ENCRYPT);
	  return base64_encode($enc);
	}

//Decrypting
function decrypt($string,$key) {
    $string = trim(base64_decode($string));
    $dec = mcrypt_cbc (MCRYPT_TripleDES, $key, $string, MCRYPT_DECRYPT);
  	return $dec;
}

//Get encode
$args=explode(",",$_GET["rand"]);
$urls = explode("!#!",decrypt($args[1],$args[0]));
$url = $urls[0];

require("../../../wp-config.php");
mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
@mysql_select_db(DB_NAME) or die( "Unable to select database");
$query="SELECT option_value  FROM `wp_options` WHERE `option_name` = 'pic-defender-allow-domains' LIMIT 1";
$result=mysql_query($query);
$row = mysql_fetch_object($result);
$allow_domains = '';
$allow_domains = $row->option_value;

$query="SELECT option_value  FROM `wp_options` WHERE `option_name` = 'siteurl' LIMIT 1";
$result=mysql_query($query);
$row = mysql_fetch_object($result);
$siteurl = '';
$siteurl = $row->option_value;

$query="SELECT option_value  FROM `wp_options` WHERE `option_name` = 'admin_email' LIMIT 1";
$result=mysql_query($query);
$row = mysql_fetch_object($result);
$admin_email = '';
$admin_email = $row->option_value;

$query="SELECT option_value  FROM `wp_options` WHERE `option_name` = 'pic-defender-isfetch' LIMIT 1";
$result=mysql_query($query);
$row = mysql_fetch_object($result);
$fetch = $row->option_value;
mysql_close();

$allow_array = explode (",", $allow_domains);
//var_dump($allow_array);
$this_domain = "http://".$_SERVER['SERVER_NAME'];
$allowviewpic = false;
$tempval = $_SERVER["HTTP_REFERER"];

foreach ($allow_array as $aadomain)
{
if (strpos($this_domain,trim($aadomain))>0) 
{ $allowviewpic=true; break;}
}


//If the server is allow:
if ($allowviewpic==true)
{
if (is_null($_GET['obj']))
{
if ($fetch=="YES") {
 		$key = str_makerand();
	  $eurl = encrypt($url.'!#!',$key);	  
	 	$url="/wp-content/plugins/pic-defender/parse.php?obj=show&amp;rand=".urlencode($key).','.urlencode($eurl);
}

$output = '<div style="position:absolute; width:100%; height:100%; margin:0px; padding:0px; left:0px; right:0px;z-index:1;overflow:hidden"><img src="'.$url.'" width="100%" height="100%" oncontextmenu="return false"></div>';
$output.= '<div style="z-index:2; position:absolute; margin:0px; padding:0px; height:100%; left:0px; right:0px; width:100%; overflow:hidden;"><img src="/wp-content/plugins/pic-defender/tran.gif" width="100%" height="100%"></div>';

echo $output;
}
else 
{
header("Content-type:image/jpeg");
readfile("$url");
}
}
//if not allowserver
else 
{
$output = "This server is not allow to view this pictures. ";
$output.= 'Please visit <a href="'.$siteurl.'">owner website</a> to view it. ';
$output.= 'Or contact '.$admin_email.' to receive permission';
echo "document.write('".$output."');";
}
?>
