<?php
class WordPressHelper {

	private $env_keys = ["DB_HOST", "DB_NAME", "DB_USER", "DB_PASSWORD",
	"DB_CHARSET", "WP_DEBUG", "WP_HOME", "WP_SITEURL", "AUTH_KEY", "AUTH_KEY",
	"SECURE_AUTH_KEY", "LOGGED_IN_KEY", "NONCE_KEY", "AUTH_SALT",
	"SECURE_AUTH_SALT", "LOGGED_IN_SALT", "NONCE_SALT"];
	
	function run() {

		foreach ($this->env_keys as $key) {
			$this->define_from_env($key);
		}

	}

	private function define_from_env($key) {

		$value = getenv($key);
		if ($value !== false) {
			define($key, $value);
		}

	}

}
