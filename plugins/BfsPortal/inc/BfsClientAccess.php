<?php
/**
 * Support BFS — profil utilisateur client, contexte projet et config persistée.
 */

class BfsClientAccess {
	const CLIENT_MAX_ACCESS = REPORTER;

	/**
	 * Options globales BFS à persister en base (config table).
	 */
	private static function isolation_options() {
		return array(
			'limit_reporters'             => ON,
			'allow_reporter_close'        => OFF,
			'allow_reporter_reopen'       => ON,
			'show_user_email_threshold'   => NOBODY,
			'allow_signup'                => OFF,
		);
	}

	/**
	 * Applique la configuration MantisBT pour le cloisonnement client (idempotent, persistée).
	 */
	public static function apply_global_config( $p_force = false ) {
		if( !$p_force && ON == plugin_config_get( 'isolation_config_applied', OFF ) ) {
			if( ON == config_get( 'limit_reporters' ) ) {
				return;
			}
		}

		require_api( 'config_api.php' );

		foreach( self::isolation_options() as $t_option => $t_value ) {
			self::persist_global_option( $t_option, $t_value );
		}

		plugin_config_set( 'isolation_config_applied', ON );
	}

	/**
	 * @param string $p_option
	 * @param mixed  $p_value
	 */
	public static function persist_global_option( $p_option, $p_value ) {
		config_set( $p_option, $p_value );
		config_set_global( $p_option, $p_value );
	}

