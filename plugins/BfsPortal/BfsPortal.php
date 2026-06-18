<?php
/**
 * Plugin BfsPortal — Portail Support BFS
 *
 * Phase 1 : squelette installable (charte graphique et fonctionnalités en Phase 2+).
 */

class BfsPortalPlugin extends MantisPlugin {
	function register() {
		$this->name        = 'BFS Support Portal';
		$this->description = 'Personnalisation graphique et fonctionnelle du portail support BFS.';
		$this->version     = '0.1.0';
		$this->author      = 'Business Financial Solutions';
		$this->url         = 'https://www.bfs.tn';
	}

	function hooks() {
		return array(
			'EVENT_LAYOUT_RESOURCES' => 'resources',
		);
	}

	function resources() {
		echo '<link rel="stylesheet" type="text/css" href="'
			. plugin_file( 'assets/css/bfs.css' )
			. '" />' . "\n";
	}
}
