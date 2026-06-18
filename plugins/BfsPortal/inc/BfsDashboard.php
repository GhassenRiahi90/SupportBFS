<?php
/**
 * Tableau de bord BFS (Mon affichage).
 */

require_once __DIR__ . '/BfsClientAccess.php';

class BfsDashboard {
	public static function render() {
		if( !auth_is_user_authenticated() || !is_page_name( 'my_view_page.php' ) ) {
			return;
		}

		require_api( 'current_user_api.php' );
		require_api( 'project_api.php' );
		require_api( 'user_api.php' );
		require_api( 'access_api.php' );

		$t_user_id = auth_get_current_user_id();
		$t_realname = user_get_name( $t_user_id );
		$t_project_id = helper_get_current_project();
		$t_can_report = access_has_any_project_level( 'report_bug_threshold' );
		$t_is_client = BfsClientAccess::is_client_user( $t_user_id );
		$t_client_projects = $t_is_client ? BfsClientAccess::get_assigned_project_ids( $t_user_id ) : array();

		$t_reported_open = user_get_reported_open_bug_count( $t_user_id, $t_project_id );
		$t_assigned_open = user_get_assigned_open_bug_count( $t_user_id, $t_project_id );

		$t_report_url = '';
		if( $t_can_report ) {
			if( ALL_PROJECTS != $t_project_id && project_exists( $t_project_id ) ) {
				$t_report_url = BfsClientAccess::get_bug_report_url( $t_project_id );
			} elseif( $t_is_client && 1 === count( $t_client_projects ) ) {
				$t_report_url = BfsClientAccess::get_bug_report_url( $t_client_projects[0] );
			}
		}
		$t_view_url = 'view_all_bug_page.php';
		echo '<div class="col-xs-12 bfs-dashboard">' . "\n";
		echo '<div class="bfs-dashboard__hero widget-box widget-color-blue2">' . "\n";
		echo '<div class="widget-header widget-header-flat">' . "\n";
		echo '<h4 class="widget-title lighter">';
		echo string_display_line( sprintf( 'Bonjour %s', $t_realname ) );
		echo '</h4></div>' . "\n";
		echo '<div class="widget-body"><div class="widget-main padding-16">' . "\n";

		echo '<p class="bfs-dashboard__lead">';
		echo 'Bienvenue sur votre portail support BFS. Consultez vos demandes en cours ou d&eacute;clarez une nouvelle demande pour votre organisation.';
		echo '</p>' . "\n";

		if( ALL_PROJECTS != $t_project_id && project_exists( $t_project_id ) ) {
			echo '<p class="bfs-dashboard__context">';
			echo '<strong>Client actif :</strong> ';
			echo string_display_line( project_get_field( $t_project_id, 'name' ) );
			echo '</p>' . "\n";
		} elseif( $t_is_client && 0 === count( $t_client_projects ) ) {
			echo '<p class="bfs-dashboard__context bfs-dashboard__context--hint">';
			echo 'Aucun espace client ne vous est assign&eacute;. Contactez <a href="mailto:support@bfs.tn">support@bfs.tn</a>.';
			echo '</p>' . "\n";
		} elseif( $t_is_client && count( $t_client_projects ) > 1 ) {
			echo '<p class="bfs-dashboard__context bfs-dashboard__context--hint">';
			echo 'S&eacute;lectionnez votre espace client dans le menu <strong>Tous les clients</strong> en haut &agrave; droite.';
			echo '</p>' . "\n";
		} else {
			echo '<p class="bfs-dashboard__context bfs-dashboard__context--hint">';
			echo 'S&eacute;lectionnez votre espace client dans le menu <strong>Tous les clients</strong> en haut &agrave; droite.';
			echo '</p>' . "\n";
		}
		echo '<div class="bfs-dashboard__actions">' . "\n";
		if( $t_can_report && !is_blank( $t_report_url ) ) {
			echo '<a class="btn btn-primary btn-white btn-round" href="' . string_sanitize_url( $t_report_url ) . '">';
			echo '<i class="fa fa-plus ace-icon"></i> Nouvelle demande</a>' . "\n";
		}
		echo '<a class="btn btn-default btn-white btn-round" href="' . helper_mantis_url( $t_view_url ) . '">';
		echo '<i class="fa fa-list-alt ace-icon"></i> Mes demandes</a>' . "\n";
		echo '<a class="btn btn-default btn-white btn-round" href="mailto:support@bfs.tn">';
		echo '<i class="fa fa-envelope ace-icon"></i> support@bfs.tn</a>' . "\n";
		echo '</div>' . "\n";

		echo '<div class="bfs-dashboard__stats">' . "\n";
		echo '<div class="bfs-dashboard__stat">';
		echo '<span class="bfs-dashboard__stat-value">' . (int)$t_reported_open . '</span>';
		echo '<span class="bfs-dashboard__stat-label">Demandes ouvertes d&eacute;clar&eacute;es par moi</span>';
		echo '</div>' . "\n";
		if( $t_assigned_open > 0 || access_has_any_project_level( 'handle_bug_threshold' ) ) {
			echo '<div class="bfs-dashboard__stat">';
			echo '<span class="bfs-dashboard__stat-value">' . (int)$t_assigned_open . '</span>';
			echo '<span class="bfs-dashboard__stat-label">Demandes qui me sont assign&eacute;es</span>';
			echo '</div>' . "\n";
		}
		echo '</div>' . "\n";

		echo '</div></div></div>' . "\n";

		echo '<div class="bfs-dashboard__links widget-box widget-color-blue2">' . "\n";
		echo '<div class="widget-header widget-header-small"><h4 class="widget-title lighter">Solutions BFS</h4></div>' . "\n";
		echo '<div class="widget-body"><div class="widget-main padding-12">' . "\n";
		echo '<ul class="bfs-dashboard__solutions">' . "\n";
		self::render_solution_link( 'Sage XRT (Cash Management)', 'Gestion de tr&eacute;sorerie et cash management.' );
		self::render_solution_link( 'Sage SXA', 'Architecture et int&eacute;gration bancaire.' );
		self::render_solution_link( 'BFS Treasury Analytics (Power BI)', 'Solution propri&eacute;taire BFS — tableaux de bord Power BI.', true );
		self::render_solution_link( 'Support G&eacute;n&eacute;ral BFS', 'Demandes g&eacute;n&eacute;rales, commerciales et contractuelles.' );
		echo '</ul></div></div></div>' . "\n";

		echo '</div>' . "\n";
		echo '<div class="clearfix"></div><div class="space-10"></div>' . "\n";
	}

