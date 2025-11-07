<?php
function mi_plugin_enqueue_assets() {
    $plugin_url = plugin_dir_url( __FILE__ ) . '../build/';

    wp_enqueue_script(
        'mi-plugin-react',
        $plugin_url . 'static/js/main.js',
        array(), // Dependencias (React ya viene incluido en el build)
        '1.0.0',
        true
    );

    wp_enqueue_style(
        'mi-plugin-css',
        $plugin_url . 'static/css/main.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'mi_plugin_enqueue_assets');