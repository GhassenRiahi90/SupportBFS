<?php
/**
 * Initialisation des données métier BFS (projets, catégories, champs custom).
 */

class BfsSeed {
	const PROJECT_SAGE_XRT = 'Sage XRT (Cash Management)';
	const PROJECT_SAGE_SXA = 'Sage SXA';
	const PROJECT_TREASURY = 'BFS Treasury Analytics (Power BI)';
	const PROJECT_GENERAL = 'Support Général BFS';

	/**
	 * Exécute le seed (idempotent).
	 */
	public static function run() {
		require_api( 'access_api.php' );
		require_api( 'authentication_api.php' );
		require_api( 'project_api.php' );
		require_api( 'category_api.php' );
		require_api( 'custom_field_api.php' );
		require_api( 'user_pref_api.php' );

		if( ON != plugin_config_get( 'portal_seeded', OFF ) ) {
			self::apply_global_config();
		}

		$t_projects = self::get_projects_definition();
		$t_descriptions = self::get_project_descriptions();
		$t_project_ids = array();

		foreach( $t_projects as $t_name => $t_categories ) {
			$t_id = self::get_project_id_by_name( $t_name );
			if( null === $t_id ) {
				$t_desc = isset( $t_descriptions[$t_name] ) ? $t_descriptions[$t_name] : '';
				$t_id = project_create( $t_name, $t_desc, 50, VS_PUBLIC, '', true, false );
				self::ensure_creator_project_access( $t_id );
			}
			$t_project_ids[] = $t_id;

			foreach( $t_categories as $t_category ) {
				if( !category_is_unique( $t_id, $t_category ) ) {
					continue;
				}
				category_add( $t_id, $t_category );
			}
		}

		self::ensure_custom_fields( $t_project_ids );
		self::fix_invalid_user_default_projects();

		if( self::all_projects_exist() ) {
			plugin_config_set( 'portal_seeded', ON );
		}
	}

	public static function all_projects_exist() {
		require_api( 'project_api.php' );

		foreach( array_keys( self::get_projects_definition() ) as $t_name ) {
			if( null === self::get_project_id_by_name( $t_name ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * MantisBT renvoie ALL_PROJECTS (0) par défaut si le nom est introuvable.
	 */
	private static function get_project_id_by_name( $p_name ) {
		$t_id = project_get_id_by_name( $p_name, false );
		if( null === $t_id || false === $t_id ) {
			return null;
		}

		return (int)$t_id;
	}

	private static function get_projects_definition() {
		return array(
			self::PROJECT_SAGE_XRT => array(
				'Connecteurs bancaires',
				'Rapprochement automatique',
				'Prévisions de trésorerie',
				'Reporting XRT',
				'Paramétrage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			self::PROJECT_SAGE_SXA => array(
				'Architecture & intégration',
				'Intégration Sage XRT',
				'Intégration bancaire',
				'Paramétrage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			self::PROJECT_TREASURY => array(
				'Connecteurs de données',
				'Tableaux de bord & rapports',
				'Accès & droits utilisateurs',
				'Performance/affichage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			self::PROJECT_GENERAL => array(
				'Demande d\'information',
				'Demande commerciale',
				'Question contractuelle',
				'Autre',
			),
		);
	}

	private static function get_project_descriptions() {
		return array(
			self::PROJECT_SAGE_XRT => 'Solution Sage XRT — gestion de trésorerie et cash management.',
			self::PROJECT_SAGE_SXA => 'Solution Sage SXA — architecture et intégration bancaire.',
			self::PROJECT_TREASURY => 'Solution propriétaire BFS — tableaux de bord Power BI pour la trésorerie.',
			self::PROJECT_GENERAL => 'Demandes générales, commerciales et contractuelles.',
		);
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
		db_param_push();
		$t_query = 'SELECT user_id, default_project FROM {user_pref} WHERE default_project > 0';
		$t_result = db_query( $t_query );

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_default = (int)$t_row['default_project'];
			if( !project_exists( $t_default ) ) {
				user_pref_set_pref( (int)$t_row['user_id'], 'default_project', ALL_PROJECTS );
			}
		}
	}

	private static function apply_global_config() {
		config_set_global( 'status_enum_string', '10:nouveau,20:en_attente_client,30:pris_en_charge,40:confirme,50:en_cours,80:resolu,90:ferme' );
		config_set_global( 'severity_enum_string', '10:information,50:mineur,60:majeur,80:bloquant' );

		config_set_global( 'status_colors', array(
			'nouveau'            => '#A8B2BA',
			'en_attente_client'  => '#FFB74D',
			'pris_en_charge'     => '#4FA8D8',
			'confirme'           => '#4FA8D8',
			'en_cours'           => '#E65100',
			'resolu'             => '#2E7D32',
			'ferme'              => '#1B5E20',
		) );
	}

	private static function ensure_custom_fields( array $p_project_ids ) {
		$t_fields = array(
			'client_company' => array(
				'type'            => CUSTOM_FIELD_TYPE_STRING,
				'label'           => 'Nom du client / société',
				'possible_values' => '',
			),
			'contract_reference' => array(
				'type'            => CUSTOM_FIELD_TYPE_STRING,
				'label'           => 'Référence contrat client',
				'possible_values' => '',
			),
			'environment' => array(
				'type'            => CUSTOM_FIELD_TYPE_ENUM,
				'label'           => 'Environnement',
				'possible_values' => 'Production|Test|Formation',
			),
			'business_urgency' => array(
				'type'            => CUSTOM_FIELD_TYPE_STRING,
				'label'           => 'Urgence métier déclarée',
				'possible_values' => '',
			),
		);

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

			foreach( $p_project_ids as $t_project_id ) {
				if( $t_project_id > 0 ) {
					custom_field_link( $t_field_id, $t_project_id );
				}
			}
		}
	}
}
