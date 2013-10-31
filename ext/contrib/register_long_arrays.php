<?php
/**
 *
 * Manual register_long_arrays workaround to keep older scripts
 * running. Contributed by Pavel Titov.
 *
 */

if (!isset($HTTP_SERVER_VARS)) {
  $HTTP_SERVER_VARS = $_SERVER;
  $HTTP_POST_VARS = $_POST;
  $HTTP_ENV_VARS = $_ENV;
  $HTTP_GET_VARS = $_GET;
  $HTTP_COOKIE_VARS = $_COOKIES;
  $HTTP_SESSION_VARS = $_SESSION;
  $HTTP_POST_FILES = $_FILES;
}

?>