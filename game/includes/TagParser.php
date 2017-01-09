<?php
/**
 * =======================================================================
 *        Main phpfile for the tag parser...
 * Author   : Bart Meuris
 * Startdate: 23/09/2002
 * File     : Parser.php
 * 
 * 
 * VERSION:
 *     1.02:
 *     		Debug fixed - didn't test this very well - missed 2 function calls that were not
 * 			corrected when making the whole bunch object-oriented (= cleaner :) )
 *     1.01:
 *     		Huge bugfix in main parser, normal HTML could be used in some cases.
 *     1.0:
 *     		First release, feature freeze atm, let's work on other stuff for some time :)
 * 
 * 23/09/2002
 *     - implemented the scantag function
 *     - started (and almost finished) the main parser
 *     - started the fixurls function (still verrrrry buggy)
 *     - added a lot of tags
 * 24/09/2002:
 *     - fixed some bugs in the parser
 *     - added the [ign][/ign], [code][/code] and [h][/h] tag.
 *     - implemented single-tag support (tags without a [/tag]) - forgot this a bit...
 *     - implemented the fixurls and smileys system.
 *     - improved and fixed the fixurls function
 *     - split configuration + documented some things :)
 * 25/09/2002
 *     - Fixed a bug with recursive tagusage of the same tag - rewrote the closing tag scanner
 *     - Documented a lot in the testcode sample
 *     - Added a maximum recursive level and a level counter.
 *     - Added a parse-tree dumpout
 *     - Implemented smiley things
 * 01/10/2002
 *     - Added check when registering a tag if the parse function that was given exists.
 *     - Separated the [img] tag, and added automatic resizing/linking to it. Also changed the 
 *       usage of this tag.
 *     - Prepared some thing to be able to pass the level and tagstack to the user parser functions.
 *     - The [url] tag now opens in a new window when the link starts with http://
 *     - bugfix for automatic url recognition for files created in pc format ("\r\n" line termination)
 * 14/10/2002
 *     - Documented the "public" functions.
 *     - Updated the TODO
 *     - Recursive parselevel as well as a tagstack is provided to tagparse functions.
 *     - Added ability to hide unknown and/or unexpected tags.
 *     - Added the unique_id parameter to the parseTags function. This can be handy for Anchor tags and linking
 *       inside posts or articles when multiple are showed on one page. This id is then stored in the
 *       $PARSE_UNIQUE_ID variable, accessible to all functions. If none is given, a random MD5 string
 *       will be generated.
 * 15/10/2002
 *     - the define "PARSE_ENABLE_FIX_URLS" can now be true, false or a string. It it is a string equal
 *       to a known tag, the function associated with that tag is used to render the url, otherwise
 *       the default internal link generator is used.
 *     - Added some statistics and let the [h][/h] use them.
 *     - Removed the $PARSE_HEAD_NUMBER - it was dirty and had nothing to do with the parser
 *       It now uses the parser statistics to generate the numbers.
 * 31/10/2002
 *     - Made a class of it - should be cleaner to use ;)
 *       There are still some issues however concerning the calling interface and functions relying on globally
 *       registred variables while these are object members - should pass the parser object to the functions,
 *       and not pass the tagstack anymore - this can be kept as a member also. The level will probably also
 *       disapear in the calling convention of the functions, and also put as a member variable into the class.
 * 06/11/2002
 *     - If the PARSE_ENABLE_FIX_URLS is a string which is not known as a tag type, it is used as the CSS class
 *       for the link generated by the internal link generator.
 *     - Documentation is completely out of sync - started modifying it to comply with the object-oriented
 *       implementation/changes.
 * 07/11/2002		RELEASE 1.0
 *     - Cleaned up the code a bit.
 *     - Added the kill_newline parameter to the addtag function for tags that don't like newlines immediately
 *       after them, or that it fucks up the layout.
 * 05/12/2002		RELEASE 1.01
 *     - Fixed a _LARGE_ bug in the parser... In tags where the inner processing was disabled ([code], ...), 
 *       normal HTML could be inserted because this text was not run thru the renderText function which performs 
 *       an htmlspecialchars on this text. Fixed now..
 * 24/12/2002		RELEASE 1.02
 * 	- Debug fixed - didn't test this very well - missed 2 function calls that were not
 * 	  corrected when making the whole bunch object-oriented (= cleaner :) )
 *     - Also added a 'post.php' sample in the releasedir in samples/ for the usage of the
 * 	  example configuration/implementation of 'SampleParser.php'.
 * 31-12-02
 *      - Bugfix: "large" bug from 05/12/2002 fixed again... Code was run thru rendertext before the tag was 
 *        rendered in tags that had inner processing turned off - resulting in some unexpected things when
 *        processing text ( < became &lt; before it was run thru the render function - which was probably not 
 *        expecting it) - htmlspecialchars treatment is now the business and responsability of the custom 
 *        tagtreatement function, and not of the interpreter anymore...
 *      - Modified the fixUrls code so it recognizes www.blabla.com alike urls
 * 01-01-03
 *      - Fixed the dumpTree function - a parameter was passed by reference and modified -> not good :p
 *      - Fixed the FIX_URL_ENDS variable.. \n and \r endings were between single quotes instead of double quotes...
 * 
 * 
 * TODO:
 * - ? Implement tagcode correction - not that hard normally and could be very 
 *      usefull for "one shot" tags, for example inserting last edit date...
 * - ? Implement a "round" word wrapper (no cutting of words) ?
 * =======================================================================
 */
