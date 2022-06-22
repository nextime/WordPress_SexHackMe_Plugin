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


function trim_text_preview($text, $len=340, $fill=false)
{
	$min="10";
	if($len < $min) $len=$min;
	if (strlen($text) > $len)
	{
    	$offset = ($len - 3) - strlen($text);
    	$text = substr($text, 0, strrpos($text, ' ', $offset)) . '...';
   }  
   if($fill)
   {
      $start=strlen($text);
      while($start < $len+1) {
         $start++;
         $text .= "&nbsp";
      }
   }
	return $text;
}

function html2text($html)
{
    // remove comments and any content found in the the comment area (strip_tags only removes the actual tags).
    $plaintext = preg_replace('#<!--.*?-->#s', '', $html);

    // put a space between list items (strip_tags just removes the tags).
    $plaintext = preg_replace('#</li>#', ' </li>', $plaintext);

    // remove all script and style tags
    $plaintext = preg_replace('#<(script|style)\b[^>]*>(.*?)</(script|style)>#is', "", $plaintext);

    // remove br tags (missed by strip_tags)
    $plaintext = preg_replace('#<br[^>]*?>#', " ", $plaintext);

    // remove all remaining html
    $plaintext = strip_tags($plaintext);

    return $plaintext;
}

?>
