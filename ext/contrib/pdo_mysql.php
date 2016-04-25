<?php
/**
 * api: php
 * title: Procedural pdo_* interface mimicking mysql_* functions
 * version: 0.9.2
 * priority: EXPERIMENTAL
 * license: Public Domain
 * type: functions
 * suggests: upgradephp
 * config:  <const name="PDO_HELPFUL" type="boolean" value="1" help="Provide additional notices for common pitfalls"/>   <const name="PDO_SEEKABLE" type="int" value="0" help="Adds a faux cursor wrapper atop PDOStatement to allow seeking across result rows. Set to 1 to enable, or any larger integer for caching rows."/>
 * category: library
 * url: http://fossil.include-once.org/upgradephp/finfo?name=ext/contrib/pdo_mysql.php
 * requires: php (>= 5.1.3), php:pdo
 *
 *
 * The dated mysql_* functions are chaperoned by deprecation nagging and cursorily
 * mentions of PDO and MSQLI. Forced-switching APIs is kinda pointless however when
 * bound parameters go unmentioned and code rewriting remains prohibitive.
 *
 * This collection of functions provide pdo_ lookalikes for most mysql_* functions.
 * Rewriting is as simple as changing to:
 *
 *   pdo_connect       <-  mysql_connect
 *   pdo_query         <-  mysql_query
 *   pdo_result        <-  mysql_result
 *   pdo_fetch_array   <-  mysql_fetch_array
 *   pdo_escape_string <-  mysql_real_escape_string
 *
 * With the addition of an extended pdo_query() interface the cumbersome SQL
 * concatenation and easily forgotten escaping are obsolete:
 *
 *   mysql_query("SELECT whatever FROM tbl WHERE id='$id' OR x=$x");
 *                                                      \       \
 * Becomes:                                               \      \
 *                                                          \     \
 *   pdo_query("SELECT whatever FROM tbl WHERE id=? OR x=?", $id, $x);
 *                                                \      \--------/
 *                                                 \---------/
 *
 * It's as simple as moving interpolated variables from the query into separate
 * parameters, and leaving plain question mark ? placeholders (without quotes)
 * in the SQL.
 * Just keep track of a matching value-to-placeholder order.
 *
 * For variables separated out that way, any previous ***_real_escape_string
 * applying becomes redundant. (Though the long-winded escaping+intermingling
 * approach was still possible if you'd unreasonably prefer).
 * Get rid of or fix any oldschool sanitize() function once you have converted
 * every innovocation to bound parameters.
 *
 *
 * Caveats and tips
 *
 *   HANDLES    pdo_query() and pdo_connect() return objects instead of
 *     resource handles. Commonly used plain boolean if() result tests may
 *     remain. But replace any is_resource() checks with is_object().
 *
 *   FREEING    You neither need pdo_close() or pdo_free_result() anymore.
 *     Just unset the result handles, it's all cleaned up automatically.
 *
 *   SEEKING    The old mysql extension was a memory hog. It did buffer all
 *     result data, so could seek back and forth. For PDO results it's best
 *     to just iterate over them only once. Consider using a foreach()
 *     preferrably.
 *     If you need arbitrary  pdo_result(), _fetch_lengths(), _data_seek()
 *     access, please enable PDO_SEEKABLE. It does refetch if need be for
 *     out-of-order retrieval. (Mysqld itself provides enough buffering.)
 *
 *   BOUND PARAMETERS    In SQL only values can be bound parameters with ?
 *     placeholders. Identifiers like table names, column names can't. You
 *     still have to use interpolation (with withelisting) for those.
 *     Likewise can't LIMIT ?,? clauses be used in PDO. Use typecasted
 *     interpolated numbers there.
 *
 *   NAMED PARAMETERS    Besides ? placeholders there are also :named_keys.
 *     pdo_query() supports those too if passed as a single array with
 *     :key => value pairs.  You can only use either or.
 *
 *   SOMETHING DOESN'T WORK    Before asking for personal support anywhere
 *     on the Internet, please have the courtesy to `print pdo_error();`
 *     first. Always enable `error_reporting(E_ALL);` and `display_errors`
 *     and keep the `PDO_HELPFUL` constant enabled while developing.
 *
 *   GLOBAL OBJECT   The pdo_ wrappers keep the default connection in the
 *     global $pdo variable. Whose name is unlikely to clash with existing
 *     code, but potentially compatible with recent as by-product.
 *
 *
 * Rewriting advise
 *
 *   HYBRID    You can mix the old-style function use with PDO-esque
 *     accessing of result sets. For example
 *
 *         pdo_query("SELECT *")->fetchAll()
 *
 *     Packs all results into one list of row arrays.
 *     Which is also significantly shorter than:
 *
 *         while ($row = pdo_fetch_assoc($result)) {
 *            $array[] = $row;
 *         }
 *
 *     But PDO even allows to directly loop over the $result object.
 *
 *         foreach (pdo_query("SELECT * FROM tbl") as $assoc) {}
 *
 *     Currently doesn't work for PDOStatement_Seekable wrapped results.
 *
 *   TRANSITION   Likewise can you access PDO itself from the global handle,
 *     if you want to progressively switch to the native interface:
 *
 *         $pdo->prepare("INSERT INTO log VALUES (?)")->execute(array($msg));
 *
 *     You can still mix in pdo_query() and other function wrappers, if you
 *     utilize the $pdo variable from the shared scope, or pass it as $link
 *     parameter.
 *
 *
 *
 *
 *
 *
 */