// Check that the script is not being accessed directly
if ( !defined('PROMATHIUS') )
{
	die("Hacking attempt");
}

class TagParser {
	/**
	 * TagParser V1.0
	 */

	var $PARSE_UNIQUE_ID; // Unique id for this text
	var $PARSE_STATISTICS; // Statistics variable
	var $PARSE_SMILEYS; // Contains the smileys to be processed
	var $PARSE_TAGS; // Contains the registred tags and taghandlers
	var $PARSE_NO_URL; // Keeps the tags that shouldn't fix url's inside
	var $PARSE_NO_SMILEY; // Keeps the tags that shouldn't process smileys inside
	var $PARSE_ENABLE_SMILEYS; // SETTING: Enable smiley processing?
	var $PARSE_SMILEY_WIDTH; // SETTING: Fixed width smileys
	var $PARSE_SMILEY_HEIGHT; // SETTING: Fixed height smileys
	var $PARSE_ENABLE_FIX_URLS; // SETTING: Enable auto-url fixing...
	var $PARSE_HIDE_UNEXPECTED_TAGS; // SETTING: hides unexpected tags
	var $PARSE_HIDE_UNKNOWN_TAGS; // SETTING: hides unknown tags
	var $PARSE_DUMP_TREE; // SETTING: DEBUG tree dump
	var $PARSE_DUMP_TREE_RECURSE_CALL; // SETTING: DEBUG recursive call tree dump
	var $PARSE_MAX_RECURSIVE_LEVELS; // SETTING: Maximum levels of recursion
	var $FIX_URL_ENDS; // Array that contains the ending strings of an url.
	var $FIX_URL_STARTS; // Array that contains the starting strings of an url.
	var $tagstack;
	var $level; 
	// Constructor...
	function TagParser()
	{ 
		// Set the default parameters
		$this->PARSE_ENABLE_SMILEYS = true;
		$this->PARSE_HIDE_UNEXPECTED_TAGS = true;
		$this->PARSE_HIDE_UNKNOWN_TAGS = false;
		$this->PARSE_DUMP_TREE = false;
		$this->PARSE_DUMP_TREE_RECURSE_CALL = false;
		$this->PARSE_MAX_RECURSIVE_LEVELS = 20;
		$this->PARSE_ENABLE_FIX_URLS = true;
		unset($this->PARSE_SMILEY_WIDTH);
		unset($this->PARSE_SMILEY_HEIGHT); 
		// Search for the following url types
		$this->FIX_URL_STARTS = array('http://', 'https://', 'ftp://', 'www.', 'aim://'); 
		// Strings that "end" an url - here are some tricky ones involved ;)
		$this->FIX_URL_ENDS = array(' ', "\r", "\n", "\t", ',', '. ', ".\n", ".\r", '(', ')', '[', ']', ';' . '<', '>', '"', "\'", '&quot;', '&nbsp;'); 
		// Kill all tags (initializes some things also
		$this->killTag();
	} 
	/**
	 */
	/**
	 */
	/**
	 * PUBLIC FUNCTIONS
	 */
	/**
	 */
	/**
	 * function parseTags($text, $unique_id)
	 */
	/**
	 * function addSmiley($smiley, $image)
	 */
	/**
	 * function addTag($name, $fnc, $innertags, $closetag, $fixurl, $smileys)
	 */
	/**
	 * function killTag($name)
	 */
	/**
	 */
	/**
	 */
	/**
	 * The actual function that should be used to parse a text with tags.
	 * 
	 * @param  $text The text which contains the tagcodes and smileys that should be parsed to HTML.
	 * @return the HTML code generated for the given text.
	 */
	function parseTags($text, $unique_id = null)
	{ 
		// Initialize statistics
		unset ($this->PARSE_STATISTICS);
		$this->PARSE_STATISTICS = array('tags' => array(), 'levels' => array(), 'global' => 0);

		if ($unique_id == null) {
			$this->PARSE_UNIQUE_ID = crypt(uniqid(rand(), 1));
		} else $this->PARSE_UNIQUE_ID = $unique_id;

		$this->tagstack = array();
		$this->level = 1;
		$parsedtext = nl2br($this->parse($text));

		unset($this->PARSE_UNIQUE_ID);
		return $parsedtext;
	} 

