<?php
/**
 * Support BFS — routage des notifications par solution.
 */

class BfsEmailNotify {
	/**
	 * @return int[]
	 */
	public static function notify_include( $p_event, $p_bug_id, $p_notify_type ) {
		if( OFF == plugin_config_get( 'solution_notify_enabled', ON ) ) {
			return array();
		}

		if( !in_array( $p_notify_type, array( 'new', 'owner', 'bugnote' ), true ) ) {
			return array();
		}

		return self::team_user_ids_for_bug( $p_bug_id );
	}

	/**
	 * @return int[]
	 */
	private static function team_user_ids_for_bug( $p_bug_id ) {
		require_api( 'bug_api.php' );
		require_api( 'category_api.php' );

		$t_bug = bug_get( $p_bug_id, true );
		if( !$t_bug->category_id ) {
			return self::default_team_user_ids();
		}

		$t_category = category_get_name( $t_bug->category_id );
		$t_solution = self::solution_from_category( $t_category );
		$t_map      = self::solution_notify_map();

		if( isset( $t_map[$t_solution] ) && is_array( $t_map[$t_solution] ) ) {
			return self::sanitize_user_ids( $t_map[$t_solution] );
		}

		return self::default_team_user_ids();
	}

	private static function solution_from_category( $p_category ) {
		$t_parts = explode( ' — ', $p_category, 2 );
		return trim( $t_parts[0] );
	}

	/**
	 * @return array<string, int[]>
	 */
	private static function solution_notify_map() {
		$t_raw = plugin_config_get( 'solution_notify_map', '' );
		if( is_blank( $t_raw ) ) {
			return array();
		}

		$t_decoded = json_decode( $t_raw, true );
		if( !is_array( $t_decoded ) ) {
			return array();
		}

		return $t_decoded;
	}

	/**
	 * @return int[]
	 */
	private static function default_team_user_ids() {
		$t_raw = plugin_config_get( 'default_notify_user_ids', '' );
		if( is_blank( $t_raw ) ) {
			return array();
		}

		return self::sanitize_user_ids( explode( ',', $t_raw ) );
	}

	/**
	 * @param array $p_ids
	 * @return int[]
	 */
	private static function sanitize_user_ids( array $p_ids ) {
		require_api( 'user_api.php' );

		$t_result = array();
		foreach( $p_ids as $t_id ) {
			$t_id = (int)trim( (string)$t_id );
			if( $t_id <= 0 || !user_exists( $t_id ) || !user_is_enabled( $t_id ) ) {
				continue;
			}
			$t_result[] = $t_id;
		}

		return array_values( array_unique( $t_result ) );
	}
}
