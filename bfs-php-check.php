<?php
/**
 * Diagnostic PHP temporaire — supprimer après vérification.
 */
header( 'Content-Type: text/plain; charset=utf-8' );

echo 'PHP: ' . PHP_VERSION . "\n";
echo 'SAPI: ' . php_sapi_name() . "\n";
echo 'mbstring: ' . ( extension_loaded( 'mbstring' ) ? 'OUI' : 'NON' ) . "\n";
echo 'mysqli: ' . ( extension_loaded( 'mysqli' ) ? 'OUI' : 'NON' ) . "\n";
echo 'php.ini: ' . ( php_ini_loaded_file() ?: '(aucun)' ) . "\n";
echo 'user_ini: ' . ( ini_get( 'user_ini.filename' ) ?: '(desactive)' ) . "\n";
echo 'scan: ' . ( php_ini_scanned_files() ?: '(aucun)' ) . "\n";
echo 'extensions: ' . implode( ', ', get_loaded_extensions() ) . "\n";