	/**
	 * Add a smiley to be parsed.
	 * 
	 * @param  $smiley The smiley "code" that should be replaced with the image.
	 *                    This will also be used for the "ALT" parameter for HTML the image tag.
	 * @param  $image The image to use for the smiley.
	 */
	function addSmiley($smiley, $image)
	{
		$this->PARSE_SMILEYS[$smiley] = $image;
	} 

	/**
	 * Add a tag to be parsed. The parameters completely control how a certain tag will react and be processed.
	 * 
	 * @param  $name The name of the tag. This is the actual text between the [] brackets.
	 *                    i.e.: [tag] - the name is "tag" here.
	 * @param  $fnc The function that has to be called to process the tag. Check the default sample 
	 *                    configuration for more information about this funcion's parameters and usage.
	 *                    If you want to call an method in an object, user an array with as element 0 the object,
	 *                    and as element 1 the methodname to call (i.e.: array($myobject, "methodInMyObject").
	 * @param  $innertags -- OPTIONAL - default: true
	 *                    Boolean value that tells the parser wether tags "inside" this tag should be
	 *                    processed or not.
	 * @param  $closetag -- OPTIONAL - default: true
	 *                    Boolean value that tells the parser if it should expect a "closing" tag for this tagtype,
	 *                    this is, expect a [/sampletag] for the [sampletag] tag.
	 *                    This can be usefull for ie. custom tags that could accept parameters and that should be
	 *                    replaced by for example a date/timestamp or smth else.
	 * @param  $fixurl -- OPTIONAL - default: true
	 *                    Boolean value that tells the parser wether it should "fix" urls inside this tag - this is,
	 *                    make links of the urls it can find.
	 * @param  $smileys -- OPTIONAL - default: true
	 *                    Boolean value that tells the parser if it should process the smileys inside this tag.
	 * @param  $kill_newline -- OPTIONAL - default: false
	 *                    If set to true, a newline following the tag will be eliminated in the parsed text.
	 */
	function addTag($name, $fnc, $innertags = true, $closetag = true, $fixurl = true, $smileys = true, $kill_newline = false)
	{
		/**
		 * // Old method - doesn't check if it is a method in an object we have to call...
		 * if (!function_exists($fnc) && ) {
		 * die('<font color=#FF0000><b>' . __FILE__ . "</b>: ERROR REGISTERING TAG: <i>The tagparser function '$fnc' does not exist!!!</i></font>");
		 * }
		 */
		if (is_string($fnc)) {
			if (method_exists($this, $fnc)) {
				// Method exists in current class.
				$tfnc[0] = &$this;
				$tfnc[1] = $fnc;
				$fnc = &$tfnc;
			} else if (!function_exists($fnc)) {
				die('<font color=#FF0000><b>' . __FILE__ . "</b>: ERROR REGISTERING TAG: <i>The tagparser function or method '$fnc' does not exist!!!</i></font>");
			} 
		} else if (!(is_array($fnc) && (is_object($fnc[0])) && method_exists($fnc[0], $fnc[1]))) {
			die('<font color=#FF0000><b>' . __FILE__ . "</b>: ERROR REGISTERING TAG: <i>The tagparser function given is not a String, or not an array containing a valid object and method!!!</i></font>");
		} 
		$this->PARSE_TAGS[$name]['fnc'] = $fnc;
		$this->PARSE_TAGS[$name]['inner'] = $innertags;
		$this->PARSE_TAGS[$name]['close'] = $closetag;
		if (!$fixurl) $this->PARSE_NO_URL[$name] = true;
		if (!$smileys) $this->PARSE_NO_SMILEY[$name] = true;
		if ($kill_newline) $this->PARSE_KILL_NL[$name] = true;
	} 

