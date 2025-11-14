<?php
/*
Plugin Name: RealState Plugin automation N8N
Description: Plugin que permite conectar rapidamente con N8N en Wordpress. 
Author: Enzo Medina.
Version: 0.0.1 Starter
License: Private Use Only
*/

// Prevent Direct Access
if (!defined('ABSPATH')) { exit; }

add_action('init', function() {
    register_post_type('property', [
        'label' => 'Propiedades',
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title','editor','thumbnail','custom-fields'],
        'has_archive' => true,
        'menu_icon' => 'dashicons-building',
    ]);

    register_taxonomy('amenities', 'property', [
        'label' => 'Amenities',
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
    ]);

    register_taxonomy('services', 'property', [
        'label' => 'Servicios',
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
    ]);

    register_taxonomy('furniture', 'property', [
        'label' => 'Muebles',
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
    ]);
});

// Meta de propiedades
add_action('init', function() {
    $meta_args_single_text = ['show_in_rest' => true, 'single' => true, 'type' => 'string', 'auth_callback' => '__return_true'];
    $meta_args_single_num  = ['show_in_rest' => true, 'single' => true, 'type' => 'number', 'auth_callback' => '__return_true'];
    $meta_args_single_bool = ['show_in_rest' => true, 'single' => true, 'type' => 'boolean', 'auth_callback' => '__return_true'];

    register_post_meta('property', 'property_type', $meta_args_single_text); //Casa , Apartamento, etc.
    register_post_meta('property', 'street',        $meta_args_single_text); // Dirección Calle
    register_post_meta('property', 'province',      $meta_args_single_text); // Provincia EJ Ciudad Vieja, Parque Batle , Ituzaingo etc.
    register_post_meta('property', 'region',        $meta_args_single_text); // Region EJ Montevideo, Canelones, Maldonado etc.

    register_post_meta('property', 'price',             $meta_args_single_num); // Precio de venta o alquiler
    register_post_meta('property', 'is_for_rent',      $meta_args_single_bool); // true=alquiler , false=venta
    register_post_meta('property', 'guarantees',        $meta_args_single_text); // solo alquiler (Ej: Deposito, Anda, etc.)
    register_post_meta('property', 'common_expenses',   $meta_args_single_num);  // solo alquiler Gastos comunes

    register_post_meta('property', 'bedrooms',      $meta_args_single_num); // Cantidad de dormitorios
    register_post_meta('property', 'bathrooms',     $meta_args_single_num); // Cantidad de baños
    register_post_meta('property', 'area_m2',       $meta_args_single_num); // Area en m2 Edificada
    register_post_meta('property', 'land_area_m2', $meta_args_single_num); // Area en m2 Terreno 

    register_post_meta('property', 'pets_allowed',  $meta_args_single_bool); // Acepta mascotas o no

    // Relación con dueño
    register_post_meta('property', 'owner_id',      ['show_in_rest' => true, 'single' => true, 'type' => 'integer', 'auth_callback' => '__return_true']); // ID del owner (CPT Owner)

    // Nota interna solo admin (expuesta en REST para admin; ocultar en frontend) 
    // Esto permite anotar detalles internos que no se muestran al público pero que pueden ser útiles para la gestión.
    register_post_meta('property', 'admin_note', [
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function() { return current_user_can('manage_options'); }
    ]);

    // Estado de disponibilidad (actualizable por n8n)
    register_post_meta('property', 'availability_status', $meta_args_single_text); // disponible/no_disponible
});