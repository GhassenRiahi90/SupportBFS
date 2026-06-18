<?php
/**
 * Point d'entrée seed / migration (délègue à BfsArchitecture).
 */

require_once __DIR__ . '/BfsArchitecture.php';
require_once __DIR__ . '/BfsClient.php';

class BfsSeed {
	public static function run() {
		BfsArchitecture::run();
	}

	public static function all_projects_exist() {
		return BfsArchitecture::is_ready();
	}
}