	/**
	 * Function that removes a certain tag, or when no parameters or null is given, it removes all tags.
	 * 
	 * @param  $name -- OPTIONAL - default: null
	 *                    The name of the tag to remove. if null, all tags are removed.
	 */
	function killTag($name = null)
	{
		if ($name == null) {
			unset($this->PARSE_TAGS);
			unset($this->PARSE_NO_URL);
			unset($this->PARSE_NO_SMILEY);
		} else {
			unset($this->PARSE_TAGS[$name]);
			unset($this->PARSE_NO_SMILEY[$name]);
			unset($this->PARSE_NO_URL[$name]);
		} 
	} 

	/**
	 */
	/**
	 */
	/**
	 * PRIVATE FUNCTIONS
	 */
	/**
	 */
	/**
	 * These Functions should not be used or modified by anyone.
	 */
	/**
	 * These are the internal parser functions.
	 */
	/**
	 */
	/**
	 */
	// Calls the function registred to a certain tag with the require_onced parameters
	// put this function inline where used to optimize a bit...
	function renderTag($name, $params, $text)
	{
		return call_user_func($this->PARSE_TAGS[$name]['fnc'], $name, $params, $text, &$this);
	} 
	// Utility that finds the first occurance of one of the strings provided from a certain offset
	function findNextString($text, $off, $searchstrings)
	{
		$minp = false;
		foreach($searchstrings as $tstr) {
			$toff = strpos($text, $tstr, $off);
			if (($toff !== false) &&
					(($minp === false) || ($toff < $minp))) {
				$minp = $toff;
				$tf = $tstr; 
				// $text = substr($text, 0, $minp);
			} 
		} 
		return $minp;
	} 
	// Utility that cuts a string to a certain length if possible and adds a string after it if it had to be cut...
	function str_cut($str, $length = 100, $fill = "...")
	{
		if (strlen($str) > $length) {
			$length = $length - strlen($fill);
			$str = substr($str, 0, $length) . $fill;
		} 
		return $str;
	} 

