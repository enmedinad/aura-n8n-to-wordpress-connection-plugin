<?php

/**
 * Plugin Name: InmoAutomator Core
 * Description: Sistema de gestión inmobiliaria conectado con n8n.
 * Version: 1.3
 */

if (! defined('ABSPATH')) exit;

// Cargar dependencias
if (file_exists(plugin_dir_path(__FILE__) . 'inmo-metaboxes.php')) require_once plugin_dir_path(__FILE__) . 'inmo-metaboxes.php';
if (file_exists(plugin_dir_path(__FILE__) . 'inmo-columns.php')) require_once plugin_dir_path(__FILE__) . 'inmo-columns.php';
if (file_exists(plugin_dir_path(__FILE__) . 'inmo-api.php')) require_once plugin_dir_path(__FILE__) . 'inmo-api.php';
if (file_exists(plugin_dir_path(__FILE__) . 'inmo-chat.php')) {
    require_once plugin_dir_path(__FILE__) . 'inmo-chat.php';
}

class InmoAutomator
{

    public function __construct()
    {
        add_action('init', array($this, 'register_cpts'));
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_propiedades'), 10, 2);
    }

    public function register_cpts()
    {
        // 1. CPT PROPIEDADES
        register_post_type('propiedad', array(
            'labels' => array(
                'name' => 'Propiedades',
                'singular_name' => 'Propiedad',
                'featured_image' => 'Imagen de Portada (Principal)', // <--- CAMBIO AQUÍ
                'set_featured_image' => 'Establecer imagen de portada',
                'remove_featured_image' => 'Quitar imagen de portada',
                'use_featured_image' => 'Usar como imagen de portada',
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-admin-home',
        ));

        // 2. CPT DUEÑOS
        register_post_type('dueno', array(
            'labels' => array('name' => 'Dueños', 'singular_name' => 'Dueño'),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title'),
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-businessperson',
        ));

        // 3. CPT CLIENTES (LEADS)
        register_post_type('cliente', array(
            'labels' => array('name' => 'Clientes / Leads', 'singular_name' => 'Cliente'),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title'),
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups',
        ));
    }

    public function disable_gutenberg_propiedades($current_status, $post_type)
    {
        if ($post_type === 'propiedad') return false;
        return $current_status;
    }
}

new InmoAutomator();
