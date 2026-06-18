<?php
/**
 * Architecture BFS v2 — projets = clients, catégories globales = solutions.
 */

class BfsArchitecture {
	const DEMO_CLIENT_NAME = 'Client Démo';
	const DEMO_USER_NAME   = 'demo.client';
	const DEMO_USER_EMAIL  = 'demo.client@support.bfs.tn';
	const DEMO_USER_REAL   = 'Utilisateur test (Client Démo)';

	const SOLUTION_SAGE_XRT = 'Sage XRT (Cash Management)';
	const SOLUTION_SAGE_SXA = 'Sage SXA';
	const SOLUTION_TREASURY = 'BFS Treasury Analytics (Power BI)';
	const SOLUTION_GENERAL  = 'Support Général BFS';

	/**
	 * Migration idempotente vers l'architecture client / catégories globales.
	 */
	public static function run() {
		if( ON == plugin_config_get( 'architecture_v2', OFF ) ) {
			return;
		}

		require_api( 'access_api.php' );
		require_api( 'authentication_api.php' );
		require_api( 'project_api.php' );
		require_api( 'category_api.php' );
		require_api( 'custom_field_api.php' );
		require_api( 'user_api.php' );
		require_api( 'user_pref_api.php' );

		self::apply_privacy_config();
		self::apply_workflow_config();
		self::ensure_global_solution_categories();
		self::retire_legacy_solution_projects();
		self::ensure_custom_fields();

		$t_demo_id = self::ensure_demo_client_project();
		self::ensure_demo_client_user( $t_demo_id );
		self::link_custom_fields_to_project( $t_demo_id );
		self::fix_invalid_user_default_projects();

		plugin_config_set( 'architecture_v2', ON );
		plugin_config_set( 'portal_seeded', ON );
	}

	public static function is_ready() {
		return ON == plugin_config_get( 'architecture_v2', OFF );
	}

	public static function get_solution_definitions() {
		return array(
			self::SOLUTION_SAGE_XRT => array(
				'Connecteurs bancaires',
				'Rapprochement automatique',
				'Prévisions de trésorerie',
				'Reporting XRT',
				'Paramétrage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			self::SOLUTION_SAGE_SXA => array(
				'Architecture & intégration',
				'Intégration Sage XRT',
				'Intégration bancaire',
				'Paramétrage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			self::SOLUTION_TREASURY => array(
				'Connecteurs de données',
				'Tableaux de bord & rapports',
				'Accès & droits utilisateurs',
				'Performance/affichage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			self::SOLUTION_GENERAL => array(
				'Demande d\'information',
				'Demande commerciale',
				'Question contractuelle',
				'Autre',
			),
		);
	}

	public static function get_legacy_solution_project_names() {
		return array_keys( self::get_solution_definitions() );
	}

	public static function global_category_label( $p_solution, $p_subtype ) {
		return $p_solution . ' — ' . $p_subtype;
	}

	private static function apply_privacy_config() {
		require_once __DIR__ . '/BfsClientAccess.php';

		BfsClientAccess::persist_global_option( 'default_project_view_status', VS_PRIVATE );
		BfsClientAccess::persist_global_option( 'default_bug_view_status', VS_PRIVATE );
		BfsClientAccess::persist_global_option( 'private_project_threshold', DEVELOPER );
		BfsClientAccess::persist_global_option( 'copyright_statement', '' );
	}

	private static function apply_workflow_config() {
		require_once __DIR__ . '/BfsClientAccess.php';

		BfsClientAccess::persist_global_option(
			'status_enum_string',
			'10:nouveau,20:en_attente_client,30:pris_en_charge,40:confirme,50:en_cours,80:resolu,90:ferme'
		);
		BfsClientAccess::persist_global_option(
			'severity_enum_string',
			'10:information,50:mineur,60:majeur,80:bloquant'
		);
		BfsClientAccess::persist_global_option( 'status_colors', array(
			'nouveau'            => '#A8B2BA',
			'en_attente_client'  => '#FFB74D',
			'pris_en_charge'     => '#4FA8D8',
			'confirme'           => '#4FA8D8',
			'en_cours'           => '#E65100',
			'resolu'             => '#2E7D32',
			'ferme'              => '#1B5E20',
		) );
	}

	private static function ensure_global_solution_categories() {
		foreach( self::get_solution_definitions() as $t_solution => $t_subtypes ) {
			foreach( $t_subtypes as $t_subtype ) {
				$t_label = self::global_category_label( $t_solution, $t_subtype );
				if( category_is_unique( ALL_PROJECTS, $t_label ) ) {
					category_add( ALL_PROJECTS, $t_label );
				}
			}
		}
	}