	function renderSmileys($text, $pass, $render)
	{
		if (($render > 0) || ($this->PARSE_ENABLE_SMILEYS !== true) || (!isSet($this->PARSE_SMILEYS))) return $text;
		$width = (isSet($this->PARSE_SMILEY_WIDTH) && is_integer($this->PARSE_SMILEY_WIDTH)) ? ' width=' . $this->PARSE_SMILEY_WIDTH : '';
		$height = (isSet($this->PARSE_SMILEY_HEIGHT) && is_integer($this->PARSE_SMILEY_HEIGHT)) ? ' height=' . $this->PARSE_SMILEY_HEIGHT : '';
		foreach ($this->PARSE_SMILEYS as $smiley => $image) {
			// rendering has to be done in 2 passes :-/
			// otherwise it conflicts with the special html characters like &nbsp;
			// $replstr = $image; // The easyest sollution - but memory intensive
			$replstr = '_' . strtr($smiley, ':;)(', '#*%') . '_'; // somewhat harder sollution - cpu intensive..
			if ($pass === 1) {
				$text = str_replace($smiley, $replstr, $text); 
				// echo "$text $smiley $replstr $text";
			} else {
				// echo $text;
				$text = str_replace($replstr, "<img src=\"$image\" border=0 alt=\"$smiley\"$width$height>", $text);
			} 
		} 
		return $text;
	} 
	// Function that searches and fixes url's
	function fixUrls($text, $render)
	{
		if (($render > 0) || (!isSet($this->PARSE_ENABLE_FIX_URLS)) || ($this->PARSE_ENABLE_FIX_URLS === false))
			return $text;

		if (is_string($this->PARSE_ENABLE_FIX_URLS)) {
			if (!isSet($this->PARSE_TAGS[$this->PARSE_ENABLE_FIX_URLS])) {
				$URLCLASS = " class='$this->PARSE_ENABLE_FIX_URLS'";
				$parse_internal = true;
			} else {
				$parse_internal = false;
			} 
		} else $parse_internal = true;

		$off = 0;
		$rettext = '';
		do {
			if (($lpos = $this->findNextString($text, $off, $this->FIX_URL_STARTS)) !== false) {
				$rettext .= substr($text, $off, $lpos - $off);
				if (($eupos = $this->findNextString($text, $lpos, $this->FIX_URL_ENDS)) === false) {
					$eupos = strlen($text);
				} 

				$url = substr($text, $lpos, $eupos - $lpos);
				$urlt = $url;
				if (strpos($url, '://') === false) $url = 'http://' . $url; 
				// echo "FOUND URL TO FIX: '$url' (text = $urlt) <br />\n";
				// echo "<pre>$text</pre><br />\n";
				if ($parse_internal)
					$rettext .= "<a href=\"$url\" target=\"_blank\"$URLCLASS>$urlt</a>";
				else 
					// $rettext .= call_user_func($this->PARSE_TAGS[$this->PARSE_ENABLE_FIX_URLS]['fnc'], $this->PARSE_ENABLE_FIX_URLS, null, $url, &$this);
					$rettext .= $this->renderTag($this->PARSE_ENABLE_FIX_URLS, $url, $urlt);
				$off = $eupos;
				if ($off >= strlen($text)) break;
			} else {
				if ($off == 0) return $text;
				else $rettext .= substr($text, $off);
				break;
			} 
		} while (true);
		return $rettext;
	} 
	function renderText($text, $rendersmiley, $renderurl, $inner = true)
	{ 
		// Render all things in the correct order...
		if ($inner)
			return $this->fixUrls($this->renderSmileys(htmlspecialchars($this->renderSmileys($text, 1, $rendersmiley)), 2, $rendersmiley), $renderurl);
		return $this->fixUrls($this->renderSmileys($this->renderSmileys($text, 1, $rendersmiley), 2, $rendersmiley), $renderurl);
	} 
	// /////////////////////////////////////////////////////////////////////////
	// Debug function - dumps a tree line
	function dumpTree($title, $str, $level, $prefix = '', $postfix = '')
	{
		if ($this->PARSE_DUMP_TREE === true)
			echo '<font face=Courier size=2>' . str_repeat('|__', $level) . '[' . str_replace(' ', '&nbsp;', str_pad($title, 20, ' ')) . " $level]&nbsp;&nbsp;&nbsp;&nbsp;</font>\"$prefix<i>" . $this->str_cut(htmlspecialchars($str)) . "</i>$postfix\"<br />\n";
	} 

