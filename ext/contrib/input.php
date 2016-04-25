<?php
 /**
  * api: php
  * title: Input $_REQUEST wrappers
  * type: interface
  * description: provides sanitization by encapsulating request superglobals against raw access
  * version: 2.5
  * revision: $Id$
  * license: Public Domain
  * depends: php:filter, php >5.0, html_purifier
  * config: <const name="INPUT_DIRECT" type="multi" value="disallow" multi="disallow|raw|log" description="filter method for direct $_REQUEST[var] access" />
  *         <const name="INPUT_QUIET" type="bool" value="0" multi="0=report all|1=no notices|2=no warnings" description="suppress access and behaviour notices" />
  * throws: E_USER_NOTICE, E_USER_WARNING, OutOfBoundsException
  *
  * Using these object wrappers ensures a single entry point and verification
  * spot for all user data. They wrap all superglobals and filter HTTP input
  * through sanitizing methods. For auditing casual and unverified access.
  *
  * Standardly they wrap: $_REQUEST, $_GET, $_POST, $_SERVER, $_COOKIE
  * And provide convenient access to filtered data:
  *   $_GET->int("search_q")                      // method
  *   $_POST->text["commentfield"]                // array syntax
  *   $_REQUEST->text->ascii->strtolower["xyz"]   // filter chains
  *
  * Available filter methods are:
  *   ->int
  *   ->float
  *   ->boolean
  *   ->name
  *   ->id
  *   ->words
  *   ->text
  *   ->ascii
  *   ->nocontrol
  *   ->spaces
  *   ->q
  *   ->escape
  *   ->regex
  *   ->in_array
  *   ->html
  *   ->purify
  *   ->json
  *   ->length
  *   ->range
  *   ->default
  * And most useful methods of the php filter extension.
  *   ->email
  *   ->url ->uri ->http
  *   ->ip
  *   ->ipv4->public
  * PHP string modification functions:
  *   ->strtolower
  *   ->addslashes
  *   ->urlencode
  *   ->strip_tags
  *   ->htmlentities
  * Automatic filter chain handler:
  *   ->array
  * Fetch multiple variables at once:
  *   ->list["var1,key,name"]
  * Process multiple entries as array:
  *   ->multi->http_build_query["id,token"]
  * Possible but should be used very consciously:
  *   ->xss
  *   ->sql
  *   ->mysql
  *   ->log
  *   ->raw
  *
  * You can also pre-define a standard filter-chain for all following calls:
  *   $_GET->nocontrol->iconv->utf7->xss->always();
  *
  * Using $__rules[] a set of filter rules can be preset per variable name.
  *
  * Some filters are a mixture of sanitizing and validation. Basically
  * all can also be used independently of the superglobals with their
  * underscore name, $str = input::_text($str);
  *
  * For the superglobals it's also possible to count($_GET); or check with
  * just $_POST() if there are contents. (Use this in lieu of empty() test.)
  * There are also the three functions ->has ->no ->keys() for array lookup.
  *
  * Defining new filters can be done as methods, or since those are picked
  * up too, as plain global functions. It's also possible to assign function
  * aliases or attach closures:
  *   $_POST->_newfilter = function($s) { return modified($s); }
  * Note that the assignment name must have an underscore prefixed.
  *
  * --
  *
  * Input validation of course is no substitute for secure application logic,
  * parameterized sql and proper output encoding. But this methodology is a
  * good base and streamlines input data handling.
  *
  * Filter methods can be bypassed in any number of ways. There's no effort
  * made at prevention here. But it's recommended to simply use ->raw() when
  * needed - not all input can be filtered anyway. This way an audit handler
  * could always attach there - when desired.
  *
  * The goal is not to coerce, but encourage security via API *simplicity*.
  *
  */


/**
 * Handler name for direct $_REQUEST["array"] access.
 *
 *   "raw" = reports with warning,
 *   "disallow" = throws an exception
 */
defined("INPUT_DIRECT") or
define("INPUT_DIRECT", "raw");

/**
 * Notice suppression.
 *
 *   0 = report all,
 *   1 = no notices,
 *   2 = ignore non-existant filter
 */
