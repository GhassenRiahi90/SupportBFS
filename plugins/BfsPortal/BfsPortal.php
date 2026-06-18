<?php
/**
 * Plugin BfsPortal — Portail Support BFS
 */

require_once __DIR__ . '/inc/BfsSeed.php';
require_once __DIR__ . '/inc/BfsArchitecture.php';
require_once __DIR__ . '/inc/BfsDashboard.php';

class BfsPortalPlugin extends MantisPlugin {
	function register() {
		$this->name        = 'BFS Support Portal';
		$this->description = 'Personnalisation graphique et fonctionnelle du portail support BFS.';
		$this->version     = '0.5.0';
		$this->author      = 'Business Financial Solutions';
		$this->url         = 'https://www.bfs.tn';
	}

	function config() {
		return array(
			'portal_seeded' => array(
				'type'    => PLUGIN_CONFIG_INT,
				'default' => OFF,
			),
			'architecture_v2' => array(
				'type'    => PLUGIN_CONFIG_INT,
				'default' => OFF,
			),
		);
	}

	function hooks() {
		return array(
			'EVENT_CORE_READY'           => 'core_ready',
			'EVENT_LAYOUT_RESOURCES'     => 'resources',
			'EVENT_LAYOUT_BODY_BEGIN'    => 'body_begin',
			'EVENT_LAYOUT_CONTENT_BEGIN' => 'content_begin',
			'EVENT_LAYOUT_PAGE_FOOTER'   => 'page_footer',
			'EVENT_MENU_MAIN_FILTER'     => 'menu_main_filter',
		);
	}

	/**
	 * Auto-réparation : migration architecture client si nécessaire.
	 */
	function core_ready() {
		if( !BfsArchitecture::is_ready() ) {
			BfsArchitecture::run();
		}
	}

	function install() {
		BfsSeed::run();
		return true;
	}

	/**
	 * Schéma plugin : déclenche le bouton « Mettre à jour » dans MantisBT.
	 */
	function schema() {
		return array(
			array( null ),
			array( null ),
			array( null ),
			array( null ),
			array( null ),
		);
	}

	/**
	 * @param int $p_schema Index d'étape de schéma (pas l'ancienne version).
	 */
	function upgrade( $p_schema ) {
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
		echo '<link rel="stylesheet" type="text/css" href="'
			. plugin_file( 'assets/css/bfs-dashboard.css' )
			. '" />' . "\n";
		echo '<script src="'
			. plugin_file( 'assets/js/bfs-portal.js' )
			. '"></script>' . "\n";
	}

	function body_begin() {
		echo '<script>document.documentElement.classList.add("bfs-portal");document.body.classList.add("bfs-portal");</script>' . "\n";
	}

	function content_begin() {
		BfsDashboard::render();
	}

	function menu_main_filter( $p_event, array $p_sidebar_items ) {
		$t_items = BfsDashboard::filter_sidebar( $p_sidebar_items );
		return array( $t_items );
	}

	function page_footer() {
		echo '<style id="bfs-hide-mantis-footer">'
			. '.footer .footer-content > .col-md-6{display:none!important}'
			. '.footer .footer-content > .bfs-portal-footer{display:block!important;width:100%}'
			. '.footer .bfs-portal-footer__row{display:flex!important;flex-wrap:wrap;align-items:center;justify-content:space-between;width:100%;gap:.5rem 1rem}'
			. '.footer .bfs-portal-footer__meta{margin-left:auto!important;text-align:right!important}'
			. '</style>' . "\n";

		$t_year = date( 'Y' );
		echo '<div class="bfs-portal-footer col-xs-12">' . "\n";
		echo '<div class="bfs-portal-footer__row">' . "\n";
		echo '<div class="bfs-portal-footer__copy">&copy; Copyright BFS ' . $t_year
			. ' | D&eacute;velopp&eacute; par '
			. '<a class="bfs-portal-footer__gas" href="https://growthacceleratorservices.tn/" '
			. 'target="_blank" rel="noopener noreferrer">Growth Accelerator Services (GAS)</a></div>' . "\n";
		echo '<div class="bfs-portal-footer__meta">Portail support clients &mdash; '
			. '<a href="mailto:support@bfs.tn">support@bfs.tn</a>'
			. ' &mdash; <a href="https://www.bfs.tn" target="_blank" rel="noopener">www.bfs.tn</a></div>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	}
}
