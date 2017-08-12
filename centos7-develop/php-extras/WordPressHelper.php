<?php
class WordPressHelper {

	private $db_host;
	private $env_keys = ["DB_NAME", "DB_USER", "DB_PASSWORD", "DB_CHARSET",
	"WP_DEBUG", "WP_HOME", "WP_SITEURL", "AUTH_KEY", "AUTH_KEY",
	"SECURE_AUTH_KEY", "LOGGED_IN_KEY", "NONCE_KEY", "AUTH_SALT",
	"SECURE_AUTH_SALT", "LOGGED_IN_SALT", "NONCE_SALT"];
	
	function __construct() {

		/* if ETCDCTL_PEERS is defined assume we are using dynamic service
		   discovery */
		if (getenv("ETCDCTL_PEERS")) {
			require_once('ServiceDiscovery.php');
			$sd = new ServiceDiscovery();
			$this->db_host = $sd->services[0]["nodes"][0]["host"] . ":" . $sd->services[0]["nodes"][0]["port"];
		} elseif (getenv("DB_HOST")) {
			$this->db_host = getenv("DB_HOST");
		}

	}

	function run() {

		if ($this->db_host) {
			define('DB_HOST', $this->db_host);
		}

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
