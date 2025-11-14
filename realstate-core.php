<?php
/*
Plugin Name: RealState Plugin automation N8N
Description: Plugin que permite conectar rapidamente con N8N en Wordpress. 
Author: Enzo Medina.
Version: 0.0.2a
License: Private Use Only
*/

// Prevent Direct Access
if (!defined('ABSPATH')) { exit; }

define('RE_CORE_PATH', plugin_dir_path(__FILE__));
define('RE_CORE_URL', plugin_dir_url(__FILE__));
// Cargar archivos necesarios
require_once RE_CORE_PATH . 'includes/helpers.php';
require_once RE_CORE_PATH . 'includes/cpt-property.php';
require_once RE_CORE_PATH . 'includes/cpt-owners.php';
require_once RE_CORE_PATH . 'includes/cpt-leads.php';
require_once RE_CORE_PATH . 'includes/rest-endpoints.php';

// Opcional: CSS inline para el admin si tu servidor bloquea archivos .css
add_action('admin_head', function() {
    echo '<style>
        .re-field { width:100%; max-width:700px; }
        .re-note { color:#666; font-size:12px; }
    </style>';
});
