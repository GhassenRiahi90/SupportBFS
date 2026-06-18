<?php
/**
 * Support BFS — relance automatique des demandes en attente client.
 */

class BfsEmailReminder {
	const STATUS_EN_ATTENTE_CLIENT = 20;

	/**
	 * Hook EVENT_CRONJOB — à planifier via scripts/cronjob.php (cPanel).
	 */
	public static function cron() {
		if( OFF == plugin_config_get( 'reminder_enabled', OFF ) ) {
			return;
		}

		require_api( 'bug_api.php' );
		require_api( 'database_api.php' );
		require_api( 'email_api.php' );
		require_api( 'user_api.php' );

		$t_days   = max( 1, (int)plugin_config_get( 'reminder_days', 5 ) );
		$t_cutoff = time() - ( $t_days * 86400 );
		$t_log    = self::load_log();

		$t_query = 'SELECT id, reporter_id, project_id, summary, last_updated
			FROM {bug}
			WHERE status=' . db_param() . ' AND last_updated <= ' . db_param();
		$t_result = db_query( $t_query, array( self::STATUS_EN_ATTENTE_CLIENT, $t_cutoff ) );

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_bug_id = (int)$t_row['id'];
			if( self::was_recently_reminded( $t_log, $t_bug_id, $t_days ) ) {
				continue;
			}

			if( self::send_reminder( $t_bug_id, (int)$t_row['reporter_id'], (int)$t_row['project_id'], $t_days ) ) {
				$t_log[$t_bug_id] = time();
			}
		}

		self::save_log( $t_log );
	}

	private static function send_reminder( $p_bug_id, $p_reporter_id, $p_project_id, $p_days ) {
		if( $p_reporter_id <= 0 || !user_exists( $p_reporter_id ) || !user_is_enabled( $p_reporter_id ) ) {
			return false;
		}

		lang_push( user_pref_get_language( $p_reporter_id, $p_project_id ) );

		$t_email   = user_get_email( $p_reporter_id );
		$t_subject = BfsEmail::format_subject(
			'EVENT_DISPLAY_EMAIL_BUILD_SUBJECT',
			'[Relance] ' . email_build_subject( $p_bug_id ),
			$p_bug_id
		);
		$t_url = string_get_bug_view_url_with_fqdn( $p_bug_id );
		$t_message = sprintf(
			"Votre demande de support est en attente d'une action de votre part depuis plus de %d jour(s).\n\n"
			. "Merci de consulter le portail et d'apporter les informations ou validations demandées par l'équipe BFS.\n\n"
			. "Lien direct vers la demande :\n%s\n\n"
			. "Pour toute question : support@bfs.tn",
			$p_days,
			$t_url
		);

		$t_id = email_store( $t_email, $t_subject, $t_message );
		lang_pop();

		return null !== $t_id;
	}

	private static function was_recently_reminded( array $p_log, $p_bug_id, $p_days ) {
		if( !isset( $p_log[$p_bug_id] ) ) {
			return false;
		}

		return ( time() - (int)$p_log[$p_bug_id] ) < ( $p_days * 86400 );
	}

	private static function load_log() {
		$t_raw = plugin_config_get( 'reminder_log', '{}' );
		$t_decoded = json_decode( $t_raw, true );

		return is_array( $t_decoded ) ? $t_decoded : array();
	}

	private static function save_log( array $p_log ) {
		$t_now = time();
		foreach( $p_log as $t_bug_id => $t_ts ) {
			if( ( $t_now - (int)$t_ts ) > ( 90 * 86400 ) ) {
				unset( $p_log[$t_bug_id] );
			}
		}

		plugin_config_set( 'reminder_log', json_encode( $p_log ) );
	}
}
