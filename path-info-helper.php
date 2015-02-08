<?php

class PathInfoHelper {
	public $TargetHost = '';
	public $RequestURI = '';
	public $ScriptFile = '';
	public $DirectoryString = '';
	public $ParameterString = '';
	public $Parameters;

	private $fieldHttpXForwardedHost = 'HTTP_X_FORWARDED_HOST';
	private $fieldHttpHost = 'HTTP_HOST';
	private $fieldPathInfo = 'PATH_INFO';
	private $fieldScriptFileName = 'SCRIPT_FILENAME';
	private $fieldScriptName = 'SCRIPT_NAME';
	private $fieldRequestUri = 'REQUEST_URI';

	private $pathSeparator = '/';
	private $searchSign = '?';

	function __construct() {
		$this->TargetHost = isset($_SERVER[$this->fieldHttpXForwardedHost]) ? $_SERVER[$this->fieldHttpXForwardedHost] : $_SERVER[$this->fieldHttpHost];
		$this->RequestURI = $_SERVER[$this->fieldRequestUri];
		$this->ScriptFile = basename($_SERVER[$this->fieldScriptFileName]);

		$this->DirectoryString = $this->getDirectoryString();
		if (substr($this->DirectoryString, -1) !== $this->pathSeparator) {
			$this->DirectoryString .= $this->pathSeparator;
		}

		$this->ParameterString = $this->getParameterString();
		$this->Parameters = explode($this->pathSeparator, trim($this->ParameterString, $this->pathSeparator));
	}

	private function getDirectoryString() {
		$scriptFile = $this->ScriptFile;
		$requestUri = $this->RequestURI;
		$searchPos = strpos($requestUri, $this->searchSign);
		if ($searchPos !== false) {
			$requestUri = substr($requestUri, 0, $searchPos);
		}

		//step 1.1 - if script filename in request uri, return the basename of request uri
		$scriptPos = strpos($requestUri, $this->pathSeparator . $scriptFile . $this->pathSeparator);
		if ($scriptPos !== false) {
			return substr($requestUri, 0, $scriptPos);
		}

		//step 1.2 - if script filename is in the end of request uri without "/", get the basename
		if (basename($requestUri) === $scriptFile) {
			return dirname($requestUri);
		}

		//step 1.3 - if script filename is totally hidden through rewrite, find same path prefix between script filename and request uri
		$requestDir = $_SERVER[$this->fieldScriptName];
		while (strlen($requestDir) > 1) {   // means $requestDir !== '/'
			if (substr($requestUri, 0, strlen($requestDir)) === $requestDir) {
				return $requestDir;
			} else {
				$requestDir = dirname($requestDir);
			}
		}

		//otherwise, the requested script should be a default index file under server root path
		return $requestUri;
	}

	private function getParameterString() {
		//step 1 - if server supports PATH_INFO (like Apache), return value directory
		if (isset($_SERVER[$this->fieldPathInfo])) {
			return $_SERVER[$this->fieldPathInfo];
		}

		//step 2 - calculate path info manually
		$scriptFile = $this->ScriptFile;
		$requestUri = $this->RequestURI;
		$searchPos = strpos($requestUri, $this->searchSign);
		if ($searchPos !== false) {
			$requestUri = substr($requestUri, 0, $searchPos);
		}

		//step 2.1 - if script filename in request uri, return the rest part of request uri after script filename
		$scriptPos = strpos($requestUri, $this->pathSeparator . $scriptFile . $this->pathSeparator);
		if ($scriptPos !== false) {
			return substr($requestUri, $scriptPos + strlen($scriptFile) + 1);
		}

		//step 2.2 - if script filename is totally hidden through rewrite, find same path prefix between script filename and request uri, then return the rest part of request uri after common path
		$requestDir = $_SERVER[$this->fieldScriptName];
		while (strlen($requestDir) > 1) {   // means $requestDir !== '/'
			if (substr($requestUri, 0, strlen($requestDir)) === $requestDir) {
				return substr($requestUri, strlen($requestDir));
			} else {
				$requestDir = dirname($requestDir);
			}
		}

		//otherwise, the requested script should be a default index file under server root path, the whole request uri should be the path info
		if (strlen($requestUri) > 1) {
			return $requestUri;
		} else {
			return '';
		}
	}

	public function getParameterValue($param) {
		for ($i = 0; $i < count($this->Parameters); $i++) {
			if ($this->Parameters[$i] === $param) {
				return isset($this->Parameters[$i + 1]) ? $this->Parameters[$i + 1] : '';
			}
		}

		return '';
	}

	public function getParameterValues($param, $valueCount) {
		for ($i = 0; $i < count($this->Parameters); $i++) {
			if ($this->Parameters[$i] === $param) {
				return array_slice($this->Parameters, $i + 1, $valueCount);
			}
		}

		return '';
	}
}