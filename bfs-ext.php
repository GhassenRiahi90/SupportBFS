<?php
header( 'Content-Type: text/plain; charset=utf-8' );
echo 'PHP ' . PHP_VERSION . "\n";
echo 'mbstring: ' . ( extension_loaded( 'mbstring' ) ? 'OUI' : 'NON' ) . "\n";
echo 'gd: ' . ( extension_loaded( 'gd' ) ? 'OUI' : 'NON' ) . "\n";
echo 'fileinfo: ' . ( extension_loaded( 'fileinfo' ) ? 'OUI' : 'NON' ) . "\n";
echo 'index.php: ' . ( file_exists( __DIR__ . '/index.php' ) ? 'present' : 'absent' ) . "\n";
echo 'wp-load.php: ' . ( file_exists( __DIR__ . '/wp-load.php' ) ? 'PRESENT (WordPress!)' : 'absent' ) . "\n";
echo 'core.php: ' . ( file_exists( __DIR__ . '/core.php' ) ? 'present (MantisBT)' : 'absent' ) . "\n";
echo 'config_inc.php: ' . ( file_exists( __DIR__ . '/config/config_inc.php' ) ? 'present' : 'absent' ) . "\n";
