<?php
/**
 * cf-log-generator (CloudFlare Log Generator)
 *
 * After deploying CloudFlare on a domain, web server access logs become
 * significantly less useful because many resources are served directly
 * from CloudFlare's cache and so bypass the web server entirely.
 *
 * This program attempts to mitigate the issue somewhat by using the
 * CloudFlare API to generate basic access logs for a website which sits
 * behind the CloudFlare reverse proxy network.
 *
 * @author Cathy J. Fitzpatrick <cathy@cathyjf.com>
 * @licence public domain (see the LICENSE file)
 */

namespace CFLogGenerator;

// =======================================================================

class CFClient {
	private $url, $email, $key;

	public function __construct($url, $email, $key) {
		$this->url = $url;
		$this->email = $email;
		$this->key = $key;
	}

	// Make a request of type $action to the CloudFlare API.
	// $data is an array of additional parameters to submit.
	private function makeRequest($action, $data = []) {
		$data['a'] = $action;
		$data['email'] = $this->email;
		$data['tkn'] = $this->key;

		$options = [
			'http' => [
				'header'  =>
					"Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data),
			]
		];
		$context  = stream_context_create($options);
		$result = file_get_contents($this->url, false, $context);

		return json_decode($result, TRUE);
	}

	public function getZoneIPs($zone, $hours, $geo) {
		$data = $this->makeRequest('zone_ips', [
			'z' => $zone,
			'hours' => $hours,
			'geo' => $geo
		]);
		if (!isset($data['response'])) {
			return FALSE;
		}
		return $data['response']['ips'];
	}
}

// =======================================================================

class GoogleMaps {
	private static $endpoint =
		'http://maps.googleapis.com/maps/api/geocode/json';

	public static function reverseGeocode($latitude, $longitude) {
		$query = http_build_query([
			'latlng' => $latitude . ',' . $longitude,
			// Google requires that we indicate whether the coordinate
			// comes from a location tracking sensor.
			'sensor' => 'false'
		]);
		$json = json_decode(file_get_contents(
				GoogleMaps::$endpoint . '?' . $query), TRUE);
		if (($json === NULL) || ($json['status'] !== 'OK') ||
				(count($json['results']) === 0)) {
			return FALSE;
		}
		// Google Maps returns a series of names in decreasing order of
		// level of detail; we'll just pick the most detailed one.
		return $json['results'][0]['formatted_address'];
	}
}

// =======================================================================

class LogGenerator {
	private $client, $zone;

	public function __construct($client, $zone) {
		$this->client = $client;
		$this->zone = $zone;
	}

	public function getVisitors($hours = 1, $geo = 1,
			$geoname = TRUE, $rdns = TRUE) {
		$visitors = $this->client->getZoneIPs($this->zone, $hours, $geo);

		// Decorate the $visitors array with additional information.
		foreach ($visitors as &$i) {
			// Reverse geocoding
			if ($geo && $geoname) {
				$i['geoname'] = GoogleMaps::reverseGeocode(
					$i['latitude'], $i['longitude']
				);
			}

			// Lookup hostnames
			if ($rdns) {
				$i['hostname'] = gethostbyaddr($i['ip']);
			}

			// Remove useless `zone_name` property
			unset($i['zone_name']);
		}

		return $visitors;
	}
}

// =======================================================================

require_once 'config.inc.php';

$client = new CFClient(
		$config['endpoint'], $config['email'],$config['api-key']);

$generators = [];
foreach ($config['zones'] as &$i) {
	$g = $generators[] = new LogGenerator($client, $i);
	print_r($g->getVisitors(1, 1, $config['geoname'], $config['rdns']));
}
