<?php 


$required_extentions=array("cli_server","openssl","gd","mysql","PDO","mbstring","tokenizer","bcmath","xml","curl","zip","fpm");



function checkPhpVersion(){
	if (version_compare(phpversion(), '7.0', '>=')){
		return "OK";
	}
	return "not OK";
}

function getMySQLVersion() { 

  $output = shell_exec('mysql -V'); 
  preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version); 

  $versionoutput = $version[0] ?? "0";

  return ($versionoutput > 5 ? "OK":"not OK");; 
}


function checkExtensions(){
	global $required_extentions;
	$not_ok = [];
	$extentions = get_loaded_extensions();

	foreach($required_extentions as $ext){
		if(!in_array($ext,$extentions)){
			array_push($not_ok,$ext);
		}
	}
	return $not_ok;

}

?>