defined("INPUT_QUIET") or
define("INPUT_QUIET", 0);


/**
 * @class Request variable input wrapper.
 *
 * The methods are defined with underscore prefix, but supposed to be used without
 * when invoked on the superglobal arrays:
 *
 * @method int   int[$field] converts input into integer
 * @method float float[$field]
 * @method name  name[$field] removes any non-alphanumeric characters
 * @method id    id[$field] alphanumeric string with dots
 * @method text  text[$field] textual data with interpunction
 *
 */  
class input implements ArrayAccess, Countable, Iterator {



    /**
     * Data filtering functions.
     *
     * These methods are usually not to be called directly. Instead use
     * $_GET->filtername["varname"] syntax without preceeding underscore
     * to access variable content.
     *
     * Categories: [e]=escape, [w]=whitelist, [b]=blacklist, [s]=sanitize, [v]=validate
     *
     */

    
    /**
     * [w]
     * Integer.
     *
     */
    function _int($data) {
        return (int)$data;
    }

    /**
     * [w]
     * Float.
     *
     */
    function _float($data) {
        return (float)$data;
    }
    
    /**
     * [w]
     * Alphanumeric strings.
     * (e.g. var names, letters and numbers, may contain international letters)
     *
     */
    function _name($data) {
        return preg_replace("/\W+/u", "", $data);
    }

    /**
     * [w]
     * Identifiers with underscores and dots,
     * like "xvar.1_2.x"
     *
     */
    function _id($data) {
        return preg_replace("#(^[^a-z_]+)|[^\w\d_.]+|([^\w_]$)#i", "", $data);
    }
    
    /**
     * [w]
     * Flow text with whitespace,
     * minimal interpunction allowed.
     *
     */
    function _words($data, $extra="") {
        return preg_replace("/[^\w\d\s,._\-+$extra]+/u", " ", strip_tags($data));
    }

    /**
     * [w]
     * Human-readable text with many special/interpunction characters:
     *  " and ' allowed, but no <, > or \
     *
     */
    function _text($data) {
        return preg_replace("/[^\w\d\s,._\-+?!;:\"\'\/`´()*=]+/u", " ", strip_tags($data));
    }
    
    /**
     * Acceptable filename characters.
     *
     * Alphanumerics and dot (but not as first character).
     * You should use `->basename` as primary filter anyway.
     *
     * @t whitelist
     *
     */
    function _filename($data) {
        return preg_replace("/^[.\s]|[^\w._+-]/", "_", $data);
    }
    
    #_datetime($name) { ... }  // as in HTML5
    #_session_id($name) { ... }  // e.g. verify last IP, stale session, user-agent
    #_
    
    /**
     * [b]
     * Filter non-ascii text out.
     * Does not remove control characters. (Similar to FILTER_FLAG_STRIP_HIGH.)
     * 
     */
    function _ascii($data) {
        return preg_replace("/[\\200-\\377]+/", "", $data);
    }

    /**
     * [b]
     * Remove control characters. (Similar to FILTER_FLAG_STRIP_LOW.)
     * 
     */
    function _nocontrol($data) {
        return preg_replace("/[\\000-\\010\\013\\014\\016-\\037\\177\\377]+/", "", $data); // all except \r \n \t
    }
    
    /**
     * [e] 
     * Exchange \r \n \t and \f \v \0 for normal spaces.
     * 
     */
    function _spaces($data) {
        return strtr($data, "\r\n\t\f\v\0", "      ");
    }

    /**
     * [x]
     * Regular expression filter.
     *
     * This either extracts (preg_match) data if you have a () capture group,
     * or functions as filter (pref_replace) if there's a [^ character class.
     * 
     */
    function _regex($data, $rx="", $match=1) {
        # validating
        if (strpos($rx, "(")) {
            if (preg_match($rx, $data, $result)) {
                return($result[$match]);
            }
        }
        # cropping
        elseif (strpos($rx, "[^")) {
            return preg_replace($rx, "", $data);
        }
    }
    
    /**
     * [w]
     * Miximum string length.
     *
     */
    function _length($data, $max=65535) {
        return substr($data, 0, $max);
    }
    
