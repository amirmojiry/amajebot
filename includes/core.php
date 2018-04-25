<?php
define('BOT_TOKEN','507927064:AAHeHwzFzmjwyRto4QbIrW8XJuf2l_08X-4');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('ADMIN_ID', '90231041');
define('SIGN', '🌍 @amajebot');


function MessageRequestJson($method, $parameters) {

  if (!$parameters) {
    $parameters = array();
  }
  
  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 3);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
  $result = curl_exec($handle);
  return $result; 
}

function prosody() {
	//set post variable
	$url = 'http://prosody.ir/index.php?option=com_wrapper&view=wrapper&Itemid=29';
	$post_data ['Lpart1'] = 'دارم از زلف سياهش گله چندان که مپرس';
	$post_data ['Lpart2'] = 'دارم از زلف سياهش گله چندان که مپرس';
	$fields = array (
		'Lpart1' => urlencode ($post_data ['Lpart1']),
		'Lpart2' => urlencode ($post_data ['Lpart2']),
	);
	//url-ify data for the POST
	foreach ($fields as $key => $value) {
		$fields_string .= $key .'='. $value. '&';
	}
	rtrim ($fields_string, '&');
	//open connection
	$handle = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($handle, CURLOPT_URL, $url);
	curl_setopt($handle, CURLOPT_POST, count ($fields));
	curl_setopt($handle, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($handle, CURLOPT_VERBOSE, TRUE);
	curl_setopt($handle, CURLOPT_HEADER, TRUE);
	//execute POST
	$result = curl_exec($handle);
	
	$header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
	$header = substr($result, 0, $header_size);
	$body = substr($result, $header_size);
	
	//close connection
	//curl_close($handle);
	
	return $body; 
}

function baseUrl(){
    return 'https://negahejahani.ir/';
}


function randomImage(){
    $images = glob("images/*.{jpg,png}",GLOB_BRACE);
    $randomImage = $images[array_rand($images)];
    return baseUrl()."faranesh/f/".$randomImage;
}


?>