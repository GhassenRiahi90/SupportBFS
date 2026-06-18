<?php
/**
 * Support BFS — audit automatique du cloisonnement client / projet.
 */

class BfsIsolation {
	const SEVERITY_OK      = 'ok';
	const SEVERITY_INFO    = 'info';
	const SEVERITY_WARNING = 'warning';
	const SEVERITY_ERROR   = 'error';

	/**
	 * @return array<int, array{severity:string, code:string, message:string, context:string}>
	 */
	public static function run_audit() {
		require_api( 'access_api.php' );
		require_api( 'project_api.php' );
		require_api( 'user_api.php' );

		$t_findings = array();

		self::check_global_config( $t_findings );
		self::check_client_projects( $t_findings );
		self::check_legacy_solution_projects( $t_findings );
		self::check_client_users( $t_findings );
		self::check_demo_user( $t_findings );

		return $t_findings;
	}

	/**
	 * @return array{total:int, errors:int, warnings:int, infos:int, oks:int}
	 */
	public static function summarize( array $p_findings ) {
		$t_summary = array(
			'total'    => count( $p_findings ),
			'errors'   => 0,
			'warnings' => 0,
			'infos'    => 0,
			'oks'      => 0,
		);

		foreach( $p_findings as $t_row ) {
			switch( $t_row['severity'] ) {
				case self::SEVERITY_ERROR:
					$t_summary['errors']++;
					break;
				case self::SEVERITY_WARNING:
					$t_summary['warnings']++;
					break;
				case self::SEVERITY_INFO:
					$t_summary['infos']++;
					break;
				default:
					$t_summary['oks']++;
			}
		}

		return $t_summary;
	}

	private static function add_finding( array &$p_findings, $p_severity, $p_code, $p_message, $p_context = '' ) {
		$p_findings[] = array(
			'severity' => $p_severity,
			'code'     => $p_code,
			'message'  => $p_message,
			'context'  => $p_context,
		);
	}

	private static function check_global_config( array &$p_findings ) {
		if( ON != config_get_global( 'limit_reporters' ) ) {
			self::add_finding(
				$p_findings,
				self::SEVERITY_WARNING,
				'limit_reporters_off',
				'limit_reporters est désactivé : les rapporteurs peuvent voir les tickets des autres clients du même projet.',
				'Configuration globale'
			);
		} else {
			self::add_finding(
				$p_findings,
				self::SEVERITY_OK,
				'limit_reporters_on',
				'limit_reporters est activé (vue limitée pour les rapporteurs).',
				'Configuration globale'
			);
		}

		if( VS_PRIVATE != config_get_global( 'default_project_view_status' ) ) {
			self::add_finding(
				$p_findings,
				self::SEVERITY_WARNING,
				'default_project_not_private',
				'Les nouveaux projets ne sont pas privés par défaut.',
				'default_project_view_status'
			);
		}

		if( DEVELOPER != config_get_global( 'private_project_threshold' ) ) {
			self::add_finding(
				$p_findings,
				self::SEVERITY_INFO,
				'private_threshold_custom',
				'private_project_threshold ≠ Developer (vérifier la politique équipe BFS).',
				'private_project_threshold'
			);
		}
	}

	private static function check_client_projects( array &$p_findings ) {
		$t_rows = project_get_all_rows();
		$t_client_count = 0;

		foreach( $t_rows as $t_row ) {
			if( OFF == $t_row['enabled'] ) {
				continue;
			}

			$t_client_count++;
			$t_name = $t_row['name'];

			if( VS_PRIVATE != (int)$t_row['view_state'] ) {
				self::add_finding(
					$p_findings,
					self::SEVERITY_ERROR,
					'project_public',
					'Le projet client « ' . $t_name . ' » n\'est pas privé.',
					'project_id=' . $t_row['id']
				);
			}

			if( empty( $t_row['inherit_global'] ) ) {
				self::add_finding(
					$p_findings,
					self::SEVERITY_WARNING,
					'project_no_global_categories',
					'Le projet « ' . $t_name . ' » n\'hérite pas des catégories globales (solutions BFS).',
					'project_id=' . $t_row['id']
				);
			}
		}

		if( 0 === $t_client_count ) {
			self::add_finding(
				$p_findings,
				self::SEVERITY_INFO,
				'no_enabled_projects',
				'Aucun projet client actif détecté.',
				''
			);
		}
	}

