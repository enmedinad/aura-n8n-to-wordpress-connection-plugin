<?php
// Añadir página de admin para ejecutar el seeder
add_action('admin_menu', function() {
    add_menu_page('Inmo Seeder', 'Generar Datos', 'manage_options', 'inmo-seeder', 'inmo_run_seeder_page');
});

function inmo_run_seeder_page() {
    if (isset($_POST['run_seeder'])) {
        inmo_generate_dummy_data();
        echo '<div class="updated"><p>✅ 5 Propiedades y 5 Dueños creados con éxito.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Generador de Datos de Prueba</h1>
        <form method="post">
            <input type="hidden" name="run_seeder" value="1">
            <button class="button button-primary button-hero">⚠️ Generar 5 Propiedades y 5 Dueños</button>
        </form>
    </div>
    <?php
}

function inmo_generate_dummy_data() {
    // 1. Crear Dueños
    $duenos_ids = [];
    
    // TU USUARIO REAL
    $my_id = wp_insert_post(['post_type'=>'dueno', 'post_title'=>'Dueño Admin (YO)', 'post_status'=>'publish']);
    update_post_meta($my_id, '_dueno_telefono', '+598 99 000 000'); // <--- EDITA ESTO EN WP DESPUÉS
    $duenos_ids[] = $my_id;

    $nombres = ['Carlos Rodriguez', 'Ana Silva', 'Felipe Massa', 'Maria Gonzalez'];
    foreach($nombres as $nom) {
        $id = wp_insert_post(['post_type'=>'dueno', 'post_title'=>$nom, 'post_status'=>'publish']);
        update_post_meta($id, '_dueno_telefono', '+598 99 ' . rand(100000, 999999));
        $duenos_ids[] = $id;
    }

    // 2. Crear Propiedades
    $props = [
        [
            'titulo' => 'Apartamento Moderno en Pocitos',
            'desc'   => 'Hermoso apto cerca de la rambla.',
            'meta'   => [
                '_inmo_ubicacion_ref' => 'Pocitos, cerca de Rambla',
                '_inmo_dormitorios' => 2, '_inmo_banos' => 1, '_inmo_sup_total' => 65,
                '_inmo_alquiler_activo' => 'Si', '_inmo_alquiler_precio' => 35000, '_inmo_gastos_comunes' => 5000,
                '_inmo_cond_mascota' => 'Si', '_inmo_garantias' => ['Anda', 'Porto']
            ]
        ],
        [
            'titulo' => 'Casa Amplia en Carrasco',
            'desc'   => 'Gran jardín y piscina.',
            'meta'   => [
                '_inmo_ubicacion_ref' => 'Carrasco Norte',
                '_inmo_dormitorios' => 4, '_inmo_banos' => 3, '_inmo_sup_total' => 400,
                '_inmo_venta_activa' => 'Si', '_inmo_venta_precio' => 450000, 
                '_inmo_cochera_tipo' => '2 Autos', '_inmo_parrillero_tipo' => 'Barbacoa'
            ]
        ],
        [
            'titulo' => 'Monoambiente Centro',
            'desc'   => 'Ideal oficina o estudiante.',
            'meta'   => [
                '_inmo_ubicacion_ref' => 'Centro, 18 de Julio',
                '_inmo_dormitorios' => 0, '_inmo_banos' => 1, '_inmo_sup_total' => 30,
                '_inmo_alquiler_activo' => 'Si', '_inmo_alquiler_precio' => 18000,
                '_inmo_apto_profesional' => 'Si'
            ]
        ],
        [
            'titulo' => 'Penthouse con Vista',
            'desc'   => 'Vista despejada a toda la ciudad.',
            'meta'   => [
                '_inmo_ubicacion_ref' => 'Buceo',
                '_inmo_dormitorios' => 3, '_inmo_banos' => 2, '_inmo_sup_total' => 120,
                '_inmo_venta_activa' => 'Si', '_inmo_venta_precio' => 280000,
                '_inmo_terraza_tipo' => 'Terraza con Parrillero'
            ]
        ],
        [
            'titulo' => 'Local Comercial Esquina',
            'desc'   => 'Gran visibilidad.',
            'meta'   => [
                '_inmo_ubicacion_ref' => 'Cordón Soho',
                '_inmo_sup_total' => 80,
                '_inmo_alquiler_activo' => 'Si', '_inmo_alquiler_precio' => 45000,
                '_inmo_garantias' => ['Porto', 'Sura']
            ]
        ]
    ];

    foreach($props as $k => $p) {
        $pid = wp_insert_post(['post_type'=>'propiedad', 'post_title'=>$p['titulo'], 'post_content'=>$p['desc'], 'post_status'=>'publish']);
        // Asignar dueño rotativo
        update_post_meta($pid, '_inmo_dueno_id', $duenos_ids[$k]); 
        
        foreach($p['meta'] as $key => $val) {
            update_post_meta($pid, $key, $val);
        }
    }
}