	/**
	 * ===========================================
	 */
	// Recursive tagparser algorithm...
	function parse($text, $renderurls = 0, $rendersmileys = 0)
	{ 
		// Dump parsetree if enabled
		if ($this->PARSE_DUMP_TREE_RECURSE_CALL === true)
			$this->dumpTree('PARSING', $text, $this->level - 1, '<font color=#FF0000>', '</font>'); 
		// Maximum recursive level if specified...
		if ((is_integer($this->PARSE_MAX_RECURSIVE_LEVELS)) &&
				($this->level > $this->PARSE_MAX_RECURSIVE_LEVELS))
			return $text;

		$off = 0;
		$rettxt = '';
		$txtlen = strlen($text);
		do {
			$stag = $this->scanTag($text, $off);
			if (is_array($stag)) {
				list ($type, $name, $param, $position, $len) = $stag; 
				// print text before tag...
				$add = substr($text, $off, $position - $txtlen);
				$this->dumpTree('PARSE BEFORE', $add, $this->level);
				$rettxt .= $this->renderText($add, $rendersmileys, $renderurls);
				if (($type != P_CLOSE_TAG) && isSet($this->PARSE_TAGS[$name]) && ($this->PARSE_TAGS[$name]['close'])) {
					// Find the closing tag...
					// BUGFIX 25/09/2002: when using same tag recursivly, the first closetag was used...
					// Sollution: scan tags until you find a closing tag, and count the same tag openings,
					// and skip that many closing tags with the same name
					$tf = 0;
					$toff = $position + $len;
					do {
						if (($ttag = $this->scanTag($text, $toff)) !== false) {
							list ($ctype, $cname, $cparam, $cpos, $clen) = $ttag;
							if ($cname == $name) {
								if ($ctype == P_CLOSE_TAG) {
									if ($tf == 0) {
										$closet_pos = $cpos;
										$missingclosetag = false;
										break;
									} else $tf--;
								} else $tf++; // another open tag of the same type...
							} 
							// else - other tag inside this tag - don't look at it..
						} else {
							$closet_pos = strlen($text);
							$missingclosetag = true;
							break;
						} 
						$toff = $cpos + $clen;
					} while (true);

					$process_text = substr($text, $position + $len, $closet_pos - ($position + $len));
					$this->updateStats($name); 
					// 05-12-02 - Bugfix: Also text within tags with no 'inner' tag treatment thru
					// the renderText function, otherwise - real html could be used
					// inside these tags...
					// 31-12-02 - Bugfix: OOPPPSSSS - previous bugfix bugged :-/
					// Let the tag handle the htmlspecialchars() treatement itself,
					// otherwise output corruption will be the result...
					if (isSet($this->PARSE_NO_URL[$name])) $renderurls++;
					if (isSet($this->PARSE_NO_SMILEY[$name])) $rendersmileys++;
					if ($this->PARSE_TAGS[$name]['inner']) {
						$this->level++;
						array_push($this->tagstack, $name);
						$process_text = $this->parse($process_text, $renderurls, $rendersmileys);
						array_pop($this->tagstack);
						$this->level--;
					} 

					$tmptxt = $this->renderTag($name, $param, $process_text);
					if (!$this->PARSE_TAGS[$name]['inner'])
						$tmptxt = $this->renderText($tmptxt, $rendersmileys, $renderurls, false);

					$rettxt .= $tmptxt;
					if (isSet($this->PARSE_NO_URL[$name])) $renderurls--;
					if (isSet($this->PARSE_NO_SMILEY[$name])) $rendersmileys--;

					$off = $closet_pos;
					if (!$missingclosetag) $off += strlen("[/$name]"); 
					// Kill a newline after a closingtag...
					if ($this->PARSE_KILL_NL[$name]) {
						if ((($text[$off] == "\r") && ($text[$off + 1] == "\n")) ||
								(($text[$off] == "\n") && ($text[$off + 1] == "\r"))) $offa = 2;
						else if (($text[$off] == "\n") || ($text[$off] == "\r")) $offa = 1;
						else $offa = 0;
						while (($off + $offa >= strlen($text)) && ($offa > 0)) {
							$offa--;
						} 
						$off += $offa;
					} 
				} else if (($type != P_CLOSE_TAG) && isSet($this->PARSE_TAGS[$name]) && (!$this->PARSE_TAGS[$name]['close'])) {
					// Single tag - no closetag expected...
					unset($process_text);
					$this->dumpTree('PARSE SINGLE', $process_text, $this->level);
					$this->updateStats($name);
					$rettxt .= $this->renderTag($name, $param, $process_text);
					$off = $position + $len;
					if ($this->PARSE_KILL_NL[$name]) {
						if ((($text[$off] == "\r") && ($text[$off + 1] == "\n")) ||
								(($text[$off] == "\n") && ($text[$off + 1] == "\r"))) $offa = 2;
						else if (($text[$off] == "\n") || ($text[$off] == "\r")) $offa = 1;
						else $offa = 0;
						while (($off + $offa >= strlen($text)) && ($offa > 0)) {
							$offa--;
						} 
						$off += $offa;
					} 
				} else {
					// !! oops !!
					// Found an unknown tag or an unexpected closing tag - just print them out ?
					if (($this->PARSE_HIDE_UNEXPECTED_TAGS === true) && isSet($this->PARSE_TAGS[$name])) {
						// known but unexpected tag - and we should hide it.
						$this->dumpTree('SKIP UNEXP', $add, $this->level);
					} else if ($this->PARSE_HIDE_UNKNOWN_TAGS === true) {
						// unknown tag - and we should to hide it.
						$this->dumpTree('SKIP UNKNOWN', $add, $this->level);
					} else {
						$add = substr($text, $position, $len);
						$rettxt .= $this->renderText($add, $rendersmileys, $renderurls);
						$this->dumpTree('PARSE UNEXP/UNKNOWN', $add, $this->level);
					} 
					$off = $position + $len;
				} 
			} else {
				// No more tags inside this text...
				$add = substr($text, $off);
				$this->dumpTree('PARSE AFTER', $add, $this->level);
				$rettxt .= $this->renderText($add, $rendersmileys, $renderurls);
				break;
			} 
		} while (true);
		$this->resetLevelStats();
		return $rettxt;
	} 