	/**
	 * Point d'entrée requête : contexte client + paramètre project_id manquant.
	 */
	public static function bootstrap_request_context() {
		if( !auth_is_user_authenticated() ) {
			return;
		}

		self::patch_missing_project_id_param();
		self::redirect_manage_pages_missing_project();
		self::enforce_single_project_context();
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
	 * URL « Nouvelle demande » avec project_id explicite si possible.
	 */
	public static function get_bug_report_url( $p_project_id = null ) {
		require_api( 'string_api.php' );

		if( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}

		$t_url = string_get_bug_report_url();
		if( ALL_PROJECTS != $p_project_id && $p_project_id > 0 ) {
			$t_url = helper_url_combine( $t_url, array( 'project_id' => $p_project_id ) );
		}

		return $t_url;
	}

	/**
	 * Contexte projet pour utilisateurs client (0 / 1 / N assignations).
	 */
	public static function enforce_single_project_context() {
		if( !auth_is_user_authenticated() || !self::is_client_user() ) {
			return;
		}

		require_api( 'helper_api.php' );
		require_api( 'user_pref_api.php' );
		require_api( 'print_api.php' );

		$t_user_id  = auth_get_current_user_id();
		$t_assigned = self::get_assigned_project_ids( $t_user_id );
		$t_count    = count( $t_assigned );

		if( 0 === $t_count ) {
			self::redirect_client_no_project();
			return;
		}

		if( 1 === $t_count ) {
			$t_project_id = $t_assigned[0];
			self::ensure_user_default_project( $t_user_id, $t_project_id );
			if( helper_get_current_project() != $t_project_id ) {
				helper_set_current_project( $t_project_id );
			}
			return;
		}

		$t_default = (int)user_pref_get_pref( $t_user_id, 'default_project' );
		if( in_array( $t_default, $t_assigned, true ) ) {
			$t_project_id = $t_default;
		} else {
			$t_project_id = min( $t_assigned );
			self::ensure_user_default_project( $t_user_id, $t_project_id );
		}

		if( helper_get_current_project() != $t_project_id ) {
			helper_set_current_project( $t_project_id );
		}

		log_event(
			LOG_WARNING,
			'BFS isolation: utilisateur client @U%d assigné à %d projets — contexte forcé sur P%d',
			$t_user_id,
			$t_count,
			$t_project_id
		);
	}

	/**
	 * Évite ERROR #200 sur POST sans project_id (ex. bug_report.php).
	 */
	public static function patch_missing_project_id_param() {
		if( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		if( !self::page_requires_project_id_param() ) {
			return;
		}

		if( gpc_isset( 'project_id' ) ) {
			return;
		}

		$t_project_id = self::resolve_effective_project_id();
		if( $t_project_id <= 0 || ALL_PROJECTS == $t_project_id ) {
			return;
		}

		$_POST['project_id'] = $t_project_id;
		$_REQUEST['project_id'] = $t_project_id;
	}

	/**
	 * Redirige les pages manage qui exigent ?project_id= quand le cookie est valide.
	 */
	public static function redirect_manage_pages_missing_project() {
		if( 'GET' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		if( !function_exists( 'is_page_name' ) ) {
			return;
		}

		$t_manage_pages = array(
			'manage_proj_edit_page.php',
			'manage_proj_cat_add.php',
			'manage_proj_ver_add.php',
		);

		$t_match = false;
		$t_current_page = '';
		foreach( $t_manage_pages as $t_page ) {
			if( is_page_name( $t_page ) ) {
				$t_match = true;
				$t_current_page = $t_page;
				break;
			}
		}

		if( !$t_match || gpc_isset( 'project_id' ) ) {
			return;
		}

		$t_project_id = self::resolve_effective_project_id();
		if( $t_project_id <= 0 || ALL_PROJECTS == $t_project_id ) {
			return;
		}

		require_api( 'print_api.php' );
		print_header_redirect( helper_url_combine( $t_current_page, array( 'project_id' => $t_project_id ) ) );
	}

	/**
	 * Un seul projet client autorisé : retire les autres assignations.
	 */
	public static function enforce_single_client_project( $p_user_id, $p_keep_project_id ) {
		if( !self::is_client_user( $p_user_id ) ) {
			return;
		}

		require_api( 'project_api.php' );
		require_api( 'user_pref_api.php' );

		$p_keep_project_id = (int)$p_keep_project_id;
		foreach( self::get_assigned_project_ids( $p_user_id ) as $t_project_id ) {
			if( $t_project_id !== $p_keep_project_id ) {
				project_remove_user( $t_project_id, $p_user_id );
				log_event(
					LOG_WARNING,
					'BFS isolation: retrait auto @U%d du projet P%d (client mono-projet)',
					$p_user_id,
					$t_project_id
				);
			}
		}

		self::ensure_user_default_project( $p_user_id, $p_keep_project_id );
	}

	public static function on_project_user_create( $p_user_id, $p_project_id ) {
		self::enforce_single_client_project( (int)$p_user_id, (int)$p_project_id );
	}

	public static function on_project_user_update( $p_user_id, $p_project_id ) {
		self::enforce_single_client_project( (int)$p_user_id, (int)$p_project_id );
	}

	/**
	 * Corrige default_project invalide (client → seul projet assigné).
	 */
	public static function repair_invalid_default_projects() {
		require_api( 'database_api.php' );
		require_api( 'user_pref_api.php' );
		require_api( 'project_api.php' );

		db_param_push();
		$t_query = 'SELECT user_id, default_project FROM {user_pref} WHERE default_project > 0';
		$t_result = db_query( $t_query );

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_user_id = (int)$t_row['user_id'];
			$t_default = (int)$t_row['default_project'];

			if( project_exists( $t_default ) && project_enabled( $t_default ) ) {
				continue;
			}

			if( self::is_client_user( $t_user_id ) ) {
				$t_assigned = self::get_assigned_project_ids( $t_user_id );
				if( 1 === count( $t_assigned ) ) {
					self::ensure_user_default_project( $t_user_id, $t_assigned[0] );
					continue;
				}
			}

			user_pref_set_pref( $t_user_id, 'default_project', ALL_PROJECTS );
		}
	}

	public static function filter_sidebar_items( array $p_items ) {
		if( !auth_is_user_authenticated() || !self::is_client_user() ) {
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
			'plugin.php?page=BfsPortal/isolation_audit',
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

	private static function ensure_user_default_project( $p_user_id, $p_project_id ) {
		require_api( 'user_pref_api.php' );

		if( (int)user_pref_get_pref( $p_user_id, 'default_project' ) !== (int)$p_project_id ) {
			user_pref_set_pref( $p_user_id, 'default_project', (int)$p_project_id );
		}
	}

	private static function resolve_effective_project_id() {
		require_api( 'helper_api.php' );

		$t_project_id = helper_get_current_project();
		if( ALL_PROJECTS != $t_project_id && $t_project_id > 0 ) {
			return (int)$t_project_id;
		}

		if( self::is_client_user() ) {
			$t_assigned = self::get_assigned_project_ids();
			if( count( $t_assigned ) >= 1 ) {
				return (int)min( $t_assigned );
			}
		}

		$t_user_id = auth_get_current_user_id();
		$t_default = (int)user_pref_get_pref( $t_user_id, 'default_project' );
		if( $t_default > 0 && project_exists( $t_default ) && project_enabled( $t_default ) ) {
			return $t_default;
		}

		require_api( 'user_api.php' );
		$t_accessible = user_get_accessible_projects( $t_user_id );
		if( !empty( $t_accessible ) ) {
			return (int)$t_accessible[0];
		}

		return ALL_PROJECTS;
	}

	private static function page_requires_project_id_param() {
		if( !function_exists( 'is_page_name' ) ) {
			return false;
		}

		return is_page_name( 'bug_report.php' );
	}

	private static function redirect_client_no_project() {
		if( !function_exists( 'is_page_name' ) ) {
			return;
		}

		$t_allowed = array(
			'my_view_page.php',
			'account_page.php',
			'account_prefs_page.php',
			'logout_page.php',
		);

		foreach( $t_allowed as $t_page ) {
			if( is_page_name( $t_page ) ) {
				return;
			}
		}

		require_api( 'print_api.php' );
		print_header_redirect( 'my_view_page.php?bfs_no_project=1' );
	}
}
