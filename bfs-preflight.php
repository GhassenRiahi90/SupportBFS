<?php
/**
 * Contrôle pré-installation Support BFS — supprimer après installation réussie.
 */
header( 'Content-Type: text/html; charset=utf-8' );

$t_checks = array();

$t_checks[] = array(
	'label' => 'PHP >= 8.1',
	'ok'    => version_compare( PHP_VERSION, '8.1.0', '>=' ),
	'detail' => 'Version actuelle : ' . PHP_VERSION,
);

$t_checks[] = array(
	'label' => 'Extension mbstring',
	'ok'    => extension_loaded( 'mbstring' ),
	'detail' => extension_loaded( 'mbstring' ) ? 'OK' : 'Activer dans cPanel → MultiPHP INI Editor (support.bfs.tn)',
);

$t_checks[] = array(
	'label' => 'Extension mysqli',
	'ok'    => extension_loaded( 'mysqli' ),
	'detail' => extension_loaded( 'mysqli' ) ? 'OK' : 'Requis pour MySQL',
);

$t_checks[] = array(
	'label' => 'Extension gd',
	'ok'    => extension_loaded( 'gd' ),
	'detail' => extension_loaded( 'gd' ) ? 'OK' : 'Requis pour graphiques / avatars',
);

$t_checks[] = array(
	'label' => 'Extension curl',
	'ok'    => extension_loaded( 'curl' ),
	'detail' => extension_loaded( 'curl' ) ? 'OK' : 'Requis pour certaines fonctions réseau',
);

$t_checks[] = array(
	'label' => 'Extension fileinfo',
	'ok'    => extension_loaded( 'fileinfo' ),
	'detail' => extension_loaded( 'fileinfo' ) ? 'OK' : 'Requis pour pièces jointes',
);

$t_config_writable = is_writable( __DIR__ . '/config' );
$t_checks[] = array(
	'label' => 'Dossier config/ inscriptible',
	'ok'    => $t_config_writable,
	'detail' => $t_config_writable ? 'OK' : 'chmod 755 ou 775 sur config/',
);

$t_config_exists = file_exists( __DIR__ . '/config/config_inc.php' );
$t_checks[] = array(
	'label' => 'config/config_inc.php present',
	'ok'    => $t_config_exists,
	'detail' => $t_config_exists ? 'OK' : 'Copier config/config_inc.php.sample → config/config_inc.php',
);

$t_attach_dir = __DIR__ . '/attachments';
if( !is_dir( $t_attach_dir ) ) {
	@mkdir( $t_attach_dir, 0755, true );
}
$t_attach_writable = is_dir( $t_attach_dir ) && is_writable( $t_attach_dir );
$t_checks[] = array(
	'label' => 'Dossier attachments/ inscriptible',
	'ok'    => $t_attach_writable,
	'detail' => $t_attach_writable ? 'OK' : 'Créer attachments/ avec chmod 755',
);

$t_all_ok = true;
foreach( $t_checks as $t_check ) {
	if( !$t_check['ok'] ) {
		$t_all_ok = false;
		break;
	}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8" />
	<title>Pré-installation — Support BFS</title>
	<style>
		body { font-family: "Segoe UI", Arial, sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; color: #333; }
		h1 { color: #0073B1; }
		.ok { color: #2E7D32; }
		.ko { color: #B71C1C; }
		li { margin: .6rem 0; }
		.next { margin-top: 1.5rem; padding: 1rem; background: #f5f9fc; border-left: 4px solid #0073B1; }
	</style>
</head>
<body>
	<h1>Pré-installation Support BFS</h1>
	<p>SAPI : <strong><?php echo htmlspecialchars( php_sapi_name(), ENT_QUOTES, 'UTF-8' ); ?></strong></p>
	<ul>
<?php foreach( $t_checks as $t_check ) { ?>
		<li class="<?php echo $t_check['ok'] ? 'ok' : 'ko'; ?>">
			<strong><?php echo $t_check['ok'] ? 'OK' : 'KO'; ?></strong>
			— <?php echo htmlspecialchars( $t_check['label'], ENT_QUOTES, 'UTF-8' ); ?>
			<br /><small><?php echo htmlspecialchars( $t_check['detail'], ENT_QUOTES, 'UTF-8' ); ?></small>
		</li>
<?php } ?>
	</ul>
<?php if( $t_all_ok ) { ?>
	<div class="next">
		<strong>Tout est vert.</strong> Passez à l’installation MantisBT :
		<a href="admin/install.php">admin/install.php</a>
	</div>
<?php } else { ?>
	<div class="next">
		<strong>Corrigez les points KO dans cPanel</strong> avant d’ouvrir install.php.
		Voir <code>REINSTALLATION.md</code> (étape 1 — PHP).
	</div>
<?php } ?>
</body>
</html>
