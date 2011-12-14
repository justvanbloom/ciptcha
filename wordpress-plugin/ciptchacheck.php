<?php
/*!
 * CIPTCHA Library for PHP
 *
 * CIPTCHA - Completely Image-Based Public Turing test to tell Computers and Humans Apart
 *
 * http://www.ciptcha.com/
 *
 * Copyright 2011, SWM
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software 
 * and associated documentation files (the "Software"), to deal in the Software without restriction, 
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, 
 * sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or 
 * substantial portions of the Software.
 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING 
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *-------------------------------------------------------------------------------------------------
 */

function ciptcha_checkcode($cip_key='',$cip_code='') {
	if(!$cip_key) {
		if (isset($_POST['cip_key'])) {
			$cip_key = $_POST['cip_key'];
		} else if(isset($_GET['cip_key'])) {
			$cip_key = $_GET['cip_key'];
		}
	}
	if(!$cip_code) {
		if (isset($_POST['cip_code'])){
			$cip_code = $_POST['cip_code'];
		} else if(isset($_GET['cip_code'])) {
			$cip_code = $_GET['cip_code'];
		}
	}
	if(!$cip_apikey) {
		if (isset($_POST['cip_apikey'])){
			$cip_apikey = $_POST['cip_apikey'];
		} else if(isset($_GET['cip_apikey'])) {
			$cip_apikey = $_GET['cip_apikey'];
		}
	}

	if(!$cip_key){return "Error: No Key.";}
	if(!$cip_code){ return "Error: No Code.";}
	if(!$cip_apikey){ return "Error: Api-Key. Please register.";}


	$cip_params = "&key=".urlencode($cip_key)."&code=".urlencode($cip_code)."&apikey=".urlencode($cip_apikey);
	$cip_result = ciptcha_getdata('checkcode.php', $cip_params);
	
	switch(intval($cip_result)) {
	case 200:	
		return "OK";
		break;
	case 401:
		return "Incorrect Code.";
		break;
	case 501:
		return "Error: Your web host has disabled all functions for handling remote requests. Please contact your web host.";
		break;
	default:
		return "Unknown Error.";
		break;
	}
}

function ciptcha_getdata($check_file='',$cip_params='') {
	$Request_URL = "http://cdn.ciptcha.com/".$check_file."?".$cip_params;
	//if ((intval(get_cfg_var('allow_url_fopen')) || intval(ini_get('allow_url_fopen'))) && function_exists('file_get_contents')) {
	//	$cip_result = @file_get_contents($Request_URL);
	//} elseif ((intval(get_cfg_var('allow_url_fopen')) || intval(ini_get('allow_url_fopen'))) && function_exists('file')) {
	//	$content = @file($Request_URL);
	//	$cip_result = @join('', $content);
	//} else
	if (function_exists('curl_init')) {

		$ch = curl_init($Request_URL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$cip_result = curl_exec($ch);
		curl_close($ch);
	} else {
		return 501;
	}

	return $cip_result;
}
?>