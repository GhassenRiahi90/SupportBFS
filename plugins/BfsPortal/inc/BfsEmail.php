<?php
/**
 * Support BFS — templates et formatage des emails de notification.
 */

class BfsEmail {
	const HTML_MARKER = '<!-- bfs-email-html -->';
	const PLAIN_BEGIN = '<!-- bfs-email-plain';
	const PLAIN_END   = '-->';

	/**
	 * Enveloppe le corps texte en HTML BFS (appelé à l'envoi, après formatage MantisBT).
	 */
	public static function wrap_if_enabled( $p_plain ) {
		if( OFF == plugin_config_get( 'email_html_enabled', ON ) ) {
			return $p_plain;
		}

		if( self::is_wrapped( $p_plain ) ) {
			return $p_plain;
		}

		return self::wrap_html( $p_plain );
	}

	/**
	 * Chaîne EVENT_DISPLAY_EMAIL_BUILD_SUBJECT : préfixe Support BFS.
	 */
	public static function format_subject( $p_event, $p_subject, $p_bug_id = null ) {
		if( OFF == plugin_config_get( 'email_branded_subject', ON ) ) {
			return $p_subject;
		}

		$t_prefix = '[Support BFS] ';
		if( 0 === strpos( $p_subject, $t_prefix ) ) {
			return $p_subject;
		}

		return $t_prefix . $p_subject;
	}

	public static function is_wrapped( $p_string ) {
		return false !== strpos( $p_string, self::HTML_MARKER );
	}

	public static function extract_plain( $p_html ) {
		$t_start = strpos( $p_html, self::PLAIN_BEGIN );
		if( false === $t_start ) {
			return trim( html_entity_decode( strip_tags( $p_html ), ENT_QUOTES, 'UTF-8' ) );
		}

		$t_start += strlen( self::PLAIN_BEGIN );
		$t_end = strpos( $p_html, self::PLAIN_END, $t_start );
		if( false === $t_end ) {
			return trim( html_entity_decode( strip_tags( $p_html ), ENT_QUOTES, 'UTF-8' ) );
		}

		return trim( substr( $p_html, $t_start, $t_end - $t_start ) );
	}

	private static function wrap_html( $p_plain ) {
		$t_plain = rtrim( $p_plain );
		$t_body  = nl2br( htmlspecialchars( $t_plain, ENT_QUOTES, 'UTF-8' ) );
		$t_logo  = self::logo_url();
		$t_year  = date( 'Y' );
		$t_path  = rtrim( config_get_global( 'path' ), '/' ) . '/';

		$t_header_logo = '';
		if( $t_logo ) {
			$t_header_logo = '<img src="' . htmlspecialchars( $t_logo, ENT_QUOTES, 'UTF-8' )
				. '" alt="Support BFS" width="160" style="display:block;max-width:160px;height:auto;border:0;" />';
		} else {
			$t_header_logo = '<div style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:0.02em;">Support BFS</div>';
		}

		$t_plain_marker = self::PLAIN_BEGIN . "\n" . $t_plain . "\n" . self::PLAIN_END;

		return '<!DOCTYPE html>'
			. '<html lang="fr"><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1" />'
			. '<title>Support BFS</title></head>'
			. '<body style="margin:0;padding:0;background:#eef2f5;font-family:Source Sans 3,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif;color:#2c3e50;">'
			. self::HTML_MARKER
			. $t_plain_marker
			. '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#eef2f5;padding:24px 12px;">'
			. '<tr><td align="center">'
			. '<table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #d8dee4;">'
			. '<tr><td style="background:linear-gradient(135deg,#0E5A8A 0%,#4FA8D8 100%);padding:24px 28px;">'
			. $t_header_logo
			. '<div style="margin-top:8px;font-size:13px;color:rgba(255,255,255,0.9);">Portail support clients BFS</div>'
			. '</td></tr>'
			. '<tr><td style="padding:28px;font-size:14px;line-height:1.6;color:#2c3e50;">'
			. $t_body
			. '</td></tr>'
			. '<tr><td style="padding:20px 28px;background:#f7f9fb;border-top:1px solid #e4e9ed;font-size:12px;line-height:1.5;color:#5a6670;">'
			. 'Portail support &mdash; <a href="mailto:support@bfs.tn" style="color:#0E5A8A;text-decoration:none;">support@bfs.tn</a>'
			. ' &mdash; <a href="https://www.bfs.tn" style="color:#0E5A8A;text-decoration:none;">www.bfs.tn</a><br />'
			. '&copy; Business Financial Solutions ' . $t_year
			. ' &mdash; <a href="' . htmlspecialchars( $t_path, ENT_QUOTES, 'UTF-8' )
			. '" style="color:#0E5A8A;text-decoration:none;">Acc&eacute;der au portail</a>'
			. '</td></tr>'
			. '</table></td></tr></table>'
			. '</body></html>';
	}

	private static function logo_url() {
		$t_relative = 'plugins/BfsPortal/assets/img/bfs-logo.png';
		$t_local    = dirname( __DIR__, 3 ) . '/' . $t_relative;

		if( !file_exists( $t_local ) ) {
			return '';
		}

		return rtrim( config_get_global( 'path' ), '/' ) . '/' . $t_relative;
	}
}