// provide development tips (deprecated and redundant functions)
defined("PDO_HELPFUL") or
define("PDO_HELPFUL", 1);


// apply PDO wrapper for random-access row seeking / faux cursor
defined("PDO_SEEKABLE") or
define("PDO_SEEKABLE", 1);


// upgradephp
defined("E_USER_DEPRECATED") or
define("E_USER_DEPRECATED", E_USER_NOTICE);




// define functions just once
if (!function_exists("pdo_query")) {




  /**
   * Return the number of rows affected by last query.
   *
   */
  function pdo_affected_rows($stmt=NULL) {
     return pdo_stmt($stmt)->rowCount();
  }




  /**
   * What mysql thinks the charset of strings in PHP to be.
   *
   */
  function pdo_client_encoding($link=NULL) {
     return pdo_query("SELECT @@character_set_client AS cs", pdo_handle($link))->fetchObject()->cs;
  }
  



  /**
   * Disconnect by removing $pdo object.
   *
   */  
  function pdo_close($link=NULL) {
     PDO_HELPFUL and pdo_trigger_error("pdo_close() Does not need to be called usually. Just unset() your database handle.", E_USER_DEPRECATED);

     if ($link) {
        unset($link);
     }
     else {
        unset($GLOBALS["pdo"]);
     }
  }




  /**
   * Connect to database.
   *
   * Also stores the newest PDO handle in the global `$pdo` variable.
   *
   */  
  function pdo_connect($server=NULL, $user=NULL, $pass=NULL, $new_link=FALSE, $client_flags=0x0000, $pconnect=0x0000) {
     
     // get params
     $server or $server = ini_get("mysql.default_host");
     $user or $user = ini_get("mysql.default_user");
     $pass or $pass = ini_get("mysql.default_password");

     // prepare Data Source Name
     $dsn = "mysql:";
     
     // servername contains a socket path
     if (strpos($server, "/")) {
        strpos($server, ":") and $server = substr($server, strpos($server, ":") + 1);
        $dsn .= "unix_socket=$server";
     }
     // or hostname and optional port
     else {
        if (strpos($server, ":")) {
           list($server, $port) = explode($server, ":");
           $dsn .= "host=$server;port=$port";
        }
        else {
           $dsn .= "host=$server";
        }
     }
     //("we don't have a dbname= at this point");
     
     // driver flags
     $flags = array(
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        #PDO::MYSQL_ATTR_INIT_COMMAND => '',   // direct queries, no prepared statements
        #PDO::MYSQL_ATTR_DIRECT_QUERY => true, // direct queries, no prepared statements
        #PDO::MYSQL_ATTR_FOUND_ROWS => true,   // rowCount() for SELECTs, not sure if this is an init parameter, or Statement setting
     );
     if ($client_flags & MYSQL_CLIENT_COMPRESS) {
        $flags[PDO::MYSQL_ATTR_COMPRESS] = 1;
     }
     if ($client_flags & MYSQL_CLIENT_SSL) {
        $flags[PDO::MYSQL_ATTR_SSL_KEY] = "client.pem";
        $flags[PDO::MYSQL_ATTR_SSL_CERT] = "cert.pem";
        $flags[PDO::MYSQL_ATTR_SSL_CA] = "ca.pem";
     }
     if ($client_flags & MYSQL_CLIENT_IGNORE_SPACE) {
        $flags[PDO::MYSQL_ATTR_IGNORE_SPACE] = 1;
     }
     if ($client_flags & 128) {
        $flags[PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
     }
     if ($pconnect) {
        $flags[PDO::ATTR_PERSISTENT] = 1;
     }
     
     // instantiate connection
     try {
        $pdo = new PDO($dsn, $user, $pass, $flags);
     }
     catch (RuntimeException $pdo) {
        PDO_HELPFUL and pdo_trigger_error("pdo_connect() Failed. {$pdo->getMessage()}", E_USER_WARNING);
        return new pdo_dummy("Database connection had failed [ErrCode{$pdo->getCode()}].");
     }
     
     // set PDO flags
     $pdo->setAttribute(PDO::ATTR_ERRMODE, (PDO_HELPFUL ? PDO::ERRMODE_WARNING : PDO::ERRMODE_SILENT)); //or PDO::ERRMODE_EXCEPTION
     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
     $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
     $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
     $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
     $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

     // done     
     return pdo_handle($pdo, "SET_HANDLE_AS_DEFAULT");
  }




  /**
   * CREATE DATABASE
   *
   */
  function pdo_create_db($dbname, $link=NULL) {
     $dbname = str_replace("`", "``", $dbname);
     return (bool)pdo_query("CREATE DATABASE `$dbname`", pdo_handle($link));
  }




  /**
   * Move pointer in result set.
   *
   */
  function pdo_data_seek($stmt, $row_number) {
      !PDO_SEEKABLE and pdo_trigger_error("pdo_data_seek() PDO_MySQL driver does not support cursors. Enable PDO_SEEKABLE.", E_USER_WARNING);
      pdo_stmt($stmt)->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row_number);
  }
  



  /**
   * Return one entry from _list_dbs().
   *
   */
  function pdo_db_name($stmt, $row, $field="Database") {
     return pdo_result($stmt, $row, $field);
  }




  /**
   * Combo of _select_db() and _query().
   *
   */
  function pdo_db_query($dbname, $query, $link=NULL, $params=NULL) {
     PDO_HELPFUL and pdo_trigger_error("pdo_db_query() is redundant. Use pdo_select_db() and pdo_query().", E_USER_DEPRECATED);

     if (pdo_select_db($dbname, pdo_handle($link))) {
        $params = func_get_args();
        array_shift($params);
        return call_user_func_array("pdo_query", $params);
     }
  }




  /**
   * DROP DATABASE
   *
   */
  function pdo_drop_db($dbname, $link=NULL) {
     $dbname = str_replace("`", "``", $dbname);
     return (bool)pdo_query("DROP DATABASE `$dbname`", pdo_handle($link));
  }




  /**
   * Get numeric error code for last failed database query.
   *
   */
  function pdo_errno($link=NULL) {
     $error = pdo_handle($link)->errorInfo();
     return $error[1];
  }




  /**
   * Get error message for last failed request.
   *
   */
  function pdo_error($link=NULL) {
     #
     #### Possibly better to also keep a $pdo_last_stmt to get possible errors from there?
     #    PDO::errorInfo might be empty
     #    Keeping PDO::ERRMODE_WARNING per default isn't as advisable for production setups.
     #
     #    Similar wrapper in https://www.kitware.com/svn/CDash/trunk/cdash/pdocore.php
     #    also just uses PDO::errorInfo
     #
     global $pdo_last_stmt;
     $link = pdo_handle($link);

     # we don't really know what ran last, PDO:: or PDOStatement::
     if (!empty($pdo_last_stmt)) {
        $error = $pdo_last_stmt[spl_object_hash($link)]->errorInfo();
     }
     else {
        $error = $link->errorInfo();
     }
     return $error[2];
  }




  /**
   * String escaping, alias to _real_escape_string()
   *
   */
  function pdo_escape_string($str, $link=NULL) {
     return pdo_real_escape_string($str, $link);
  }




  /**
   * Get result row.
   * Note you still have to use the MYSQL_ constants, _BOTH, _ASSOC, or _NUMeric array.
   *
   */
  function pdo_fetch_array($stmt, $type=3) {
  
     $map = array(
         MYSQL_ASSOC/*1*/ => PDO::FETCH_ASSOC/*2*/,
         MYSQL_NUM/*2*/ => PDO::FETCH_NUM/*3*/,
         MYSQL_BOTH/*3*/ => PDO::FETCH_BOTH/*4*/,
         // no consistency so far, so let's embrace and extend it:
         0=>4, "assoc"=>2, "num"=>3, "both"=>4, "ASSOC"=>2, "NUM"=>3, "BOTH"=>4, "MYSQL_ASSOC"=>2, "MYSQL_NUM"=>3, "MYSQL_BOTH"=>4,
     );
  
     return pdo_stmt($stmt)->fetch(isset($map[$type]) ? $map[$type] : $type);
  }




  /**
   * Get result row.
   * Always an associative array as result.
   *
   */
  function pdo_fetch_assoc($stmt) {
     return pdo_stmt($stmt)->fetch(PDO::FETCH_ASSOC);
  }




  /**
   * Get table attributes/meta for a column.
   * Doesn't match up 1:1.
   *
   * See also http://www.phpcl***es.org/browse/file/47988.html
   * (discovered too late, similar intent except for the class wrapping,
   * the getColumnMeta conversion seems more complete);
   * so maybe lets use _list_fields() / SHOW COLUMNS FROM .. for details
   # Doc ~/php/php-5.5.5/ext/pdo_mysql/mysql_statement.c for PDO types
   *
   */
  function pdo_fetch_field($stmt, $column_id) {
  
     // map meta types to what mysql_fetch_field returned
     $native_map = array(
         "BOOL"=>"bool", "NULL"=>"null", "BLOB"=>"blob",
         "LONG"=>"int", "LONGLONG"=>"int", "BIT"=>"int", "ENUM"=>"int", "DECIMAL"=>"int",
         "VAR_STRING"=>"string", "STRING"=>"string", "VARCHAR"=>"string",
         "YEAR"=>"string",
     );
     $pdo_map = array(
         PDO::PARAM_STR => "string",
         PDO::PARAM_INT => "int",
         PDO::PARAM_BOOL => "bool",
         PDO::PARAM_NULL => "null",
         PDO::PARAM_LOB => "blob",
     );

     // PDO meta field set (no conflicting locals, so extract() will do)
     extract(pdo_stmt($stmt)->getColumnMeta($column_id));
     
     // convert format
     return (object)array(
        "name" => $name,
        "table" => $table,
        "def" => NULL,  // undocumented
        "max_length" => $len,  // PDO reads from ->max_len, but doesn't match up with libmysqls results
        "not_null" => in_array("not_null", $flags),
        "primary_key" => in_array("primary_key", $flags),
        "unique_key" => in_array("unique_key", $flags),
        "multiple_key" => in_array("multiple_key", $flags),
        "numeric" => $native_type == "LONG",
        "blob" => $native_type == "BLOB",
        "type" => isset($native_type) ? $native_map[$native_type] : $pdo_map[$pdo_type],
        "unsigned" => in_array("unsigned", $flags),
        "zerofill" => in_array("zerofill", $flags),
     );
  }




  /**
   * Returns the list of strlen()s for the last result row.
   *
   */
  function pdo_fetch_lengths($stmt) {
  
      // no can do
      if (!PDO_SEEKABLE or !$stmt instanceof PDOStatement_Seekable) {
         pdo_trigger_error("pdo_fetch_lengths() Cannot retrieve the last row to calculate string lengths, no cursor available. Enable PDO_SEEKABLE." . (PDO_HELPFUL ? " Better yet do the field length calculation yourself with <a>array_map('strlen',\$row)</a> on the last pdo_fetch_row()." : ""), E_USER_WARNING);
      }

      // with _Seekable the last row is always cached
      return array_map("strlen", pdo_stmt($stmt)->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_PRIOR));
  }




  /**
   * Get result row as object.
   *
   */
  function pdo_fetch_object($stmt, $classname="stdClass", $params=array()) {
     return pdo_stmt($stmt)->fetchObject($classname, $params);
  }
  



  /**
   * Get result row as indexed list.
   *
   */
  function pdo_fetch_row($stmt) {
     return pdo_stmt($stmt)->fetch(PDO::FETCH_NUM);
  }




  /**
   * _fetch_field() meta data broken up into individual functions.
   *
   */
  function pdo_field_flags($stmt, $col) {
     $meta = pdo_stmt($stmt)->getColumnMeta($col);
     return implode(" ", $meta["flags"]);
  }
  function pdo_field_len($stmt, $col) {
     return pdo_fetch_field($stmt, $col)->max_length;
  }
  function pdo_field_name($stmt, $col) {
     return pdo_fetch_field($stmt, $col)->name;
  }
  function pdo_field_seek($stmt, $col) {
     pdo_trigger_error("pdo_field_seek() Has no effect. Please use the \$col parameter for all pdo_field_*() functions.", E_USER_WARNING);
  }
  function pdo_field_table($stmt, $col) {
     return pdo_fetch_field($stmt, $col)->table;
  }
  function pdo_field_type($stmt, $col) {
     return pdo_fetch_field($stmt, $col)->type;
  }




  /**
   * Unallocating a query result.
   *
   * This call has little practical use.
   * Rather just unset() your pdo_query $result object variables.
   *
   */
  function pdo_free_result($stmt) {
     PDO_HELPFUL and trigger_error("pdo_free_result() usage is mostly redundant. PHP cleans up resource handles/objects if you just unset() them, or move out of scope.", E_USER_DEPRECATED);

     // free up some resources
     try {
        pdo_stmt($stmt)->fetchAll();
        pdo_stmt($stmt)->closeCursor();
     }
     catch (PDOException $e) {
        // yeah, let's ignore that
     }

     // unsetting here has no effect on the outer scope
     $stmt = NULL;
  }




  /**
   * Get libmysql/mysqlnd client version.
   *
   */
  function pdo_get_client_info($link=NULL) {
     return pdo_handle($link)->getAttribute(PDO::ATTR_CLIENT_VERSION);
  }




  /**
   * Get Connection: STATUS
   * mostly "Localhost via UNIX socket"
   *
   */
  function pdo_get_host_info($link=NULL) {
     return pdo_handle($link)->getAttribute(PDO::ATTR_CONNECTION_STATUS);
  }
  



  /**
   * MySQL protocol version.
   *
   */
  function pdo_get_proto_info($link=NULL) {
     return pdo_query("SELECT @@protocol_version AS ver", pdo_handle($link))->fetchObject()->ver;
  }




  /**
   * Mysqld/Mariadb server version.
   *
   */
  function pdo_get_server_info($link=NULL) {
     return pdo_query("SELECT VERSION() as version", pdo_handle($link))->fetchObject()->version;
  }




  /**
   * Show last queries.
   *
   * @stub  We don't keep a log currently.
   *
   # See ~/php/php-5.5.5/ext/mysql/php_mysql.c
   # haven't looked up what libmysql does here; presumably PHP keeps the query log
   *
   */
  function pdo_info($link=NULL) {
     return "";
  }




  /**
   * Last inserted ID.
   *
   */
  function pdo_insert_id($link=NULL) {
     return pdo_handle($link)->lastInsertId();
     //pdo_query("SELECT last_insert_id() AS id", $link)->fetchObject()->id;
  }




  /**
   * SHOW DATABASES
   *
   */
  function pdo_list_dbs($link=NULL) {
     return pdo_query("SHOW DATABASES", pdo_handle($link));
  }




  /**
   * Show table definition.
   *
   */
  function pdo_list_fields($dbname, $table, $link=NULL) {
     $dbname = str_replace("`", "``", $dbname);
     $table = str_replace("`", "``", $table);
     return pdo_query("SHOW COLUMNS FROM `$dbname`.`$table`", pdo_handle($link))->fetchAll();
  }




  /**
   * Retrieves the current MySQL server threads.
   *
   */
  function pdo_list_processes($link=NULL) {
     return pdo_query("SHOW FULL PROCESSLIST", pdo_handle($link));
  }




  /**
   * SHOW TABLES result set.
   *
   */
  function pdo_list_tables($dbname, $link=NULL) {
     $dbname = str_replace("`", "``", $dbname);
     return pdo_query("SHOW TABLES FROM `$dbname`", pdo_handle($link));
  }




  /**
   * Number of fields (columns) in the result rows.
   *
   */
  function pdo_num_fields($stmt) {
     return pdo_stmt($stmt)->columnCount();
  }




  /**
   * Amount of results for last SELECT query.
   *
   # See: http://stackoverflow.com/a/883523
   #
   #  * PDOStmt::rowCount() works for UPDATE/INSERT/DELETE,
   #    but also SELECT since pdo_query() sets ::MYSQL_ATTR_FOUND_ROWS then.
   #
   #  * FOUND_ROWS() is a nice alternative,
   #    But only if _num_rows() is called just after the _query() or no
   #    intermediate query was run, no second database connection is open.
   #
   #  * mysql_ did actually use fetchAll()+count()
   #
   */
  function pdo_num_rows($stmt=NULL) {
  
     /* Unsolicited advise; maybe redundant now that there's two fallbacks.
        Users utilizing _num_rows() however are likely to for($i)-iterate over
        the resultset with indexed pdo_result() calls [which won't work].
     */
     //PDO_HELPFUL and pdo_trigger_error("pdo_num_rows() Should be avoided. To get the number of results, instead use \$result->fetchAll() to get the complete row list and just count() it.", E_USER_NOTICE);

     // let's try this anyway
     if ($num = pdo_stmt($stmt)->rowCount()) {
        return $num;
     }
     
     // fallback
     return pdo_query("SELECT FOUND_ROWS() as num")->fetchObject()->num;
  }




  /**
   * Open a persistent PDO connection.
   *
   */
  function pdo_pconnect($server, $user, $pass, $new_link=FALSE, $client_flags=0x0000, $pconnect=0x0000) {
     return pdo_connect($server, $user, $pass, $new_link, $client_flags, $pconnect=TRUE);
  }




  /**
   * Keep connection open.
   * (Doesn't actually do that. But mysqlnd/pdo_mysql implicitly.)
   *
   */
  function pdo_ping($link=NULL) {
     return pdo_query("SELECT 1", pdo_handle($link)) ? true : false;
  }




  /**
   * Run query.
   *
   * Note that this can be invoked with just a SQL string:
   *
   *   pdo_query("SELECT 1,2,3");
   *
   * Or with bound parameters:
   *
   *   pdo_query("SELECT ?,?,?", 1, 2, 3);
   *
   * Or an indexed array of bound parameters:
   *
   *   pdo_query("SELECT :a, :b, :c", $array);
   *
   * And an optional database connection $link either before or after a parameter list:
   *
   *   pdo_query("SELECT 1,?,?", $link, 2, 3);
   *   pdo_query("SELECT 1,?,?", 2, 3, $link);
   *
   */
  function pdo_query($sql, $link=NULL, $params=NULL) {

     // separate params from $sql and $link     
     $params = func_get_args();
     $sql = TRIM( array_shift($params) );
     $flags = array();
     $direct = false;
     

     // find pdo $link     
     if (count($params)) {
        // $link can be the first element
        if (is_object(reset($params))) {
           $link = array_shift($params);
        }
        // or the last
        elseif (is_object(end($params))) {
           $link = array_pop($params);
        }
     }
     // or we use the default $pdo
     $link = pdo_handle($link);
     
     // is $params a list to pdo_query(), or just one array with :key=>value pairs?
     if (count($params)==1 && is_array($params[0])) {
        $params = array_shift($params);
     }


     // add PDO_MySQL driver flag / workaround for specific query types
     switch (strtoupper(substr($sql, 0, strspn(strtoupper($sql), "SELECT,USE,CREATE")))) {

        // ought to make ->rowCount() work
        case "SELECT":
           $flags[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
           break;

        // temporarily disable prepared statement mode for unbindable directives
        case "USE":
           $direct = true;
           $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
           break;

        default:
     }


     // unparameterized query()
     if ($direct) {
        $stmt = $link->query($sql);
        $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
     }
     // or prepare() and execute()
     else {
        if ($stmt = $link->prepare($sql, $flags)) {   // no try-catch in _WARNING mode
           $stmt->execute($params);
        }
     }
     
     // result
     if (!$stmt and PDO_HELPFUL) {
        pdo_trigger_error("pdo_query() SQL query failed, see pdo_error()", E_USER_WARNING);
     }
     elseif (PDO_SEEKABLE & !$direct) {
        return new PDOStatement_Seekable($stmt, $params);
     }
     else {
        return $stmt;
     }
  }




  /**
   * Escape *strings* for use in SQL context.
   *
   * Note that you still need to enclose string values in 'single quotes' when
   * interpolating/concatenating it into queries.
   *
   * And you shouldn't keep using this function, if you already use ? placeholders
   * with separate value parameters to pdo_query().
   *
   */
  function pdo_real_escape_string($str, $link=NULL) {
     return substr(pdo_handle($link)->quote($str), 1, -1);
  }




  /**
   * Return one field from a result row.
   *
   */
  function pdo_result($stmt, $row, $field=0) {
  
     // Scroll to and retrieve the given $row (maybe it's buffered even)
     if (PDO_SEEKABLE) {
        $rows = array(
           $row => pdo_stmt($stmt)->fetch(PDO::FETCH_BOTH, PDO::FETCH_ORI_ABS, $row)
        );
     }
     
     // workaround: fetch everything, works just once
     else {
        pdo_trigger_error("pdo_result() Can currently only be used once, for fetching a value from the first \$row. Scrolling only works with PDO_SEEKABLE enabled.", E_USER_WARNING);

        $rows = pdo_stmt($stmt)->fetchAll(PDO::FETCH_BOTH);
        #static $rows[hash(stmt)]; //would otherwise bind the result set here
     }
     
     // check if found
     if (isset($rows[$row][$field])) {
        return $rows[$row][$field];
     }
     else {
        pdo_trigger_error("pdo_result() Couldn't find row [$row] and column `$field`.", E_USER_NOTICE);
     }
     
  }




  /**
   * USE database_name;
   *
   */
  function pdo_select_db($dbname, $link=NULL) {
     $dbname = str_replace("`", "``", $dbname);
     return (bool)pdo_query("USE `$dbname`", pdo_handle($link));
  }


  
  
  /**
   * Set charset used for strings from client, in transport, and for result data.
   *
   */
  function pdo_set_charset($charset, $link=NULL) {
     $charset = pdo_handle($link)->quote($charset);
     return (bool)pdo_query("SET NAMES $charset", pdo_handle($link));
  }




  /**
   * Return server status and some settings.
   *
   */
  function pdo_stat() {
     return array_column(pdo_query("SHOW STATUS")->fetchAll(), "Value", "Variable_name");
  }




  /**
   * SHOW TABLES [$i] after pdo_list_tables()
   *
   */
  function pdo_tablename($stmt, $i) {
     return pdo_result($stmt, $i, 0);
  }




  /**
   * Returns the connection/thread ID.
   * It's unique among all connected clients.
   *
   */
  function pdo_thread_id($link=NULL) {
     return pdo_query("SELECT connection_id() AS id", pdo_handle($link))->fetchObject()->id;
  }




  /**
   * Switch to unbuffered mode before issuing SQL query.
   *
   */
  function pdo_unbuffered_query($sql, $link=NULL, $params=NULL) {
  
     // find optional $link param
     $params = func_get_args();
     $link = array_filter($params, "is_object");
     $link = pdo_handle($link ? reset($link) : NULL);
     $link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
     
     // invoke real pdo_query()
     $result = call_user_func_array("pdo_query", $params);

     // reset to buffered mode
     $link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
     
     // done
     return $result;
  }






  ##############################  helper functions  #############################



  
  /**
   * Determine if to use the current PDO $link, or the last used handle
   * which is kept in global `$pdo`;
   *
   */
  function pdo_handle($link, $connect=0) {
     global $pdo;

     // got a PDO handle as parameter already
     if (is_object($link)) {

        // implicitly update
        if ($connect && !is_a($link, "pdo_dummy")) {
           $pdo = $link;
        }
     }
     
     // last used handle
     elseif (isset($pdo) && is_object($pdo)) {
        $link = $pdo;
     }
     
     // not connected yet
     else {
        $link = new pdo_dummy("You don't have an open database connection, but tried to invoke a call anyway.");
     }

     return $link;
  }




  /**
   * Check if statement (result handle) is usable.
   *
   */
  function pdo_stmt($stmt) {

     if (is_object($stmt)) {
        return $stmt;
     }
     else {
        return new pdo_dummy("Your last query was unsuccessful and returned FALSE instead of a fetchable result. See pdo_error() to find out why.");
     }
  }


  
  
  /**
   * Error messages.
   *
   * Augments the actual origin path/filename and linenumber.
   * Adds some HTML prettyfication.
   *
   */
  function pdo_trigger_error($msg, $level=E_USER_NOTICE) {

     // locate source of last pdo_*() invocation
     foreach ((debug_backtrace()) as $src) {
     
        // skip any functions in the current file
        if (empty($src["file"])) {
           $src["file"] = "{main}";
           $src["line"] = "-";
        }
        if ($src["file"] != __FILE__) {
           $dir = dirname($src["file"]);
           $file = basename($src["file"]);
           $msg .= " - caused by $dir/<b>$file</b>, line <b>$src[line]</b>, when calling <a>$src[function]()</a>;";
           break;
        }
     }
     
     // pass on to actual error handler chain
     trigger_error($msg, (($level == 1<<14) && (PHP_VERSION < 5.3)) ? E_USER_NOTICE : $level);
  }



  /**
   * Provide userland faux cursor support. Simply refetches query results
   * for out-of-order seeking. It also implements a configurable-sized
   * cache, but per default just keeps the last row. (PDO_SEEKABLE can be
   * an integer to define the cache length).
   *
   */
  class PDOStatement_Seekable implements Iterator {

     // the actual PDOStatement
     private $stmt = NULL;
     // bound parameters for reexecution
     private $params = array();

     // row counter
     private $i = -1;
     private $max;
     // last fetched rows
     private $cache = array();


     // keep proxied PDOStatement and data necessary to rerun it
     function __construct($stmt, $params) {
        $this->stmt = $stmt;
        $this->params = $params;
        $this->max = $stmt->rowCount() - 1;
     }


     // invoke wrapped PDOStatement
     function __call($func, $args) {
        return call_user_func_array(array($this->stmt, $func), $args);
     }


     // repopulate PDOStatement
     function reExecute() {
         $this->stmt->execute($this->params);
         $this->i = -1;   // last fetched row
         $this->prior_row = false;   // corresponds to [$i]
     }


     // fetch a single row
     function fetch($type=PDO::FETCH_ASSOC, $ori=PDO::FETCH_ORI_NEXT, $offset=0) {

        // calculate offset
        $last = & $this->i;
        $target = array(                                    
            PDO::FETCH_ORI_NEXT => $last + 1,
            PDO::FETCH_ORI_PRIOR => $last,
            PDO::FETCH_ORI_REL => $last + $offset,
            PDO::FETCH_ORI_FIRST => -1,
            PDO::FETCH_ORI_LAST => $this->rowCount() - 1,
            PDO::FETCH_ORI_ABS => $offset,
        );
        $target = $target[$ori];
#print "seek($last->$target) ";
        
        // last row? got that covered!
        if (isset($this->cache[$target])) {
#print "seek(==) ";
            return $this->rowType($type, $this->cache[$target]);
        }
        
        // moving farther backwards
        if ($target < $last) {
#print "seek(<<) ";
            $this->reExecute();
        }
        
        // jump forwards
        while ($target > $last + 1) {
#print "seek(>>) ";
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            $last++;
            if (!$row) {
               return pdo_trigger_error("PDOStatement_Seekable: scrolling past last row", E_USER_WARNING) and $row;
            }
        }

        // actually fetch next row
        if ($row = $this->stmt->fetch(PDO::FETCH_ASSOC)) {
#print "seek(ft) ";
           assert($target == ++$last);
           // keep last row(s)
           if (count($this->cache) > PDO_SEEKABLE) {
              $this->cache = array_slice($this->cache, $last, -PDO_SEEKABLE, true);
           }
           $this->cache[$last] = $row;
        }
        return $this->rowType($type, $row);
     }


     // convert associative array to requested type
     function rowType($type, $row) {
        switch ($row ? $type : false) {
           case             false: return false;
           case    PDO::FETCH_NUM: return array_values($row);
           case   PDO::FETCH_BOTH: return array_merge($row, array_values($row));
           case  PDO::FETCH_ASSOC: return $row;
           case PDO::FETCH_OBJECT: return (object)$row;
        }
     }
     

     // Iterator handling
     function current() {
        return $this->fetch();
     }
     function rewind() {
        if ($this->i >= 0) {
           $this->reExecute();
        }
     }
     // PDOStatement itself implements just Traversable internally, not Iterator, so we have to handle the keys ourselves
     function key() {
        return $this->i >= 0 ? $this->i : NULL;
     }
     function next() {
        return $this->i + 1;
     }
     function valid() {
        return $this->i < $this->max;
     }


     # fetchAll
     # fetchObject
  }

  

  /**
   * For dummy PDO instances, to avoid fatal Call-to-member-function-on-non-object errors.
   *
   */
  class pdo_dummy {
  
     /**
      * Catch any ->prepare, ->quote, etc. calls when no proper PDO connection
      * or PDOStatement was passed.
      *
      */
     function __call($func, $args) {
        pdo_trigger_error("pdo_dummy::{$func}() {$this->msg}", E_USER_WARNING);
     }

     /**
      * Concretise error message.
      *
      */     
     public $msg;
     function __construct($msg = "You passed an invalid database or result handle..") {
        $this->msg = $msg;
     }
  
  }




  /**
   * Instantiate a placeholder until pdo_connect() was called.
   *
   */
  global $pdo;

  if (!isset($pdo) || !is_object($pdo)) {
     $pdo = new pdo_dummy("You haven't opened a pdo_connect() handle yet.");
  }  // pdo_handle() takes care already, but we could concretise the help message here



}




