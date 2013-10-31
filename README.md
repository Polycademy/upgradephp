UpgradePHP
==========

This is Git clone of the amazing UpgradePHP project. It's a shim/polyfill for PHP 5.3/5.4 functions for PHP 4.1 to PHP 5.1 installations. I wanted it on Git since I mostly work via Git and not the fossil DVCS. The below information is copied from the official sources. It can be adapted to be autoloaded from composer. But it's probably better for you to pick and choose which polyfills you want.

- Version: 18.1
- Official Site: http://include-once.org/p/upgradephp
- Official Repository: http://fossil.include-once.org/upgradephp

Explanation
-----------

With 'upgrade.php' on-hand, you can use many features from newer PHP versions (up to 5.3/5.4 currently) without losing compatibility to older interpreters and outdated webserver setups. It takes care of emulating any functions (with their original names) that are missing at runtime.

You just include() it into your application. Then you're freed from micromanaging backwards compliance and wasting time with workarounds. Simply use the more featureful PHP functions.

Following functions are currently emulated: gzdecode · hex2bin · class_uses · zlib_encode · zlib_decode · session_status · http_response_code · http_redirect · http_send_content_type · json_encode · json_decode · preg_filter · lcfirst · array_replace · strptime · error_get_last · preg_last_error · lchown · lchgrp · ob_get_headers · xmlentities · stripos · strripos · str_ireplace · get_headers · headers_list · fprintf · vfprintf · str_split · http_build_query · convert_uuencode · convert_uudecode · scandir · idate · time_nanosleep · strpbrk · php_real_logo_guid · php_egg_logo_guid · php_strip_whitespace · php_check_syntax · get_declared_interfaces · array_combine · array_walk_recursive · substr_compare · spl_classes · class_parents · session_commit · dns_check_record · dns_get_mx · setrawcookie · file_put_contents · file_get_contents · fnmatch · glob · array_key_exists · array_intersect_assoc · array_diff_assoc · html_entity_decode · str_word_count · str_shuffle · get_include_path · set_include_path · restore_include_path · str_rot13 · array_change_key_case · array_fill · array_chunk · md5_file · is_a · fmod · floatval · is_infinite · is_nan · is_finite · var_export · strcoll · diskfreespace · disktotalspace · vprintf · vsprintf · import_request_variables · hypot · log1p · expm1 · sinh · cosh · tanh · asinh · acosh · atanh · array_udiff_uassoc · array_udiff_assoc · array_diff_uassoc · array_udiff · array_uintersect_uassoc · array_uintersect_assoc · array_uintersect · array_intersect_uassoc · mime_content_type · image_type_to_mime_type · image_type_to_extension · exif_imagetype · array_filter · array_map · is_callable · array_search · array_reduce · is_scalar · localeconv · call_user_func_array · call_user_method_array · array_sum · constant · is_null · pathinfo · escapeshellarg · is_uploaded_file · move_uploaded_file · strncasecmp · wordwrap · php_uname · php_sapi_name

The emulated functions are of course only ever used, if the current PHP interpreter doesn't provide them itself.

So the native functions get called as usual on current setups. But your applications are guaranteed to work unchanged for anybody else, regardless of PHP version. Only a minimum compatibility requirement of "PHP 4.1" remains.

A few classes are also emulated. But it's kind of too much work to port SPL or other extensions over. No contributions so far. And why it never occurred to PHP.net developers to do a reference implementation for anything is somewhat unclear. 

The upgrade.php package also provides multiple emulation include scripts for larger PHP extensions, like gettext, ftp and ctype, mime functions, odbc, dba. See also the Wiki in the Fossil repository.

Of course, the emulated modules don't behave 100% exactly like the original C implementations - but the upgrade.php extensions work much better than common workaround snippets, and should be 'good enough' for most applications. 

Usage
-----

```php
if (PHP_VERSION < 5.1) { include_once("upgrade.php"); } 
json_encode("this will always work now"); 
```
Modules in Ext
--------------

gettext.php adds _() and various gettext() features. This extension is commonly used for localizing applications. Unlike other methods this is extremely easy to apply in source code.

ftp.php reimplements FTP access using plain PHP socket functions.

ctype.php provides a set of character classification functions.

mime.php has MIME magic filetype detection functions.

pdo.php is an emulatoion of the PDO database abstraction layer for PHP 4.x and PHP 5.0.

pspell.php uses the commandline aspell utility to emulate the pspell_*() type of functions for checking for natural language spelling errors.

bcmath.php for arbitrary precision math functions.

php40array.php archives the more seldomly used array functions. This has been extracted from the main script, to keep it lean.

The historic php40.php provides some functions for PHP 4.0 compatibility. Alltough nobody probably still has such old PHP versions running. 

Non Standard
------------

Distributed alongside upgrade.php are a few function collections, which aren't really standard PHP. The author just has been too lazy to instantiat individual project hosting sites.

http_query.class.php provides HttpRequest funtionality. It is partly compatible to PEAR::HTTPRequest, but mainly in place to later have an emulation for the PHP/PECL http extension and http_* functions.

input.php is a highly recommended security feature. It wraps the input arrays $_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER into an object-oriented access layer. This OO access wrapper only allows access to submitted form data and HTTP variables through filter features. In essence this enforces input validation, because raw access is prevented (or at least can be logged). 

License
-------

Unless noted otherwise, all scripts are Public domain.

The PDO extension for example, is not. 