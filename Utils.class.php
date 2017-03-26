<?php
/**
 * @author zangwenxue
 * @date 2014/2/17
 * @brief 通用方法类
 *  
 **/
class Utils
{
	/**
	 * check if the first arg starts with the second arg
	 *
	 * @param string $str		the string to search in
	 * @param string $needle	the string to be searched
	 * @return bool	true or false
	 * @author zhujt
	 **/
	public static function starts_with($str, $needle)
	{
		$pos = stripos($str, $needle);
		return $pos === 0;
	}

	/**
	 * check if the first arg ends with the second arg
	 *
	 * @param string $str		the string to search in
	 * @param string $needle	the string to be searched
	 * @return bool	true or false
	 * @author zhujt
	 **/
	public static function ends_with($str, $needle)
	{
		$pos = stripos($str, $needle);
		if( $pos === false ) {
			return false;
		}
		return ($pos + strlen($needle) == strlen($str));
	}

	public static function addslashes_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'addslashes_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::addslashes_recursive($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return addslashes($var);
		} else {
			return $var;
		}
	}

	/**
	 * undoes any magic quote slashing from an array, like the $_GET, $_POST, $_COOKIE
	 *
	 * @param array	$val	Array to be noslashing
	 * @return array The array with all of the values in it noslashed
	 * @author zhujt
	 **/
	public static function noslashes_recursive($val)
	{
		if (get_magic_quotes_gpc()) {
			$val = self::stripslashes_recursive($val);
		}
		return $val;
	}

	public static function stripslashes_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'stripslashes_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::stripslashes_recursive($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return stripslashes($var);
		} else {
			return $var;
		}
	}
	/**
	 * Convert string or array to requested character encoding
	 *
	 * @param mix $var	variable to be converted
	 * @param string $in_charset	The input charset.
	 * @param string $out_charset	The output charset
	 * @return mix	The array with all of the values in it noslashed
	 * @see http://cn2.php.net/manual/en/function.iconv.php
	 * @author zhujt
	 **/
	public static function iconv_recursive($var, $in_charset = 'UTF-8', $out_charset = 'GBK')
	{
		if (is_array($var)) {
			$rvar = array();
			foreach ($var as $key => $val) {
				$rvar[$key] = self::iconv_recursive($val, $in_charset, $out_charset);
			}
			return $rvar;
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::iconv_recursive($val, $in_charset, $out_charset);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return iconv($in_charset, $out_charset, $var);
		} else {
			return $var;
		}
	}

	/**
	 * Check if the text is gbk encoding
	 *
	 * @param string $str	text to be check
	 * @return bool
	 * @author zhujt
	 **/
	public static function is_gbk($str)
	{
		return preg_match('%^(?:[\x81-\xFE]([\x40-\x7E]|[\x80-\xFE]))*$%xs', $str);
	}

	/**
	 * Check if the text is utf8 encoding
	 *
	 * @param string $str	text to be check
	 * @return bool Returns true if input string is utf8, or false otherwise
	 * @author zhujt
	 **/
	public static function is_utf8($str)
	{
		return preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]'.	// ASCII
			'| [\xC2-\xDF][\x80-\xBF]'.				//non-overlong 2-byte
			'| \xE0[\xA0-\xBF][\x80-\xBF]'.			//excluding overlongs
			'| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.	//straight 3-byte
			'| \xED[\x80-\x9F][\x80-\xBF]'.			//excluding surrogates
			'| \xF0[\x90-\xBF][\x80-\xBF]{2}'.		//planes 1-3
			'| [\xF1-\xF3][\x80-\xBF]{3}'.			//planes 4-15
			'| \xF4[\x80-\x8F][\x80-\xBF]{2}'.		//plane 16
			')*$%xs', $str);
	}

	public static function txt2html($text)
	{
		return htmlspecialchars($text, ENT_QUOTES, 'GB2312');
	}

	/**
	 * Escapes text to make it safe to display in html.
	 * FE may use it in Javascript, we also escape the QUOTES
	 *
	 * @param string $str	text to be escaped
	 * @return string	escaped string in gbk
	 * @author zhujt
	 **/
	public static function escape_html_entities($str)
	{
		return htmlspecialchars($str, ENT_QUOTES, 'GB2312');
	}

	/**
	 * Escapes text to make it safe to use with Javascript
	 *
	 * It is usable as, e.g.:
	 *  echo '<script>alert(\'begin'.escape_js_quotes($mid_part).'end\');</script>';
	 * OR
	 *  echo '<tag onclick="alert(\'begin'.escape_js_quotes($mid_part).'end\');">';
	 * Notice that this function happily works in both cases; i.e. you don't need:
	 *  echo '<tag onclick="alert(\'begin'.txt2html_old(escape_js_quotes($mid_part)).'end\');">';
	 * That would also work but is not necessary.
	 *
	 * @param string $str	text to be escaped
	 * @param bool $quotes	whether should wrap in quotes
	 * @return string
	 * @author zhujt
	 **/
	public static function escape_js_quotes($str, $quotes = false)
	{
		$str = strtr($str, array('\\'	=> '\\\\',
			"\n"	=> '\\n',
			"\r"	=> '\\r',
			'"'	=> '\\x22',
			'\''	=> '\\\'',
			'<'	=> '\\x3c',
			'>'	=> '\\x3e',
			'&'	=> '\\x26'));

		return $quotes ? '"'. $str . '"' : $str;
	}

	public static function escape_js_in_quotes($str, $quotes = false)
	{
		$str = strtr($str, array('\\"'	=> '\\&quot;',
			'"'	=> '\'',
			'\''	=> '\\\'',
		));

		return $quotes ? '"'. $str . '"' : $str;
	}

	/**
	 * Redirect to the specified page
	 *
	 * @param string $url	the specified page's url
	 * @param bool $top_redirect	Whether need to redirect the top page frame
	 * @author zhujt
	 **/
	public static function redirect($url, $top_redirect = true)
	{
		header('Location: ' . $url);
		exit();
	}

	/**
	 * Get current page's real url
	 * 
	 * @return string
	 * @author zhujt
	 **/
	public static function current_url()
	{
		$scheme = 'http';
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$scheme = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
		} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$scheme = 'https';
		}

		return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Whether current request is https request
	 * 
	 * @return bool
	 * @author zhujt
	 */
	public static function is_https_request()
	{
		$scheme = 'http';
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$scheme = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
		} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$scheme = 'https';
		}
		return ($scheme == 'https');
	}

	/**
	 * Remove specified params from the query parameters of the url
	 * 
	 * @param string $url
	 * @param array|string $params
	 * @return string
	 */
	public static function remove_queries_from_url($url, $params)
	{
		if (is_string($params)) {
			$params = explode(',', $params);
		}

		$parts = parse_url($url);
		if ($parts === false || empty($parts['query'])) {
			return $url;
		}

		$get = array();
		parse_str($parts['query'], $get);
		foreach ($params as $key) {
			unset($get[$key]);
		}

		$url = $parts['scheme'] . '://' . $parts['host'];
		if (isset($parts['port'])) {
			$url .= ':' . $parts['host'];
		}

		$url .= $parts['path'];
		if (!empty($get)) {
			$url .= '?' . http_build_query($get);
		}

		if (!empty($parts['fragment'])) {
			$url .= '#' . $parts['fragment'];
		}

		return $url;
	}

	/**
	 * Converts charactors in the string to upper case
	 *
	 * @param string $str string to be convert
	 * @return string
	 * @author zhujt
	 **/
	public static function strtoupper($str)
	{
		$uppers =
			array('A','B','C','D','E','F','G','H','I','J','K','L','M','N',
				'O', 'P','Q','R','S','T','U','V','W','X','Y','Z');
		$lowers =
			array('a','b','c','d','e','f','g','h','i','j','k','l','m','n',
				'o','p','q','r','s','t','u','v','w','x','y','z');
		return str_replace($lowers, $uppers, $str);
	}

	/**
	 * Converts charactors in the string to lower case
	 *
	 * @param string $str	string to be convert
	 * @return string
	 * @author zhujt
	 **/
	public static function strtolower($str)
	{
		$uppers =
			array('A','B','C','D','E','F','G','H','I','J','K','L','M','N',
				'O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$lowers =
			array('a','b','c','d','e','f','g','h','i','j','k','l','m','n',
				'o','p','q','r','s','t','u','v','w','x','y','z');
		return str_replace($uppers, $lowers, $str);
	}

	/**
	 * Urlencode a variable recursively, array keys and object property names
	 * will not be encoded, so you would better use ASCII to define the array
	 * key name or object property name.
	 *
	 * @param mixed $var
	 * @return  mixed, with the same variable type
	 * @author zhujt
	 **/
	public static function urlencode_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'urlencode_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::urlencode_recursive($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return urlencode($var);
		} else {
			return $var;
		}
	}

	/**
	 * Urldecode a variable recursively, array keys and object property
	 * names will not be decoded, so you would better use ASCII to define
	 * the array key name or object property name.
	 *
	 * @param mixed $var
	 * @return  mixed, with the same variable type
	 * @author zhujt
	 **/
	public static function urldecode_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'urldecode_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::urldecode_recursive($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return urldecode($var);
		} else {
			return $var;
		}
	}

	/**
	 * Encode a string according to the RFC3986
	 * @param string $s
	 * @return string
	 */
	public static function urlencode3986($var)
	{
		return str_replace('%7E', '~', rawurlencode($var));
	}

	/**
	 * Decode a string according to RFC3986.
	 * Also correctly decodes RFC1738 urls.
	 * @param string $s
	 */
	public static function urldecode3986($var)
	{
		return rawurldecode($var);
	}

	/**
	 * Urlencode a variable recursively according to the RFC3986, array keys
	 * and object property names will not be encoded, so you would better use
	 * ASCII to define the array key name or object property name.
	 *
	 * @param mixed $var
	 * @return  mixed, with the same variable type
	 * @author zhujt
	 **/
	public static function urlencode3986_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'urlencode3986_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::urlencode3986($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return str_replace('%7E', '~', rawurlencode($var));
		} else {
			return $var;
		}
	}

	/**
	 * Urldecode a variable recursively according to the RFC3986, array keys
	 * and object property names will not be decoded, so you would better use
	 * ASCII to define the array key name or object property name.
	 *
	 * @param mixed $var
	 * @return  mixed, with the same variable type
	 * @author zhujt
	 **/
	public static function urldecode3986_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'urldecode3986_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::urldecode3986_recursive($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return rawurldecode($var);
		} else {
			return $var;
		}
	}

	/**
	 * Base64_encode a variable recursively, array keys and object property
	 * names will not be encoded, so you would better use ASCII to define the
	 * array key name or object property name.
	 *
	 * @param mixed $var
	 * @return mixed, with the same variable type
	 * @author zhujt
	 **/
	public static function base64_encode_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'base64_encode_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::base64_encode_recursive($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return base64_encode($var);
		} else {
			return $var;
		}
	}

	/**
	 * Base64_decode a variable recursively, array keys and object property
	 * names will not be decoded, so you would better use ASCII to define the
	 * array key name or object property name.
	 *
	 * @param mixed $var
	 * @return mixed, with the same variable type
	 * @author zhujt
	 **/
	public static function base64_decode_recursive($var)
	{
		if (is_array($var)) {
			return array_map(array('Utils', 'base64_decode_recursive'), $var);
		} elseif (is_object($var)) {
			$rvar = null;
			foreach ($var as $key => $val) {
				$rvar->{$key} = self::base64_decode_recursive($val);
			}
			return $rvar;
		} elseif (is_string($var)) {
			return base64_decode($var);
		} else {
			return $var;
		}
	}

	/**
	 * Remove BOM string (0xEFBBBF in hex) for input string which is added
	 * by windows when create a UTF-8 file.
	 * 
	 * @param string $str
	 * @return string
	 * @author zhujt
	 */
	public static function remove_bom($str)
	{
		if (substr($str, 0, 3) === pack('CCC', 0xEF, 0xBB, 0xBF)) {
			$str = substr($str, 3);
		}
		return $str;
	}

	/**
	 * Generate a unique random key using the methodology
	 * recommend in php.net/uniqid
	 *
	 * @return string a unique random hex key
	 **/
	public static function generate_rand_key()
	{
		return md5(uniqid(mt_rand(), true));
	}

	public static function generate_rand_str($len = 32, $seed = '')
	{
		if (empty($seed)) {
			$seed = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ';
		}
		$seed_len = strlen($seed);
		$word = '';
		//随机种子更唯一
		mt_srand((double)microtime() * 1000000 * getmypid());
		for ($i = 0; $i < $len; ++$i) {
			$word .= $seed{mt_rand() % $seed_len};
		}
		return $word;
	}

	/**
	 * Send email by sendmail command
	 *
	 * @param string $from	mail sender
	 * @param string $to	mail receivers
	 * @param string $subject	subject of the mail
	 * @param string $content	content of the mail
	 * @param string $cc
	 * @return int result of sendmail command
	 * @author zhujt
	 **/
	public static function sendmail($from, $to, $subject, $content, $cc = null)
	{
		if (empty($from) || empty($to) || empty($subject) || empty($content)) {
			return false;
		}

		$mailContent = "To:$to\nFrom:$from\n";
		if (!empty($cc)) {
			$mailContent .= "Cc:$cc";
		}
		$mailContent .= "Subject:$subject\nContent-Type:text/html;charset=utf-8\n\n$content";

		$output = array();
		exec("echo -e '" . $mailContent . "' | /usr/sbin/sendmail -t", $output, $ret);

		return $ret;
	}

	/**
	 * Trim the right '/'s of an uri path, e.g. '/xxx//' will be sanitized to '/xxx'
	 *
	 * @param string $uri URI to be trim
	 * @return string sanitized uri
	 * @author zhujt
	 **/
	public static function sanitize_uri_path($uri)
	{
		$arrUri = explode('?', $uri);
		$arrUri = parse_url($arrUri[0]);
		$path = $arrUri['path'];

		$path = rtrim(trim($path), '/');
		if (!$path) {
			return '/';
		}
		return preg_replace('#/+#', '/', $path);
	}

	/**
	 * Check whether input url has http:// or https:// as its scheme,
	 * if hasn't, it will add http:// as its prefix
	 * @param string $url
	 * @return string
	 */
	public static function http_scheme_auto_complete($url)
	{
		$url = trim($url);
		if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
			$url = 'http://' . $url;
		}
		return $url;
	}

	/**
	 * Check whether the url is under allowed domains
	 * 
	 * @param string $url Url to be check
	 * @param array|string $allowed_domains domain list in index array or ',' seperated string
	 * @return bool
	 */
	public static function is_domain_allowed($url, $allowed_domains)
	{
		if (is_string($allowed_domains)) {
			$allowed_domains = explode(',', $allowed_domains);
		}

		$host = parse_url($url, PHP_URL_HOST);
		if (empty($host)) {
			return false;
		}

		foreach ($allowed_domains as $domain) {
			if (self::ends_with($host, $domain)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the two bytes are a chinese charactor
	 *
	 * @param char $lower_chr	lower bytes of the charactor
	 * @param char $higher_chr	higher bytes of the charactor
	 * @return bool Returns true if it's a chinese charactor, or false otherwise
	 * @author liaohuiqin
	 **/
	public static function is_cjk($lower_chr, $higher_chr)
	{
		if (($lower_chr >= 0xb0 && $lower_chr <= 0xf7 && $higher_chr >= 0xa1 && $higher_chr <= 0xfe) ||
			($lower_chr >= 0x81 && $lower_chr <= 0xa0 && $higher_chr >= 0x40 && $higher_chr<=0xfe) ||
			($lower_chr >= 0xaa && $lower_chr <= 0xfe && $higher_chr >= 0x40 && $higher_chr <=0xa0)) {
				return true;
			}
		return false;
	}

	/**
	 * 检查一个字符是否是gbk图形字符
	 *
	 * @param char $lower_chr	lower bytes of the charactor
	 * @param char $higher_chr	higher bytes of the charactor
	 * @return bool Returns true if it's a chinese graph charactor, or false otherwise
	 * @author liaohq
	 **/
	public static function is_gbk_graph($lower_chr, $higher_chr)
	{
		if (($lower_chr >= 0xa1 && $lower_chr <= 0xa9 && $higher_chr >= 0xa1 && $higher_chr <= 0xfe) ||
			($lower_chr >= 0xa8 && $lower_chr <= 0xa9 && $higher_chr >= 0x40 && $higher_chr <= 0xa0)) {
				return true;
			}
		return false;
	}

	/**
	 * 检查字符串中每个字符是否是gbk范围内可见字符，包括图形字符和汉字, 半个汉字将导致检查失败,
	 * ascii范围内不可见字符允许，默认$str是gbk字符串,如果是其他编码可能会失败
	 * 
	 * @param string $str string to be checked
	 * @return  bool 都是gbk可见字符则返回true，否则返回false
	 * @author liaohq
	 **/
	public static function  check_gbk_seen($str)
	{
		$len = strlen($str);
		$chr_value = 0;

		for ($i = 0; $i < $len; $i++) {
			$chr_value = ord($str[$i]);
			if ($chr_value < 0x80) {
				continue;
			} elseif ($chr_value === 0x80) {
				//欧元字符;
				return false;
			} else {
				if ($i + 1 >= $len) {
					//半个汉字;
					return false;
				}
				if (!self::is_cjk(ord($str[$i]), ord($str[$i + 1])) &&
					!self::is_gbk_graph(ord($str[$i]), ord($str[$i + 1]))) {
						return false;
					}
			}
			$i++;
		}
		return true;
	}

	/**
	 * 检查$str是否由汉字/字母/数字/下划线/.组成，默认$str是gbk编码
	 *
	 * @param string $str string to be checked
	 * @return  bool
	 * @author liaohq
	 **/
	public static function check_cjkalnum($str)
	{
		$len = strlen($str);
		$chr_value = 0;

		for ($i = 0; $i < $len; $i++) {
			$chr_value = ord($str[$i]);
			if ($chr_value < 0x80) {
				if (!ctype_alnum($str[$i]) && $str[$i] != '_' && $str[$i] != '.') {
					return false;
				}
			} elseif ($chr_value === 0x80) {
				//欧元字符;
				return false;
			} else {
				if ($i + 1 >= $len) {
					//半个汉字;
					return false;
				}
				if (!self::is_cjk(ord($str[$i]), ord($str[$i + 1]))) {
					return false;
				}
				$i++;
			}
		}
		return true;
	}

	/**
	 * 检查字符串是否是gbk汉字，默认字符串的编码格式是gbk
	 *
	 * @param string $str string to be checked
	 * @return  bool
	 * @author liaohq
	 **/
	public static function check_cjk($str)
	{
		$len = strlen($str);
		$chr_value = 0;

		for ($i = 0; $i < $len; $i++) {
			$chr_value = ord($str[$i]);
			if ($chr_value <= 0x80) {
				return false;
			} else {
				if ($i + 1 >= $len) {
					//半个汉字;
					return false;
				}
				if (!self::is_cjk(ord($str[$i]), ord($str[$i + 1]))) {
					return false;
				}
				$i++;
			}
		}
		return true;
	}

	/**
	 * check whether the url is safe
	 * 
	 * @param string $url	URL to be checked
	 * @return bool
	 * @author zhujt
	 **/
	public static function is_valid_url($url)
	{
		if (strlen($url) > 0) {
			if (!preg_match('/^https?:\/\/[^\s&<>#;,"\'\?]+(|#[^\s<>"\']*|\?[^\s<>"\']*)$/i',
				$url, $match)) {
					return false;
				}
		}
		return true;
	}

	/**
	 * check whether the email address is valid
	 * 
	 * @param string $email Email to be checked
	 * @return bool
	 * @author zhujt
	 **/
	public static function is_valid_email($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
		/*
		if (strlen($email) > 0) {
			if (!preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})$/i',
							$email, $match)) {
				return false;
			}
		}
		return true;
		 */
	}

	/**
	 * Check whether the email is in the specified whitelist domains
	 * @param string $email Email to be checked
	 * @param array|string $whitelist Domain list seperated by ',' or an index array
	 * @return bool
	 * @author zhujt
	 */
	public static function is_email_in_whitelist($email, $whitelist)
	{
		if (!self::is_valid_email($email)) {
			return false;
		}

		if (is_string($whitelist)) {
			$whitelist = explode(',', $whitelist);
		}

		list($user, $domain) = explode('@', $email);
		if (empty($domain)) {
			return false;
		}

		return in_array($domain, $whitelist);
	}

	/**
	 * Check whether it is a valid phone number
	 * 
	 * @param string $phone	Phone number to be checked
	 * @return bool
	 * @author zhujt
	 **/
	public static function is_valid_phone($phone)
	{
		if (strlen($phone) > 0) {
			if (!preg_match('/^([0-9]{11}|[0-9]{3,4}-[0-9]{7,8}(-[0-9]{2,5})?)$/i',
				$phone, $match)) {
					return false;
				}
			return true;
		}
		return false;
	}
	/**
	 * 验证日期
	 * @param  $date yyyy-mm-dd
	 *
	 * @return bool
	 *
	 * @param addTime 2014-03-12
	 * @param author  guanxiongbo
	 */
	public static function  check_date($date){
		
		$rule = '/^\d{4}\-\d{2}\-\d{2}$/';
		if(!preg_match($rule,$date,$result)){
			return false;
		}
		return true;
	}
	/**
	 * Check whether it is a valid ip list, each ip is delemited by ','
	 * 
	 * @param string $iplist Ip list string to be checked
	 * @return bool
	 * @author zhujt
	 **/
	public static function is_valid_iplist($iplist)
	{
		$iplist = trim($iplist);
		if (strlen($iplist) > 0) {
			if (!preg_match('/^(([0-9]{1,3}\.){3}[0-9]{1,3})(,(\s)*([0-9]{1,3}\.){3}[0-9]{1,3})*$/i',
				$iplist, $match)) {
					return false;
				}
		}
		return true;
	}

	/**
	 * Generate a 64 unsigned number signature.
	 *
	 * @param array	$params	params to be signatured
	 * @return int 64 unsigned number signature
	 **/
	public static function sign64($value) {
		$str = md5 ( $value, true );
		$high1 = unpack ( "@0/L", $str );
		$high2 = unpack ( "@4/L", $str );
		$high3 = unpack ( "@8/L", $str );
		$high4 = unpack ( "@12/L", $str );
		if(!isset($high1[1]) || !isset($high2[1]) || !isset($high3[1]) || !isset($high4[1]) ) {
			return false;
		}
		$sign1 = $high1 [1] + $high3 [1];
		$sign2 = $high2 [1] + $high4 [1];
		$sign = ($sign1 & 0xFFFFFFFF) | ($sign2 << 32);
		return sprintf ( "%u", $sign );
	}

	/**
	 * Generate a number mod result.
	 *
	 * @param int	$number	params to be mod
	 * @param int	$mod	params to mod
	 * @return int mod result of the number
	 **/
	public static function mod($number, $mod) {
		if(0 < intval($number)) {
			return $number%$mod;
		}
		$length = strlen($number);
		$left = 0;
		for($i = 0; $i < $length; $i++) {
			$digit = substr($number, $i, 1);
			$left = intval($left.$digit);
			if($left < $mod) {
				continue;
			}else if($left == $mod) {
				$left = 0;
				continue;
			}else{
				$left = $left%$mod;
			}
		}
		return $left;
	}

	public static function getHash($hashKey, $subTable) {
		if (! is_numeric ( $hashKey ) && ! is_string ( $hashKey )) {
			return false;

		}
		if (is_numeric ( $hashKey )) {
			$hash = $hashKey;
		} else {
			$str = strval ( $hashKey );
			$hash = self::getHashFromString ( $str );
		}
		if (intval ( $hash ) > 0) {
			$ret = $hash % $subTable;
		} else {
			$ret = Utils::mod ( $hash, $subTable );
		}
		return $ret;
	}

	public static function getHashFromString($str) {
		if (empty ( $str )) {
			return 0;
		}
		$h = 0;
		for($i = 0, $j = strlen ( $str ); $i < $j; $i = $i + 3) {
			$h = 5 * $h + ord ( $str [$i] );
		}
		return $h;
	}

	/**
	 * Check the array contains key or not.
	 *
	 * @param array	$arr_need	keys must exist
	 * @param array $arr_arg	array to check
	 * @return boolean true | false
	 **/
	static function check_exist_array($arr_need, $arr_arg) {
		$arr_diff = array_diff ( $arr_need, array_keys ( $arr_arg ) );
		if (! empty ( $arr_diff )) {
			return false;
		}
		return true;
	}

	/**
	 * Check the int input is valid or not.
	 *
	 * @param int $value	number value
	 * @param int $max max value to check
	 * @param int $min min value to check
	 * @param boolean $compare true to check max,false not to check max
	 * @return boolean true | false
	 **/
	static function check_int($value, $min = 0, $max = -1, $compare = true) {
		if(is_null($value)) {
			return false;
		}
		if(!is_numeric($value)) {
			return false;
		}
		if(intval($value) != $value) {
			return false;
		}
		if(true === $compare && $value < $min) {
			return false;
		}
		if(true === $compare && 0 <= $max && $max < $value) {
			return false;
		}
		
		return true;
	}

	/**
	 * Check the string input length is valid or not.
	 *
	 * @param int $value	string value
	 * @param int $max_length max value length to check
	 * @param int $min_length min value length to check
	 * @return boolean true | false
	 **/
	static function check_string($value, $max_length = NULL, $min_length = 1) {
		if(is_null($value)) {
			return false;
		}

		if(mb_strlen($value,"utf-8") < $min_length) {
			return false;
		}
		if(!is_null($max_length) && mb_strlen($value,"utf-8") > $max_length) {
			CLog::debug('string check , > max');
			return false;
		}
		
		return true;
	}

	//按字符串生成hash数值
	public static function hash_string($str)
	{   
		if (empty($str)) return 0;
		$h = 0;
		for ($i=0,$j=strlen($str); $i<$j; $i=$i+2)
		{   
			$h = 5*$h + ord($str[$i]);
		}   
		return $h; 
	}  

	public static function getErrorCode($ex) {
		$errcode = $ex->getMessage();
		if (0 < ($pos = strpos($errcode,' '))) {
			$errcode = substr($errcode, 0, $pos); 
		}   
		return $errcode;
	}
	/**
      * 字符串加密以及解密函数
      *
      * @param string $string 原文或者密文
      * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
      * @param string $key 密钥
      * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
      * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
      *
      * @example
      *
      *     $a = authcode('abc', 'ENCODE', 'key');
      *     $b = authcode($a, 'DECODE', 'key'); // $b(abc)
      *
      *     $a = authcode('abc', 'ENCODE', 'key', 3600);
      *     $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
      */
    public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

         $ckey_length = 4;    // 随机密钥长度 取值 0-32;
                     // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
                     // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
                     // 当此值为 0 时，则不产生随机密钥

         $key = md5($key ? $key : 'fhjk');
         $keya = md5(substr($key, 0, 16));
         $keyb = md5(substr($key, 16, 16));
         $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

         $cryptkey = $keya.md5($keya.$keyc);
         $key_length = strlen($cryptkey);

         $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
         $string_length = strlen($string);

         $result = '';
         $box = range(0, 255);

         $rndkey = array();
         for($i = 0; $i <= 255; $i++) {
             $rndkey[$i] = ord($cryptkey[$i % $key_length]);
         }

         for($j = $i = 0; $i < 256; $i++) {
             $j = ($j + $box[$i] + $rndkey[$i]) % 256;
             $tmp = $box[$i];
             $box[$i] = $box[$j];
             $box[$j] = $tmp;
         }

         for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
             $box[$a] = $box[$j];
            $box[$j] = $tmp;
             $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
         }

         if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
             } else {
                 return '';
             }
         } else {
             return $keyc.str_replace('=', '', base64_encode($result));
         }

     }
	 
	/**
	 * 字符截取 支持UTF8/GBK
	 * @param  $string 要截取的字符串
	 * @param  $length 截取长度
	 * @param  $dot	   后缀符
	 *
	 * @return $string
	 *
	 * @param Copy From phpcms/func by ChengBo
	 * @param addTime 2014-03-10
	 */
    public static function str_cut($string, $length, $dot = '...') {
		$strlen = strlen($string);
		if ($strlen <= $length)
			return $string;
		$string = str_replace(array(' ', '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵', ' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
		$strcut = '';
		$length = intval($length - strlen($dot) - $length / 3);
		$n = $tn = $noc = 0;
		while ($n < strlen($string)) {
			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n++;
				$noc++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			} elseif (224 <= $t && $t <= 239) {
				$tn = 3;
				$n += 3;
				$noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 2;
			} else {
				$n++;
			}
			if ($noc >= $length) {
				break;
			}
		}
		if ($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr($string, 0, $n);
		$strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);

		return $strcut . $dot;
	}


	//生成随机数
	public static function randomNumber($num='6'){
		for ($i=0; $i < $num; $i++) { 
		   $str = rand(0,9);
		   $newNum .= $str;
		}
		return  $newNum;
	}
	/**
	 * 验证用户名合法性
	 * @param  $username 要验证的用户名
	 *
	 * @return bool
	 *
	 * @param addTime 2014-03-12
	 * @param author  gaunxiongbo
	 */
	public static function  check_username($username){
		$username = trim($username);
		if(empty($username)){
			return false;
		}

        $rule = '/^[a-zA-Z][a-zA-Z0-9_]{3,13}$/';
        if(!preg_match($rule,$username)){
        	return false;
        } 
        return true;  
	}	
	/**
	 * 验证密码合法性
	 * @param  $password 密码
	 *
	 * @return bool
	 *
	 * @param addTime 2014-03-12
	 * @param author  gaunxiongbo
	 */
	public static function  check_password($password){
		$password = trim($password);
		if(empty($password)){
			return false;
		}

        $rule = '/^[\w]{6,16}$/';
        if(!preg_match($rule,$password)){
        	return false;
        } 
        return true;  
	}
	/**
	 * 验证最后一位是字母的数字字串
	 * @param  $num 要验证的字串
	 *
	 * @return bool
	 * @param author  liushilei
	 */
	public static function  check_num($num){
		 
        $rule = '/^\d{4,49}[a-zA-Z\d]$/';
        if(!preg_match($rule,$num)){
        	return false;
        } 
        return true;  
	}	
	 /**
	 * 分页函数
	 *
	 * @param $num 信息总数
	 * @param $curr_page 当前分页
	 * @param $perpage 每页显示数
	 * @param $urlrule URL规则
	 * @param $array 需要传递的数组，用于增加额外的方法
	 * @return 分页
	 */
	public static function pages($num, $curr_page, $perpage = 20, $urlrule = '', $setpages = 10,$type = 1) {
		
       if(!strstr($urlrule,"\$page")) {
            if($urlrule == '') {
                $urlrule = self::url_par('page={$page}');
            } else {
                $urlrule = self::url_par('page={$page}',$urlrule);
            }
        }
	    $multipage = '';            
	    if ($num > $perpage) {
	        $page = $setpages + 1;
	        $offset = ceil($setpages / 2 - 1);
	        $pages = ceil($num / $perpage);
	        if (defined('IN_ADMIN') && !defined('PAGES'))
	            define('PAGES', $pages);
	        $from = $curr_page - $offset;
	        $to = $curr_page + $offset;
	        $more = 0;
	        if ($page >= $pages) {
	            $from = 2;
	            $to = $pages - 1;
	        } else {
	            if ($from <= 1) {
	                $to = $page - 1;
	                $from = 2;
	            } elseif ($to >= $pages) {
	                $from = $pages - ($page - 2);
	                $to = $pages - 1;
	            }
	            $more = 1;
	        }
	        $multipage .= '';//'共<font>' . $pages . '</font>页<font>' . $num . '</font>条&nbsp;';
	        if($type == 1){
				if ($curr_page > 0) {
					$multipage .= '<p>';
					$multipage .= $curr_page == 1 ? '': '<a href="' . self::pageurl($urlrule, 1) . '">首页</a>';
					$multipage .= $curr_page == 1 ? '': '<a href="' . self::pageurl($urlrule, $curr_page - 1) . '">上一页</a>';
					if ($curr_page == 1) {
						$multipage .= '<span class="current">1</span>';
					} elseif ($curr_page > 2*$offset && $more) {
						//$multipage .= '...&nbsp;&nbsp;';
						//$multipage .= '<a href="' . self::pageurl($urlrule, 1) . '">1</a> ...&nbsp;&nbsp;';
					} else {
						$multipage .= '<a href="' . self::pageurl($urlrule, 1) . '">1</a>';
					}
				}
				for ($i = $from; $i <= $to; $i++) {
					if ($i != $curr_page) {
						$multipage .= '<a href="' . self::pageurl($urlrule, $i) . '">' . $i . '</a>';
					} else {
						$multipage .= '<span class="current">'.$i.'</span>';
					}
				}
				if ($curr_page < $pages) {
					if ($curr_page < $pages - $offset && $more) {
						//$multipage .= ' ...&nbsp;&nbsp;';
						$multipage .= ' ...&nbsp;&nbsp;<a href="' . self::pageurl($urlrule, $pages) . '">' . $pages . '</a>';
					} else {
						$multipage .= '<a href="' . self::pageurl($urlrule, $pages) . '">' . $pages . '</a>';
					}
				} elseif ($curr_page == $pages) {
					$multipage .= '<span class="current">' . $pages . '</span>';
				} else {
					$multipage .= '<a href="' . self::pageurl($urlrule, $pages) . '">' . $pages . '</a>';
				}

				$multipage .= $curr_page == $pages ? '' : '<a href="' . self::pageurl($urlrule, $curr_page + 1) . '">下一页</a>';
				$multipage .= $curr_page == $pages ? '' : '<a href="' . self::pageurl($urlrule, $pages) . '">末页</a>';
				$multipage .= '</p>';
			}elseif ($type == 2) {
				if ($curr_page > 0) {
                    $multipage .= '<nav><ul class="pagination">';
                    $multipage .= $curr_page == 1 ? '':'<li class=""><a href="'.self::pageurl($urlrule, $curr_page - 1) .'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';

                    if ($curr_page == 1) {
                        $multipage .= '<li class="active"><a href="#">1<span class="sr-only"></span></a></li>';
                    } elseif ($curr_page > 2 * $offset && $more) {
                        //$multipage .= '...&nbsp;&nbsp;';
                        //$multipage .= '<a href="' . self::pageurl($urlrule, 1) . '">1</a> ...&nbsp;&nbsp;';
                    } else {
                        $multipage .= '<li class=""><a href="'.self::pageurl($urlrule, 1) . '">1<span class="sr-only"></span></a></li>';
                    }
                }
                for ($i = $from; $i <= $to; $i++) {
                    if ($i != $curr_page) {
                        $multipage .= '<li class=""><a href="'.self::pageurl($urlrule, $i) . '">' . $i . '<span class="sr-only"></span></a></li>';
                    } else {
                        $multipage .= '<li class="active"><a href="#">'.$i.'<span class="sr-only"></span></a></li>';
                    }
                }
                if ($curr_page < $pages) {
                    if ($curr_page < $pages - $offset && $more) { 
                        $multipage .= '<li class="disabled"><a href="">...<span class="sr-only"></span></a></li><li class=""><a href="'.self::pageurl($urlrule,$pages).'">'.$pages.'<span class="sr-only"></span></a></li>';
                    } else {
                        $multipage .= '<li class=""><a href="'.self::pageurl($urlrule,$pages).'">'.$pages.'<span class="sr-only"></span></a></li>';
                    }
                } elseif ($curr_page == $pages) {
                    $multipage .= '<li class="active"><a href="#">'.$pages.'<span class="sr-only"></span></a></li>';
                } else {
                    $multipage .= '<li class=""><a href="'.self::pageurl($urlrule,$pages).'">'.$pages.'<span class="sr-only"></span></a></li>';

                }

                if($curr_page == $pages){
                    $multipage .= '<li class="disabled"><a href="'.self::pageurl($urlrule, $pages).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
                }else{
                    $multipage .= '<li><a href="'.self::pageurl($urlrule, $curr_page + 1).'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
                }
                $multipage .= '</ul></nav>';
			}
	    }
	    return $multipage;
	}
    
    
	 /**
	 * 分页函数
	 *
	 * @param $num 信息总数
	 * @param $curr_page 当前分页
	 * @param $perpage 每页显示数
	 * @param $urlrule URL规则
     * @param $showAll 是否显示所有页数(个人中心没有页数显示限制)
	 * @return 分页
	 */
	public static function wapPages($num, $curr_page, $perpage = 20, $urlrule = '', $showAll=false) {
		if($urlrule == ''){
    	    $urlrule = self::url_par('page={$page}');
    	}
	    $multipage = '';
	    if ($num > $perpage) {
            $maxPageSize = CommonConst::COMMON_MAX_PAGESIZE;    //最大显示页数
            $totalPage = ceil($num/$perpage);    //总页数
            if ( !$showAll && ($totalPage > $maxPageSize)) {
                $totalPage = $maxPageSize;
            }
            $pageUpUrl = $pageDownUrl = 'javascript:void(0);';  //上一页, 下一页的url

            if ($curr_page > 1) {
                $pageUpUrl = self::pageurl($urlrule, $curr_page-1);
            }

            if ($curr_page < $totalPage) {
                $pageDownUrl = self::pageurl($urlrule, $curr_page+1);
            }
            
            $multipage .= "<div class='cir pageStyle'>
                            <ul>
                                <p><a href='{$pageUpUrl}'>上一页</a></p>
                                <p class='pageStyle_1'><span>{$curr_page}</span>/{$totalPage}</p>
                                <p><a href='{$pageDownUrl}'>下一页</a></p>
                                <p>
                                    <input name='' class='pageGo' type='text'/>
                                    <a onclick='pageGo(this);' href='javascript:void(0);' style='font-size:14px;'>GO</a>
                                    <input type='hidden' class='totalPage' value='{$totalPage}'>
                                    <input type='hidden' class='urlRule' value='{$urlrule}'>
                                </p>
                            </ul>
                        </div>";
                                    
             //wap分页相关js方法                       
             $multipage .= "<script type='text/javascript'>
                                //页面跳转
                                function pageGo(obj){
                                    var obj = $(obj);
                                    var totalPage = parseInt(obj.siblings('.totalPage').eq(0).val());
                                    var pageObj = obj.siblings('.pageGo').eq(0);
                                    var pageGo = parseInt(pageObj.val());
                                    
                                    //验证跳转页数输入的合法性
                                    var reg = /^[1-9]\d*$/g;
                                    if ( !reg.test(pageGo) || (pageGo > totalPage)) {
                                        pageObj.val('');
                                        return false;
                                    }
                                    
                                    var urlRule = obj.siblings('.urlRule').eq(0).val();
                                    window.location.href = urlRule.replace(/\{\\\$page\}/, pageGo);
                                }
                            </script>";
	    }
	    return $multipage;
	}

	/**
	 * 返回分页路径
	 *
	 * @param $urlrule 分页规则
	 * @param $page 当前页
	 * @param $array 需要传递的数组，用于增加额外的方法
	 * @return 完整的URL路径
	 */
	private static function pageurl($urlrule, $page) {
        if (strpos($urlrule, '~')) {
	        $urlrules = explode('~', $urlrule);
	        $urlrule = $page < 2 ? $urlrules[0] : $urlrules[1];
	    }
	    $findme = array('{$page}');
	    $replaceme = array($page);
	    
	    $url = str_replace($findme, $replaceme, $urlrule);
	    $url = str_replace(array('http://', '//', '~'), array('~', '/', 'http://'), $url);
        return $url;
	}

	/**
	 * URL路径解析，pages 函数的辅助函数
	 *
	 * @param $par 传入需要解析的变量 默认为，page={$page}
	 * @param $url URL地址
	 * @return URL
	 */
	private static function url_par($par, $url = '') {
	    if ($url == '')
	        $url = self::get_url();
	    $pos = strpos($url, '?');
	    if ($pos === false) {
	        $url .= '?' . $par;
	    } else {
	        $querystring = substr(strstr($url, '?'), 1);
	        parse_str($querystring, $pars);
	        $query_array = array();
	        foreach ($pars as $k => $v) {
	            if ($k != 'page' && $k !='appid' && $k!='token')
	                $query_array[$k] = $v;
	        }
            if(count($query_array)>0) {
	             $querystring = http_build_query($query_array) . '&' . $par;
            } else {
	             $querystring = $par;
            }
	        $url = substr($url, 0, $pos) . '?' . $querystring;
	    }
	    return $url;
	}

	 /**
	 * 获取当前页面完整URL地址
	 */
	private static function get_url() {
	    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	    $php_self = $_SERVER['PHP_SELF'] ? ($_SERVER['PHP_SELF']) : ($_SERVER['SCRIPT_NAME']);
	    $path_info = isset($_SERVER['PATH_INFO']) ? ($_SERVER['PATH_INFO']) : '';
	    $relate_url = isset($_SERVER['REQUEST_URI']) ? ($_SERVER['REQUEST_URI']) : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . ($_SERVER['QUERY_STRING']) : $path_info);
	    return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
	}
	//获取用户ip地址
	public static function getClientIP()
	{
		if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip     =   $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENTIP'])) {
			$ip     =   $_SERVER['HTTP_CLIENTIP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		} elseif (isset($_SERVER['HTTP_H_FORWARDED_FOR']) && !empty($_SERVER['HTTP_H_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_H_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
    }
    /**
     * 过滤掉为空的值
     * @param unknown_type $val
     * @return boolean
     * @author JiangQingChuan
     */
    public function  filter($val)
    {
    	$val = trim($val);
    	if(isset($val)  && $val != '')
    	{
    		return true;
    	}
    	return false;
    }
    
    /**
     * 检查是否是合法的手机号, 合法的手机号是指1开头的11位数字字符串
     * 
     * @param  string $mobile	要验证的手机号
     * @return boolean 如果要验证的手机号是合法的手机号, 返回TRUE; 否则返回FALSE
     */
 	public static function check_mobile($mobile, $strict = FALSE) {
 		if($strict) {
 			return !!preg_match('/^1\d{10}$/', $mobile);
 		} else {
 			return !!preg_match('/^1\d{10}$/', $mobile);
 		}
	}
	
	/**
     * 检查是否是合法的座机号, 合法的座机号符合以下的规则：
     * 1. 0开头的三到四位的区号(数字字符串), 可选(如果$require_city_code参数为真, 则区号必须)
     * 2. 区号和座机号之间的连接符(中横线), 可选
     * 3. 七到八位的座机号(数字字符串), 必须
     * 4. 二到五位的分机号及前缀连接符(中横线), 可选(如果有分机号, 座机号和分机号之间的连接符则是必须)
     * 
     * @param  string  $mobile				要验证的座机号
     * @param  boolean $require_city_code	是否必须包含区号, 默认FALSE表示不必须
     * @return boolean 如果要验证的座机号是合法的座机号, 返回TRUE; 否则返回FALSE
     */
	public static function check_phone($phone, $require_city_code = FALSE) {
		if($require_city_code) {
			// 区号必须包含
			return !!preg_match('/^0\d{2,3}-?\d{7,8}(?:-\d{2,5})?$/', $phone);
		} else {
			// 区号可选
			return !!preg_match('/^(?:0\d{2,3}-?)?\d{7,8}(?:-\d{2,5})?$/', $phone);
		}
	}

	/**
	 * @author yuli
	 * @since 2014/4/18
	 * @param int    $time 时间戳
	 * @param string $str  返回时间的格式 默认：Y-m-d H:i:s
	 * @brief wap医义诊往期、预告、进行中 ，根据时间戳获取上午还是下午
	 *  
	 **/
	public static function getUpDowmTime($time,$str = 'Y-m-d H:i:s')
	{
		if(!empty($time)){
			return str_replace(array('AM','PM'),array('上午','下午'),date('A '.$str,$time));
		}else{
			return false;
		}
	}
	
	/**
	 * @author yuli
	 * @since 2014/4/18
	 * @param int    $time 时间戳,可以是整形的时间戳也可以是一维数组
	 * @example int  1397088000 
	 * @example Array
	 *			(
	 *			    [0] => 1397814216
	 *			    [1] => 1397088000
	 *			)
	 * @param string $str  默认 星期
	 * @brief 根据时间戳获取星期几
	 *  
	 **/
	public static function getWeekDays($time,$str='星期')
	{
		if(!empty($time)){
			$weekarray = array("日","一","二","三","四","五","六");
			if(is_array($time)){
				$arr = array();
				foreach($time as $val){
					if(!empty($val)){
						$arr[] = $str.$weekarray[date("w",$val)];					
					}
				}
				return $arr;
			}
			return $str.$weekarray[date("w",$time)];
		}else{
			return false;
		}
	}
	
	/**
	 * 用字符分割字符串
	 * @param string $str 要分割的字符串
	 * @param int $length 分割长度
	 * @param string $character 分割字符
	 * @param author yanghuichao
	 * @example split_str(12345, 3, ',') return 12,345
	 */ 
	public static function split_str($str, $length = 3, $character = ','){
		$ret = '';
		$char = '';
		$str = (string)$str;
		for($i=strlen($str)-1, $n=0; $i >= 0; $i = $i-3,$n++){
			$len = $length;
			$pos = $i - 2;
			if($pos < 0){
				$len = 3 + $pos;
				$pos = 0;
			}
			$s = substr($str, $pos, $len);
			$ret = $s . $char . $ret;
			$char = $character;
		}
		return $ret;
	}
	
	/**
	 *	创建 文件夹
	 *  @param $dir 要创建的文件夹路径
	 *
	 * @param author  ChengBo
	 * @param addTime 2014-05-07
	 */
	public static function mkdirs($dir){
		if(is_dir($dir)){
            return true;
        }

        $parent = dirname($dir);
		if (is_dir($parent) || Utils::mkdirs($parent)) {
            return @mkdir($dir, 0777);
        }

        return false;
	}

	/**
     * 将问题中的月龄转成相应的年龄显示
     * @param int $monthAge     问题中的月龄
     */
    public static function processMonthAge($monthAge) {
        if ($monthAge == 0) {
            return '未知';
        } elseif ( ($monthAge > 0) && ($monthAge <= 24) ) {
            return $monthAge.'个月';
        }  else {
            return ceil($monthAge/12).'岁';
        }
    }

    /**
     * 将时间戳转换成多长时间前，和日期
     * @param  int     $time     时间戳
     * @return string  日期，多少秒前，多少分钟前，多少小时前，多少天前
     */
    public static function timeAgo($time, $format="Y-m-d")
    {
    	$diffTime = abs(time()-$time);

    	if ($diffTime < 60) {
    		return $diffTime.'秒前';
    	} 
    	elseif ($diffTime < 3600 && $diffTime >=60) {
    		return floor($diffTime/60).'分钟前';
    	}
    	elseif ($diffTime < (3600*24) && $diffTime >=3600) {
    		return floor($diffTime/3600).'小时前';
    	}
    	elseif ( (date('Ym') == date('Ym',$time)) && $diffTime >=(3600*24)) {
    		return floor($diffTime/(3600*24)).'天前';
    	}
    	else{
    		return date($format,$time);
    	}
    }
    
    /**
     * 将时间戳转换成聊天列表显示的时间
     * @param  int     $time     时间戳
     * @return string 
     */
    public static function showChatListTime($time)
    {
        $nowTime = time();
        $todayStartTime = strtotime(date('Y-m-d', $nowTime));
        $todayEndTime = $todayStartTime + 86400;
        
        $yesterdayStartTime  = $todayStartTime - 86400;
        $yesterdayEndTime = $todayStartTime -1;
        //前推一周的起始时间
        $weekStartTime = $yesterdayStartTime - 432000;
        //今天12点的时间
        //$todayTwelveTime = $todayStartTime + 43200;
        
        if($time >= $todayStartTime && $time <= $todayEndTime){
            return date('H:i', $time);
        }
        if($time >= $yesterdayStartTime && $time <= $yesterdayEndTime){
            return '昨天';
        }
        if($time >= $weekStartTime && $time <= $yesterdayStartTime){
            $week = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
            return $week[date('w',$time)];
        }
        if($time <= $weekStartTime){
            return date('m-d', $time);
        }
        return date('Y-m-d', $time);
    }

	
    /**
     * 生成token
     *
     * @param  array  $params 要计算的参数数组
     * @param  string $secret 密钥
     * @return string token
     */
    public static function generate_token($params, $secret) {
    	$args = array();
    	ksort($params);
    	foreach($params as $k => $v) {
    		$v = is_null($v) ? '' : $v;
    		$args[] = $k . '=' . $v;
    	}
    	return sha1(md5(join('#', $args)) . '@' . $secret);
    }
    /**
     * 用户名匹配
     * @return [int] [int]
     * @author [chuhailei]
     * @date_add(2014/05/20)
     */
    public static function make_seed()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }    
    /**
     * 字符替换substr_replace
     * @return [string] [被替换的字符]
     * @author [guanxiongbo]
     * @date_add(2014/05/21)
     *
      * @example
      *  Utils::str_replace($mobile,3,6,'*');
      *  rerurn 186******92
     */
    public static function str_replace($str,$start_num,$end_num,$replace = '*')
    {
    	$s = '';
    	for ($i = 0; $i < $end_num; $i++) { 
    		$s .= $replace;
    	}
    	return  $replace_str = substr_replace($str, $s,$start_num, $end_num);
    }

	//ip字符串转数字
	public static function getLongIp($strIp) {
		return bindec(decbin(ip2long($strIp)));
	}

	//是否含utf-8占位符
	public static function is_utf8_replace_char($str) {
		if(preg_match("/\x{fffd}/u",$str)) {
			return true;
	    }
		return false;
	}
	
	/**
	 * 生成签名后的URL
	 * 
	 * @param  string url		URL, 可以含有请求参数
	 * @param  array  $params	其他额外的请求参数
	 * @return string 签名后的URL
	 */
	public static function generatSignedURL($url, Array $params = NULL) {
		$req_params = array();
		
		// 解析请求URL, 将请求参数分解到请求参数数组中
		if(strpos($url, '?') !== FALSE) {
			// url含有请求参数
			list($url, $query_string) = explode('?', $url);
			foreach(explode('&', $query_string) as $param) {
				list($key, $value) = explode('=', $param);
				$req_params[$key] = urldecode($value);
			}
		}
		
		// 如果有额外的请求参数, 合并到请求参数数组中
		if(!empty($params)) {
			$req_params += $params;
		}
		
		// 如果请求参数数组中含有appid和/或token，要删除
		if(isset($req_params['appid'])) {
			unset($req_params['appid']);
		}
		if(isset($req_params['token'])) {
			unset($req_params['token']);
		}
		
		// 生成token并拼接到请求字符串后面
		$req_params['token'] = self::generate_token($req_params, CommonConst::$appSecrets['inner']);
		
		// 返回签名后的请求URL
		return $url . '?' . http_build_query($req_params);
	}
	
	/**
	 * 跳转到签名后的URL
	 * 
	 * @param string $url		URL, 可以含有请求参数
	 * @param array  $params	其他额外的请求参数
	 */
	public static function redirectSingedURL($url, Array $params = NULL) {
		self::redirect(self::generatSignedURL($url, $params));
	}
	
	/**
	 * 验证签名的URL是否合法
	 * 
	 * @param  string $url	要验证的签名后的URL
	 * @return 验证成功返回TRUE, 否则返回FALSE
	 */
	public static function checkSignedURL($url) {
		
		// 解析请求URL, 将请求参数分解到请求参数数组中
		if(strpos($url, '?') !== FALSE) {
			$req_params = array();
			
			// url含有请求参数
			list($url, $query_string) = explode('?', $url);
			foreach(explode('&', $query_string) as $param) {
				list($key, $value) = explode('=', $param);
				$req_params[$key] = urldecode($value);
			}
			
			// 如果请求参数数组中含有appid和/或token，要删除
			if(isset($req_params['appid'])) {
				$req_appid = $req_params['appid'];
				unset($req_params['appid']);
			} else {
				$req_appid = 'inner';
			}
			
			if(isset($req_params['token'])) {
				$req_token = $req_params['token'];
				unset($req_params['token']);
			}
			
			// 生成token
			$token = self::generate_token($req_params, CommonConst::$appSecrets[$req_appid]);
			if($req_token != $token || (isset($req_params['time']) && (time() - $req_params['time'] > 1800))) {
				return false;
			}
		}
		
		return true;
	}
	
	 /**
	  * 根据生日获取年龄
	  * 30天以内显示天，一年之内显示月，否则年
	  * @param  string $birthday	生日
	  * @return 年龄
	  */
	 public static function getAgeByBirthdayBak($birthday) {
	 	$cur = date('Y-m-d');
	 	if($birthday >= $cur) {
	 		return '0天';
	 	}

        $birthday = date("Y-m-d",strtotime($birthday));
	 	
	 	list($c_year, $c_month, $c_date)  = explode('-', $cur);
	 	list($b_year, $b_month, $b_date)  = explode('-', $birthday);
	 	
	 	if($c_year === $b_year && $c_month === $b_month) {
	 		// 日龄
	 		return ($c_date - $b_date) . '天';
	 	} else if($c_year === $b_year) {
	 		// 月龄
	 		return ($c_month - $b_month) . '个月';
	 	} else {
			//修改后 Bug情况[$birthday=2013-11-12 $cur=2014-09-30]返回0岁问题 20140930 updateBy ChengBo
	 		$_age = $c_year - $b_year;
			if($_age == 1){
				if($b_month <= $c_month){
					return $_age.'岁';
				}else{
					return $c_month+(12-$b_month).'个月';
				}
			}else{
				if($c_month < $b_month || ($c_month === $b_month  && $c_date < $b_date)) {
					return ($_age - 1) . '岁';
				} else {
					return $_age . '岁';
				}
			}
	 	}
	 }

	 /**
	  * 根据生日获取年龄
	  * 一年之内显示月，否则年
	  * @param  string $birthday	生日
      * @param  bool    $returnMonthAge(true:返回月龄,比如23，false:返回字符串的年龄，比如2岁，2个月)
      * @modified zhangliang
	  * @return 年龄
	  */
	 public static function getAgeByBirthday($birthday, $timeStamp = 0, $returnMonthAge = false) {
        $timeStamp = ($timeStamp ? $timeStamp : time());
        $cur = date('Y-m-d', $timeStamp);
        list($c_year, $c_month, $c_date)  = explode('-', $cur);
        list($b_year, $b_month, $b_date)  = explode('-', $birthday);

        $minusSec = $timeStamp - strtotime($birthday);
        $days = ceil($minusSec/86400);
        if($days <= 31) {
            $age = 1;
        }else{
            $age = ($c_year - $b_year)*12;
            if($c_year - $b_year <= 2){
                $age = $age + $c_month - $b_month + 1;
            }
        }
        if($returnMonthAge === true){
            return $age;
        }
        return self::processMonthAge($age);
	 }

	/**
	* post 方法上传文件
	* @author wangyunjie
	* @param filename 要上传的文件路径
	* @param url      要上传的url
	* @return max 
    */
	public static function postUploadFiles($fileName,$url){
		if(!file_exists($fileName)){
			return false;
		}

		if(empty($url)){
			return false;	
		}
		$ch = curl_init();
		$post_data = array(
		   'file' => '@'.$fileName
		);  
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ch, CURLOPT_POST, true);  
		curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_URL, $url);
		$info= curl_exec($ch);
		curl_close($ch);

		return $info;
	}
    /**
     *计算某个经纬度的周围某段距离的正方形的四个点
     *@param lng float 经度
     *@param lat float 纬度
     *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
     *@return array 正方形的四个点的经纬度坐标
     */
    public static function returnSquarePoint($lng, $lat,$distance = 5){
        $EARTH_RADIUS = 6371;//地球半径，平均半径为6371km
        $dlng =  2 * asin(sin($distance / (2 * $EARTH_RADIUS)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);

        $dlat = $distance/$EARTH_RADIUS;
        $dlat = rad2deg($dlat);

        return array(
            'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
            'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
            'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
            'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
        );
    }
    /**
     * 获取圆
     * @param $d
     * @return float
     */
    public static function rad($d){
        return $d * M_PI / 180.0;
    }

    /**
     * 获取两个坐标点之间的距离，单位km，小数点后2位
     * @param $lat1经度
     * @param $lng1纬度
     * @param $lat2
     * @param $lng2
     * @return float|int
     */
    public static function GetDistance($lat1, $lng1, $lat2, $lng2){
        $EARTH_RADIUS = 6378.137;
        $radLat1 = self::rad($lat1);
        $radLat2 = self::rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = self::rad($lng1) - self::rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s * $EARTH_RADIUS;
        $s = round($s * 100) / 100;
        return $s;
    }
    /**
     * 二维数组给定键值排序
     * @param $arr
     * @param $keys
     * @param string $type
     * @return array
     */
    public static function array_sort($arr,$keys,$type='asc'){
        $keysvalue = $new_array = array();
        if(is_array($arr) && !empty($arr)){
            foreach ($arr as $k=>$v){
                if(isset($v[$keys])) $keysvalue[$k] = $v[$keys];
            }
        }else{
            return $arr;
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
    
    /**
     * 数组中的为null值的元素赋值为空字符串
     * 本方法仅当数据库有字段默认值为NULL的时候调用
     * @param $arr
     * @return array
     */
    public static function array_nulltostr(&$arr=array()){
		if (!is_array($arr)) {
			return false;
		}
		
		foreach ($arr as $key => &$val) {
			if (is_array ($val)) {
				self::array_nulltostr($val);
			} else {
				if(NULL === $val) $arr[$key] = '';
				if(is_int($val)) $arr[$key] = (string)$val;
			}
		}
		
		return $arr;
    }

    /**
     * 根据权重获取开通的服务
     * @param $weight
     * @return array|bool
     */
    public static function getPowerByWeight($weight) {
        $arr =array(4,2,1);
        if ($weight <= 0 || $weight>array_sum($arr)) {
            return false;
        }
        $new_arr = array();
        foreach ($arr as $value) {
            if ($weight >= $value) {
                $new_arr[] = $value;
                $weight = $weight - $value;
            }
        }
        return $new_arr;
    }

    /**
     * 根据服务值，获取所有可能开通这种服务的weight值
     * @param $service
     * @return array
     */
    public static function getWeightByService($service) {
        $arr =array(4,2,1);        //1: 1 3 5 7
        if (!in_array($service, $arr)) {
            return false;
        }
        $new_arr = array();
        for ($i = pow(2, 0); $i < pow(2, count($arr)); $i++) {
            $new_i = decbin($i);    //将权限转成二进制
            $new_service = strlen($new_i) - log($service, 2) - 1;
            $curr = substr($new_i, $new_service, 1);
            if ($i>=$service && $curr) {
                $new_arr[] = $i;
            }
        }
        return $new_arr;
    }
   public static function  dump($value,$type = '',$header=true){
            if($header){
              header("Content-type: text/html; charset=utf-8");
            }
	    if($type == 1){
	      echo '<pre>';
	      var_dump($value);
	    } else {
	      echo '<pre>';
	      print_r($value);
	    }
  	}  
  	public static function imgToEmotion($html) {
		$html = strip_tags($html, '<img>, <a>');
		if (strpos($html, 'dialogs/emotion') !== false) {
			$html = preg_replace('#<img\s*src="(.*?)" title="(.*?)"/>#i', "/$2", $html);
		}
		$html = str_replace(array("　", "\t"), '', $html);
		$html = str_replace("&nbsp;", "\t", $html);
		return trim($html);
	}
  	public static function sendWeixinText($hid,$openid,$msg){
    	$hospitalApp = WechathospitalConfig::$hospitals[$hid];
    	CLog::debug(sprintf("sendWeixinText hid[%d] openid[%s] msg[%s]",$hid,$openid,$msg));
			if (empty($hospitalApp)){
				CLog::fatal('hospital Appid and appsecret not setup');
			}
			$token = WeixinAccount::getToken($hid);
			$wexin = new Weixin(array(
					'token' => $token,
					'appid' => $hospitalApp['appid'],
					'appsecret' => $hospitalApp['appsecret']
					));
			return $wexin->sendText($msg,$openid);
    }
    public static function sendWeixinNews($hid,$openid,$msg){
    	$hospitalApp = WechathospitalConfig::$hospitals[$hid];
    	CLog::debug(sprintf("sendWeixinText hid[%d] openid[%s] msg[%s]",$hid,$openid,var_export($msg,1)));
			if (empty($hospitalApp)){
				CLog::fatal('hospital Appid and appsecret not setup');
			}
			$token = WeixinAccount::getToken($hid);
			$wexin = new Weixin(array(
					'token' => $token,
					'appid' => $hospitalApp['appid'],
					'appsecret' => $hospitalApp['appsecret']
					));
			return $wexin->sendNews($msg,$openid);
    }
    
    /**
     * 判断访问的来源是否是手机端
     * @author yaojiaming
     * @return boolean
     */
     public static function isMobile(){
    	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    	if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    	{
    		return true;
    	}
    
    	// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    	if (isset ($_SERVER['HTTP_VIA']))
    	{
    		// 找不到为flase,否则为true
    		return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    	}
    
    	// 判断手机发送的客户端标志,兼容性有待提高
    	if (isset ($_SERVER['HTTP_USER_AGENT']))
    	{
    		$clientkeywords = array (
    				'nokia',
    				'sony',
    				'ericsson',
    				'mot',
    				'samsung',
    				'htc',
    				'sgh',
    				'lg',
    				'sharp',
    				'sie-',
    				'philips',
    				'panasonic',
    				'alcatel',
    				'lenovo',
    				'iphone',
    				//'ipod',
    				'blackberry',
    				'meizu',
    				'android',
    				'netfront',
    				'symbian',
    				'ucweb',
    				'windowsce',
    				'palm',
    				'operamini',
    				'operamobi',
    				'openwave',
    				'nexusone',
    				'cldc',
    				'midp',
    				'wap',
    				'mobile'
    		);
			//判断飞华公司电视
    		if (preg_match("/InettvBrowser/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
				return false;
			}
    
    		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
    		if (preg_match("/pad/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
    			//排除pad
    			return false;
    		}
    
    		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
    		{
    			return true;
    
    		}
    	}
    
    	// 协议法，因为有可能不准确，放到最后判断
    	if (isset ($_SERVER['HTTP_ACCEPT']))
    	{
    		// 如果只支持wml并且不支持html那一定是移动设备
    		// 如果支持wml和html但是wml在html之前则是移动设备
    		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
    		{
    			return true;
    
    		}
    	}
    	return false;
    }
	
	
    /**
     * 根据身份证获取生日
 	 * @param $idCard 身份证
     * @return int 时间戳
     */
	public static function getBirthByIDCARD($idCard){
		$ptn_15 = '/^\d{6}(\d{6})\d{5}$/';
		$ptn_18 = '/^\d{6}(\d{8})\d{3}[0-9xX]$/';
		$curYear = date('Y');
		$curMonth = date('m');
		$birth = 0;
		if(preg_match($ptn_15, $idCard, $match)){
			$birth = strtotime(1900 + $match[1]);
		}else if(preg_match($ptn_18, $idCard, $match)){
			$birth = strtotime($match[1]);
		}
		return $birth;
	}

    /**
     * 验证数组
     * @param $val
     * @return bool
     */
    public static function check_array($val){
        return is_array($val) && !empty($val);
    }

    /**
     * 验证范围
     * @param $val
     * @param array $range
     * @return bool
     */
    public static function check_range($val, $range = array()){
        return in_array($val, $range);
    }

    /**
     * 验证范围
     * @param $key
     * @param $keys
     * @return bool
     */
    public static function check_key($key, $keys){
        return array_key_exists($key, $keys);
    }

    /**
     * @todo    获取{$n}周周{$i}的时间戳;分词插入停诊表的时候使用
     * @paran   $n {$n}n周之后
     * @param   $i{1,2,3,4,5,6,7}
     * @param   $type{1:本周周{$i}日期+"00:00:00";2:1:本周周{$i}日期+当前时间;3:本周周{$i}日期+"23:59:59"}
     * @return  int{十位的时间戳}
     * @author  liuhaoyang
     */
    public static function  getFutureTimeByCurrWeekDay($n,$i,$type=2){
        $nowweek=date('w')==0?7:date('w');//当前星期几
        $weektime=($nowweek-$i-$n*7)*86400;//n周后周$i的时间差值
        $weektime=time()-$weektime;//n周后周$i的时间戳
    	$date =  date('Y-m-d',$weektime);//n周后周$i的日期形式
    	if(1 == $type){
    		$date = $date.'00:00:01';
    	}
    	if(2 == $type){
    		$date =  date('Y-m-d H:i:s',$weektime);
    	}
    	if(3 == $type){
    		$date = $date.'23:59:59';
    	}
    	return strtotime($date);
    }
    
    
    /**
     * 过滤html,空格,换行
     * @param string $str
     * @author zangwenxue
     * @return string
     */
    public static function filterChar($str) {
    	$search = array(
    		"<br />","<br>","<br/>","\r\n","\r","\n","&nbsp;",
    	 	"&amp;", "ldquo;", "rdquo;", "bull;", "&rsquo;", "\t",
    		"NULL", "null", "&mdash"
    	);
    	$replace = array(
    		"","","", "", "", 
    		"", "", "", "", "", "", "","","",""
    		);
    	$str = preg_replace( "@<script(.*?)</script>@is", "", $str );
    	$str = preg_replace( "@<iframe(.*?)</iframe>@is", "", $str );
    	$str = preg_replace( "@<style(.*?)</style>@is", "", $str );
    	$str = preg_replace( "@<(.*?)>@is", "", $str );
    	$str = strip_tags(trim($str));
    	return str_replace($search, $replace, $str);
    }
    /**
     * 密码 :限用英文字母、数字和下划线，
     * 		英文字母区分大小写，密码长度为6-16位
     * @param string $str 
     * @author zangwenxue
     * @return boolean
     */
    public static function checkPwd($str){
    	$pwd = trim($str);
    	if(is_string($pwd)){
	    	$pattern = '/^[_0-9a-z]{6,16}$/i';
	    	preg_match($pattern, $str, $matches);
    	}
    	return empty($matches)? false : true;
    }
    /**
     * 验证患者名称 由英文,汉字或数字组成
     * @param string $str
     * @param int $type  0 所有 1 汉字和英文
     * @author zangwenxue
     * @return bool
     */
     public static function checkPatientName($str,$type=0){
    
     $encode = mb_detect_encoding($str, array("UTF-8","GB2312","GBK"));
     if($encode == "UTF-8"){
          if($type==1){
              $status = preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z]+$/u", $str);
          }else{
              $status = preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $str);
          }
     
     }else{
         if($type==1){
             $status = preg_match("/^[".chr(0xa1)."-".chr(0xff)."A-Za-z]+$/", $str);
         }else{
             $status = preg_match("/^[".chr(0xa1)."-".chr(0xff)."A-Za-z0-9]+$/", $str); 
         }
     
     }
     return $status;
     }
     /**
     * 验证字符串只是中文
     * @param string $str
     * @return bool
     */
     public static function checkStringZh($str){
    
     $encode = mb_detect_encoding($str, array("UTF-8","GB2312","GBK"));
     if($encode == "UTF-8"){
     $status = preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $str);
     }else{
     $status = preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/", $str);
     }
     return $status;
     }
    /**
     * APNS NOTIFICATION
     * @param type $clientId 用来筛选 Push Certificate 
     * @param array $arrTokenMsg = array
     *                               'tokenA' => 'contentA',
     *                               'tokenB' => 'contentB',
     *                           );
     * @param array $arrCustom = array(
     *                               'key1' => (mixed)'val2'
     *                               'key2' => (mixed)'val2'
     *                           );
     */
    public static function apnsPush($clientId, array $arrTokenMsg, array $arrCustom = array()){
        CLog::debug('APNS: %s#%s', $clientId, var_export($arrTokenMsg,true));

        $arrQueueMsg = array();
        foreach($arrTokenMsg as $token => $content){
            // clean token
            $token = preg_replace('/[^0-9a-f]/', '', $token);
            // Instantiate a new Message with a single recipient
            $message = new ApnsMessage($token);
            // Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
            // over a ApnsPHP_Message object retrieved with the getErrors() message.
            //$message->setCustomIdentifier("Message-Badge-3");

            // Set badge icon to "1"
            //$message->setBadge(1);

            $message->setText($content);

            // Play the default sound
            $message->setSound();

            foreach ($arrCustom as $key => $val){
                // Set a custom property
                $message->setCustomProperty($key, $val);
            }

            // Set another custom property
            //$message->setCustomProperty('acme3', array('bing', 'bong'));

            // Set the expiry value to 30 seconds
            $message->setExpiry(30);
            $arrQueueMsg[] = $message;
        }
        CLog::debug('APNS QUEUE: %s', var_export($arrQueueMsg,true));

        try{
            self::send($clientId, $arrQueueMsg);
        }catch(Exception $ex){
            CLog::debug('APNS EXCEPTION: %s#%s#%s', $clientId, var_export($arrTokenMsg,true),var_export($arrCustom,true));
        }
    }
    
    
    private static function send($clientId, array $arrQueueMsg) {
        /**
         * Using Autoload all classes are loaded on-demand
         * require_once 'ApnsPHP/Autoload.php';
         * Instanciate a new ApnsPHP_Push object
         *
         * How to Generate a Push Certificate
         * [root@mail cron]# openssl pkcs12 -in clientid.p12 -out clientid.pem -nodes -clcerts
         *
         */
        //可以根据clientid选择证书
        //根据代码位置选择环境和证书
        switch (substr(__FILE__, strlen('/fh21_data/fh21_web/'),12)){
            case "fh21_develop":
                $APNS_ENV = ApnsAbstract::ENVIRONMENT_SANDBOX;
                $CLIENT_CERT = sprintf("/fh21_data/fh21_web/fh21_develop/iask/conf/apns-%d-dev.pem",$clientId);
                $entrustPem = "/fh21_data/fh21_web/fh21_develop/iask/conf/entrust_root_certification_authority.pem";
                break;
            default:
//            	$APNS_ENV = ApnsAbstract::ENVIRONMENT_SANDBOX;
//            	$CLIENT_CERT = sprintf("/fh21_data/fh21_web/fh21_new/iask/conf/apns-%d-dev.pem",$clientId);
//            	$entrustPem = "/fh21_data/fh21_web/fh21_new/iask/conf/entrust_root_certification_authority.pem";
//            	break;
            	
                $APNS_ENV = ApnsAbstract::ENVIRONMENT_PRODUCTION;
                $CLIENT_CERT = sprintf("/fh21_data/fh21_web/fh21_new/iask/conf/apns-%d-dis.pem",$clientId);
                $entrustPem = "/fh21_data/fh21_web/fh21_new/iask/conf/entrust_root_certification_authority.pem";
                break;
        }
        
        
        $push = new ApnsPush( $APNS_ENV, $CLIENT_CERT);


        /**
         * Set the Root Certificate Autority to verify the Apple remote peer
         *
         * How to Generate a Push Certificate
         * [root@mail cron]# wget https://www.entrust.net/downloads/binary/entrust_2048_ca.cer -O - > entrust_root_certification_authority.pem
         * [root@mail cron]# echo >> entrust_root_certification_authority.pem
         * [root@mail cron]# wget https://www.entrust.net/downloads/binary/entrust_ssl_ca.cer  -O - >> entrust_root_certification_authority.pem
         *
         */
        $push->setRootCertificationAuthority($entrustPem);
        
        //Set Provider certificate passphrase
        //不必当成参数来传递，根据environment 和 clientid 能够确定pem和passphrase
        $push->setProviderCertificatePassphrase('123456');
        
        // Connect to the Apple Push Notification Service
        $push->connect();

        foreach($arrQueueMsg as $msg){
            // Add the message to the message queue
            $push->add($msg);
        }

        // Send all messages in the message queue
        $push->send();
        // Disconnect from the Apple Push Notification Service
        $push->disconnect();

        // Examine the error message container
        $aErrorQueue = $push->getErrors();
        if (!empty($aErrorQueue)) {
            CLog::debug('APNS_PUSH:%s',var_export($aErrorQueue,true));
        }
    }    

	public static function removeEmoji($text) {
		$clean_text = ""; 
		// Match Emoticons
		$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
		$clean_text = preg_replace($regexEmoticons, '', $text);

		// Match Miscellaneous Symbols and Pictographs
		$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
		$clean_text = preg_replace($regexSymbols, '', $clean_text);

		// Match Transport And Map Symbols
		$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
		$clean_text = preg_replace($regexTransport, '', $clean_text);

		// Match Miscellaneous Symbols
		$regexMisc = '/[\x{2600}-\x{26FF}]/u';
		$clean_text = preg_replace($regexMisc, '', $clean_text);

		// Match Dingbats
		$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
		$clean_text = preg_replace($regexDingbats, '', $clean_text);

		return preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '';}, $clean_text); 
	}

	/*
	 * 说明：函数功能是把一个图像裁剪为任意大小的图像，图像不变形 参数说明：输入 需要处理图片的 文件名，生成新图片的保存文件名，生成新图片的宽，生成新图片的高
	*/
	// 获得任意大小图像，不足地方拉伸，不产生变形，不留下空白
	public static function resizeImage($src_file, &$dst_file, $new_width, $new_height) {
		if ($new_width < 1 || $new_height < 1) {
			CLog::debug('params width or height error !');
			return false;
		}
		if (! file_exists ( $src_file )) {
			CLog::debug('file is not exists !');
			return false;
		}
		
		// 图像类型
		$type = exif_imagetype ( $src_file );
		$support_type = array (
				IMAGETYPE_JPEG,
				IMAGETYPE_PNG,
				IMAGETYPE_GIF
		);

		if (! in_array ( $type, $support_type, true )) {
			CLog::debug('this type of image does not support! only support jpg , gif or png');
			return false;
		}
		
		// Load image
		switch ($type) {
			case IMAGETYPE_JPEG :
				$src_img = imagecreatefromjpeg ( $src_file );
				break;
			case IMAGETYPE_PNG :
				$src_img = imagecreatefrompng ( $src_file );
				break;
			case IMAGETYPE_GIF :
				$src_img = imagecreatefromgif ( $src_file );
				break;
			default :
				CLog::debug('Load image error!');
				return false;
		}

		$w = imagesx ( $src_img );
		$h = imagesy ( $src_img );
		$ratio_w = 1.0 * $new_width / $w;
		$ratio_h = 1.0 * $new_height / $h;
		$ratio = 1.0;
		// 生成的图像的高宽比原来的都小，或都大 ，原则是 取大比例放大，取大比例缩小（缩小的比例就比较小了）
		if (($ratio_w < 1 && $ratio_h < 1) || ($ratio_w > 1 && $ratio_h > 1)) {
			if ($ratio_w < $ratio_h) {
				$ratio = $ratio_h; // 情况一，宽度的比例比高度方向的小，按照高度的比例标准来裁剪或放大
			} else {
				$ratio = $ratio_w;
			}
			// 定义一个中间的临时图像，该图像的宽高比 正好满足目标要求
			$inter_w = ( int ) ($new_width / $ratio);
			$inter_h = ( int ) ($new_height / $ratio);
			 
			$inter_img = imagecreatetruecolor ( $inter_w, $inter_h );
			imagecopy ( $inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h );
			// 生成一个以最大边长度为大小的是目标图像$ratio比例的临时图像
			// 定义一个新的图像
			$new_img = imagecreatetruecolor ( $new_width, $new_height );
			imagecopyresampled ( $new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height, $inter_w, $inter_h );
			switch ($type) {
				case IMAGETYPE_JPEG :
					imagejpeg ( $new_img, $dst_file, 100 ); // 存储图像
					break;
				case IMAGETYPE_PNG :
					imagepng ( $new_img, $dst_file);
					break;
				case IMAGETYPE_GIF :
					imagegif ( $new_img, $dst_file);
					break;
				default :
					break;
			}
		}
		// 2 目标图像 的一个边大于原图，一个边小于原图 ，先放大平普图像，然后裁剪
		// =if( ($ratio_w < 1 && $ratio_h > 1) || ($ratio_w >1 && $ratio_h <1) )
		else {
			$ratio = $ratio_h > $ratio_w ? $ratio_h : $ratio_w; // 取比例大的那个值
			// 定义一个中间的大图像，该图像的高或宽和目标图像相等，然后对原图放大
			$inter_w = ( int ) ($w * $ratio);
			$inter_h = ( int ) ($h * $ratio);
			$inter_img = imagecreatetruecolor ( $inter_w, $inter_h );
			// 将原图缩放比例后裁剪
			imagecopyresampled ( $inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h, $w, $h );
			// 定义一个新的图像
			$new_img = imagecreatetruecolor ( $new_width, $new_height );
			imagecopy ( $new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height );
			switch ($type) {
				case IMAGETYPE_JPEG :
					imagejpeg ( $new_img, $dst_file, 100 ); // 存储图像
					break;
				case IMAGETYPE_PNG :
					imagepng ( $new_img, $dst_file);
					break;
				case IMAGETYPE_GIF :
					imagegif ( $new_img, $dst_file);
					break;
				default :
					break;
			}
		}
	} 
    /**
     * 检查输入是不是金额格式 标准形如：123.12|123
     * 
     * @param  string $money	要验证的数据
     * @return boolean 如果要验证的数据是合法的金额格式, 返回TRUE; 否则返回FALSE
     */
 	public static function check_money($money) {
            $pattern='/^([1-9][\d]{0,9}|0)(\.[\d]{1,2})?$/';
            return  preg_match($pattern,$money);
	}

    //快速排序
    /**
     * 根据二维数组某键值快速排序
     * @param $array二维数组
     * @param $left 0，数组0下标
     * @param $right 数组元素个数
     * @param $order desc 降序，asc升序
     * @param $key 排序的键名
     */
    public static function quick_sort(&$array, $left, $right, $key, $order = 'desc')
    {
        if($left >= $right)return;
        $i = $left; $j = $right;$x = $array[$left][$key]; $tmp = $array[$left];
        if($order == 'desc'){
            while ($i < $j)
            {
                while($i < $j && $array[$j][$key] <= $x) // 从右向左找第一个大于等x的数
                    $j--;
                if($i < $j)
                    $array[$i++] = $array[$j];

                while($i < $j && $array[$i][$key] > $x) // 从左向右找第一个小于x的数
                    $i++;
                if($i < $j)
                    $array[$j--] = $array[$i];
            }
        }else{
            while ($i < $j)
            {
                while($i < $j && $array[$j][$key] >= $x) // 从右向左找第一个小于x的数
                    $j--;
                if($i < $j)
                    $array[$i++] = $array[$j];

                while($i < $j && $array[$i][$key] < $x) // 从左向右找第一个大于等于x的数
                    $i++;
                if($i < $j)
                    $array[$j--] = $array[$i];
            }

        }
        $array[$i] = $tmp;
        self::quick_sort($array, $left, $i - 1, $key, $order); // 递归调用
        self::quick_sort($array, $left + 1, $right, $key, $order);
    }

    //base64_encode转码传参替换“+”“/”
    public static function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    //base64_encode解码替换“+”“/”
    public static function  urlsafe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
	//解析url的query成数组
	public static function convertUrlQuery($query) {
		$queryParts = explode('&', $query);
		$params = array();
		foreach($queryParts as $param) {
			$item = explode('=', $param);
			$params[$item[0]] = $item[1];
		}
		return $params;
    }
    //统一价格格式
    public static function unifyPriceFormat($price){

        if(is_null($price)){
            return "0";
        }
        list($integer, $float)  = explode('.', $price);
        $integer = (int)$integer;
        $float = (int)$float;
        if($float < 1){
            return (string)$integer;
        }else{
            return (string)$price;
        }
    } 
    
    /**
     * 字符串空格过滤且判断长度函数
     * 
     * @param str $string 字符串
     * @param str $max_length 长度最小限制
     * @param str $min_length 长度最大限制 最大值要大于等于最小值
     * @return boolean or string 如果符合条件返回去掉两端空格的字符串，如果不满足返回false
     */
    public static function checkStringLen ($string,$max_length,$min_length=1){
        $str = trim($string," ");
        
        $_str = str_replace(" ", "", $str);
        
        //判断其去掉空格后的长度
        if(self::check_string($_str, $max_length, $min_length)){
            return $str;
        }
        else{
            return false;
        }
    }

    /**
     * 版本比较
     * 
     * @param str $stand_ver 被比较的版本
     * @param str $client_ver 当前客户端版本
     * @return int  -1小于 0为相等 1为大于
     */
   	public static function compare_version($stand_ver = '', $client_ver)
	{
		$result = 0;
		if(false === self::check_string($stand_ver)) return $result;
		if(false === self::check_string($client_ver)) return $result;
		
		$stand_pieces = explode ( '.', $stand_ver );
		$client_pieces = explode ( '.', $client_ver );

		for($i = 0; $i < count ( $stand_pieces ); $i ++) {
			if( !isset($stand_pieces [$i]) || !isset($client_pieces [$i]) ){
				return $result;
			}
			$stand_segment = intval ( $stand_pieces [$i] );
			$client_segment = intval ( $client_pieces [$i] );
			if ($client_segment > $stand_segment) {
				$result = 1;
				break;
			} elseif ($client_segment < $stand_segment) {
				$result = - 1;
				break;
			}
		}

		return $result;
	}
	
	/**
	 * 判断小数点位数
	 *
	 * @param str $num 输入的东西
	 * @return int 
	 */
	public static function getFloatLength($num) {
		$count = 0;
		$temp = explode('.', $num);

		if(count($temp) > 1) {
			$decimal = end($temp);
			$count = strlen($decimal);
		}
	
		return $count;
	}
	
	/**
	 * 判断是否为爬虫访问
	 *
	 * @return boolean
	 */
	public static function isCrawler()
	{
		$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$spiders   = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
		foreach($spiders as $spider)
		{
			$spider = strtolower($spider);
			if(strpos($userAgent, $spider) !== false)
			{
				return true;
			}
		}
	
		return false;
	}
}

