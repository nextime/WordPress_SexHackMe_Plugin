<?php

namespace wp_SexHackMe;

function sexhack_getURL($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $out = curl_exec($ch);
    curl_close($ch);
    return $out;
}


function trim_text_preview($text, $len=340)
{
	$min="10";
	if($len < $min) $len=$min;
	if (strlen($text) > $len)
	{
    	$offset = ($len - 3) - strlen($text);
    	$text = substr($text, 0, strrpos($text, ' ', $offset)) . '...';
	}  
	return $text;
}

?>