/**
 * Fallback code for absent/compiled-out mysql_ extension itself.
 * Should PHP 5.7/5.8 remove ext/mysql as surmised, we can just alias
 * the new pdo_* functions back onto mysql_*.
 * Though such unchanged codebases don't benefit from the more flexible
 * function signature / bound params then.
 *
 */
if (!function_exists("mysql_query")) {

   // autogenerated via ReflectionFunction (parameter properties unknown for internal functions)
   function mysql_connect($hostname=NULL, $username=NULL, $password=NULL, $new=NULL, $flags=NULL) { return pdo_connect(func_get_args()); }
   function mysql_pconnect($hostname=NULL, $username=NULL, $password=NULL, $flags=NULL) { return pdo_pconnect(func_get_args()); }
   function mysql_close($link_identifier=NULL) { return pdo_close(func_get_args()); }
   function mysql_select_db($database_name=NULL, $link_identifier=NULL) { return pdo_select_db(func_get_args()); }
   function mysql_query($query=NULL, $link_identifier=NULL) { return pdo_query(func_get_args()); }
   function mysql_unbuffered_query($query=NULL, $link_identifier=NULL) { return pdo_unbuffered_query(func_get_args()); }
   function mysql_db_query($database_name=NULL, $query=NULL, $link_identifier=NULL) { return pdo_db_query(func_get_args()); }
   function mysql_list_dbs($link_identifier=NULL) { return pdo_list_dbs(func_get_args()); }
   function mysql_list_tables($database_name=NULL, $link_identifier=NULL) { return pdo_list_tables(func_get_args()); }
   function mysql_list_fields($database_name=NULL, $table_name=NULL, $link_identifier=NULL) { return pdo_list_fields(func_get_args()); }
   function mysql_list_processes($link_identifier=NULL) { return pdo_list_processes(func_get_args()); }
   function mysql_error($link_identifier=NULL) { return pdo_error(func_get_args()); }
   function mysql_errno($link_identifier=NULL) { return pdo_errno(func_get_args()); }
   function mysql_affected_rows($link_identifier=NULL) { return pdo_affected_rows(func_get_args()); }
   function mysql_insert_id($link_identifier=NULL) { return pdo_insert_id(func_get_args()); }
   function mysql_result($result=NULL, $row=NULL, $field=NULL) { return pdo_result(func_get_args()); }
   function mysql_num_rows($result=NULL) { return pdo_num_rows(func_get_args()); }
   function mysql_num_fields($result=NULL) { return pdo_num_fields(func_get_args()); }
   function mysql_fetch_row($result=NULL) { return pdo_fetch_row(func_get_args()); }
   function mysql_fetch_array($result=NULL, $result_type=NULL) { return pdo_fetch_array(func_get_args()); }
   function mysql_fetch_assoc($result=NULL) { return pdo_fetch_assoc(func_get_args()); }
   function mysql_fetch_object($result=NULL, $class_name=NULL, $ctor_params=NULL) { return pdo_fetch_object(func_get_args()); }
   function mysql_data_seek($result=NULL, $row_number=NULL) { return pdo_data_seek(func_get_args()); }
   function mysql_fetch_lengths($result=NULL) { return pdo_fetch_lengths(func_get_args()); }
   function mysql_fetch_field($result=NULL, $field_offset=NULL) { return pdo_fetch_field(func_get_args()); }
   function mysql_field_seek($result=NULL, $field_offset=NULL) { return pdo_field_seek(func_get_args()); }
   function mysql_free_result($result=NULL) { return pdo_free_result(func_get_args()); }
   function mysql_field_name($result=NULL, $field_index=NULL) { return pdo_field_name(func_get_args()); }
   function mysql_field_table($result=NULL, $field_offset=NULL) { return pdo_field_table(func_get_args()); }
   function mysql_field_len($result=NULL, $field_offset=NULL) { return pdo_field_len(func_get_args()); }
   function mysql_field_type($result=NULL, $field_offset=NULL) { return pdo_field_type(func_get_args()); }
   function mysql_field_flags($result=NULL, $field_offset=NULL) { return pdo_field_flags(func_get_args()); }
   function mysql_escape_string($string=NULL) { return pdo_escape_string(func_get_args()); }
   function mysql_real_escape_string($string=NULL, $link_identifier=NULL) { return pdo_real_escape_string(func_get_args()); }
   function mysql_stat($link_identifier=NULL) { return pdo_stat(func_get_args()); }
   function mysql_thread_id($link_identifier=NULL) { return pdo_thread_id(func_get_args()); }
   function mysql_client_encoding($link_identifier=NULL) { return pdo_client_encoding(func_get_args()); }
   function mysql_ping($link_identifier=NULL) { return pdo_ping(func_get_args()); }
   function mysql_get_client_info() { return pdo_get_client_info(func_get_args()); }
   function mysql_get_host_info($link_identifier=NULL) { return pdo_get_host_info(func_get_args()); }
   function mysql_get_proto_info($link_identifier=NULL) { return pdo_get_proto_info(func_get_args()); }
   function mysql_get_server_info($link_identifier=NULL) { return pdo_get_server_info(func_get_args()); }
   function mysql_info($link_identifier=NULL) { return pdo_info(func_get_args()); }
   function mysql_set_charset($charset_name=NULL, $link_identifier=NULL) { return pdo_set_charset(func_get_args()); }
   function mysql_fieldname($result=NULL, $field_index=NULL) { return pdo_fieldname(func_get_args()); }
   function mysql_fieldtable($result=NULL, $field_offset=NULL) { return pdo_fieldtable(func_get_args()); }
   function mysql_fieldlen($result=NULL, $field_offset=NULL) { return pdo_fieldlen(func_get_args()); }
   function mysql_fieldtype($result=NULL, $field_offset=NULL) { return pdo_fieldtype(func_get_args()); }
   function mysql_fieldflags($result=NULL, $field_offset=NULL) { return pdo_fieldflags(func_get_args()); }
   function mysql_selectdb($database_name=NULL, $link_identifier=NULL) { return pdo_selectdb(func_get_args()); }
   function mysql_freeresult($result=NULL) { return pdo_freeresult(func_get_args()); }
   function mysql_numfields($result=NULL) { return pdo_numfields(func_get_args()); }
   function mysql_numrows($result=NULL) { return pdo_numrows(func_get_args()); }
   function mysql_listdbs($link_identifier=NULL) { return pdo_listdbs(func_get_args()); }
   function mysql_listtables($database_name=NULL, $link_identifier=NULL) { return pdo_listtables(func_get_args()); }
   function mysql_listfields($database_name=NULL, $table_name=NULL, $link_identifier=NULL) { return pdo_listfields(func_get_args()); }
   function mysql_db_name($result=NULL, $row=NULL, $field=NULL) { return pdo_db_name(func_get_args()); }
   function mysql_dbname($result=NULL, $row=NULL, $field=NULL) { return pdo_dbname(func_get_args()); }
   function mysql_tablename($result=NULL, $row=NULL, $field=NULL) { return pdo_tablename(func_get_args()); }
   function mysql_table_name($result=NULL, $row=NULL, $field=NULL) { return pdo_table_name(func_get_args()); }

   // and the few constants
   define("MYSQL_ASSOC", 1);
   define("MYSQL_NUM", 2);
   define("MYSQL_BOTH", 3);
   define("MYSQL_CLIENT_COMPRESS", 32);
   define("MYSQL_CLIENT_SSL", 2048);
   define("MYSQL_CLIENT_INTERACTIVE", 1024);
   define("MYSQL_CLIENT_IGNORE_SPACE", 256);
                            
}


?>