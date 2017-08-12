<?php
class ServiceDiscovery {

	/* Currently only support for etcd */

	public $services;

	private $service_name;
	private $service_list;
	private $etcd_base;

	function __construct($service_name = NULL, $etcd_base = "/services") {

		$this->services = apc_fetch("sd_services");
		if ($this->services !== false) {
			return;
		}

		error_log("Fetching services from etcd");

		/* Allow specifying of service_name directly or via ENV before falling back to "mysql" */
		if ($service_name == NULL) {
			$this->service_name = (getenv("SD_SERVICE_NAME")) ? getenv("SD_SERVICE_NAME") : "mysql";
		} else {
			$this->service_name = $service_name;
		}

		/* Base URL for etcd requests */
		$this->etcd_base = $etcd_base;

		/* Get the service list */
		$this->get_service_list();

		/* Start getting some hosts */
		foreach ($this->service_list as $service) {
			$etcd_result = $this->etcd_get($service, "");
			if ($etcd_result->node->nodes) {
				foreach ($etcd_result->node->nodes as $node) {
					$value = explode(":", $node->value);
					$service_results[] = array("id" => basename($node->key), "host" => $value[0], "port" => $value[1]);
				}
				$this->services[] = array("name" => basename($service), "nodes" => $service_results);
			}
		}

		/* Save the services to apc */
		apc_add("sd_services", $this->services, 10);
	}

	function get_service_list() {
		/* Find the path to the service incase we want to match multiple services */
		$search = trim(dirname($this->service_name), ". \t\n\r\0\x0B");
		$result = $this->etcd_get($search);

		/* Find the services that match the service_name patten */
		foreach ($result->node->nodes as $item) {
			if (fnmatch($this->etcd_base."/".$this->service_name, $item->key)) {
				$this->service_list[] = $item->key;
			}
		}
		sort($this->service_list);
	}

	private function etcd_get($url, $base = false) {
		$base = ($base===false) ? $this->etcd_base : $base;
		/* Get some key from etcd */
		$etcd_url = explode(",", getenv("ETCDCTL_PEERS"))[0]."/v2/keys".$base.$url;
		require_once('HTTP/Request2.php');
		$request = new HTTP_Request2($etcd_url, HTTP_Request2::METHOD_GET);
		$res = $request->send();

		return json_decode($res->getBody());
	}

}
