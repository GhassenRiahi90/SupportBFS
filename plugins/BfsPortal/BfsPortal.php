<?php
/**
 * Plugin BfsPortal — Portail Support BFS
 */

require_once __DIR__ . '/inc/BfsSeed.php';

class BfsPortalPlugin extends MantisPlugin {
	function register() {
		$this->name        = 'BFS Support Portal';
		$this->description = 'Personnalisation graphique et fonctionnelle du portail support BFS.';
		$this->version     = '0.3.2';
		$this->author      = 'Business Financial Solutions';
		$this->url         = 'https://www.bfs.tn';
	}

	function config() {
		return array(
			'portal_seeded' => array(
				'type'    => PLUGIN_CONFIG_INT,
				'default' => OFF,
			),
		);
	}

	function hooks() {
		return array(
			'EVENT_LAYOUT_RESOURCES'   => 'resources',
			'EVENT_LAYOUT_BODY_BEGIN'  => 'body_begin',
			'EVENT_LAYOUT_PAGE_FOOTER' => 'page_footer',
		);
	}

	function install() {
		BfsSeed::run();
		return true;
	}

	function upgrade( $p_old_version ) {
		BfsSeed::run();
		return true;
	}

	function resources() {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com" />' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />' . "\n";
		echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap" />' . "\n";
		echo '<link rel="stylesheet" type="text/css" href="'
			. plugin_file( 'assets/css/bfs.css' )
			. '" />' . "\n";
		echo '<link rel="stylesheet" type="text/css" href="'
			. plugin_file( 'assets/css/bfs-footer.css' )
			. '" />' . "\n";
		echo '<script src="'
			. plugin_file( 'assets/js/bfs-portal.js' )
			. '"></script>' . "\n";
	}

	function body_begin() {
		echo '<script>document.documentElement.classList.add("bfs-portal");document.body.classList.add("bfs-portal");</script>' . "\n";
	}

	function page_footer() {
		echo '<style id="bfs-hide-mantis-footer">'
			. '.footer .footer-content > .col-md-6{display:none!important}'
			. '.footer .footer-content > .bfs-portal-footer{display:block!important;width:100%}'
			. '</style>' . "\n";

		$t_year = date( 'Y' );
		echo '<div class="bfs-portal-footer col-xs-12">' . "\n";
		echo '<p class="bfs-portal-footer__title">Business Financial Solutions</p>' . "\n";
		echo '<p class="bfs-portal-footer__meta">Portail support clients &mdash; '
			. '<a href="mailto:support@bfs.tn">support@bfs.tn</a>'
			. ' &mdash; <a href="https://www.bfs.tn" target="_blank" rel="noopener">www.bfs.tn</a></p>' . "\n";
		echo '<p class="bfs-portal-footer__copy">&copy; Copyright BFS ' . $t_year
			. ' | D&eacute;velopp&eacute; par '
			. '<a class="bfs-portal-footer__gas" href="https://growthacceleratorservices.tn/" '
			. 'target="_blank" rel="noopener noreferrer">Growth Accelerator Services (GAS)</a></p>' . "\n";
		echo '</div>' . "\n";
	}
}
