<?php
/**
 * Handles requests for secure files by url.
 *
 * @package securefiles
 * @author Hamish Campbell <hn.campbell@gmail.com>
 * @copyright copyright (c) 2010, Hamish Campbell 
 */
class SecureFileController extends Controller implements PermissionProvider {

	static $htaccess_file = ".htaccess";

	/**
	 * Secure files htaccess rules
	 * 
	 * @return string
	 */	
	static function HtaccessRules() {
		$rewrite = 
			"RemoveHandler .php .phtml .php3 ,php4 .php5 .inc \n" . 
			"RemoveType .php .phtml .php3 .php4 .php5 .inc \n" .
			"RewriteEngine On\n" .
			"RewriteBase " . (BASE_URL ? BASE_URL : "/") . "\n" . 
			"RewriteCond %{REQUEST_URI} ^(.*)$\n" .
			"RewriteRule (.*) " . SAPPHIRE_DIR . "/main.php?url=%1&%{QUERY_STRING} [L]\n";
		return $rewrite;
	}

	/**
	 * Process incoming requests passed to this controller
	 * 
	 * @return HTTPResponse
	 */
	function handleAction() {
		$url = array_key_exists('url', $_GET) ? $_GET['url'] : $_SERVER['REQUEST_URI'];
		$file = File::find(Director::makeRelative($url));
		if($file instanceof File) {
			return ($file->canView())
				? $this->FileFound($file)
				: $this->FileNotAuthorized("Not Authorized");
		} else {
			return $this->FileNotFound("Not Found");
		}
	}
	
	/**
	 * File Not Found response
	 * 
	 * @param $body Optional message body
	 * @return HTTPResponse
	 */
	function FileNotFound($body = "") {
		return new HTTPResponse($body, 404);
	}
	
	/**
	 * File not authorized response
	 * 
	 * @param $body Optional message body
	 * @return HTTPResponse
	 */
	function FileNotAuthorized($body = "") {
		Security::permissionFailure($this, $body);
	}
	
	/**
	 * File found response
	 *
	 * @param $file File to send
	 * @see HTTPRequest::send_file()
	 */
	function FileFound($file) {
		return HTTPRequest::send_file(file_get_contents($file->FullPath), $file->Filename);
	}	
	
	/**
	 * Permission provider for access to secure files
	 * 
	 * @return array
	 */
	function providePermissions() {
		return array(
			'SECURE_FILE_ACCESS' => _t('SecureFiles.SECUREFILEACCESS', 'Access to Secured Files'),
		);
	}

}
?>
