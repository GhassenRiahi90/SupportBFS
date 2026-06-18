<?php
/**
 * Support BFS — profil utilisateur client et restrictions d'accès.
 */

class BfsClientAccess {
	const CLIENT_MAX_ACCESS = REPORTER;

	/**
	 * Applique la configuration MantisBT pour le cloisonnement client (idempotent).
	 */
	public static function apply_global_config() {
		if( ON == plugin_config_get( 'isolation_config_applied', OFF ) ) {
			return;
		}

		config_set_global( 'limit_reporters', ON );
		config_set_global( 'allow_reporter_close', OFF );
		config_set_global( 'allow_reporter_reopen', ON );
		config_set_global( 'show_user_email_threshold', NOBODY );
		config_set_global( 'allow_signup', OFF );

		plugin_config_set( 'isolation_config_applied', ON );
	}

	/**
	 * Utilisateur client : niveau global <= Rapporteur et sans accès équipe BFS.
	 */
	public static function is_client_user( $p_user_id = null ) {
		if( !auth_is_user_authenticated() && null === $p_user_id ) {
			return false;
		}

		require_api( 'access_api.php' );
		require_api( 'user_api.php' );

		if( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		if( $p_user_id <= 0 || !user_exists( $p_user_id ) ) {
			return false;
		}

		if( user_is_administrator( $p_user_id ) ) {
			return false;
		}

		$t_global = access_get_global_level( $p_user_id );
		if( $t_global >= config_get( 'private_project_threshold' ) ) {
			return false;
		}

		return $t_global <= self::CLIENT_MAX_ACCESS;
	}

	/**
	 * Projets explicitement assignés à l'utilisateur (table project_user_list).
	 *
	 * @return int[]
	 */
	public static function get_assigned_project_ids( $p_user_id = null ) {
		require_api( 'user_api.php' );

		if( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		$t_rows = user_get_assigned_projects( $p_user_id );
		return array_map( 'intval', array_keys( $t_rows ) );
	}

	/**
	 * Verrouille le projet courant sur l'unique espace client assigné.
	 */
	public static function enforce_single_project_context() {
		if( !auth_is_user_authenticated() || !self::is_client_user() ) {
			return;
		}

		require_api( 'helper_api.php' );

		$t_assigned = self::get_assigned_project_ids();
		if( 1 !== count( $t_assigned ) ) {
			return;
		}

		$t_project_id = $t_assigned[0];
		if( helper_get_current_project() != $t_project_id ) {
			helper_set_current_project( $t_project_id );
		}
	}

	/**
	 * Filtre le menu latéral pour les utilisateurs client.
	 */
	public static function filter_sidebar_items( array $p_items ) {
		if( !auth_is_user_authenticated() ) {
			return $p_items;
		}

		if( !self::is_client_user() ) {
			return $p_items;
		}

		$t_hide_fragments = array(
			'changelog_page.php',
			'roadmap_page.php',
			'summary_page.php',
			'main_page.php',
			'proj_doc_page.php',
			'wiki.php',
			'billing_page.php',
			'manage_',
			'adm_',
			'plugin.php?page=BfsPortal/isolation',
		);

		$t_filtered = array();
		foreach( $p_items as $t_item ) {
			if( !isset( $t_item['url'] ) ) {
				$t_filtered[] = $t_item;
				continue;
			}

			$t_skip = false;
			foreach( $t_hide_fragments as $t_hide ) {
				if( false !== strpos( $t_item['url'], $t_hide ) ) {
					$t_skip = true;
					break;
				}
			}

			if( isset( $t_item['title'] ) && 'manage_link' === $t_item['title'] ) {
				$t_skip = true;
			}

			if( !$t_skip ) {
				$t_filtered[] = $t_item;
			}
		}

		return $t_filtered;
	}

	/**
	 * Journalise un avertissement si un client est assigné à plusieurs projets.
	 */
	public static function on_project_user_create( $p_event, array $p_payload ) {
		if( !isset( $p_payload['user_id'] ) ) {
			return;
		}

		$t_user_id = (int)$p_payload['user_id'];
		if( !self::is_client_user( $t_user_id ) ) {
			return;
		}

		$t_assigned = self::get_assigned_project_ids( $t_user_id );
		if( count( $t_assigned ) > 1 ) {
			log_event(
				LOG_WARNING,
				'BFS isolation: utilisateur client @U%d assigné à %d projets (attendu: 1)',
				$t_user_id,
				count( $t_assigned )
			);
		}
	}
}
