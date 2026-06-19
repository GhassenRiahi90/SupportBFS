<?php
header( 'Content-Type: text/plain; charset=utf-8' );

echo 'PHP: ' . PHP_VERSION . "\n";
echo 'SAPI: ' . php_sapi_name() . "\n";
echo 'mbstring: ' . ( extension_loaded( 'mbstring' ) ? 'OUI' : 'NON' ) . "\n";
echo 'mysqli: ' . ( extension_loaded( 'mysqli' ) ? 'OUI' : 'NON' ) . "\n";

$t_modules = array(
	'/opt/cpanel/ea-php82/root/usr/lib64/php/modules/mbstring.so',
	'/opt/cpanel/ea-php83/root/usr/lib64/php/modules/mbstring.so',
	'/opt/alt/php82/usr/lib64/php/modules/mbstring.so',
);

echo "\nmodules:\n";
foreach( $t_modules as $t_path ) {
	echo $t_path . ' => ' . ( @file_exists( $t_path ) ? 'EXISTS' : 'missing' ) . "\n";
}

echo "\nloaded: " . implode( ', ', get_loaded_extensions() ) . "\n";
