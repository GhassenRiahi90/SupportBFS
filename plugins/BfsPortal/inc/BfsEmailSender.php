<?php
/**
 * Support BFS — envoi HTML multipart via PHPMailer.
 */

require_once dirname( __DIR__, 3 ) . '/core/classes/EmailSenderPhpMailer.class.php';
require_once __DIR__ . '/BfsEmail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as phpmailerException;

class BfsEmailSender extends EmailSenderPhpMailer {
	/**
	 * @param EmailMessage $p_message The email to send
	 * @return bool
	 */
	public function send( EmailMessage $p_message ) : bool {
		$p_message->text = BfsEmail::wrap_if_enabled( $p_message->text );

		if( !BfsEmail::is_wrapped( $p_message->text ) ) {
			return parent::send( $p_message );
		}

		global $g_phpMailer;

		if( is_null( $g_phpMailer ) ) {
			if( PHPMAILER_METHOD_SMTP == config_get( 'phpMailer_method' ) ) {
				register_shutdown_function( 'phpmailer_close' );
			}

			$g_phpMailer = new PHPMailer( true );
			PHPMailer::$validator = 'html5';
		}

		$t_mail = $g_phpMailer;
		$t_alt  = BfsEmail::extract_plain( $p_message->text );

		if( !empty( $p_message->hostname ) ) {
			$t_mail->Hostname = $p_message->hostname;
		}

		$t_mail->setLanguage( lang_get( 'phpmailer_language', $p_message->lang ) );

		switch( config_get( 'phpMailer_method' ) ) {
			case PHPMAILER_METHOD_MAIL:
				$t_mail->isMail();
				break;

			case PHPMAILER_METHOD_SENDMAIL:
				$t_mail->isSendmail();
				break;

			case PHPMAILER_METHOD_SMTP:
				$t_mail->isSMTP();
				$t_mail->SMTPKeepAlive = true;

				if( !is_blank( config_get( 'smtp_username' ) ) ) {
					$t_mail->SMTPAuth = true;
					$t_mail->Username = config_get( 'smtp_username' );
					$t_mail->Password = config_get( 'smtp_password' );
				}

				if( is_blank( config_get( 'smtp_connection_mode' ) ) ) {
					$t_mail->SMTPAutoTLS = false;
				} else {
					$t_mail->SMTPSecure = config_get( 'smtp_connection_mode' );
				}

				$t_mail->Port = config_get( 'smtp_port' );
				break;
		}

		if( ON == config_get_global( 'email_smime_enable' ) ) {
			$t_mail->sign(
				config_get_global( 'email_smime_cert_file' ),
				config_get_global( 'email_smime_key_file' ),
				config_get_global( 'email_smime_key_password' ),
				config_get_global( 'email_smime_extracerts_file' )
			);
		}

		if( config_get_global( 'email_dkim_enable' ) ) {
			$t_mail->DKIM_domain = config_get_global( 'email_dkim_domain' );
			$t_mail->DKIM_private = config_get_global( 'email_dkim_private_key_file_path' );
			$t_mail->DKIM_private_string = config_get_global( 'email_dkim_private_key_string' );
			$t_mail->DKIM_selector = config_get_global( 'email_dkim_selector' );
			$t_mail->DKIM_passphrase = config_get_global( 'email_dkim_passphrase' );
			$t_mail->DKIM_identity = config_get_global( 'email_dkim_identity' );
		}

		$t_mail->isHTML( true );
		$t_mail->CharSet = $p_message->charset;
		$t_mail->Host = config_get( 'smtp_host' );
		$t_mail->From = config_get( 'from_email' );
		$t_mail->Sender = config_get( 'return_path_email' );
		$t_mail->FromName = config_get( 'from_name' );
		$t_mail->Encoding = 'quoted-printable';

		foreach( $p_message->cc as $t_cc ) {
			$t_mail->addCC( $t_cc );
		}

		foreach( $p_message->bcc as $t_bcc ) {
			$t_mail->addBCC( $t_bcc );
		}

		$t_log_msg = 'Error: message could not be sent - ';

		try {
			foreach( $p_message->to as $t_recipient ) {
				$t_mail->addAddress( $t_recipient );
			}
		} catch ( phpmailerException $e ) {
			log_event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			self::reset_mail( $t_mail );
			return false;
		}

		$t_mail->Subject = $p_message->subject;
		$t_mail->Body = $p_message->text;
		$t_mail->AltBody = $t_alt;

		foreach( $p_message->headers as $t_key => $t_value ) {
			switch( strtolower( $t_key ) ) {
				case 'message-id':
					$t_mail->set( 'MessageID', $t_value );
					break;
				default:
					$t_mail->addCustomHeader( $t_key . ': ' . $t_value );
					break;
			}
		}

		try {
			$t_success = $t_mail->send();
			if( !$t_success ) {
				log_event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			}
		} catch ( phpmailerException $e ) {
			log_event( LOG_EMAIL, $t_log_msg . $t_mail->ErrorInfo );
			$t_success = false;
		}

		self::reset_mail( $t_mail );

		return $t_success;
	}

	/**
	 * @param PHPMailer $p_mail
	 * @return void
	 */
	private static function reset_mail( PHPMailer &$p_mail ) : void {
		$p_mail->clearAllRecipients();
		$p_mail->clearAttachments();
		$p_mail->clearReplyTos();
		$p_mail->clearCustomHeaders();
	}
}