    /**
     * [w]
     * Range ensures value is between given minimum and maximum.
     * (Does not convert to integer itself.)
     *
     */
    function _range($data, $min, $max) {
        return ($data > $max) ? $max : (($data < $min) ? $min : $data);
    }

    /**
     * [b]
     * Fallback value for absent/falsy values.
     *
     */
    function _default($data, $default) {
        return empty($data) ? $default : $data;
    }

    /**
     * [w] 
     * Boolean recognizes 1 or 0 and textual values like "false" and "off" or "no".
     *
     */
    function _boolean($data) {
        if (empty($data) || $data==="0" || in_array(strtolower($data), array("false", "off", "no", "n", "wrong", "not", "-"))) {
            return false;
        }
        elseif ($data==="1" || in_array(strtolower($data), array("true", "on", "yes", "right", "y", "ok"))) {
            return true;
        }
        else return NULL;
    }

    /**
     * [w]
     * Ensures field is in array of allowed values.
     *
     * Works with arrays, but also string list. If you supply a "list,list,list" then
     * the comparison is case-insensitive.
     *
     */
    function _in_array($data, $array) {
        if (is_array($array) ? in_array($data, $array) : in_array(strtolower($data), explode(",", strtolower($array)))) {
            return $data;
        }
    }
    
    
    ###### filter_var() wrappers #########

    /**
     * [w]
     * Common case email syntax.
     *
     * (Close to RFC2822 but disallows square brackets or double quotes, no verification of TLDs,
     * doesn't restrict underscores in domain names, ignores i@an and anyone@localhost.)
     *
     */
    function _email($data, $validate=1) {
        $data = preg_replace("/[^\w!#$%&'*+/=?_`{|}~@.\[\]\-]/", "", $data);  // like filter_var
        if (!$validate || preg_match("/^(?!\.)[.\w!#$%&'*+/=?^_`{|}~-]+@(?:(?!-)[\w-]{2,}\.)+[\w]{2,6}$/i", trim($data))) {
            return $data;
        }
    }

    /**
     * [s] 
     * URI characters. (Actually IRI)
     *
     */
    function _uri($data) {
    # we should encode all-non chars
        return preg_replace("/[^-\w\d\$.+!*'(),{}\|\\~\^\[\]\`<>#%\";\/?:@&=]+/u", "", $data);  // same as SANITIZE_URL
    }
    
    /**
     * [w]
     * URL syntax
     *
     * This is an alias for FILTER_VALIDATE_URL. Beware that it lets a few unwanted schemes
     * through (file:// and mailto:) and what you'd consider misformed URLs (http://http://whatever).
     *
     */
    function _url($data) {
        return filter_var($data, FILTER_VALIDATE_URL);
    }

