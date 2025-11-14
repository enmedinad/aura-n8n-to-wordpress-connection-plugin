<?php
/*
Plugin Part: Rest Endpoints
Description: Define los endpoints REST personalizados para interactuar con los Custom Post Types (CPT) de Propiedades, Dueños y Leads. Permite operaciones CRUD y consultas específicas para gestionar los datos inmobiliarios.
Author: Enzo Medina.
Version: 0.0.2 Starter
License: Private Use Only
*/

// Prevent Direct Access
if (!defined('ABSPATH')) { exit; }

// Búsqueda específica con filtros (más eficiente para n8n)
add_action('rest_api_init', function() {
    register_rest_route('realestate/v1', '/search', [
        'methods' => 'GET',
        'callback' => 're_search_properties',
        'permission_callback' => '__return_true', // público de lectura
    ]);

    register_rest_route('realestate/v1', '/lead', [
        'methods' => 'POST',
        'callback' => 're_create_lead',
        'permission_callback' => function() {
            return is_user_logged_in() || re_verify_token();
        },
    ]);
});

function re_search_properties(WP_REST_Request $req) {
    $type      = sanitize_text_field($req->get_param('property_type'));
    $province  = sanitize_text_field($req->get_param('province'));
    $region    = sanitize_text_field($req->get_param('region'));
    $price = absint($req->get_param('price'));
    $bedrooms  = absint($req->get_param('bedrooms'));

    $meta_query = ['relation' => 'AND'];
    if ($type)      { $meta_query[] = ['key' => 'property_type', 'value' => $type, 'compare' => '=']; }
    if ($province)  { $meta_query[] = ['key' => 'province', 'value' => $province, 'compare' => '=']; }
    if ($region)    { $meta_query[] = ['key' => 'region', 'value' => $region, 'compare' => '=']; }
    if ($price) { $meta_query[] = ['key' => 'price', 'value' => $price, 'compare' => '>=', 'type' => 'NUMERIC']; }
    if ($bedrooms)  { $meta_query[] = ['key' => 'bedrooms', 'value' => $bedrooms, 'compare' => '>=', 'type' => 'NUMERIC']; }

    $q = new WP_Query([
        'post_type' => 'property',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'meta_query' => $meta_query,
    ]);

    $items = [];
    foreach ($q->posts as $p) {
        $items[] = [
            'id' => $p->ID,
            'title' => get_the_title($p),
            'link' => get_permalink($p),
            'price' => (float) get_post_meta($p->ID, 'price', true),
            'property_type' => get_post_meta($p->ID, 'property_type', true),
            'province' => get_post_meta($p->ID, 'province', true),
            'region' => get_post_meta($p->ID, 'region', true),
            'bedrooms' => (int) get_post_meta($p->ID, 'bedrooms', true),
            'bathrooms'=> (int) get_post_meta($p->ID, 'bathrooms', true),
            'area_m2'  => (float) get_post_meta($p->ID, 'area_m2', true),
            'pets_allowed' => (bool) get_post_meta($p->ID, 'pets_allowed', true),
            'amenities' => wp_get_post_terms($p->ID, 'amenities', ['fields' => 'names']),
            'services'  => wp_get_post_terms($p->ID, 'services',  ['fields' => 'names']),
            'furniture' => wp_get_post_terms($p->ID, 'furniture', ['fields' => 'names']),
            // No exponemos admin_note aquí
        ];
    }
    return rest_ensure_response(['items' => $items, 'total' => (int)$q->found_posts]);
}

function re_create_lead(WP_REST_Request $req) {
    $name   = sanitize_text_field($req->get_param('customer_name'));
    $email  = sanitize_email($req->get_param('email'));
    $phone  = sanitize_text_field($req->get_param('phone'));
    $propId = absint($req->get_param('property_id'));
    $status = sanitize_text_field($req->get_param('status')) ?: 'interesado';
    $date   = sanitize_text_field($req->get_param('date')) ?: current_time('mysql');
    $assigned = sanitize_text_field($req->get_param('assigned_to'));

    if (!$name || !$propId) {
        return new WP_Error('invalid_params', 'Nombre y property_id son obligatorios', ['status' => 400]);
    }

    $lead_id = wp_insert_post([
        'post_type' => 'lead',
        'post_title' => $name,
        'post_status' => 'publish', // en backend
    ]);

    if (is_wp_error($lead_id)) {
        return new WP_Error('insert_failed', 'No se pudo crear el lead', ['status' => 500]);
    }

    update_post_meta($lead_id, 'customer_name', $name);
    if ($email)  update_post_meta($lead_id, 'email', $email);
    if ($phone)  update_post_meta($lead_id, 'phone', $phone);
    update_post_meta($lead_id, 'property_id', $propId);
    update_post_meta($lead_id, 'status', $status);
    update_post_meta($lead_id, 'date', $date);
    if ($assigned) update_post_meta($lead_id, 'assigned_to', $assigned);

    return rest_ensure_response(['id' => $lead_id, 'status' => 'ok']);
}

// Ejemplo simple de verificación por token (ajústalo a tu flujo de seguridad)
function re_verify_token() {
    $hdrs = getallheaders();
    $token = isset($hdrs['X-Api-Token']) ? $hdrs['X-Api-Token'] : '';
    $expected = get_option('re_api_token'); // guárdalo en tu admin
    return $token && $expected && hash_equals($expected, $token);
}