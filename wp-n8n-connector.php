<?php
/*
Plugin Name: n8n Connector
Description: Plugin que permite conectar rapidamente con N8N en Wordpress. 
Author: Enzo Medina.
Version: 0.0.2a
License: Private Use Only
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Seguridad

// Definir constantes del plugin
define( 'WP_N8N_CONNECTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_N8N_CONNECTOR_URL', plugin_dir_url( __FILE__ ) );

// Incluir archivos principales
require_once WP_N8N_CONNECTOR_PATH . 'admin/admin-settings.php';
require_once WP_N8N_CONNECTOR_PATH . 'admin/admin-page.php';
require_once WP_N8N_CONNECTOR_PATH . 'includes/shortcodes.php';
require_once WP_N8N_CONNECTOR_PATH . 'includes/api-connections.php';
require_once WP_N8N_CONNECTOR_PATH . 'includes/helpers.php';

// Registrar menú en el admin
add_action('admin_menu', 'wp_n8n_add_admin_menu');
function wp_n8n_add_admin_menu() {
    add_menu_page(
        'WP N8N Connector',          // Título de la página
        'N8N Connector',             // Texto en el menú
        'manage_options',            // Capacidad requerida
        'wp-n8n-connector',          // Slug
        'wp_n8n_settings_page',      // Callback para renderizar
        'dashicons-share',           // Icono
        20                           // Posición
    );
}

// Cargar estilos del admin
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style(
        'wp-n8n-admin',
        plugins_url('admin/admin-styles.css', __FILE__),
        array(),
        '0.1'
    );
});

?>