	private static function retire_legacy_solution_projects() {
		foreach( self::get_legacy_solution_project_names() as $t_name ) {
			$t_id = project_get_id_by_name( $t_name, false );
			if( false === $t_id || null === $t_id ) {
				continue;
			}

			$t_id = (int)$t_id;
			if( project_get_bug_count( $t_id ) > 0 ) {
				$t_row = project_get_row( $t_id );
				project_update(
					$t_id,
					$t_row['name'],
					$t_row['description'],
					(int)$t_row['status'],
					(int)$t_row['view_state'],
					$t_row['file_path'],
					false,
					(bool)$t_row['inherit_global']
				);
				continue;
			}

			project_delete( $t_id );
		}
	}

	private static function ensure_demo_client_project() {
		$t_id = project_get_id_by_name( self::DEMO_CLIENT_NAME, false );
		if( false === $t_id || null === $t_id ) {
			$t_id = project_create(
				self::DEMO_CLIENT_NAME,
				'Projet de validation — cloisonnement client (tickets privés, accès limité).',
				50,
				VS_PRIVATE,
				'',
				true,
				true
			);
			self::ensure_creator_project_access( $t_id );
		}

		return (int)$t_id;
	}

	private static function ensure_demo_client_user( $p_project_id ) {
		require_api( 'authentication_api.php' );

		$t_user_id = user_get_id_by_name( self::DEMO_USER_NAME, false );
		if( false === $t_user_id ) {
			$t_password = auth_generate_random_password();
			user_create(
				self::DEMO_USER_NAME,
				$t_password,
				self::DEMO_USER_EMAIL,
				REPORTER,
				false,
				true,
				self::DEMO_USER_REAL
			);
			plugin_config_set( 'demo_client_password', $t_password );
			$t_user_id = user_get_id_by_name( self::DEMO_USER_NAME, false );
		}

		if( false === $t_user_id ) {
			return;
		}

		$t_user_id = (int)$t_user_id;
		if( !access_has_project_level( VIEWER, $p_project_id, $t_user_id ) ) {
			project_add_user( $p_project_id, $t_user_id, REPORTER );
		}

		BfsClientAccess::enforce_single_client_project( $t_user_id, $p_project_id );
	}

	private static function ensure_custom_fields() {
		$t_fields = BfsClient::get_custom_field_definitions();
		foreach( $t_fields as $t_name => $t_def ) {
			$t_field_id = custom_field_get_id_from_name( $t_name );
			if( false === $t_field_id ) {
				$t_field_id = custom_field_create( $t_name );
				custom_field_update( $t_field_id, array(
					'name'              => $t_name,
					'type'              => $t_def['type'],
					'default_value'     => '',
					'possible_values'   => $t_def['possible_values'],
					'valid_regexp'      => '',
					'access_level_r'    => VIEWER,
					'access_level_rw'   => REPORTER,
					'length_min'        => 0,
					'length_max'        => 255,
					'filter_by'         => true,
					'display_report'    => true,
					'display_update'    => true,
					'display_resolved'  => true,
					'display_closed'    => true,
					'require_report'    => false,
					'require_update'    => false,
					'require_resolved'  => false,
					'require_closed'    => false,
				) );
			}
		}
	}

	private static function link_custom_fields_to_project( $p_project_id ) {
		if( $p_project_id <= 0 ) {
			return;
		}

		foreach( array_keys( BfsClient::get_custom_field_definitions() ) as $t_name ) {
			$t_field_id = custom_field_get_id_from_name( $t_name );
			if( false !== $t_field_id ) {
				custom_field_link( $t_field_id, $p_project_id );
			}
		}
	}

	private static function ensure_creator_project_access( $p_project_id ) {
		if( !auth_is_user_authenticated() ) {
			return;
		}

		$t_user_id = auth_get_current_user_id();
		$t_manage_project_access_level = config_get( 'manage_project_threshold', null, $t_user_id, $p_project_id );

		if( !access_has_project_level( $t_manage_project_access_level, $p_project_id, $t_user_id ) ) {
			$t_access_level = access_get_global_level( $t_user_id );
			if( $t_access_level < $t_manage_project_access_level ) {
				$t_access_level = $t_manage_project_access_level;
			}

			project_add_user( $p_project_id, $t_user_id, $t_access_level );
		}
	}

	private static function fix_invalid_user_default_projects() {
		BfsClientAccess::repair_invalid_default_projects();
	}
}
