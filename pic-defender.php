<?php

/*
Plugin Name:Pic Defender
Plugin URI: http://www.forexp.net/wordpress-plugins/plugins/pic-defender/
Description: Defends your pictures, images from source stealing in softway 
Version: 1.0.1
Author: jwall
Author URI: http://www.forexp.net
*/				

class jwall_pic_defender {
	static $plugin_url = "/wp-content/plugins/pic-defender/";
	static $syntax = "/\[PDef(.*?)\](.*?)\[\/PDef]/";
	
	// Encrypting
	function encrypt($string, $key) {
    $enc=mcrypt_cbc (MCRYPT_TripleDES, $key, $string, MCRYPT_ENCRYPT);
	  return base64_encode($enc);
	}

	// Decrypting
	function decrypt($string, $key) {
    $string = trim(base64_decode($string));
    $dec = mcrypt_cbc (MCRYPT_TripleDES, $key, $string, MCRYPT_DECRYPT);
  	return $dec;
	}

	function str_makerand ($minlength = 10, $maxlength = 15, $useupper = true, $usespecial = false, $usenumbers = true) {
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}	

  function add_menu() {
		add_submenu_page('options-general.php','Pic Defender Settings','Pic Defender Settings',8,__FILE__,array('jwall_pic_defender','settings'));
  }
  
  function settings() {

	echo '<div class="wrap"><h2>Pic Defender Setting</h2></div>';
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    // Save settings
        $sfetch = is_null($_POST['fetch'])?"NO":"YES";
		    if (!get_option('pic-defender-isfetch')) 
					add_option('pic-defender-isfetch',$sfetch);					
		    else
					update_option('pic-defender-isfetch',$sfetch);
					
		    if (!get_option('pic-defender-allow-domains')) 
					add_option('pic-defender-allow-domains',$_POST['domains']);					
		    else
					update_option('pic-defender-allow-domains',$_POST['domains']);	
					
		    echo '<div id="message" class="updated fade">Settings saved!</div>';  	
  }
  	$alldomains = get_option('pic-defender-allow-domains');
  	$isfetch = get_option('pic-defender-isfetch');
  	$checked = ($isfetch=="YES")? "checked=checked": "";
 
?>
    <form action="<?php echo $_SERVER['REQUEST_URI']?>" method="POST">
		Allow these domains: <input type="text" name="domains" value="<?php echo $alldomains?>"style="width:50em">
		<div>Access from these domains (or subdomain) are allowed, you can enter multiple domain separate by a comma.</div>		
		<div>Exp: forexp.net, vysajp.org</div>				
		<br/>
		<input type="checkbox" value="1" name="fetch" <?php echo $checked; ?> >&nbsp;Fetch through this server
		<div style="color:red">Checking this checked box HEAVILY damages your bandwidth, however making url of your pics disappear completely. </div>	
		<br/>
		<input type="submit" value="Save Settings"/>
    </form><br />

<?php
    }

	public function plugin_callback($matches) {
 
 		$key = self::str_makerand();
	  //$args = self::encrypt($matches[1].'!#!',$key);
	  if ($matches[2]=="") $eurl = ""; else $eurl = self::encrypt($matches[2].'!#!',$key);	  
	  
	  $source='"'.self::$plugin_url."parse.php?rand=".urlencode($key).','.urlencode($eurl).'"';
	  
	  $ret= "<iframe src=$source"." $matches[1] ".'"border="none" scrolling="no" frameborder="0" margin="0" padding="0">Your browser doesnot support iframe</iframe>'; 
	  return $ret;  
	  
	  
    return $ret;
	}

	function dofilter($content) {
		$output = preg_replace_callback(self::$syntax, array('self','plugin_callback'), $content);
   	return ($output);
	}	

}

// required filters
add_filter('the_content', array('jwall_pic_defender','dofilter'));
//add_filter('comment_text', 'sourceDefend_plugin');
add_action('admin_menu',array('jwall_pic_defender','add_menu'));
//add_action('plugins_loaded',array('jwall_pic_defender','load'));


?>
