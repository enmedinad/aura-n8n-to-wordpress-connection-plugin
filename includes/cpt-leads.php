<?php
/*
Plugin Part: Custom Post Type Leads
Description: Define el Custom Post Type (CPT) para Leads y sus metadatos asociados. Encargado de gestionar a los clientes interesados en propiedades.
Author: Enzo Medina.
Version: 0.0.2a
License: Private Use Only
*/

// Prevent Direct Access
if (!defined('ABSPATH')) { exit; }

add_action('init', function() {
    register_post_type('lead', [
        'label' => 'Leads',
        'public' => false,        // no se muestra en frontend
        'show_ui' => true,        // visible en admin
        'show_in_rest' => true,   // n8n puede crear/actualizar
        'supports' => ['title','custom-fields'],
        'menu_icon' => 'dashicons-id',
    ]);
});

// Meta de lead
add_action('init', function() {
    $text = ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => '__return_true'];
    $int  = ['show_in_rest' => true, 'single' => true, 'type' => 'integer', 'auth_callback' => '__return_true'];

    register_post_meta('lead', 'customer_name', $text); // Nombre del cliente interesado
    register_post_meta('lead', 'email',         $text); // Email del cliente interesado
    register_post_meta('lead', 'phone',         $text); // Teléfono del cliente interesado

    register_post_meta('lead', 'query_text',         $text); // Consulta Realizada por el cliente
    register_post_meta('lead', 'criteria_structured', $text); // Criterios estructurados (JSON)

    register_post_meta('lead', 'property_id',  $int); // ID de la propiedad del interesado (CPT-Property)
    register_post_meta('lead', 'status',       $text); // Estado del lead (nuevo, contactado, cerrado, etc.) Solo para Seguimiento interno , Default = "nuevo"
    register_post_meta('lead', 'date',         $text); // ISO string
    register_post_meta('lead', 'assigned_to',  $text); // username/ID del encargado de gestionar el lead (solo para seguimiento interno) Default = "" (sin asignar)
});