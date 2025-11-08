<?php
// Seguridad
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Registrar opciones y secciones
add_action('admin_init', 'wp_n8n_register_settings');

function wp_n8n_register_settings() {
    // Registrar opciones
    register_setting('wp_n8n_settings_group', 'wp_n8n_webhook_consulta');
    register_setting('wp_n8n_settings_group', 'wp_n8n_webhook_agenda');

    // Sección principal
    add_settings_section(
        'wp_n8n_main_section',
        'Webhooks de n8n',
        null,
        'wp-n8n-connector'
    );

    // Campo: webhook de consultas
    add_settings_field(
        'wp_n8n_webhook_consulta',
        'Webhook de consultas',
        'wp_n8n_webhook_consulta_field',
        'wp-n8n-connector',
        'wp_n8n_main_section'
    );

    // Campo: webhook de agenda
    add_settings_field(
        'wp_n8n_webhook_agenda',
        'Webhook de agenda',
        'wp_n8n_webhook_agenda_field',
        'wp-n8n-connector',
        'wp_n8n_main_section'
    );
}

// Renderizar campo de consultas
function wp_n8n_webhook_consulta_field() {
    $value = get_option('wp_n8n_webhook_consulta', '');
    echo '<input type="text" name="wp_n8n_webhook_consulta" value="' . esc_attr($value) . '" style="width:100%;">';
}

// Renderizar campo de agenda
function wp_n8n_webhook_agenda_field() {
    $value = get_option('wp_n8n_webhook_agenda', '');
    echo '<input type="text" name="wp_n8n_webhook_agenda" value="' . esc_attr($value) . '" style="width:100%;">';
}