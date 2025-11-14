<?php
/*
Plugin Part: Custom Post Type Property.
Description: Define el Custom Post Type (CPT) para Propiedades, sus taxonomías asociadas (Amenities, Servicios, Muebles) y los metadatos relevantes para cada propiedad.
Author: Enzo Medina.
Version: 0.0.2a
License: Private Use Only
*/

// Prevent Direct Access
if (!defined('ABSPATH')) { exit; }

/**
 * Registrar CPT Propiedades y sus taxonomías
 */
add_action('init', function() {
    register_post_type('property', [
        'label' => 'Propiedades',
        'public' => true,
        'show_in_rest' => true, // expone en REST API para n8n
        'supports' => ['title','editor','thumbnail','custom-fields'],
        'has_archive' => true,
        'menu_icon' => 'dashicons-building',
    ]);

    // Taxonomías para clasificar propiedades
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

/**
 * Registrar metacampos expuestos en REST
 */
add_action('init', function() {
    $text = ['show_in_rest'=>true,'single'=>true,'type'=>'string','auth_callback'=>'__return_true'];
    $num  = ['show_in_rest'=>true,'single'=>true,'type'=>'number','auth_callback'=>'__return_true'];
    $bool = ['show_in_rest'=>true,'single'=>true,'type'=>'boolean','auth_callback'=>'__return_true'];

    register_post_meta('property','property_type',$text);
    register_post_meta('property','property_subtype',$text);
    register_post_meta('property','price_sale',$num);
    register_post_meta('property','price_rent',$num);
    register_post_meta('property','common_expenses',$num);
    register_post_meta('property','is_for_rent',$bool);
    register_post_meta('property','guarantees',$text);
    register_post_meta('property','bedrooms',$num);
    register_post_meta('property','bathrooms',$num);
    register_post_meta('property','area_m2',$num);
    register_post_meta('property','land_area_m2',$num);
    register_post_meta('property','orientation',$text);
    register_post_meta('property','distribution',$text);
    register_post_meta('property','view',$text);
    register_post_meta('property','map_location',$text);
    register_post_meta('property','owner_id',['show_in_rest'=>true,'single'=>true,'type'=>'integer','auth_callback'=>'__return_true']);
    register_post_meta('property','property_gallery',$text);
    register_post_meta('property','admin_note',[
        'show_in_rest'=>true,'single'=>true,'type'=>'string',
        'auth_callback'=>function(){ return current_user_can('manage_options'); }
    ]);
    register_post_meta('property','availability_status',$text);
});

/**
 * Metaboxes personalizados en el editor clásico
 */
add_action('add_meta_boxes', function() {
    add_meta_box('property_details','Detalles de la Propiedad','re_property_details_metabox','property','normal','high');
    add_meta_box('property_gallery','Galería de Imágenes','re_property_gallery_metabox','property','normal','default');
    add_meta_box('property_admin_note','Nota Interna (solo admin)','re_property_admin_note_metabox','property','side','default');
});

/**
 * Renderizar metabox principal
 */
function re_property_details_metabox($post) {
    // Recuperar valores
    $fields = [
        'property_type','property_subtype','price_sale','price_rent','common_expenses',
        'is_for_rent','guarantees','bedrooms','bathrooms','area_m2','land_area_m2',
        'orientation','distribution','view','map_location','owner_id'
    ];
    $values = [];
    foreach ($fields as $f) { $values[$f] = get_post_meta($post->ID,$f,true); }

    ?>
    <table class="form-table">
        <tr><th>Tipo de Propiedad</th>
            <td>
                <select name="property_type">
                    <option value="Casa" <?php selected($values['property_type'],'Casa'); ?>>Casa</option>
                    <option value="Apartamento" <?php selected($values['property_type'],'Apartamento'); ?>>Apartamento</option>
                    <option value="Terreno" <?php selected($values['property_type'],'Terreno'); ?>>Terreno</option>
                </select>
            </td>
        </tr>
        <tr><th>Subtipo</th><td><input type="text" name="property_subtype" value="<?php echo esc_attr($values['property_subtype']); ?>"></td></tr>
        <tr><th>Precio Venta</th><td><input type="number" name="price_sale" value="<?php echo esc_attr($values['price_sale']); ?>"></td></tr>
        <tr><th>Precio Alquiler</th><td><input type="number" name="price_rent" value="<?php echo esc_attr($values['price_rent']); ?>"></td></tr>
        <tr><th>Gastos Comunes</th><td><input type="number" name="common_expenses" value="<?php echo esc_attr($values['common_expenses']); ?>"></td></tr>
        <tr><th>¿Es alquiler?</th><td><input type="checkbox" name="is_for_rent" value="1" <?php checked($values['is_for_rent'],1); ?>></td></tr>
        <tr><th>Garantías</th><td><input type="text" name="guarantees" value="<?php echo esc_attr($values['guarantees']); ?>"></td></tr>
        <tr><th>Dormitorios</th><td><input type="number" name="bedrooms" value="<?php echo esc_attr($values['bedrooms']); ?>"></td></tr>
        <tr><th>Baños</th><td><input type="number" name="bathrooms" value="<?php echo esc_attr($values['bathrooms']); ?>"></td></tr>
        <tr><th>Área edificada (m²)</th><td><input type="number" name="area_m2" value="<?php echo esc_attr($values['area_m2']); ?>"></td></tr>
        <tr><th>Área terreno (m²)</th><td><input type="number" name="land_area_m2" value="<?php echo esc_attr($values['land_area_m2']); ?>"></td></tr>
        <tr><th>Orientación</th><td><input type="text" name="orientation" value="<?php echo esc_attr($values['orientation']); ?>"></td></tr>
        <tr><th>Distribución</th><td><input type="text" name="distribution" value="<?php echo esc_attr($values['distribution']); ?>"></td></tr>
        <tr><th>Vista</th><td><input type="text" name="view" value="<?php echo esc_attr($values['view']); ?>"></td></tr>
        <tr><th>Ubicación (Google Maps)</th><td><input type="text" name="map_location" value="<?php echo esc_attr($values['map_location']); ?>"></td></tr>
        <tr><th>ID Dueño</th><td><input type="number" name="owner_id" value="<?php echo esc_attr($values['owner_id']); ?>"></td></tr>
    </table>
    <?php
}

/**
 * Renderizar metabox de galería
 */
function re_property_gallery_metabox($post) {
    $gallery = get_post_meta($post->ID,'property_gallery',true);
    echo '<textarea name="property_gallery" rows="4" style="width:100%;">'.esc_textarea($gallery).'</textarea>';
    echo '<p class="description">IDs de imágenes separadas por comas (subidas a la librería de medios).</p>';
}

/**
 * Renderizar metabox de nota interna
 */
function re_property_admin_note_metabox($post) {
    $note = get_post_meta($post->ID,'admin_note',true);
    echo '<textarea name="admin_note" rows="4" style="width:100%;">'.esc_textarea($note).'</textarea>';
    echo '<p class="description">Nota interna visible solo para administradores.</p>';
}

/**
 * Guardar metacampos al guardar el post Property
 */
add_action('save_post_property', function($post_id) {
    // Evitar autosaves o falta de permisos
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Lista de campos que esperamos
    $fields = [
        'property_type','property_subtype','price_sale','price_rent','common_expenses',
        'is_for_rent','guarantees','bedrooms','bathrooms','area_m2','land_area_m2',
        'orientation','distribution','view','map_location','owner_id',
        'property_gallery','admin_note'
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = $_POST[$field];

            // Sanitización según tipo
            if (in_array($field, ['price_sale','price_rent','common_expenses','bedrooms','bathrooms','area_m2','land_area_m2','owner_id'])) {
                $value = intval($value);
            } elseif ($field === 'is_for_rent') {
                $value = $_POST[$field] ? 1 : 0;
            } else {
                $value = sanitize_text_field($value);
            }

            update_post_meta($post_id, $field, $value);
        } else {
            // Si el campo no está en POST (ej. checkbox desmarcado), lo limpiamos
            if ($field === 'is_for_rent') {
                update_post_meta($post_id, $field, 0);
            }
        }
    }
});