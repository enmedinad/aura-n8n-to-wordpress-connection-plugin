<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_Metaboxes {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_custom_boxes' ) );
        add_action( 'save_post', array( $this, 'save_custom_data' ) );
    }

    // 1. Registrar las Cajas
    public function add_custom_boxes() {
        // Caja Principal en PROPIEDADES (Datos + Selección de Dueño)
        add_meta_box( 'inmo_propiedad_data', 'Datos de la Propiedad y Dueño', array( $this, 'render_propiedad_data' ), 'propiedad', 'normal', 'high' );
        
        // Caja de Lista de Interesados en PROPIEDADES (Solo lectura / Navegación)
        add_meta_box( 'inmo_propiedad_leads', 'Clientes Interesados (Leads)', array( $this, 'render_propiedad_leads_list' ), 'propiedad', 'normal', 'high' );

        // Caja en DUEÑOS (Datos + Lista de sus propiedades)
        add_meta_box( 'inmo_dueno_data', 'Información del Dueño', array( $this, 'render_dueno_data' ), 'dueno', 'normal', 'high' );

        // Caja en CLIENTES (Vinculación con Propiedad)
        add_meta_box( 'inmo_cliente_data', 'Gestión del Lead', array( $this, 'render_cliente_data' ), 'cliente', 'side', 'high' );
    }

    // --- RENDER: PROPIEDADES ---

    public function render_propiedad_data( $post ) {
        // Recuperar valores actuales
        $dueno_id = get_post_meta( $post->ID, '_inmo_dueno_id', true );
        $precio   = get_post_meta( $post->ID, '_inmo_precio', true );
        $tipo     = get_post_meta( $post->ID, '_inmo_tipo_operacion', true );
        
        // Obtener lista de Dueños para el select
        $duenos = get_posts( array( 'post_type' => 'dueno', 'numberposts' => -1, 'post_status' => 'any' ) );
        
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        ?>
        <style>.inmo-row { margin-bottom: 15px; border-bottom:1px solid #eee; padding-bottom:10px; } label { font-weight:bold; display:block; margin-bottom:5px;}</style>
        
        <div class="inmo-row">
            <label>Seleccionar Dueño:</label>
            <select name="_inmo_dueno_id" class="widefat">
                <option value="">-- Sin asignar --</option>
                <?php foreach ( $duenos as $d ) : ?>
                    <option value="<?php echo $d->ID; ?>" <?php selected( $dueno_id, $d->ID ); ?>>
                        <?php echo esc_html( $d->post_title ); ?> (ID: <?php echo $d->ID; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if($dueno_id): ?>
                <p><a href="<?php echo get_edit_post_link($dueno_id); ?>" target="_blank" class="button button-secondary">Ir a la ficha del Dueño ➡</a></p>
            <?php endif; ?>
        </div>

        <div class="inmo-row">
            <label>Tipo de Operación:</label>
            <select name="_inmo_tipo_operacion">
                <option value="Venta" <?php selected($tipo, 'Venta'); ?>>Venta</option>
                <option value="Alquiler" <?php selected($tipo, 'Alquiler'); ?>>Alquiler</option>
                <option value="Traspaso" <?php selected($tipo, 'Traspaso'); ?>>Traspaso</option>
            </select>
        </div>

        <div class="inmo-row">
            <label>Precio / Valor:</label>
            <input type="text" name="_inmo_precio" value="<?php echo esc_attr( $precio ); ?>" class="widefat">
        </div>
        
        <?php
    }

    public function render_propiedad_leads_list( $post ) {
        // Buscar Clientes que tengan guardada ESTA propiedad en su meta
        $leads = get_posts( array(
            'post_type'  => 'cliente',
            'meta_key'   => '_cliente_propiedad_id',
            'meta_value' => $post->ID,
            'numberposts' => -1
        ));

        if ( empty( $leads ) ) {
            echo '<p>No hay interesados registrados aún.</p>';
            return;
        }

        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>Nombre Cliente</th><th>Teléfono</th><th>Status</th><th>Agente</th><th>Acción</th></tr></thead><tbody>';
        
        foreach ( $leads as $lead ) {
            $telefono = get_post_meta( $lead->ID, '_cliente_telefono', true );
            $status   = get_post_meta( $lead->ID, '_cliente_status_proceso', true );
            $agente_id = get_post_meta( $lead->ID, '_cliente_agente', true ); // ID de usuario WP
            $agente_info = get_userdata($agente_id);
            $nombre_agente = $agente_info ? $agente_info->display_name : 'Sin asignar';

            echo '<tr>';
            echo '<td><strong>' . esc_html( $lead->post_title ) . '</strong></td>';
            echo '<td>' . esc_html( $telefono ) . '</td>';
            echo '<td><span class="badge">' . esc_html( $status ) . '</span></td>';
            echo '<td>' . esc_html( $nombre_agente ) . '</td>';
            echo '<td><a href="' . get_edit_post_link( $lead->ID ) . '" class="button button-small">Ver Lead</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    // --- RENDER: DUEÑOS ---

    public function render_dueno_data( $post ) {
        $telefono = get_post_meta( $post->ID, '_dueno_telefono', true );
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        ?>
        <p>
            <label>Teléfono de Contacto:</label>
            <input type="text" name="_dueno_telefono" value="<?php echo esc_attr($telefono); ?>" class="widefat">
        </p>

        <hr>
        <h4>Propiedades de este Dueño:</h4>
        <?php
        // Buscar Propiedades que tengan a ESTE dueño asignado
        $props = get_posts( array(
            'post_type' => 'propiedad',
            'meta_key'  => '_inmo_dueno_id',
            'meta_value' => $post->ID,
            'numberposts' => -1
        ));

        if($props) {
            echo '<ul>';
            foreach($props as $p) {
                $estado = get_post_status($p->ID) == 'publish' ? '✅ Publicada' : '⚠️ Borrador';
                echo '<li>' . $estado . ' - <a href="'.get_edit_post_link($p->ID).'">'.esc_html($p->post_title).'</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Este dueño no tiene propiedades asignadas aún.</p>';
        }
    }

    // --- RENDER: CLIENTES (LEADS) ---

    public function render_cliente_data( $post ) {
        $prop_id = get_post_meta( $post->ID, '_cliente_propiedad_id', true );
        $status  = get_post_meta( $post->ID, '_cliente_status_proceso', true );
        $telefono = get_post_meta( $post->ID, '_cliente_telefono', true );

        // Para el select de propiedades (podría ser muy largo, idealmente usaríamos AJAX search, pero por ahora un select simple)
        $props = get_posts( array('post_type' => 'propiedad', 'numberposts' => 50) ); 
        
        wp_nonce_field( 'inmo_save_meta', 'inmo_nonce' );
        ?>
        <p>
            <label>Propiedad de Interés:</label>
            <select name="_cliente_propiedad_id" class="widefat">
                <option value="">-- Seleccionar --</option>
                <?php foreach($props as $p): ?>
                    <option value="<?php echo $p->ID; ?>" <?php selected($prop_id, $p->ID); ?>>
                        <?php echo $p->post_title; ?> (ID: <?php echo $p->ID; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
             <?php if($prop_id): ?>
                <a href="<?php echo get_edit_post_link($prop_id); ?>" style="display:block; margin-top:5px; text-align:right;">Ir a Propiedad &raquo;</a>
            <?php endif; ?>
        </p>
        
        <p>
            <label>Teléfono:</label>
            <input type="text" name="_cliente_telefono" value="<?php echo esc_attr($telefono); ?>" class="widefat">
        </p>

        <p>
            <label>Status Proceso:</label>
            <select name="_cliente_status_proceso" class="widefat">
                <?php 
                $estados = ['En Proceso', 'En Espera', 'Visitando', 'Reservado', 'Realizado', 'Descartado'];
                foreach($estados as $est) {
                    echo '<option value="'.$est.'" '.selected($status, $est, false).'>'.$est.'</option>';
                }
                ?>
            </select>
        </p>
        <?php
    }

    // --- GUARDAR DATOS ---

    public function save_custom_data( $post_id ) {
        // Verificar nonce y permisos
        if ( ! isset( $_POST['inmo_nonce'] ) || ! wp_verify_nonce( $_POST['inmo_nonce'], 'inmo_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        // Guardar Propiedad
        if ( 'propiedad' === $_POST['post_type'] ) {
            if(isset($_POST['_inmo_dueno_id'])) update_post_meta( $post_id, '_inmo_dueno_id', sanitize_text_field( $_POST['_inmo_dueno_id'] ) );
            if(isset($_POST['_inmo_precio'])) update_post_meta( $post_id, '_inmo_precio', sanitize_text_field( $_POST['_inmo_precio'] ) );
            if(isset($_POST['_inmo_tipo_operacion'])) update_post_meta( $post_id, '_inmo_tipo_operacion', sanitize_text_field( $_POST['_inmo_tipo_operacion'] ) );
        }

        // Guardar Dueño
        if ( 'dueno' === $_POST['post_type'] ) {
            if(isset($_POST['_dueno_telefono'])) update_post_meta( $post_id, '_dueno_telefono', sanitize_text_field( $_POST['_dueno_telefono'] ) );
        }

        // Guardar Cliente
        if ( 'cliente' === $_POST['post_type'] ) {
            if(isset($_POST['_cliente_propiedad_id'])) update_post_meta( $post_id, '_cliente_propiedad_id', sanitize_text_field( $_POST['_cliente_propiedad_id'] ) );
            if(isset($_POST['_cliente_status_proceso'])) update_post_meta( $post_id, '_cliente_status_proceso', sanitize_text_field( $_POST['_cliente_status_proceso'] ) );
            if(isset($_POST['_cliente_telefono'])) update_post_meta( $post_id, '_cliente_telefono', sanitize_text_field( $_POST['_cliente_telefono'] ) );
        }
    }
}

new Inmo_Metaboxes();