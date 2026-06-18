<?php
/**
 * Initialisation des données métier BFS (projets, catégories, champs custom).
 */

class BfsSeed {
	/**
	 * Exécute le seed une seule fois (idempotent).
	 */
	public static function run() {
		if( ON == plugin_config_get( 'portal_seeded', OFF ) ) {
			return;
		}

		require_api( 'project_api.php' );
		require_api( 'category_api.php' );
		require_api( 'custom_field_api.php' );

		self::apply_global_config();

		$t_projects = array(
			'Sage XRT (Cash Management)' => array(
				'Connecteurs bancaires',
				'Rapprochement automatique',
				'Prévisions de trésorerie',
				'Reporting XRT',
				'Paramétrage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			'Sage SXA' => array(
				'Architecture & intégration',
				'Intégration Sage XRT',
				'Intégration bancaire',
				'Paramétrage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			'BFS Treasury Analytics (Power BI)' => array(
				'Connecteurs de données',
				'Tableaux de bord & rapports',
				'Accès & droits utilisateurs',
				'Performance/affichage',
				'Formation utilisateur',
				'Anomalie/Bug',
			),
			'Support Général BFS' => array(
				'Demande d\'information',
				'Demande commerciale',
				'Question contractuelle',
				'Autre',
			),
		);

		$t_descriptions = array(
			'Sage XRT (Cash Management)' => 'Solution Sage XRT — gestion de trésorerie et cash management.',
			'Sage SXA' => 'Solution Sage SXA — architecture et intégration bancaire.',
			'BFS Treasury Analytics (Power BI)' => 'Solution propriétaire BFS — tableaux de bord Power BI pour la trésorerie.',
			'Support Général BFS' => 'Demandes générales, commerciales et contractuelles.',
		);

		$t_project_ids = array();

		foreach( $t_projects as $t_name => $t_categories ) {
			$t_id = project_get_id_by_name( $t_name );
			if( false === $t_id ) {
				$t_desc = isset( $t_descriptions[$t_name] ) ? $t_descriptions[$t_name] : '';
				$t_id = project_create( $t_name, $t_desc, 50, VS_PUBLIC, '', true, false );
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

		plugin_config_set( 'portal_seeded', ON );
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
				custom_field_link( $t_field_id, $t_project_id );
			}
		}
	}
}