	private static function check_legacy_solution_projects( array &$p_findings ) {
		$t_legacy = array(
			BfsArchitecture::SOLUTION_SAGE_XRT,
			BfsArchitecture::SOLUTION_SAGE_SXA,
			BfsArchitecture::SOLUTION_TREASURY,
			BfsArchitecture::SOLUTION_GENERAL,
		);

		foreach( $t_legacy as $t_name ) {
			$t_id = project_get_id_by_name( $t_name, false );
			if( false === $t_id || null === $t_id ) {
				continue;
			}

			$t_row = project_get_row( (int)$t_id );
			if( OFF == $t_row['enabled'] ) {
				self::add_finding(
					$p_findings,
					self::SEVERITY_OK,
					'legacy_disabled',
					'Ancien projet solution « ' . $t_name . ' » correctement désactivé.',
					'project_id=' . $t_id
				);
				continue;
			}

			self::add_finding(
				$p_findings,
				self::SEVERITY_ERROR,
				'legacy_solution_active',
				'Ancien projet solution « ' . $t_name . ' » encore actif (risque de mélange clients).',
				'project_id=' . $t_id
			);
		}
	}

	private static function check_client_users( array &$p_findings ) {
		require_api( 'database_api.php' );

		db_param_push();
		$t_query = 'SELECT id, username, enabled, access_level FROM {user} WHERE enabled=' . db_param();
		$t_result = db_query( $t_query, array( 1 ) );

		while( $t_user = db_fetch_array( $t_result ) ) {
			$t_user_id = (int)$t_user['id'];
			if( !BfsClientAccess::is_client_user( $t_user_id ) ) {
				continue;
			}

			$t_username = $t_user['username'];
			$t_assigned = BfsClientAccess::get_assigned_project_ids( $t_user_id );
			$t_count    = count( $t_assigned );

			if( 0 === $t_count ) {
				self::add_finding(
					$p_findings,
					self::SEVERITY_WARNING,
					'client_no_project',
					'Utilisateur client « ' . $t_username . ' » sans projet assigné.',
					'user_id=' . $t_user_id
				);
				continue;
			}

			if( 1 === $t_count ) {
				self::add_finding(
					$p_findings,
					self::SEVERITY_OK,
					'client_single_project',
					'Utilisateur client « ' . $t_username . ' » limité à un seul projet.',
					'user_id=' . $t_user_id
				);
				continue;
			}

			self::add_finding(
				$p_findings,
				self::SEVERITY_ERROR,
				'client_multi_project',
				'Utilisateur client « ' . $t_username . ' » assigné à ' . $t_count . ' projets (attendu: 1).',
				'user_id=' . $t_user_id
			);
		}
	}

	private static function check_demo_user( array &$p_findings ) {
		$t_user_id = user_get_id_by_name( BfsArchitecture::DEMO_USER_NAME, false );
		if( false === $t_user_id ) {
			self::add_finding(
				$p_findings,
				self::SEVERITY_INFO,
				'demo_user_missing',
				'Utilisateur test demo.client absent (non bloquant).',
				''
			);
			return;
		}

		$t_assigned = BfsClientAccess::get_assigned_project_ids( $t_user_id );
		$t_demo_id  = project_get_id_by_name( BfsArchitecture::DEMO_CLIENT_NAME, false );

		if( 1 === count( $t_assigned ) && false !== $t_demo_id && (int)$t_assigned[0] === (int)$t_demo_id ) {
			self::add_finding(
				$p_findings,
				self::SEVERITY_OK,
				'demo_user_ok',
				'Utilisateur demo.client correctement limité au projet Client Démo.',
				'user_id=' . $t_user_id
			);
			return;
		}

		self::add_finding(
			$p_findings,
			self::SEVERITY_WARNING,
			'demo_user_scope',
			'Utilisateur demo.client : périmètre projet à vérifier (attendu: Client Démo uniquement).',
			'user_id=' . $t_user_id
		);
	}
}
