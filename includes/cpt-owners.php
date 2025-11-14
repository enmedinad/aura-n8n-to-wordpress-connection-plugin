<?php
/*
Plugin Part: Custom Post Type Owners
Description: Define el Custom Post Type (CPT) para Dueños de Propiedades y sus metadatos asociados. Encargado de gestionar la información de los propietarios de las propiedades listadas.
Author: Enzo Medina.
Version: 0.0.2a
License: Private Use Only
*/

// Prevent Direct Access
if (!defined('ABSPATH')) { exit; }

add_action('init', function() {
    register_post_type('owner', [
        'label' => 'Dueños',
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'supports' => ['title','custom-fields'],
        'menu_icon' => 'dashicons-admin-users',
    ]);
});

// Meta de dueño (Numero De Telefono, Nombre del dueño y Propiedad relacionada)
add_action('init', function() {
    $text = ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => '__return_true'];

    register_post_meta('owner', 'full_name', $text);
    register_post_meta('owner', 'phone', $text); // obligatorio para WhatsApp bot
    // Propiedad relacionada al dueño (CPT-Property)
    register_post_meta('owner', 'related_property_id', ['show_in_rest' => true, 'single' => true, 'type' => 'integer', 'auth_callback' => '__return_true']);
});