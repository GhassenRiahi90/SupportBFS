<?php
header( 'Content-Type: text/plain; charset=utf-8' );

echo 'PHP: ' . PHP_VERSION . "\n";
echo 'SAPI: ' . php_sapi_name() . "\n";
echo 'mbstring: ' . ( extension_loaded( 'mbstring' ) ? 'OUI' : 'NON' ) . "\n";

$t_ini = __DIR__ . '/.user.ini';
echo 'user_ini.file: ' . ( @file_exists( $t_ini ) ? 'present' : 'ABSENT' ) . "\n";
echo 'user_ini.cache_ttl: ' . @ini_get( 'user_ini.cache_ttl' ) . "\n";
if( @file_exists( $t_ini ) ) {
	echo "\n.user.ini:\n" . @file_get_contents( $t_ini ) . "\n";
}

$t_so = '/opt/cpanel/ea-php82/root/usr/lib64/php/modules/mbstring.so';
echo 'mbstring.so: ' . ( @file_exists( $t_so ) ? 'EXISTS' : 'missing' ) . "\n";
