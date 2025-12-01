<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_Metaboxes {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_custom_boxes' ) );
        add_action( 'save_post', array( $this, 'save_custom_data' ) );
        // Scripts para subir imágenes (Galería)
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_script' ) );
    }

    public function enqueue_media_script() {
        if ( 'propiedad' === get_post_type() ) {
            wp_enqueue_media();
            // Pequeño script inline para manejar el botón de subida de galería
            wp_add_inline_script( 'media-upload', "
                jQuery(document).ready(function($){
                    $('.inmo-upload-gallery').click(function(e) {
                        e.preventDefault();
                        var button = $(this);
                        var custom_uploader = wp.media({
                            title: 'Seleccionar Imágenes para la Galería',
                            button: { text: 'Usar estas imágenes' },
                            multiple: true
                        }).on('select', function() {
                            var attachment_ids = [];
                            var selection = custom_uploader.state().get('selection');
                            selection.map( function( attachment ) {
                                attachment = attachment.toJSON();
                                attachment_ids.push(attachment.id);
                            });
                            $('#_inmo_galeria').val(attachment_ids.join(','));
                            $('.inmo-gallery-preview').text('Imágenes seleccionadas: ' + selection.length);
                        }).open();
                    });
                });
            ");
        }
    }

    // 1. REGISTRAR CAJAS
    public function add_custom_boxes() {
        // A. Caja Principal (Dueño + Operación + Ficha Técnica)
        add_meta_box( 'inmo_main_data', '🏠 Información de la Propiedad', array( $this, 'render_main_data' ), 'propiedad', 'normal', 'high' );
        
        // B. Galería y Notas
        add_meta_box( 'inmo_gallery_data', '📷 Galería y Notas Internas', array( $this, 'render_gallery_data' ), 'propiedad', 'normal', 'default' );

        // C. Lista de Interesados (Solo lectura)
        add_meta_box( 'inmo_leads_list', '👥 Clientes Interesados (Leads)', array( $this, 'render_leads_list' ), 'propiedad', 'normal', 'low' );

        // D. Cajas para Dueños y Clientes (Se mantienen igual)
        add_meta_box( 'inmo_dueno_data', 'Información del Dueño', array( $this, 'render_dueno_data' ), 'dueno', 'normal', 'high' );
        add_meta_box( 'inmo_cliente_data', 'Gestión del Lead', array( $this, 'render_cliente_data' ), 'cliente', 'side', 'high' );
    }

    // --- RENDER: PROPIEDAD PRINCIPAL ---
    public function render_main_data( $post ) {
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        
        // Recuperar datos
        $dueno_id = get_post_meta( $post->ID, '_inmo_dueno_id', true );
        $tipo_prop = get_post_meta( $post->ID, '_inmo_tipo_propiedad', true );
        $tipo_oper = get_post_meta( $post->ID, '_inmo_tipo_operacion', true );
        $garantias = get_post_meta( $post->ID, '_inmo_garantias', true ) ?: array(); // Array
        $docs      = get_post_meta( $post->ID, '_inmo_documentacion', true ) ?: array(); // Array
        
        // Datos Ficha Técnica
        $m2 = get_post_meta( $post->ID, '_inmo_m2', true );
        $dorm = get_post_meta( $post->ID, '_inmo_dormitorios', true );
        $banos = get_post_meta( $post->ID, '_inmo_banos', true );
        $gastos = get_post_meta( $post->ID, '_inmo_gastos_comunes', true );
        $habitaciones = get_post_meta( $post->ID, '_inmo_habitaciones', true );

        // Listas para selects/checkboxes
        $duenos_list = get_posts( array( 'post_type' => 'dueno', 'numberposts' => -1 ) );
        $tipos_prop_opt = ['Apartamento', 'Casa', 'Terreno', 'Local Comercial', 'Oficina', 'Campo'];
        $tipos_oper_opt = ['Venta', 'Alquiler', 'Traspaso', 'Venta y Alquiler'];
        $garantias_opt = ['ANDA', 'Contaduría', 'Aseguradoras (Porto/Sura)', 'Depósito BHU', 'Propiedad'];
        $docs_opt = ['Títulos', 'Planos', 'Cédula Catastral', 'BPS al día', 'Contribución al día'];

        ?>
        <style>
            .inmo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .inmo-section { background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-bottom: 15px; }
            .inmo-section h4 { margin-top: 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
            .inmo-row { margin-bottom: 10px; }
            .inmo-row label { font-weight: 600; display: block; margin-bottom: 3px; }
            .inmo-checkbox-group label { display: inline-block; margin-right: 15px; font-weight: normal; }
        </style>

        <div class="inmo-grid">
            <div>
                <div class="inmo-section">
                    <h4>🔗 Vinculación</h4>
                    <div class="inmo-row">
                        <label>Dueño de la Propiedad:</label>
                        <select name="_inmo_dueno_id" class="widefat">
                            <option value="">-- Seleccionar Dueño --</option>
                            <?php foreach ( $duenos_list as $d ) : ?>
                                <option value="<?php echo $d->ID; ?>" <?php selected( $dueno_id, $d->ID ); ?>>
                                    <?php echo esc_html( $d->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="inmo-section">
                    <h4>💰 Datos de Operación</h4>
                    <div class="inmo-row">
                        <label>Tipo de Propiedad:</label>
                        <select name="_inmo_tipo_propiedad" class="widefat">
                            <?php foreach($tipos_prop_opt as $opt) echo '<option value="'.$opt.'" '.selected($tipo_prop, $opt, false).'>'.$opt.'</option>'; ?>
                        </select>
                    </div>
                    <div class="inmo-row">
                        <label>Tipo de Operación:</label>
                        <select name="_inmo_tipo_operacion" class="widefat">
                            <?php foreach($tipos_oper_opt as $opt) echo '<option value="'.$opt.'" '.selected($tipo_oper, $opt, false).'>'.$opt.'</option>'; ?>
                        </select>
                    </div>
                    
                    <div class="inmo-row">
                        <label>Garantías Aceptadas (Alquiler/Traspaso):</label>
                        <div class="inmo-checkbox-group">
                            <?php foreach($garantias_opt as $g): ?>
                                <label><input type="checkbox" name="_inmo_garantias[]" value="<?php echo $g; ?>" <?php checked(in_array($g, $garantias)); ?>> <?php echo $g; ?></label><br>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="inmo-row">
                        <label>Documentación Exigida (Venta):</label>
                        <div class="inmo-checkbox-group">
                            <?php foreach($docs_opt as $doc): ?>
                                <label><input type="checkbox" name="_inmo_documentacion[]" value="<?php echo $doc; ?>" <?php checked(in_array($doc, $docs)); ?>> <?php echo $doc; ?></label><br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="inmo-section">
                    <h4>📏 Ficha Técnica</h4>
                    <div class="inmo-row">
                        <label>Superficie (M2):</label>
                        <input type="number" name="_inmo_m2" value="<?php echo esc_attr($m2); ?>" class="widefat">
                    </div>
                    <div class="inmo-row">
                        <label>Dormitorios:</label>
                        <input type="number" name="_inmo_dormitorios" value="<?php echo esc_attr($dorm); ?>" class="widefat">
                    </div>
                    <div class="inmo-row">
                        <label>Baños:</label>
                        <input type="number" name="_inmo_banos" value="<?php echo esc_attr($banos); ?>" class="widefat">
                    </div>
                    <div class="inmo-row">
                        <label>Total Habitaciones:</label>
                        <input type="number" name="_inmo_habitaciones" value="<?php echo esc_attr($habitaciones); ?>" class="widefat">
                    </div>
                    <div class="inmo-row">
                        <label>Gastos Comunes ($):</label>
                        <input type="text" name="_inmo_gastos_comunes" value="<?php echo esc_attr($gastos); ?>" class="widefat">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // --- RENDER: GALERÍA Y NOTAS ---
    public function render_gallery_data( $post ) {
        $nota = get_post_meta( $post->ID, '_inmo_nota_interna', true );
        $galeria_ids = get_post_meta( $post->ID, '_inmo_galeria', true );
        ?>
        <p>
            <label><strong>Nota Interna (Admin):</strong></label>
            <textarea name="_inmo_nota_interna" rows="3" class="widefat" placeholder="Detalles privados sobre la negociación o estado..."><?php echo esc_textarea($nota); ?></textarea>
        </p>
        <hr>
        <p>
            <label><strong>Galería de Fotografías:</strong></label><br>
            <input type="hidden" id="_inmo_galeria" name="_inmo_galeria" value="<?php echo esc_attr($galeria_ids); ?>">
            <button class="button inmo-upload-gallery">Seleccionar / Subir Fotos</button>
            <span class="inmo-gallery-preview" style="margin-left:10px; color:#666;">
                <?php echo $galeria_ids ? 'Imágenes guardadas: ' . count(explode(',', $galeria_ids)) : 'Ninguna imagen seleccionada'; ?>
            </span>
            <p class="description">Haz clic para abrir la biblioteca, selecciona múltiples fotos y pulsa "Usar estas imágenes".</p>
        </p>
        <?php
    }

    // --- RENDER: LISTA DE LEADs (IGUAL QUE ANTES) ---
    public function render_leads_list( $post ) {
        $leads = get_posts( array(
            'post_type'  => 'cliente',
            'meta_key'   => '_cliente_propiedad_id',
            'meta_value' => $post->ID,
            'numberposts' => -1
        ));

        if ( empty( $leads ) ) { echo '<p>No hay interesados registrados.</p>'; return; }

        echo '<table class="widefat striped"><thead><tr><th>Cliente</th><th>Teléfono</th><th>Status</th><th>Agente</th><th>Acción</th></tr></thead><tbody>';
        foreach ( $leads as $lead ) {
            $tel = get_post_meta( $lead->ID, '_cliente_telefono', true );
            $st = get_post_meta( $lead->ID, '_cliente_status_proceso', true );
            $agente = get_userdata( get_post_meta( $lead->ID, '_cliente_agente', true ) );
            echo '<tr>
                <td>' . esc_html( $lead->post_title ) . '</td>
                <td>' . esc_html( $tel ) . '</td>
                <td>' . esc_html( $st ) . '</td>
                <td>' . ($agente ? $agente->display_name : '-') . '</td>
                <td><a href="' . get_edit_post_link( $lead->ID ) . '" class="button button-small">Ver Ficha</a></td>
            </tr>';
        }
        echo '</tbody></table>';
    }

    // --- RENDER: DUEÑO Y CLIENTE (MANTENER CÓDIGO SIMPLE ANTERIOR) ---
    public function render_dueno_data( $post ) {
        $telefono = get_post_meta( $post->ID, '_dueno_telefono', true );
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        echo '<p><label>Teléfono:</label><input type="text" name="_dueno_telefono" value="'.esc_attr($telefono).'" class="widefat"></p>';
    }

    public function render_cliente_data( $post ) {
        $prop_id = get_post_meta( $post->ID, '_cliente_propiedad_id', true );
        $status  = get_post_meta( $post->ID, '_cliente_status_proceso', true );
        $telefono = get_post_meta( $post->ID, '_cliente_telefono', true );
        
        // Select de Propiedades
        $props = get_posts(array('post_type'=>'propiedad','numberposts'=>50)); 
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        
        echo '<p><label>Propiedad de Interés:</label><select name="_cliente_propiedad_id" class="widefat"><option value="">--</option>';
        foreach($props as $p) echo '<option value="'.$p->ID.'" '.selected($prop_id, $p->ID, false).'>'.$p->post_title.'</option>';
        echo '</select></p>';

        echo '<p><label>Teléfono:</label><input type="text" name="_cliente_telefono" value="'.esc_attr($telefono).'" class="widefat"></p>';
        
        echo '<p><label>Status:</label><select name="_cliente_status_proceso" class="widefat">';
        foreach(['En Proceso','En Espera','Visitando','Reservado','Realizado'] as $s) echo '<option value="'.$s.'" '.selected($status, $s, false).'>'.$s.'</option>';
        echo '</select></p>';
    }

    // --- GUARDAR DATOS ---
    public function save_custom_data( $post_id ) {
        if ( ! isset( $_POST['inmo_nonce'] ) || ! wp_verify_nonce( $_POST['inmo_nonce'], 'inmo_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if ( 'propiedad' === $_POST['post_type'] ) {
            // Textos simples
            $fields = ['_inmo_dueno_id', '_inmo_tipo_propiedad', '_inmo_tipo_operacion', '_inmo_m2', 
                       '_inmo_dormitorios', '_inmo_banos', '_inmo_gastos_comunes', '_inmo_habitaciones', 
                       '_inmo_nota_interna', '_inmo_galeria'];
            
            foreach($fields as $f) {
                if(isset($_POST[$f])) update_post_meta($post_id, $f, sanitize_text_field($_POST[$f]));
            }

            // Arrays (Checkbox)
            $checks = ['_inmo_garantias', '_inmo_documentacion'];
            foreach($checks as $c) {
                $val = ( isset($_POST[$c]) && is_array($_POST[$c]) ) ? $_POST[$c] : array();
                update_post_meta($post_id, $c, $val); // Guarda el array tal cual
            }
        }

        if ( 'dueno' === $_POST['post_type'] ) {
            if(isset($_POST['_dueno_telefono'])) update_post_meta( $post_id, '_dueno_telefono', sanitize_text_field( $_POST['_dueno_telefono'] ) );
        }

        if ( 'cliente' === $_POST['post_type'] ) {
            if(isset($_POST['_cliente_propiedad_id'])) update_post_meta( $post_id, '_cliente_propiedad_id', sanitize_text_field( $_POST['_cliente_propiedad_id'] ) );
            if(isset($_POST['_cliente_status_proceso'])) update_post_meta( $post_id, '_cliente_status_proceso', sanitize_text_field( $_POST['_cliente_status_proceso'] ) );
            if(isset($_POST['_cliente_telefono'])) update_post_meta( $post_id, '_cliente_telefono', sanitize_text_field( $_POST['_cliente_telefono'] ) );
        }
    }
}

new Inmo_Metaboxes();