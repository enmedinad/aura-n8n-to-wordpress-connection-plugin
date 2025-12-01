<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_Metaboxes {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_custom_boxes' ) );
        add_action( 'save_post', array( $this, 'save_custom_data' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'tiny_mce_before_init', array( $this, 'custom_editor_placeholder' ) );
    }

    // --- CONFIGURACIÓN Y SCRIPTS (Igual que antes) ---
    public function custom_editor_placeholder( $settings ) {
        $settings['placeholder'] = 'Esta es la Descripción que leerá la IA y los clientes. Detallar Solo lo Clave.';
        return $settings;
    }

    public function enqueue_scripts() {
        global $post;
        if ( ! $post || 'propiedad' !== $post->post_type ) return;
        wp_enqueue_media();
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.inmo-upload-gallery').click(function(e) {
                e.preventDefault();
                var custom_uploader = wp.media({
                    title: 'Seleccionar Imágenes', button: { text: 'Usar' }, multiple: true
                }).on('select', function() {
                    var ids = [];
                    custom_uploader.state().get('selection').map( function( attachment ) {
                        ids.push(attachment.toJSON().id);
                    });
                    $('#_inmo_galeria').val(ids.join(','));
                    $('.inmo-gallery-preview').text('✅ ' + ids.length + ' imágenes seleccionadas');
                }).open();
            });
            $('#btn-refresh-duenos').click(function(e){
                e.preventDefault();
                if(confirm('Se recargará la página. Asegúrate de guardar cambios antes.')) location.reload();
            });
        });
        </script>
        <style>
            .inmo-zone-title { background: #2271b1; color: white; padding: 10px; margin: 20px 0 10px 0; border-radius: 4px; font-size: 1.1em; }
            .inmo-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
            .inmo-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .inmo-field { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
            .inmo-field label { font-weight: bold; display: block; margin-bottom: 5px; color: #444; font-size: 0.9em; }
            .inmo-field select, .inmo-field input[type="text"], .inmo-field input[type="number"], .inmo-field input[type="date"] { width: 100%; }
            .inmo-checks label { display: block; margin-bottom: 4px; font-weight: normal; font-size: 0.9em; }
            .inmo-alert-admin { border-left: 4px solid #d63638; padding-left: 10px; background: #fff5f5; }
            .inmo-alert-public { border-left: 4px solid #00a32a; padding-left: 10px; background: #f0fcf4; }
        </style>
        <?php
    }

    public function add_custom_boxes() {
        add_meta_box( 'inmo_main_data', '🏢 Gestión Integral de la Propiedad', array( $this, 'render_main_data' ), 'propiedad', 'normal', 'high' );
        add_meta_box( 'inmo_gallery_data', '📷 Galería y Notas', array( $this, 'render_gallery_data' ), 'propiedad', 'normal', 'default' );
        add_meta_box( 'inmo_leads_list', '👥 Interesados', array( $this, 'render_leads_list' ), 'propiedad', 'normal', 'low' );
        add_meta_box( 'inmo_dueno_data', 'Datos Dueño', array( $this, 'render_dueno_data' ), 'dueno', 'normal', 'high' );
        add_meta_box( 'inmo_cliente_data', 'Datos Lead', array( $this, 'render_cliente_data' ), 'cliente', 'side', 'high' );
    }

    public function render_main_data( $post ) {
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        $dueno_id = get_post_meta( $post->ID, '_inmo_dueno_id', true );
        $duenos = get_posts( array( 'post_type' => 'dueno', 'numberposts' => -1, 'post_status' => 'any' ) );
        ?>
        
        <div style="background: #f0f6fc; padding: 15px; border: 1px solid #c5d9ed; margin-bottom: 10px;">
            <label style="font-size: 1.2em; font-weight: bold;">👤 Dueño de la Propiedad</label>
            <div style="display: flex; gap: 10px; margin-top: 5px;">
                <select name="_inmo_dueno_id" class="widefat" style="max-width: 400px;">
                    <option value="">-- Seleccionar Dueño --</option>
                    <?php foreach ( $duenos as $d ) : ?>
                        <option value="<?php echo $d->ID; ?>" <?php selected( $dueno_id, $d->ID ); ?>><?php echo esc_html( $d->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="<?php echo admin_url('post-new.php?post_type=dueno'); ?>" target="_blank" class="button button-primary">➕ Nuevo Dueño</a>
                <button type="button" id="btn-refresh-duenos" class="button button-secondary">🔄 Refrescar</button>
            </div>
        </div>

        <h3 class="inmo-zone-title">📍 ZONA: Ubicación</h3>
        <div class="inmo-grid-2">
            <div class="inmo-field inmo-alert-admin">
                <label>🔒 Ubicación Real (Solo Admin/Interno):</label>
                <input type="text" name="_inmo_ubicacion_real" value="<?php echo esc_attr(get_post_meta($post->ID, '_inmo_ubicacion_real', true)); ?>" placeholder="Ej: Av. Italia 4532 Apto 401">
                <p class="description">Dirección exacta. NUNCA se comparte públicamente.</p>
            </div>
            <div class="inmo-field inmo-alert-public">
                <label>🌍 Ubicación Referenciada (Para Clientes):</label>
                <input type="text" name="_inmo_ubicacion_ref" value="<?php echo esc_attr(get_post_meta($post->ID, '_inmo_ubicacion_ref', true)); ?>" placeholder="Ej: Malvín, cerca de Av. Italia y Comercio">
                <p class="description">Zona aproximada para mostrar a la IA y clientes.</p>
            </div>
        </div>

        <h3 class="inmo-zone-title">🧾 ZONA: Gastos y Mantenimiento</h3>
        <div class="inmo-grid-3">
             <?php 
             $this->render_input($post, 'Gastos Comunes ($)', '_inmo_gastos_comunes');
             $this->render_bool($post, 'Gastos Incluidos en Precio', '_inmo_gastos_incluidos');
             ?>
             <div class="inmo-field"><p class="description">Aplica tanto para Alquiler como para Venta (información de mantenimiento).</p></div>
        </div>

        <h3 class="inmo-zone-title">🛋️ ZONA 1: Comodidades</h3>
        <div class="inmo-grid-3">
            <?php 
            $this->render_input($post, 'Dormitorios', '_inmo_dormitorios', 'number');
            $this->render_input($post, 'Baños', '_inmo_banos', 'number');
            $this->render_input($post, 'Suites', '_inmo_suites', 'number');
            $this->render_bool($post, 'Toilettes', '_inmo_toilettes');
            $this->render_bool($post, 'Living', '_inmo_living');
            $this->render_bool($post, 'Comedor', '_inmo_comedor');
            $this->render_bool($post, 'Living Comedor', '_inmo_living_comedor');
            $this->render_select($post, 'Tipo de Cocina', '_inmo_cocina_tipo', ['Americana','Amplia','Bien Equipada','Con Barra','Con Desayunador','Con Lavadero','Con Luz Natural','Con Terraza','Cocina','Cocina Comedor','Completa','Definida','Exterior','Grande','Integrada','Interna','Kitchinette','Reciclada','Semi-Integrada']);
            $this->render_input($post, 'Capacidad (Personas)', '_inmo_capacidad', 'number');
            $this->render_input($post, 'Cantidad Camas', '_inmo_camas', 'number');
            $this->render_bool($post, 'Dep. Servicio', '_inmo_dep_servicio');
            $this->render_bool($post, 'Baño Servicio', '_inmo_bano_servicio');
            $this->render_bool($post, 'Amoblada', '_inmo_amoblada');
            $this->render_bool($post, 'Lavadero', '_inmo_lavadero');
            $this->render_select($post, 'Calefacción', '_inmo_calefaccion_tipo', ['A Gas','A Gas Oil','Acumuladores de Calor','Calefaccion Central','Calefactores','Central','Electrica','Electrica con Radiadores','Estufa a Leña','Estufa Alto Rendimiento','Losa Radiante','No','Si']);
            $this->render_select($post, 'Aire Acondicionado', '_inmo_aire_tipo', ['Aire Acondicionado','Aire en Dormitorio Ppal','Aire Individual','Ventiladores','Si','No']);
            $this->render_select($post, 'Parrillero', '_inmo_parrillero_tipo', ['Barbacoa','Cerrado','Estilo Americano','Grande','No','Si','Parrillero','Pergola','Quincho','Techado']);
            $this->render_bool($post, 'Adaptada Movilidad', '_inmo_movilidad');
            ?>
        </div>

        <h3 class="inmo-zone-title">📐 ZONA 2: Características y Superficies</h3>
        <div class="inmo-grid-3">
            <?php 
            $this->render_input($post, 'Sup. Propia (m²)', '_inmo_sup_propia', 'number');
            $this->render_input($post, 'Sup. Total (m²)', '_inmo_sup_total', 'number');
            $this->render_input($post, 'Sup. Cubierta (m²)', '_inmo_sup_cubierta', 'number');
            $this->render_input($post, 'Sup. Semi-Cubierta', '_inmo_sup_semi', 'number');
            $this->render_input($post, 'Sup. Balcón', '_inmo_sup_balcon', 'number');
            $this->render_select($post, 'Estado', '_inmo_estado', ['A Estrenar','Impecable','Excelente','Muy Bueno','Bueno','Reparaciones Sencillas','De Epoca','Regular','Reciclado','Refaccionar','Malo','Muy Malo','En pozo','En Obras']);
            $this->render_bool($post, 'Apto Profesional', '_inmo_apto_profesional');
            $this->render_bool($post, 'Baulera', '_inmo_baulera');
            $this->render_select($post, 'Terraza / Balcón', '_inmo_terraza_tipo', ['Abierta','Abierta c/parrillero','Al Frente','Azotea','Azotea c/parrillero','Balcón','Balcón c/parrilla','Balcón Cerrado','Balcón Frances','c/Hidromasajes','c/Lavadero','Cerrada','Cerrada c/parrillero','Con Jacuzzi','Con Piscina','Con Vista','Doble','Grande','Integrada','No','Si','Patio','Sobre Techo','Solarium','Techada','Terraza','Terraza con Parrillero']);
            $this->render_select($post, 'Cochera', '_inmo_cochera_tipo', ['No','Si','Fija','No Fija','Techada','Abierta','2 Autos','3 Autos','4 Autos','5 Autos','Hasta 10 Autos','Individual']);
            $this->render_select($post, 'Garage', '_inmo_garage_tipo', ['No','Si','No Fija','Opcional','En Subsuelo','2 Autos','3 Autos','4 Autos','5 Autos','Hasta 10 Autos','Con Baño']);
            $this->render_select($post, 'Estacionamiento', '_inmo_estacionamiento_tipo', ['Estacionamiento','Exclusivo','Si','No','Fijo','Vigilado']);
            ?>
        </div>

        <div class="inmo-grid-2">
            <div>
                <h3 class="inmo-zone-title">💰 ZONA VENTA</h3>
                <div class="inmo-field">
                    <?php 
                    $this->render_bool($post, '🟢 En Venta', '_inmo_venta_activa');
                    echo '<hr>';
                    $this->render_input($post, 'Precio Venta', '_inmo_venta_precio');
                    $this->render_input($post, 'Precio Libre', '_inmo_venta_libre');
                    $this->render_input($post, 'Precio Tasación', '_inmo_venta_tasacion');
                    $this->render_input($post, 'Saldo Banco', '_inmo_venta_saldo_banco');
                    ?>
                    <div style="margin-top:10px;">
                        <label>Vigencia Venta:</label>
                        <input type="date" name="_inmo_venta_vigencia" value="<?php echo esc_attr(get_post_meta($post->ID, '_inmo_venta_vigencia', true)); ?>">
                    </div>
                    <?php
                    echo '<hr>';
                    $this->render_bool($post, 'Permuta', '_inmo_venta_permuta');
                    $this->render_bool($post, 'Oferta', '_inmo_venta_oferta');
                    $this->render_bool($post, 'Financia', '_inmo_venta_financia');
                    $this->render_bool($post, 'Tiene Renta', '_inmo_venta_renta');
                    $this->render_input($post, '% Renta', '_inmo_venta_renta_valor');
                    $this->render_bool($post, 'Habilitada Banco', '_inmo_venta_banco');
                    
                    echo '<span style="display:block;margin-top:10px;font-weight:bold;">Documentación Exigida:</span>';
                    $docs = ['Títulos','Planos','Cédula Catastral','BPS al día','Contribución al día'];
                    $actual_docs = get_post_meta($post->ID, '_inmo_documentacion', true) ?: [];
                    foreach($docs as $d) echo '<label><input type="checkbox" name="_inmo_documentacion[]" value="'.$d.'" '.checked(in_array($d, $actual_docs), true, false).'> '.$d.'</label>';
                    ?>
                </div>
            </div>

            <div>
                <h3 class="inmo-zone-title">🔑 ZONA ALQUILER / TRASPASO</h3>
                <div class="inmo-field">
                    <?php 
                    $this->render_bool($post, '🟢 En Alquiler', '_inmo_alquiler_activo');
                    echo '<hr>';
                    $this->render_input($post, 'Precio Alquiler', '_inmo_alquiler_precio');
                    $this->render_select($post, 'Moneda', '_inmo_alquiler_moneda', ['$ Uruguayo', 'USD']);
                    $this->render_select($post, 'Periodo', '_inmo_alquiler_periodo', ['Mensual', 'Anual', 'Quincenal', 'Diario']);
                    $this->render_bool($post, 'Muebles Incluidos', '_inmo_muebles_incluidos');

                    echo '<span style="display:block;margin-top:10px;font-weight:bold;">Condiciones del Dueño:</span>';
                    $this->render_select($post, 'Mascotas', '_inmo_cond_mascota', ['Si', 'No', 'Solo Pequeñas', 'Solo Medianas', 'Consultar']);
                    $this->render_bool($post, 'Acepta Fumador', '_inmo_cond_fumador');
                    $this->render_bool($post, 'Acepta Niños', '_inmo_cond_ninos');

                    echo '<span style="display:block;margin-top:10px;font-weight:bold;">Garantías Aceptadas:</span>';
                    $garantias = ['Deposito','Propiedad','Anda','Porto','CGN','MVOTMA','SURA','FIDECIU','LUC','Mapfre','Santander'];
                    $actual_gar = get_post_meta($post->ID, '_inmo_garantias', true) ?: [];
                    echo '<div class="inmo-grid-2">';
                    foreach($garantias as $g) echo '<label><input type="checkbox" name="_inmo_garantias[]" value="'.$g.'" '.checked(in_array($g, $actual_gar), true, false).'> '.$g.'</label>';
                    echo '</div>';
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    // --- HELPERS (Igual que antes) ---
    private function render_input($post, $label, $id, $type = 'text') {
        $val = get_post_meta($post->ID, $id, true);
        echo '<div class="inmo-field"><label>' . $label . ':</label><input type="'.$type.'" name="'.$id.'" value="' . esc_attr($val) . '"></div>';
    }
    private function render_select($post, $label, $id, $options) {
        $val = get_post_meta($post->ID, $id, true);
        echo '<div class="inmo-field"><label>' . $label . ':</label><select name="'.$id.'"><option value="">--</option>';
        foreach($options as $opt) echo '<option value="'.$opt.'" '.selected($val, $opt, false).'>'.$opt.'</option>';
        echo '</select></div>';
    }
    private function render_bool($post, $label, $id) {
        $val = get_post_meta($post->ID, $id, true);
        echo '<div class="inmo-field"><label>' . $label . ':</label><select name="'.$id.'"><option value="">--</option><option value="Si" '.selected($val,'Si',false).'>Si</option><option value="No" '.selected($val,'No',false).'>No</option></select></div>';
    }

    // --- RENDERIZADORES EXTRA (Galería, Leads, etc - Igual que antes) ---
    public function render_gallery_data( $post ) {
        $nota = get_post_meta( $post->ID, '_inmo_nota_interna', true );
        $ids = get_post_meta( $post->ID, '_inmo_galeria', true );
        ?>
        <p><label><strong>Nota Interna:</strong></label><textarea name="_inmo_nota_interna" rows="3" class="widefat"><?php echo esc_textarea($nota); ?></textarea></p>
        <hr><p><input type="hidden" id="_inmo_galeria" name="_inmo_galeria" value="<?php echo esc_attr($ids); ?>"><button class="button inmo-upload-gallery">📷 Subir Fotos</button><span class="inmo-gallery-preview" style="margin-left:10px;"><?php echo $ids ? '✅ ' . count(explode(',', $ids)) . ' fotos' : ''; ?></span></p>
        <?php
    }

    public function render_leads_list( $post ) {
        $leads = get_posts( array('post_type' => 'cliente', 'meta_key' => '_cliente_propiedad_id', 'meta_value' => $post->ID, 'numberposts' => -1 ));
        if(empty($leads)){echo '<p>Sin interesados.</p>';return;}
        echo '<table class="widefat striped"><thead><tr><th>Cliente</th><th>Teléfono</th><th>Status</th><th>Acción</th></tr></thead><tbody>';
        foreach($leads as $l){
            $tel = get_post_meta($l->ID,'_cliente_telefono',true); $st = get_post_meta($l->ID,'_cliente_status_proceso',true);
            echo '<tr><td>'.esc_html($l->post_title).'</td><td>'.$tel.'</td><td>'.$st.'</td><td><a href="'.get_edit_post_link($l->ID).'" class="button button-small">Ver</a></td></tr>';
        }
        echo '</tbody></table>';
    }

    public function render_dueno_data( $post ) {
        echo '<p><label>Teléfono:</label><input type="text" name="_dueno_telefono" value="'.esc_attr(get_post_meta($post->ID,'_dueno_telefono',true)).'" class="widefat"></p>';
    }

    public function render_cliente_data( $post ) {
        $pid = get_post_meta($post->ID,'_cliente_propiedad_id',true);
        $props = get_posts(array('post_type'=>'propiedad','numberposts'=>50));
        echo '<p><label>Propiedad:</label><select name="_cliente_propiedad_id" class="widefat"><option value="">--</option>';
        foreach($props as $p) echo '<option value="'.$p->ID.'" '.selected($pid,$p->ID,false).'>'.$p->post_title.'</option>';
        echo '</select></p>';
        echo '<p><label>Teléfono:</label><input type="text" name="_cliente_telefono" value="'.esc_attr(get_post_meta($post->ID,'_cliente_telefono',true)).'" class="widefat"></p>';
        echo '<p><label>Status:</label><select name="_cliente_status_proceso" class="widefat">';
        foreach(['En Proceso','En Espera','Visitando','Reservado','Realizado'] as $s) echo '<option value="'.$s.'" '.selected(get_post_meta($post->ID,'_cliente_status_proceso',true),$s,false).'>'.$s.'</option>';
        echo '</select></p>';
    }

    // --- GUARDADO ---
    public function save_custom_data( $post_id ) {
        if ( ! isset( $_POST['inmo_nonce'] ) || ! wp_verify_nonce( $_POST['inmo_nonce'], 'inmo_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['post_type'] ) ) return;

        if ( 'propiedad' === $_POST['post_type'] ) {
            $fields = [
                '_inmo_dueno_id', '_inmo_nota_interna', '_inmo_galeria',
                // Ubicacion y Gastos
                '_inmo_ubicacion_real', '_inmo_ubicacion_ref', '_inmo_gastos_comunes', '_inmo_gastos_incluidos',
                // Zona 1
                '_inmo_dormitorios', '_inmo_banos', '_inmo_suites', '_inmo_toilettes', '_inmo_living', '_inmo_comedor', '_inmo_living_comedor',
                '_inmo_cocina_tipo', '_inmo_capacidad', '_inmo_camas', '_inmo_dep_servicio', '_inmo_bano_servicio', '_inmo_amoblada',
                '_inmo_lavadero', '_inmo_calefaccion_tipo', '_inmo_aire_tipo', '_inmo_parrillero_tipo', '_inmo_movilidad',
                // Zona 2
                '_inmo_sup_propia', '_inmo_sup_total', '_inmo_sup_cubierta', '_inmo_sup_semi', '_inmo_sup_balcon',
                '_inmo_estado', '_inmo_apto_profesional', '_inmo_baulera', '_inmo_terraza_tipo', '_inmo_cochera_tipo', '_inmo_garage_tipo', '_inmo_estacionamiento_tipo',
                // Venta
                '_inmo_venta_activa', '_inmo_venta_precio', '_inmo_venta_libre', '_inmo_venta_tasacion', '_inmo_venta_saldo_banco',
                '_inmo_venta_vigencia', '_inmo_venta_permuta', '_inmo_venta_oferta', '_inmo_venta_financia', '_inmo_venta_renta', '_inmo_venta_renta_valor', '_inmo_venta_banco',
                // Alquiler
                '_inmo_alquiler_activo', '_inmo_alquiler_precio', '_inmo_alquiler_moneda', '_inmo_alquiler_periodo', 
                '_inmo_muebles_incluidos',
                '_inmo_cond_mascota', '_inmo_cond_fumador', '_inmo_cond_ninos'
            ];
            foreach($fields as $f) if(isset($_POST[$f])) update_post_meta($post_id, $f, sanitize_text_field($_POST[$f]));
            
            // Arrays
            $checks = ['_inmo_garantias', '_inmo_documentacion'];
            foreach($checks as $c) {
                $val = ( isset($_POST[$c]) && is_array($_POST[$c]) ) ? $_POST[$c] : array();
                update_post_meta($post_id, $c, $val);
            }
        }
        
        // Guardados simples de Dueño y Cliente
        if('dueno'===$_POST['post_type'] && isset($_POST['_dueno_telefono'])) update_post_meta($post_id,'_dueno_telefono',sanitize_text_field($_POST['_dueno_telefono']));
        if('cliente'===$_POST['post_type']){
            if(isset($_POST['_cliente_propiedad_id'])) update_post_meta($post_id,'_cliente_propiedad_id',sanitize_text_field($_POST['_cliente_propiedad_id']));
            if(isset($_POST['_cliente_status_proceso'])) update_post_meta($post_id,'_cliente_status_proceso',sanitize_text_field($_POST['_cliente_status_proceso']));
            if(isset($_POST['_cliente_telefono'])) update_post_meta($post_id,'_cliente_telefono',sanitize_text_field($_POST['_cliente_telefono']));
        }
    }
}
new Inmo_Metaboxes();