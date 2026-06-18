<?php
/**
 * Support BFS — page d'audit du cloisonnement client.
 */

require_once dirname( __DIR__ ) . '/inc/BfsIsolation.php';
require_once dirname( __DIR__ ) . '/inc/BfsClientAccess.php';
require_once dirname( __DIR__ ) . '/inc/BfsArchitecture.php';

require_api( 'access_api.php' );

access_ensure_global_level( DEVELOPER );

layout_page_header( 'Audit cloisonnement BFS' );
layout_page_begin();

$t_findings = BfsIsolation::run_audit();
$t_summary  = BfsIsolation::summarize( $t_findings );

echo '<div class="col-md-12 col-xs-12">';
echo '<div class="space-10"></div>';
echo '<div class="widget-box widget-color-blue2">';
echo '<div class="widget-header widget-header-flat">';
echo '<h4 class="widget-title lighter">Audit cloisonnement client — Support BFS</h4>';
echo '</div>';
echo '<div class="widget-body"><div class="widget-main padding-16">';

echo '<p>Vérification automatique de la confidentialité multi-clients (1 projet = 1 client).</p>';

echo '<div class="bfs-audit-summary">';
echo '<span class="label label-default">Total : ' . (int)$t_summary['total'] . '</span> ';
echo '<span class="label label-danger">Erreurs : ' . (int)$t_summary['errors'] . '</span> ';
echo '<span class="label label-warning">Avertissements : ' . (int)$t_summary['warnings'] . '</span> ';
echo '<span class="label label-info">Infos : ' . (int)$t_summary['infos'] . '</span> ';
echo '<span class="label label-success">OK : ' . (int)$t_summary['oks'] . '</span>';
echo '</div>';

echo '<div class="space-10"></div>';
echo '<div class="table-responsive">';
echo '<table class="table table-bordered table-condensed table-striped">';
echo '<thead><tr>';
echo '<th>Niveau</th><th>Code</th><th>Message</th><th>Contexte</th>';
echo '</tr></thead><tbody>';

foreach( $t_findings as $t_row ) {
	$t_label = 'default';
	switch( $t_row['severity'] ) {
		case BfsIsolation::SEVERITY_ERROR:
			$t_label = 'danger';
			break;
		case BfsIsolation::SEVERITY_WARNING:
			$t_label = 'warning';
			break;
		case BfsIsolation::SEVERITY_OK:
			$t_label = 'success';
			break;
		case BfsIsolation::SEVERITY_INFO:
			$t_label = 'info';
			break;
	}

	echo '<tr>';
	echo '<td><span class="label label-' . $t_label . '">' . string_attribute( strtoupper( $t_row['severity'] ) ) . '</span></td>';
	echo '<td><code>' . string_display_line( $t_row['code'] ) . '</code></td>';
	echo '<td>' . string_display_line( $t_row['message'] ) . '</td>';
	echo '<td><small>' . string_display_line( $t_row['context'] ) . '</small></td>';
	echo '</tr>';
}

echo '</tbody></table></div>';

echo '<p class="text-muted"><small>';
echo 'Relancer après chaque onboarding client. Voir ONBOARDING_CLIENT.md pour la procédure.';
echo '</small></p>';

echo '</div></div></div>';
echo '</div>';

layout_page_end();
