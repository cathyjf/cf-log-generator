<?php

namespace CFLogGenerator;

$config = [

	// CloudFlare email address.
	'email' => 'user@example.com',

	// CloudFlare API key.
	'api-key' => '[example]',

	// Zones for which to compile stats.
	'zones' => [
		'example.com'
	],

	// Whether to look up a geoname for each visitor using the Google Maps
	// reverse geocoding API -- this is quite slow and will probably be
	// prohibitive for a busy zone.
	'geoname' => TRUE,

	// Whether to perform a reverse DNS query on each visitor. This is
	// relatively slow (but not as slow as Google Maps) and may need to
	// be disabled for a busy zone.
	'rdns' => TRUE,

	// Endpoint for CloudFlare API.
	// You should not need to modify this under normal circumstances.
	'endpoint' => 'https://www.cloudflare.com/api_json.html'

];
