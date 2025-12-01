<?php
/**
 * Plugin Name: InmoAutomator Core
 * Description: Sistema de gestión inmobiliaria conectado con n8n (Propiedades, Dueños, Clientes).
 * Version: 1.0
 * Author: Enzo Medina
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Seguridad

class InmoAutomator {

    public function __construct() {
        add_action( 'init', array( $this, 'register_cpts' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        // Forzar Editor Clásico para Propiedades
        add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg_propiedades' ), 10, 2 );
    }

    public function register_cpts() {
        // 1. CPT PROPIEDADES
        register_post_type( 'propiedad', array(
            'labels' => array( 'name' => 'Propiedades', 'singular_name' => 'Propiedad' ),
            'public' => true,
            'has_archive' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Editor activa el clásico
            'show_in_rest' => true, // Necesario para n8n
            'menu_icon' => 'dashicons-admin-home',
        ));

        // 2. CPT DUEÑOS
        register_post_type( 'dueno', array(
            'labels' => array( 'name' => 'Dueños', 'singular_name' => 'Dueño' ),
            'public' => false, // No necesitan URL pública frontend por lo general
            'show_ui' => true,
            'supports' => array( 'title' ), // El nombre va en el título
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-businessperson',
        ));

        // 3. CPT CLIENTES (LEADS)
        register_post_type( 'cliente', array(
            'labels' => array( 'name' => 'Clientes / Leads', 'singular_name' => 'Cliente' ),
            'public' => false,
            'show_ui' => true,
            'supports' => array( 'title' ), // Nombre del cliente en título
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups',
        ));
    }

    public function register_taxonomies() {
        // Amenities (Cocina, Living, etc.)
        register_taxonomy( 'amenities', 'propiedad', array(
            'label' => 'Amenities',
            'hierarchical' => true, // Como categorías (checkboxes)
            'show_in_rest' => true,
        ));

        // Condiciones del Dueño (No mascotas, etc.)
        register_taxonomy( 'condiciones_dueno', 'propiedad', array(
            'label' => 'Condiciones Dueño',
            'hierarchical' => true,
            'show_in_rest' => true,
        ));
    }

    public function disable_gutenberg_propiedades( $current_status, $post_type ) {
        if ( $post_type === 'propiedad' ) return false; // Desactiva Gutenberg
        return $current_status;
    }
}



new InmoAutomator();