    /**
     * [v]
     * More restrictive HTTP/FTP url syntax.
     * No usernames allowed, no empty port, pathnames/qs/anchor are not checked.
     *
     # see also http://internet.ls-la.net/folklore/url-regexpr.html
     */
    function _http($data) {
        return preg_match("~
            (?(DEFINE)  (?<byte> 2[0-4]\d |25[0-5] |1\d\d |[1-9]?\d)  (?<ip>(?&byte)(\.(?&byte)){3})  (?<hex>[0-9a-fA-F]{1,4})  )
        ^   (?<proto>https?|ftps?)  ://
            # (?<user> \w+(:\w+)?@ )?
            ( (?<host>  (?:[a-z][a-z\d_\-\$]*\.?)+)
             |(?<ipv6>  \[     (?! [:\w]*:::          # ASSERT: no triple ::: colons
                                 |(:\w+){8}|(\w+:){8} # not more than 7 : colons
                                 |(\w*:){7}\w+\.      # not more than 6 : if there's a .
                                 | [:\w]*::[:\w]+:: ) # double :: colon must be unique
                               (?= [:\w]*::           # don't count if there is one ::
                                 |(\w+:){6}\w+[:.])   # else require six : and one . or :
              (?: :|(?&hex):)+((?&hex)|(?&ip)|:) \])  # MATCH: combinations of HEX : IP
             |(?<ipv4>  (?&ip) )     )
            (?<port> :\d{1,5} )?   # the integer isn't optional if port : colon present (unlike FILTER_VALIDATE_URL)
            (?<path> [/][^?#\s]* )?
            (?<qury> [?][^#\s]* )?
            (?<frgm> [#]\S* )?
        \z~ix", $data, $uu) ? $data : NULL;#(print_r($uu) ? $data : $data)
    }
    
/*
# http://phpcentral.com/208-url-validation-in-php.html

$urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?
[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?
(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
*/

    /**
     * [w] 
     * IP address
     *
     */
    function _ip($data) {
        return filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4|FILTER_FLAG_IPV6) ? $data : NULL;
    }

    /**
     * [w]
     * IPv4 address
     *
     */
    function _ipv4($data) {
        return filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $data : NULL;
    }
    
    /**
     * [w]
     * must be public IP address
     *
     */
    function _public($data) {
        return filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE) ? $data : NULL;
    }


    ###### format / representation #########
    
    
    /**
     * [v]
     * HTML5 datetime / datetime_local, date, time
     *
     */
    function _datetime($data) {
        return preg_match("/^\d{0}\d\d\d\d -(0\d|1[012]) -([012]\d|3[01]) T ([01]\d|2[0-3]) :[0-5]\d :[0-5]\d   (Z|[+-]\d\d:\d\d|\.\d\d)$/x", $data) ? $data : NULL;
    }
    function _date($data) {
        return preg_match("/^\d\d\d\d -(0\d|1[012]) -([012]\d|3[01])$/x", $data) ? $data : NULL;
    }
    function _time($data) {
        return preg_match("/^([01]\d|2[0-3]) :[0-5]\d :[0-5]\d  (\.\d\d)?$/x", $data) ? $data : NULL;
    }

    /**
     * [v]
     * HTML5 color
     *
     */
    function _color($data) {
        return preg_match("/^#[0-9A-F]{6}$/i", $data) ? strtoupper($data) : NULL;
    }


    /**
     * [w]
     * Telephone numbers (HTML5 <input type=tel> makes no constraints.)
     *
     */
    function _tel($data) {
        $data = preg_replace("#[/.\s]+#", "-", $data);
        if (preg_match("/^(\+|00)?(-?\d{2,}|\(\d+\)){2,}(-\d{2,}){,3}(\#\d+)?$/", $data)) {
            return trim($data, "-");
        }
    }


    /**
     * [v]
     * Verify minimum consistency (RFC4627 regex) and decode json data.
     *
     */
    function _json($data) { 
        if (!preg_match('/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/', preg_replace('/"(\\.|[^"\\])*"/g', '', $data))) {
            return json_decode($data);
        }
    }
    
    /**
     * [v]
     * XML tree.
     *
     */
    function _xml($data) {
        return simplexml_load_string($data);
    }

    /**
     * [w]
     * Clean html string via HTMLPurifier.
     *
     */
    function _purify($data) {
        $h = new HTMLPurifier;
        return $h->purify( $data );
    }

    /**
     * [e]
     * HTML escapes.
     *
     * This is actually an output filter. But might be useful to mirror input back into
     * form fields instantly `<input name=field value="<?= $_GET->html["field"] ?>">`
     *
     * @param $data string
     * @return string
     */
    function _html($data) {

        return htmlspecialchars($data, ENT_QUOTES, "UTF-8", false);
    }

    /**
     * [e]
     * Escape all significant special chars.
     *
     */
    function _escape($data) {
        return preg_replace("/[\\\\\[\]\{\}\[\]\'\"\`\´\$\!\&\?\/\>\<\|\*\~\;\^]/", "\\$1", $data);
    }

    /**
     * [e]
     * Addslashes
     *
     */
    function _q($data) {
        return addslashes($data);
    }

    /**
     * [b]
     * Minimal XSS detection.
     * Attempts no cleaning, just bails if anything looks suspicious.
     *
     * If something is XSS contaminated, it's spam and not worth to process further.
     * WEAK filters are better than no filters, but you should ultimatively use ->html purifier instead.
     *
     */
    function _xss($data) {
        if (preg_match("/[<&>]/", $data)) {   // looks remotely like html
            $html = $data;
            
            // remove filler
            $html = preg_replace("/&#(\d);*/e", "ord('$1')", $html);   // escapes
            $html = preg_replace("/&#x(\w);*/e", "ord(dechex('$1'))", $html);   // escapes
            $html = preg_replace("/[\x00-\x20\"\'\`\´]/", "", $html);   //  whitespace + control characters, also any quotes
            $html .= preg_replace("#/\*[^<>]*\*/#", "", $html);   // in-JS obfuscation comments

            // alert patterns
            if (preg_match("#[<<]/*(\?import|applet|embed|object|script|style|!\[CDATA\[|title|body|link|meta|base|i?frame|frameset|i?layer)#iUu", $html, $uu)
             or preg_match("#[<>]\w[^>]*(\w{3,}[=:]+(javascript|ecmascript|vbscript|jscript|python|actionscript|livescript):)#iUu", $html, $uu)
             or preg_match("#[<>]\w[^>]*(on(mouse\w+|key\w+|focus\w*|blur|click|dblclick|reset|select|change|submit|load|unload|error|abort|drag|Abort|Activate|AfterPrint|AfterUpdate|BeforeActivate|BeforeCopy|BeforeCut|BeforeDeactivate|BeforeEditFocus|BeforePaste|BeforePrint|BeforeUnload|Begin|Blur|Bounce|CellChange|Change|Click|ContextMenu|ControlSelect|Copy|Cut|DataAvailable|DataSetChanged|DataSetComplete|DblClick|Deactivate|Drag|DragEnd|DragLeave|DragEnter|DragOver|DragDrop|Drop|End|Error|ErrorUpdate|FilterChange|Finish|Focus|FocusIn|FocusOut|Help|KeyDown|KeyPress|KeyUp|LayoutComplete|Load|LoseCapture|MediaComplete|MediaError|MouseDown|MouseEnter|MouseLeave|MouseMove|MouseOut|MouseOver|MouseUp|MouseWheel|Move|MoveEnd|MoveStart|OutOfSync|Paste|Pause|Progress|PropertyChange|ReadyStateChange|Repeat|Reset|Resize|ResizeEnd|ResizeStart|Resume|Reverse|RowsEnter|RowExit|RowDelete|RowInserted|Scroll|Seek|Select|SelectionChange|SelectStart|Start|Stop|SyncRestored|Submit|TimeError|TrackChange|Unload|URLFlip)[^<\w=>]*[=:]+)[^<>]{3,}#iUu", $html, $uu)
             or preg_match("#[<>]\w[^>]*(\w{3,}[=:]+(-moz-binding:))#iUu", $html, $uu)
             or preg_match("#[<>]\w[^>]*(style[=:]+[^<>]*(expression\(|behaviour:|script:))#iUu", $html, $uu))
            {
                $this->_log($data, "DETECTED XSS PATTERN ({$uu['1']}),");
                $data = "";
                die("input::_xss");
            }
        }
        return $data;
    }

    /**
     * [w]
     * Cleans utf-8 from invalid sequences and alternative representations.
     * (BEWARE: performance drain)
     *
     */
    function _iconv($data) {
        return iconv("UTF-8", "UTF-8//IGNORE", $data);
    }

    /**
     * [b]
     * Few dangerous UTF-7 sequences
     * (only necessary if output pages don't have a charset specified)
     *
     */
    function _utf7($data) {
        return preg_replace("/[+]A(D[w40]|C[IYQU])(-|$)?/", "", $data);;
    }

    /**
     * [e] 
     * Escape for concatenating data into sql query.
     * (suboptimal, use parameterized queries instead)
     *
     */
    function _sql($data) {
        INPUT_QUIET or trigger_error("SQL escaping of input variable '$this->__varname'.", E_USER_NOTICE);
        return db()->quote($data);  // global object
    }
    function _mysql($data) {
        INPUT_QUIET or trigger_error("SQL escaping of input variable '$this->__varname'.", E_USER_NOTICE);
        return mysql_real_escape_string($data);
    }
    



    ###### pseudo filters ##############


    /**
     * [x]
     * Unfiltered access should obviously be avoided. But it's not always possible,
     * so this method exists and will just trigger a notice in debug mode.
     *
     */
    function _raw($data) {
        INPUT_QUIET or trigger_error("Unfiltered input variable \${$this->__title}['{$this->__varname}'] accessed.", E_USER_NOTICE);
        return $data;
    }
    /**
     * [x]
     * Unfiltered access, but logs variable name and value.
     *
     */
    function _log($data, $reason="manual log") {
        syslog(LOG_NOTICE, "php7://input:_log@{$_SERVER['SERVER_NAME']} accessing \${$this->__title}['{$this->__varname}'] variable, $reason, content=" . substr($this->_id(json_encode($data)), 0, 48));
        return $data;
    }

    /**
     * [b]
     * Abort with fatal error. (Used as fallback for INPUT_DIRECT access.)
     *
     */
    function _disallow($data) {
        throw OutOfBoundsException("Direct \$_REQUEST[\"$this->__varname\"] is not allowed, add ->filter method, or change INPUT_DIRECT if needed.");
    }



   

    ######  implementation  ################################################




    /**
     * Array data from previous superglobal.
     *
     * (It's pointless to make this a priv/proteced attribute, as raw data could be accessed
     * in any number of ways. Not the least would be to just not use the input filters.)
     *
     */
    var $__vars = array();

    
    /**
     * Name of superarray this filter object wraps.
     * (e.g. "_GET" or "_SERVER")
     *
     */
    var $__title = "";


    /**
     * Currently accessed array key.
     *
     */
    var $__varname = "";


    /**
     * Amassed filter list.
     * Each ->method->chain name will be appended here. Gets automatically reset after
     * a succesful variable access.
     *
     * Each entry is in `array("methodname", array("param1", 2, 3))` format.
     *
     */
    var $__filter = array();  // filterchain method stack


    /**
     * Automatically appended filter list. (Simply combined with current `$__filter`s).
     *
     */
    var $__always = array();


    /**
     * Currently accessed array keys.
     *
     */
    var $__rules = array(     // pre-defined varname filters
        // "varname.." => array(  array("length",array(256), array("nocontrol",array())  ),
    );


    /**
     * Initialize object from
     *
     * @param array  one of $_REQUEST, $_GET or $_POST etc.
     */
    function __construct($_INPUT, $title="") {
        $this->__vars = $_INPUT;   // stripslashes on magic_quotes_gpc might go here, but we have no word if we actually receive a superglobal or if it wasn't already corrected
        $this->__title = $title;
    }

    
    /**
     * Sets default filter chain.
     * These are ALWAYS applied in CONJUNCTION to ->manually->specified->filters.
     *
     */
    function always() {
        $this->__always = $this->__filter;
        $this->__filter = array();
    }
    
    
    /**
     * Normalize array keys (to UPPER case), for e.g. $_SERVER vars.
     *
     */
    function key_case($case=CASE_UPPER) {
        $this->__vars = array_change_key_case($this->__vars, $case);
    }
    
    
    /**
     * Executes current filter or filter chain on given $varname.
     *
     */
    function filter($varname, $reset_filter_afterwards=NULL) {

        // direct/raw access can occour if invoked via offsetGet[] or filter() method called directly
        if (empty($this->__filter)) {
            $this->__filter[] = array(INPUT_DIRECT, array());

            // or apply a pre-defined filter chain
            if (isset($this->__rules[$varname])) {
                // must be in internal nested format
                $this->__filter = $this->rules[$varname];
            }
        }

        // retrieve value for selected input variable
        $this->__varname = $varname;
        if (isset($this->__vars[$varname])) {
            $data = $this->__vars[$varname];
        }
        elseif (count($this->__filter) and ("list" == $this->__filter[0][0])) {
            $data = NULL;    // do nothing, as values will be fetched by a multiplex handler
        }
        else {
            INPUT_QUIET or trigger_error("Undefined input variable \${$this->__title}['{$this->__varname}']", E_USER_NOTICE);   // undecorative
            $data = NULL;    // run through filters anyway (log)
        }
        
        // implicit ->array filter handling for lists (= if value is an array)
        $ARRAY = (ARRAY("array",(array)NULL));  # complex trigger token
        if (is_array($data) && !in_array($ARRAY, $this->__filter)) {
            array_unshift($this->__filter, array("array", array()));
        }
        
        // apply filters (we're building an ad-hoc merged array here, because ->apply works on the reference, and some filters expect ->__filter to contain the complete current list)
        $this->__filter = array_merge($this->__filter, $this->__always);
        $data = $this->apply($data, $this->__filter);
        
        // the Traversable array interface resets the filter list after each request, see ->current()
        if ($reset_filter_afterwards) {
            $this->__filter = $reset_filter_afterwards;
        }

        // done
        return $data;
    }


    /**
     * Runs list of filters on data. Uses either methods, bound closures, or global functions
     * if the filter name matches.
     *
     * It works on an array reference and with array_shift() because filters are allowed to
     * modify the list at runtime (->array requires to).
     *
     */
    function apply($data, &$filterchain) {
        while ($f = array_shift($filterchain)) {
            list($filtername, $args) = $f;
            
            // an override function name or closure was set
            if (isset($this->{"_$filtername"})) {
                $filtername = $this->{"_$filtername"};
            }
            // call _filter method
            if (is_string($filtername) && method_exists($this, "_$filtername")) {
                $data = call_user_func_array(array($this, "_$filtername"), array_merge(array($data), (array)$args));
            }
            // ordinary php function, or closure, or rebound method
            elseif (is_callable($filtername)) {
                $data = call_user_func($filtername, $data);
            }
            else {
                INPUT_QUIET>=2 or trigger_error("unknown filter '" . (is_scalar($filtername) ? $filtername : "closure") . "', falling back on wiping non-alpha characters from '{$this->__varname}'", E_USER_WARNING);
                $data = $this->_name($data);
            }
        }
        return $data;
    }


    /**
     * @multiplex
     *
     * List data / array value handling.
     *
     * This virtual filter hijacks the original filter chain, and applies it
     * to sub values.
     *
     */
    function _array($data) {

        // save + swap out the current filter chain
        list($multiplex, $this->__filter) = array($this->__filter, array());

        // iteratively apply original filter chain on each array entry
        $data = (array) $data;
        foreach (array_keys($data) as $i) {
            $chain = $multiplex;
            $data[$i] = $this->apply($data[$i], $chain);
        }

        return $data;
    }


    /**
     * @multiplex
     *
     * Grab a collection of input variables, names delimited by comma.
     * Implicitly makes it an ->_array() list.
     * The _array handler is implicitly used for indexed values. _list
     * and _multi can be used for associative arrays, given a key list.
     * 
     * @example  extract($_GET->list->text["name,title,date,email,comment"]);
     * @php5.4+  $_GET->list[['key1','key2','key3']];
     *
     * @bugs  hacky, improper way to intercept, fetches from $__vars[] directly,
     *        uses $__varname instead of $data, may prevent replays,
     *        main will trigger a notice anyway as VAR1,VAR2,.. is undefined
     *
     */
    function _list($keys, $pass_array=FALSE) {

        // get key list
        if (is_array($this->__varname)) {
            $keys = $this->__varname;
        }
        else {
            $keys = explode(",", $this->__varname);
        }

        // slice out named values from ->__vars
        $data = array_intersect_key($this->__vars, array_flip($keys));

        // chain to _array multiplex handler
        if (!$pass_array) {
            return $this->_array($data);
        }
        // process fetched list as array (for user-land functions like http_build_query)
        else {
            return $data;
        }
    }
    
    /**
     * Processes collection of input variables.
     * Passes list on as array to subsequent filters.
     *
     */
    function _multi($keys) {
        return $this->_list($keys, "process_as_array");
    }


    
    /**
     * @hide
     *
     * Ordinary method calls are captured here. Any ->method("name") will trigger
     * the filter and return variable data, just like ->method["name"] would. It
     * just allows to supply additional method parameters.
     *
     */
    function __call($filtername, $args) {  // can have arguments
        $this->__filter[] = array($filtername, array_slice($args, 1));
        return $this->filter($args[0]);
    }

    
    /**
     * @hide
     *
     * Wrapper to capture ->name->chain accesses.
     *
     */
    function __get($filtername) {
        //
        // we could do some heuristic chaining here,
        // if the last entry in the ->attrib->attrib list is not a valid method name,
        // but a valid varname, we should execute the filter chain rather than add.
        //
        $this->__filter[] = array($filtername, array());  // add filter to list
        return $this;  // fluent interface
    }
    


    /**
     * @hide ArrayAccess
     *
     * Normal $_ARRAY["name"] syntax access is redirected through the filter here.
     *
     */
    function offsetGet($varname) {
        // never chains
        return $this->filter($varname);
    }

    /**
     * @hide ArrayAccess
     *
     * Needed for commonplace isset($_POST["var"]) checks.
     *
     */
    function offsetExists($name) {
        return isset($this->__vars[$name]);
    }

    /**
     * @hide
     * Sets value in array. Note that it only works for array["xy"]= top-level
     * assignments. Subarrays are always retrieved by value (due to filtering)
     * and cannot be set item-wise.
     *
     * @discouraged
     * Manipulating the input array is indicative for state tampering and thus
     * throws a notice per default.
     *
     */
    function offsetSet($name, $value) {
        INPUT_QUIET or trigger_error("Manipulation of input variable \${$this->__title}['{$this->__varname}']", E_USER_NOTICE);
        $this->__vars[$name] = $value;
    }
    
    /**
     * @hide
     * Removes entry.
     *
     * @discouraged
     * Triggers a notice per default.
     *
     */
    function offsetUnset($name) {
        INPUT_QUIET or trigger_error("Removed input variable \${$this->__title}['{$this->__varname}']", E_USER_NOTICE);
        unset($this->__vars[$name]);
    }



    /**
     * Generic array features.
     * Due to being reserved words `isset` and `empty` need custom method names here:
     *
     *  ->has(isset)
     *  ->no(empty)
     *  ->keys()
     * 
     * Has No Keys seems easy to remember.
     *
     */

    /**
     * isset/array_key_exists check.
     *
     */
    function has($name) {
        return isset($this->__vars[$name]);
    }
    
    /**
     * empty() probing,
     * Tests if variable is absent or falsy.
     *
     */
    function no($name) {
        return empty($this->__vars[$name]);
    }
    
    /**
     * Returns list of all contained keys.
     *
     */
    function keys() {
        return array_keys($this->__vars);
    }


    /**
     * @hide Traversable
     *
     * Allows to loop over all array entries in a foreach loop. A supplied filterlist
     * is reapplied for each iteration.
     *
     * - Basically all calls are mirrored onto ->__vars` internal array pointer.
     * 
     */
    function current() {
        return $this->filter(key($this->__vars), /*reset_filter_afterwards=to what it was before*/$this->__filter);
    }
    function key() {
        return key($this->__vars);
    }
    function next() {
        return next($this->__vars);
    }
    function rewind() {
        return reset($this->__vars);
    }
    function valid() {
        if (key($this->__vars) !== NULL) {
            return TRUE;
        }
        else {
            // also reset the filter list after we're done traversing all entries
            $this->__filters=array();
            return FALSE;
        }
    }


    /**
     * @hide Countable
     * 
     */
    function count() {
        return count($this->__vars);
    }
    
    
    /**
     * @hide Allows testing variable presence with e.g. if ( $_POST() )
     *       Alternatively $_POST("var") is an alias to $_POST["var"].
     *
     */
    function __invoke($varname=NULL) {
    
        // treat it as variable access
        if (!is_null($varname)) {
            return $this->offsetGet($varname);
        }
        
        // do the count() call
        else {
            return $this->count();
        }
    }

}



/**
 * @autorun
 *
 */
$_SERVER = new input($_SERVER, "_SERVER");
$_REQUEST = new input($_REQUEST, "_REQUEST");
$_GET = new input($_GET, "_GET");
$_POST = new input($_POST, "_POST");
$_COOKIE = new input($_COOKIE, "_COOKIE");
#$_SESSION
#$_ENV
#$_FILES required a special handler

//          print_r(  $_SERVER->list->text[[USER,HOME]]  );



?>