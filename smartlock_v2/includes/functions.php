<?php

# by JiangCat
# 整个网站使用的独立功能性函数
/*
class functions
{
	// 脚本执行结束的时候执行，类似__destruct()。
	function finish() {
		if ( !USE_SHUTDOWN ) {
			$this->do_shutdown();
		}
	}

	// 具体执行的结束代码
	function do_shutdown() {
		global $DB;
		if ( $DB ) {
			$DB->return_die = 0;
			// 如果有“后优先执行”代码，在脚本被释放前执行。
			if ( $DB->shutdown_queries ) {
				foreach ( $DB->shutdown_queries AS $query ) {
					$DB->query_unbuffered($query);
				}
			}
			$DB->return_die = 1;
			$DB->shutdown_queries = array();
			$DB->close_db();
		}
	}
}
*/
// 分析并返回合理的邮件地址，否则返回空字符串。
function clean_email($email = '') {
	$email = trim($email);
	if (preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s\'"<>]+\.+[a-z]{2,6}))$#si', $email))
		return $email;
	return '';
}

// 过滤POST和GET输入内容，防止注入。
function init_input() {
	if ( empty($_GET) && empty($_POST) )
		return array();

	$return = array();
	foreach( array($_GET, $_POST) AS $type ) {
		if ( is_array($type) ) {
			$k = clean_key($k);
			foreach ( $type AS $k => $v ) {
				if ( is_array($v) ) {
					foreach ( $v AS $k1 => $v1 ) {
						$k1 = clean_key($k1);
						$return[$k][$k1] = clean_value($v1);
					}
				}
				else {
					$return[$k] = clean_value($v);
				}
			}
		}
	}
	return $return;
}

// 对POST和GET的键进行过滤。
function clean_key($key) {
	if ( is_numeric($key) )
		return $key;
	else if ( empty($key) )
		return '';

	if ( strpos($key, '..') !== false )
		$key = str_replace('..', '', $key);
	if ( strpos($key, '__') !== false )
		$key = preg_replace('/__(?:.+?)__/', '', $key);

	return preg_replace('/^([\w\.\-_]+)$/', '\\1', $key);
}

// 对POST和GET的值进行过滤。
function clean_value($val) {
	if ( is_numeric($val) )
		return $val;
	else if ( empty($val) )
		return is_array($val) ? array() : '';

	$val = stripslashes($val);

//	if ( $thisenc = mb_check_encoding ($val, 'UTF-8') != TRUE )
//		$val = mb_convert_encoding($val, 'UTF-8');

	$val = preg_replace('/&(?!#[0-9]+;)/si', '&amp;', $val);

//	$val = preg_replace("/<script/i", "&#60;script", $val);
//	$pregfind = array('&#032;', '<!--', '-->', '>', '<', '"', '!', "'", "\n", '$', "\r");
//	$pregreplace = array(' ', '&#60;&#33;--', '--&#62;', '&gt;', '&lt;', '&quot;', '&#33;', '&#39;', '<br />', '&#036;', '');

	$pregfind = array('&#032;', '>', '<', '"', "'", '$', '\\');
	$pregreplace = array(' ', '&gt;', '&lt;', '&quot;', '&#39;', '&#036;', '&#092;');
	$val = str_replace($pregfind, $pregreplace, $val);

//	$val = nl2br($val);
	$val = str_replace("\r\n", "<br />", $val);
	$val = str_replace("\n", "<br />", $val);

	return $val;
}

// 最底层致命错误。
function notify_fail($msg='') {
	if ( !$msg )
		echo 'Error!';
	else
		echo 'Error: '.$msg;
	die();
}

// 生成一段“可读”或“完全随机”的乱序码。
function generate_hash($l=32, $readable=FALSE) {
	$w = 'lIOozZq-!~*()!^_|';
	$r = 'abcdefghijkmnprstuvwxyABCDEFGHJKLMNPQRSTUVWXY0123456789';
	$e = $readable ? str_split($r) : str_split(($r.$w));
	$x = '';
	for ( $i=0; $i<$l; $i++ )
		$x .= $e[rand(0,(count($e)-1))];
	return $x;
}

// 从一个Array中删除一个元素。
function remove_from_array($n, $a) {
	$r = array();
	foreach ( $a AS $l )
		if ( $l != $n )
			$r[] = $l;
	return $r;
}

function json_addslashes($s='') {
	return str_replace(array("'", "&#39;"), "\\'", $s);
//	return str_replace(array("'", "&#39;"), "&amp;#39;", $s);
}

function clean_array($arr) {
	foreach ( $arr AS $k => $v ) {
		if ( is_array($v) )
			$arr[$k] = clean_array($v);
		else
			$arr[$k] = is_numeric($v) ? $v+0 : $v;
	}
	return $arr;
}

// Redirect a page request
function page_redirect($url='') {
	if ( !$url )
		$url = './';
	$url = str_replace('&amp;', '&', $url);
	while ( ob_get_length() )
		ob_end_clean();
	@header("location: $url");
	die();
}

?>