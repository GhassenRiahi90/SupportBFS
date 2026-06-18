<?php
/**
 * Création de projets clients BFS (privés, catégories globales héritées).
 */

class BfsClient {
	/**
	 * Crée un projet client privé avec héritage des catégories globales de solutions.
	 *
	 * @param string $p_name        Nom du client (ex. "Carrefour Tunisie").
	 * @param string $p_description Description optionnelle.
	 * @return int Identifiant du projet créé.
	 */
	public static function create_client_project( $p_name, $p_description = '' ) {
		require_api( 'access_api.php' );
		require_api( 'authentication_api.php' );
		require_api( 'project_api.php' );
		require_api( 'custom_field_api.php' );

		$t_existing = project_get_id_by_name( $p_name, false );
		if( false !== $t_existing && null !== $t_existing ) {
			return (int)$t_existing;
		}

		$t_id = project_create(
			$p_name,
			$p_description,
			50,
			VS_PRIVATE,
			'',
			true,
			true
		);

		self::ensure_creator_access( $t_id );
		self::link_custom_fields( $t_id );

		return $t_id;
	}

	/**
	 * Associe un utilisateur client (reporter) à un seul projet client.
	 */
	public static function assign_client_user( $p_project_id, $p_user_id, $p_access_level = null ) {
		require_api( 'access_api.php' );
		require_api( 'project_api.php' );
		require_api( 'user_pref_api.php' );

		if( null === $p_access_level ) {
			$p_access_level = REPORTER;
		}

		if( !access_has_project_level( VIEWER, $p_project_id, $p_user_id ) ) {
			project_add_user( $p_project_id, $p_user_id, $p_access_level );
		}

		user_pref_set_pref( $p_user_id, 'default_project', $p_project_id );
	}

	public static function get_custom_field_definitions() {
		return array(
			'client_company' => array(
				'type'            => CUSTOM_FIELD_TYPE_STRING,
				'possible_values' => '',
			),
			'contract_reference' => array(
				'type'            => CUSTOM_FIELD_TYPE_STRING,
				'possible_values' => '',
			),
			'environment' => array(
				'type'            => CUSTOM_FIELD_TYPE_ENUM,
				'possible_values' => 'Production|Test|Formation',
			),
			'business_urgency' => array(
				'type'            => CUSTOM_FIELD_TYPE_STRING,
				'possible_values' => '',
			),
		);
	}

	private static function link_custom_fields( $p_project_id ) {
		foreach( array_keys( self::get_custom_field_definitions() ) as $t_name ) {
			$t_field_id = custom_field_get_id_from_name( $t_name );
			if( false !== $t_field_id ) {
				custom_field_link( $t_field_id, $p_project_id );
			}
		}
	}

	private static function ensure_creator_access( $p_project_id ) {
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
}