	/**
	 * * Update the tag statistics
	 */
	function updateStats($name)
	{ 
		// update the tag level count statistics
		if (!isSet($this->PARSE_STATISTICS['levels'][$this->level][$name]))
			$this->PARSE_STATISTICS['levels'][$this->level][$name] = 1;
		else
			$this->PARSE_STATISTICS['levels'][$this->level][$name]++; 
		// update the tag global count statistics
		if (!isSet($this->PARSE_STATISTICS['tags'][$name]))
			$this->PARSE_STATISTICS['tags'][$name] = 1;
		else
			$this->PARSE_STATISTICS['tags'][$name]++;

		$this->PARSE_STATISTICS['global'];
	} 
	function resetLevelStats()
	{
		unset($this->PARSE_STATISTICS['levels'][$this->level]);
	} 

	/**
	 * * Scans for the next [tag] with an optional parameter or a closing tag.
	 */
	function scanTag($text, $offset = 0, $endpos = -1)
	{
		$txlen = strlen($text); 
		// if ($txlen < $offset) return false;
		if ($endpos === -1) $endpos = $txlen; 
		// get the end of the tag
		if ((($tcpos = strpos($text, ']', $offset)) === false) || ($tcpos > $endpos)) return false; 
		// Get the last tag start before the tagclosing char...
		$tpos = $offset;
		do {
			$tpos = strpos($text, '[', $tpos);
			if (($tpos !== false) && ($tpos < $tcpos)) {
				$tspos = $tpos;
				$tpos++;
			} else break;
		} while (true); 
		// Check if we found a valid tag start...
		if (!isSet($tspos)) return $this->scanTag($text, $tcpos + 1, $endpos);

		$offset = $tspos + 1;
		$position = $tspos; 
		// check if it is a closing tag...
		if ($text[$offset] == '/') {
			$offset++;
			$type = P_CLOSE_TAG;
			$tppos = $tcpos;
			$len = 3;
		} else {
			$type = P_OPEN_TAG; 
			// Check for parameters...
			$tppos = strpos($text, '=', $offset);
			if (($tppos !== false) && ($tppos < $tcpos)) {
				$param = substr($text, $tppos + 1 , $tcpos - ($tppos + 1));
				$len = 3 + strlen($param);
			} else {
				$tppos = $tcpos;
				$len = 2;
			} 
		} 
		$name = substr($text, $offset, $tppos - $offset);
		$len += strlen($name);
		return array($type, $name, $param, $position, $len);
	} 
} 
