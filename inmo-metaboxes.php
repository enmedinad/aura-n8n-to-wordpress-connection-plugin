<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_Metaboxes {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_custom_boxes' ) );
        add_action( 'save_post', array( $this, 'save_custom_data' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'tiny_mce_before_init', array( $this, 'custom_editor_placeholder' ) );
    }

    // --- 1. CONFIGURACIÓN Y SCRIPTS (NUEVO JS DE GALERÍA) ---
    
    public function custom_editor_placeholder( $settings ) {
        $settings['placeholder'] = 'Esta es la Descripción que leerá la IA y los clientes. Detallar Solo lo Clave.';
        return $settings;
    }

    public function enqueue_scripts( $hook ) {
        global $post;

        // Solo cargar en edición de post/nuevo post
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) return;
        
        // Solo para CPT 'propiedad'
        if ( ! $post || 'propiedad' !== $post->post_type ) return;

        // Cargar Media Uploader de WP
        wp_enqueue_media();
        
        ?>
        <style>
            /* Estilos Generales */
            .inmo-zone-title { background: #2271b1; color: white; padding: 10px; margin: 20px 0 10px 0; border-radius: 4px; font-size: 1.1em; }
            .inmo-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
            .inmo-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .inmo-field { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
            .inmo-field label { font-weight: bold; display: block; margin-bottom: 5px; color: #444; font-size: 0.9em; }
            .inmo-field select, .inmo-field input[type="text"], .inmo-field input[type="number"], .inmo-field input[type="date"] { width: 100%; }
            .inmo-checks label { display: block; margin-bottom: 4px; font-weight: normal; font-size: 0.9em; }
            .dueno-status-alert { background: #fff8e5; border: 1px solid #f0c33c; padding: 10px; border-radius: 5px; margin-top: 10px; }

            /* ESTILOS NUEVOS DE GALERÍA */
            .inmo-gallery-wrapper { margin-top: 10px; }
            .inmo-gallery-list { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; margin-bottom: 10px; }
            .inmo-gallery-item { position: relative; border: 1px solid #ccc; background: #fff; padding: 2px; }
            .inmo-gallery-item img { width: 100%; height: auto; display: block; }
            .inmo-gallery-remove { 
                position: absolute; top: -5px; right: -5px; 
                background: red; color: white; border-radius: 50%; 
                width: 18px; height: 18px; text-align: center; line-height: 18px; 
                cursor: pointer; font-size: 12px; font-weight: bold; border: 1px solid #fff;
            }
            .inmo-gallery-remove:hover { background: darkred; }
            .inmo-gallery-counter { font-size: 0.9em; color: #666; margin-top: 5px; text-align: right; }
        </style>

        <script type="text/javascript">
        jQuery(document).ready(function($){
            
            // --- LÓGICA GALERÍA ---
            var frame;
            var maxImages = 15;
            var $container = $('.inmo-gallery-list');
            var $input = $('#_inmo_galeria');
            var $counter = $('.inmo-count');

            // 1. ABRIR MEDIOS
            $('.inmo-add-gallery').on('click', function(e){
                e.preventDefault();

                // Si ya existe el frame, ábrelo
                if ( frame ) { frame.open(); return; }

                // Crear frame
                frame = wp.media({
                    title: 'Seleccionar Imágenes (Máx 15)',
                    button: { text: 'Añadir a la Galería' },
                    multiple: true // Permite seleccionar varias
                });

                // Al seleccionar
                frame.on('select', function() {
                    var selection = frame.state().get('selection');
                    var currentIDs = $input.val() ? $input.val().split(',') : [];
                    
                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();
                        // Evitar duplicados y chequear máximo
                        if ( currentIDs.length < maxImages && !currentIDs.includes(attachment.id.toString()) ) {
                            currentIDs.push(attachment.id);
                            // Agregar visualmente
                            var thumb = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                            $container.append('<div class="inmo-gallery-item" data-id="'+attachment.id+'"><img src="'+thumb+'"><div class="inmo-gallery-remove">X</div></div>');
                        }
                    });

                    // Actualizar Input
                    $input.val(currentIDs.join(','));
                    updateCounter(currentIDs.length);
                });

                frame.open();
            });

            // 2. BORRAR IMAGEN INDIVIDUAL
            $container.on('click', '.inmo-gallery-remove', function() {
                var $item = $(this).parent();
                var idToRemove = $item.data('id').toString();
                var currentIDs = $input.val().split(',');

                // Filtrar array
                var newIDs = currentIDs.filter(function(id) { return id !== idToRemove; });

                $input.val(newIDs.join(','));
                $item.remove();
                updateCounter(newIDs.length);
            });

            function updateCounter(num) {
                $('.inmo-count').text(num);
                if(num >= maxImages) { $('.inmo-add-gallery').prop('disabled', true).text('Límite Alcanzado'); }
                else { $('.inmo-add-gallery').prop('disabled', false).text('📂 Añadir Imágenes'); }
            }
            
            // 3. REFRESCAR DUEÑOS
            $('#btn-refresh-duenos').on('click', function(e){
                e.preventDefault();
                if(confirm('¿Guardar cambios antes de recargar?')) $('#publish').click();
            });
        });
        </script>
        <?php
    }

    // --- 2. REGISTRO DE CAJAS ---

    public function add_custom_boxes() {
        add_meta_box( 'inmo_main_data', '🏢 Gestión Integral de la Propiedad', array( $this, 'render_main_data' ), 'propiedad', 'normal', 'high' );
        add_meta_box( 'inmo_leads_list', '👥 Interesados', array( $this, 'render_leads_list' ), 'propiedad', 'normal', 'low' );
        
        // SIDEBAR: Galería (NUEVA)
        add_meta_box( 'inmo_gallery_side', '📷 Galería (Máx 15)', array( $this, 'render_gallery_side' ), 'propiedad', 'side', 'low' );
        
        add_meta_box( 'inmo_dueno_data', '👤 Ficha del Dueño', array( $this, 'render_dueno_data' ), 'dueno', 'normal', 'high' );
        add_meta_box( 'inmo_cliente_data', 'Datos Lead', array( $this, 'render_cliente_data' ), 'cliente', 'side', 'high' );
    }

    // --- 3. RENDER GALERÍA LATERAL (NUEVO HTML) ---

    public function render_gallery_side( $post ) {
        $ids_str = get_post_meta( $post->ID, '_inmo_galeria', true );
        $ids_arr = $ids_str ? explode(',', $ids_str) : [];
        $count = count($ids_arr);
        ?>
        <div class="inmo-gallery-wrapper">
            <div class="inmo-gallery-list">
                <?php 
                if ( ! empty( $ids_arr ) ) {
                    foreach ( $ids_arr as $img_id ) {
                        $url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
                        if ( $url ) {
                            echo '<div class="inmo-gallery-item" data-id="'.$img_id.'">';
                            echo '<img src="'.esc_url($url).'">';
                            echo '<div class="inmo-gallery-remove">X</div>';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            
            <input type="hidden" id="_inmo_galeria" name="_inmo_galeria" value="<?php echo esc_attr($ids_str); ?>">
            
            <button type="button" class="button button-secondary inmo-add-gallery" style="width:100%;">📂 Añadir Imágenes</button>
            <div class="inmo-gallery-counter">Imágenes: <span class="inmo-count"><?php echo $count; ?></span>/15</div>
        </div>
        <p class="description">Usa la <strong>Imagen de Portada</strong> (arriba) como principal. Aquí sube el resto.</p>
        <?php
    }

    // --- 4. RENDER PRINCIPAL (SIN CAMBIOS, PERO INCLUIDO PARA INTEGRIDAD) ---
    // Mantenemos tu estructura de zonas (Ubicación, Gastos, Comodidades...)
    
    public function render_main_data( $post ) {
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        $dueno_id = get_post_meta( $post->ID, '_inmo_dueno_id', true );
        $duenos = get_posts( array( 'post_type' => 'dueno', 'numberposts' => -1, 'post_status' => 'any' ) );
        $nota = get_post_meta( $post->ID, '_inmo_nota_interna', true );
        ?>
        <div style="background: #f0f6fc; padding: 15px; border: 1px solid #c5d9ed; margin-bottom: 10px;">
            <div class="inmo-grid-2">
                <div>
                    <label style="font-size: 1.1em; font-weight: bold;">👤 Dueño:</label>
                    <div style="display: flex; gap: 10px; margin-top: 5px;">
                        <select name="_inmo_dueno_id" class="widefat">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ( $duenos as $d ) : ?>
                                <option value="<?php echo $d->ID; ?>" <?php selected( $dueno_id, $d->ID ); ?>><?php echo esc_html( $d->post_title ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <a href="<?php echo admin_url('post-new.php?post_type=dueno'); ?>" target="_blank" class="button">➕</a>
                        <button type="button" id="btn-refresh-duenos" class="button">🔄</button>
                    </div>
                </div>
                <div>
                    <label style="font-size: 1.1em; font-weight: bold;">📝 Nota Admin:</label>
                    <textarea name="_inmo_nota_interna" rows="2" class="widefat" placeholder="Datos privados..."><?php echo esc_textarea($nota); ?></textarea>
                </div>
            </div>
        </div>
        <h3 class="inmo-zone-title">📍 ZONA: Ubicación</h3>
        <div class="inmo-grid-2">
            <div class="inmo-field inmo-alert-admin"><label>🔒 Real (Admin):</label><input type="text" name="_inmo_ubicacion_real" value="<?php echo esc_attr(get_post_meta($post->ID, '_inmo_ubicacion_real', true)); ?>"></div>
            <div class="inmo-field inmo-alert-public"><label>🌍 Referencia (Pública):</label><input type="text" name="_inmo_ubicacion_ref" value="<?php echo esc_attr(get_post_meta($post->ID, '_inmo_ubicacion_ref', true)); ?>"></div>
        </div>
        <h3 class="inmo-zone-title">🧾 ZONA: Gastos</h3>
        <div class="inmo-grid-3"><?php $this->render_input($post, 'Gastos ($)', '_inmo_gastos_comunes'); $this->render_bool($post, 'Gastos Incluidos', '_inmo_gastos_incluidos'); ?></div>
        <h3 class="inmo-zone-title">🛋️ ZONA 1: Comodidades</h3>
        <div class="inmo-grid-3">
            <?php 
            $this->render_input($post, 'Dormitorios', '_inmo_dormitorios', 'number'); $this->render_input($post, 'Baños', '_inmo_banos', 'number');
            $this->render_input($post, 'Suites', '_inmo_suites', 'number'); $this->render_bool($post, 'Toilettes', '_inmo_toilettes');
            $this->render_bool($post, 'Living', '_inmo_living'); $this->render_bool($post, 'Comedor', '_inmo_comedor'); $this->render_bool($post, 'Living Comedor', '_inmo_living_comedor');
            $this->render_select($post, 'Cocina', '_inmo_cocina_tipo', ['Americana','Amplia','Bien Equipada','Con Barra','Con Desayunador','Con Lavadero','Definida','Integrada','Kitchinette','Semi-Integrada']);
            $this->render_input($post, 'Capacidad', '_inmo_capacidad', 'number'); $this->render_input($post, 'Camas', '_inmo_camas', 'number');
            $this->render_bool($post, 'Dep. Servicio', '_inmo_dep_servicio'); $this->render_bool($post, 'Baño Servicio', '_inmo_bano_servicio');
            $this->render_bool($post, 'Amoblada', '_inmo_amoblada'); $this->render_bool($post, 'Lavadero', '_inmo_lavadero');
            $this->render_select($post, 'Calefacción', '_inmo_calefaccion_tipo', ['A Gas','Electrica','Estufa a Leña','Losa Radiante','No','Si']);
            $this->render_select($post, 'Aire Acond.', '_inmo_aire_tipo', ['Si','No','En Dormitorio','Splits']);
            $this->render_select($post, 'Parrillero', '_inmo_parrillero_tipo', ['No','Si','Barbacoa','Techado','Medio Tanque']);
            $this->render_bool($post, 'Movilidad Reducida', '_inmo_movilidad');
            ?>
        </div>
        <h3 class="inmo-zone-title">📐 ZONA 2: Características</h3>
        <div class="inmo-grid-3">
            <?php 
            $this->render_input($post, 'Sup. Propia', '_inmo_sup_propia', 'number'); $this->render_input($post, 'Sup. Total', '_inmo_sup_total', 'number');
            $this->render_input($post, 'Sup. Cubierta', '_inmo_sup_cubierta', 'number'); $this->render_input($post, 'Sup. Balcón', '_inmo_sup_balcon', 'number');
            $this->render_select($post, 'Estado', '_inmo_estado', ['A Estrenar','Impecable','Muy Bueno','Bueno','Regular','Reciclado','Refaccionar','En pozo']);
            $this->render_bool($post, 'Apto Profesional', '_inmo_apto_profesional'); $this->render_bool($post, 'Baulera', '_inmo_baulera');
            $this->render_select($post, 'Terraza/Balcón', '_inmo_terraza_tipo', ['No','Si','Balcón','Terraza','Patio','Azotea']);
            $this->render_select($post, 'Cochera', '_inmo_cochera_tipo', ['No','Si','Fija','Techada','Abierta']);
            $this->render_select($post, 'Garage', '_inmo_garage_tipo', ['No','Si','Fija','Subsuelo']);
            $this->render_select($post, 'Estacionamiento', '_inmo_estacionamiento_tipo', ['No','Si','Vigilado']);
            ?>
        </div>
        <div class="inmo-grid-2">
            <div>
                <h3 class="inmo-zone-title">💰 VENTA</h3>
                <div class="inmo-field">
                    <?php $this->render_bool($post, 'En Venta', '_inmo_venta_activa'); echo '<hr>';
                    $this->render_input($post, 'Precio Venta', '_inmo_venta_precio'); $this->render_input($post, 'Precio Tasación', '_inmo_venta_tasacion');
                    $this->render_input($post, 'Saldo Banco', '_inmo_venta_saldo_banco'); $this->render_bool($post, 'Permuta', '_inmo_venta_permuta');
                    $this->render_bool($post, 'Oferta', '_inmo_venta_oferta'); $this->render_bool($post, 'Financia', '_inmo_venta_financia');
                    $this->render_bool($post, 'Habilitada Banco', '_inmo_venta_banco');
                    echo '<br><strong>Docs:</strong><br>'; foreach(['Títulos','Planos','Catastro','BPS'] as $d) echo '<label><input type="checkbox" name="_inmo_documentacion[]" value="'.$d.'" '.checked(in_array($d,get_post_meta($post->ID,'_inmo_documentacion',true)?:[]),true,false).'> '.$d.'</label>';
                    ?>
                </div>
            </div>
            <div>
                <h3 class="inmo-zone-title">🔑 ALQUILER</h3>
                <div class="inmo-field">
                    <?php $this->render_bool($post, 'En Alquiler', '_inmo_alquiler_activo'); echo '<hr>';
                    $this->render_input($post, 'Precio', '_inmo_alquiler_precio'); $this->render_select($post, 'Moneda', '_inmo_alquiler_moneda', ['$','USD']);
                    $this->render_bool($post, 'Muebles', '_inmo_muebles_incluidos');
                    $this->render_select($post, 'Mascotas', '_inmo_cond_mascota', ['Si','No','Pequeñas','Consultar']);
                    $this->render_bool($post, 'Niños', '_inmo_cond_ninos');
                    echo '<br><strong>Garantías:</strong><br>';
                    echo '<div class="inmo-grid-2">'; foreach(['Anda','Porto','Sura','CGN','Propiedad','Deposito'] as $g) echo '<label><input type="checkbox" name="_inmo_garantias[]" value="'.$g.'" '.checked(in_array($g,get_post_meta($post->ID,'_inmo_garantias',true)?:[]),true,false).'> '.$g.'</label>'; echo '</div>';
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    // --- HELPERS Y OTROS METABOXES (Dueño, Leads, Save) ---

    private function render_input($post, $label, $id, $type='text') { echo '<div class="inmo-field"><label>'.$label.':</label><input type="'.$type.'" name="'.$id.'" value="'.esc_attr(get_post_meta($post->ID,$id,true)).'"></div>'; }
    private function render_select($post, $label, $id, $opts) { $val = get_post_meta($post->ID,$id,true); echo '<div class="inmo-field"><label>'.$label.':</label><select name="'.$id.'"><option value="">--</option>'; foreach($opts as $o) echo '<option value="'.$o.'" '.selected($val,$o,false).'>'.$o.'</option>'; echo '</select></div>'; }
    private function render_bool($post, $label, $id) { $this->render_select($post,$label,$id,['Si','No']); }

    public function render_leads_list($post) { /* ... Leads List (Igual) ... */ }
    
    public function render_dueno_data($post) {
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        $telefono = get_post_meta( $post->ID, '_dueno_telefono', true ); $status = get_post_meta( $post->ID, '_dueno_status_propiedad', true );
        $check = get_post_meta( $post->ID, '_dueno_consultado', true ); $fecha = get_post_meta( $post->ID, '_dueno_fecha_check', true );
        ?>
        <div class="inmo-grid-2">
            <div><p><label>📞 Teléfono:</label><input type="text" name="_dueno_telefono" value="<?php echo esc_attr($telefono); ?>" class="widefat"></p>
            <div class="dueno-status-alert"><label>🚩 Status:</label><select name="_dueno_status_propiedad" class="widefat"><option value="">--</option><?php foreach(['Disponible','En Proceso','Reservada','Alquilada','Vendida'] as $s) echo '<option value="'.$s.'" '.selected($status,$s,false).'>'.$s.'</option>'; ?></select></div></div>
            <div><label>🤖 Check IA:</label><textarea name="_dueno_consultado" rows="2" class="widefat"><?php echo esc_textarea($check); ?></textarea><input type="date" name="_dueno_fecha_check" value="<?php echo esc_attr($fecha); ?>"></div>
        </div>
        <?php
    }
    
    public function render_cliente_data($post) { /* ... Leads Data (Igual) ... */ }

    // --- GUARDAR ---
    public function save_custom_data( $post_id ) {
        if ( ! isset( $_POST['inmo_nonce'] ) || ! wp_verify_nonce( $_POST['inmo_nonce'], 'inmo_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['post_type'] ) ) return;

        if ( 'propiedad' === $_POST['post_type'] ) {
            $fields = ['_inmo_dueno_id','_inmo_nota_interna','_inmo_galeria','_inmo_ubicacion_real','_inmo_ubicacion_ref','_inmo_gastos_comunes','_inmo_gastos_incluidos',
            '_inmo_dormitorios','_inmo_banos','_inmo_suites','_inmo_toilettes','_inmo_living','_inmo_comedor','_inmo_living_comedor','_inmo_cocina_tipo','_inmo_capacidad','_inmo_camas','_inmo_dep_servicio','_inmo_bano_servicio','_inmo_amoblada','_inmo_lavadero','_inmo_calefaccion_tipo','_inmo_aire_tipo','_inmo_parrillero_tipo','_inmo_movilidad',
            '_inmo_sup_propia','_inmo_sup_total','_inmo_sup_cubierta','_inmo_sup_semi','_inmo_sup_balcon','_inmo_estado','_inmo_apto_profesional','_inmo_baulera','_inmo_terraza_tipo','_inmo_cochera_tipo','_inmo_garage_tipo','_inmo_estacionamiento_tipo',
            '_inmo_venta_activa','_inmo_venta_precio','_inmo_venta_libre','_inmo_venta_tasacion','_inmo_venta_saldo_banco','_inmo_venta_vigencia','_inmo_venta_permuta','_inmo_venta_oferta','_inmo_venta_financia','_inmo_venta_renta','_inmo_venta_renta_valor','_inmo_venta_banco',
            '_inmo_alquiler_activo','_inmo_alquiler_precio','_inmo_alquiler_moneda','_inmo_alquiler_periodo','_inmo_muebles_incluidos','_inmo_cond_mascota','_inmo_cond_fumador','_inmo_cond_ninos'];
            foreach($fields as $f) if(isset($_POST[$f])) update_post_meta($post_id, $f, sanitize_text_field($_POST[$f]));
            $checks = ['_inmo_garantias', '_inmo_documentacion'];
            foreach($checks as $c) { $val = ( isset($_POST[$c]) && is_array($_POST[$c]) ) ? $_POST[$c] : array(); update_post_meta($post_id, $c, $val); }
        }
        if ( 'dueno' === $_POST['post_type'] ) {
            foreach(['_dueno_telefono','_dueno_status_propiedad','_dueno_consultado','_dueno_fecha_check'] as $f) if(isset($_POST[$f])) update_post_meta($post_id, $f, sanitize_text_field($_POST[$f]));
        }
        if ( 'cliente' === $_POST['post_type'] ) {
            foreach(['_cliente_propiedad_id','_cliente_status_proceso','_cliente_telefono'] as $f) if(isset($_POST[$f])) update_post_meta($post_id, $f, sanitize_text_field($_POST[$f]));
        }
    }
}
new Inmo_Metaboxes();