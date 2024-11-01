<?php
/*
Plugin Name: Site Checklist
Plugin URI: http://van-ons.nl
Description: Going live just became easier. Use this tool under Tool -> Checklist.
Author: Marijn Bent
Version: 1.0.6
Author URI: http://marijnbent.nl
=
*/

if (!defined('WPINC')) {
    die;
}

use VOCheck\Plugin;

spl_autoload_register('vocheck_autoloader');
function vocheck_autoloader($class_name) {
    if (false !== strpos( $class_name, 'VOCheck')) {
        $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $class_file = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
        require_once $classes_dir . $class_file;
    }
}

add_action( 'plugins_loaded', 'vocheck_init' ); // Hook initialization function
function vocheck_init() {
    new Plugin(dirname(__FILE__));
}
?>