	public static function filter_sidebar( array $p_items ) {
		if( !auth_is_user_authenticated() ) {
			return $p_items;
		}

		if( BfsClientAccess::is_client_user() ) {
			$t_filtered = BfsClientAccess::filter_sidebar_items( $p_items );
			self::append_sidebar_links( $t_filtered );
			return $t_filtered;
		}

		if( access_get_global_level( auth_get_current_user_id() ) >= DEVELOPER ) {
			self::append_sidebar_links( $p_items );
			return $p_items;
		}

		$t_hide_urls = array(
			'changelog_page.php',
			'roadmap_page.php',
			'summary_page.php',
		);

		$t_filtered = array();
		foreach( $p_items as $t_item ) {
			if( !isset( $t_item['url'] ) ) {
				$t_filtered[] = $t_item;
				continue;
			}
			$t_skip = false;
			foreach( $t_hide_urls as $t_hide ) {
				if( false !== strpos( $t_item['url'], $t_hide ) ) {
					$t_skip = true;
					break;
				}
			}
			if( !$t_skip ) {
				$t_filtered[] = $t_item;
			}
		}

		self::append_sidebar_links( $t_filtered );

		return $t_filtered;
	}

	private static function append_sidebar_links( array &$p_items ) {
		$t_exists = function( $p_needle ) use ( $p_items ) {
			foreach( $p_items as $t_item ) {
				if( isset( $t_item['url'] ) && false !== strpos( $t_item['url'], $p_needle ) ) {
					return true;
				}
			}
			return false;
		};

		if( !$t_exists( 'support@bfs.tn' ) ) {
			$p_items[] = array(
				'url'   => 'mailto:support@bfs.tn',
				'title' => 'bfs_contact_support_link',
				'icon'  => 'fa-envelope',
			);
		}

		if( !$t_exists( 'www.bfs.tn' ) ) {
			$p_items[] = array(
				'url'   => 'https://www.bfs.tn',
				'title' => 'bfs_website_link',
				'icon'  => 'fa-external-link',
			);
		}
	}

	private static function render_solution_link( $p_title, $p_desc, $p_proprietary = false ) {
		echo '<li class="bfs-dashboard__solution">';
		echo '<strong>' . $p_title . '</strong>';
		if( $p_proprietary ) {
			echo ' <span class="bfs-proprietary-badge">Solution propri&eacute;taire BFS</span>';
		}
		echo '<br /><span class="bfs-dashboard__solution-desc">' . $p_desc . '</span>';
		echo '</li>' . "\n";
	}
}
