<?php
// Seguridad
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Callback que renderiza la página de configuración
function wp_n8n_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración del Agente Inmobiliario</h1>
        <p>Agrega aquí las URLs de tus webhooks de n8n para consultas y agendas.</p>

        <form method="post" action="options.php">
            <?php
            // Cargar configuración registrada en admin-settings.php
            settings_fields('wp_n8n_settings_group');
            do_settings_sections('wp-n8n-connector');
            submit_button('Guardar cambios');
            ?>
        </form>
    </div>
    <?php
}