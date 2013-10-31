UpgradePHP
==========

This is Git clone of the amazing UpgradePHP project. It's a shim/polyfill for PHP 5.3/5.4 functions for PHP 4.1 and higher installations. I wanted it on Git since I mostly work via Git and not the fossil DVCS. 

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

Public Domain (= compatible to all open source and free software licenses) 