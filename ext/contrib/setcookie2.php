<?php

 
/**
 * Set-Cookie2 support, with a workaround for headers_sent.
 * Originally a rejected patch http://marc.info/?l=php-internals&m=118802375714734
 * Kind of redundant, since none of the current browsers seems to support it.
 *
 */
if (!function_exists("setcookie2")) {
   function setcookie2($name, $value="", $maxage=NULL, $comment=NULL, $commenturl=NULL, $path=NULL, $domain=NULL, $portlist=NULL, $secure=NULL, $httponly=NULL, $version=1) {

       if (strpbrk($name.$value, "\r\n\000\013\014")) {
           trigger_error("Invalid control characters in cookie name or value", E_USER_WARNING);
       }
       else {
           extract(array_map("urlencode", get_defined_vars()));
           $cookie = "$name=$value"
                   . ($maxage	? "; maxage=".intval($maxage)		: "")
                   . ($comment	? "; comment=\"$comment\""		: "")
                   .($commenturl? "; commenturl=\"$commenturl\""	: "")
                   . ($path	? "; path=\"$path\""			: "")
                   . ($domain	? "; domain=$domain"			: "")
                   . ($portlist	? "; portlist=\"$portlist\""		: "")
                   . ($secure	? "; secure"				: "")
                   . ($httponly	? "; httponly"				: "")
                   . ($version	? "; version=".intval($version)		: "");
           if (!headers_sent() || strstr(ini_get("defalt_mimetype"), "+xml")) {
               header("Set-Cookie2: $cookie");
           }
           else {
               trigger_error("Headers already sent. Using a meta http-equiv workaround", E_USER_NOTICE);
               print "<meta http-equiv=\"Set-Cookie2\" content=\"" . htmlspecialchars($cookie, ENT_QUOTES, "UTF-8") . "\">";
           }
       }
   }
}


