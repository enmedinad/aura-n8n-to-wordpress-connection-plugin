<?php
/*
Plugin Name: n8n Connector
Description: Plugin standalone con front-end en React , Conexion con N8N. 
Author: Enzo Medina.
Version: 0.0.2a
License: Private Use Only
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Seguridad

// Incluir funciones de carga
require_once plugin_dir_path( __FILE__ ) . 'includes/enqueue.php';

// Shortcode para renderizar el contenedor React
function mi_plugin_shortcode() {
    return '<div id="mi-plugin-root"></div>';
}
add_shortcode('mi_plugin', 'mi_plugin_shortcode');






?>