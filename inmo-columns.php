<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_Columns {

    public function __construct() {
        // Dueños
        add_filter( 'manage_dueno_posts_columns', array( $this, 'set_dueno_columns' ) );
        add_action( 'manage_dueno_posts_custom_column', array( $this, 'render_dueno_columns' ), 10, 2 );
        
        // Propiedades (Opcional, pero útil)
        add_filter( 'manage_propiedad_posts_columns', array( $this, 'set_propiedad_columns' ) );
        add_action( 'manage_propiedad_posts_custom_column', array( $this, 'render_propiedad_columns' ), 10, 2 );
    }

    // --- DUEÑOS ---

    public function set_dueno_columns( $columns ) {
        // Reordenamos: Checkbox, Titulo, Telefono, Propiedad ID, Status, Check IA, Fecha
        $new = array();
        $new['cb'] = $columns['cb'];
        $new['title'] = 'Nombre Dueño';
        $new['telefono'] = '📞 Teléfono';
        $new['propiedad_link'] = '🏠 Propiedad (ID)';
        $new['status_prop'] = '🚩 Status';
        $new['check_ia'] = '🤖 Último Check';
        $new['date'] = 'Fecha Creación';
        return $new;
    }

    public function render_dueno_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'telefono':
                echo get_post_meta( $post_id, '_dueno_telefono', true );
                break;

            case 'propiedad_link':
                // Buscamos propiedad vinculada
                $props = get_posts(array('post_type'=>'propiedad', 'meta_key'=>'_inmo_dueno_id', 'meta_value'=>$post_id));
                if($props) {
                    foreach($props as $p) {
                        echo '<a href="'.get_edit_post_link($p->ID).'"><strong>ID: '.$p->ID.'</strong></a><br>';
                        echo '<small>'.mb_strimwidth($p->post_title, 0, 20, '...').'</small>';
                    }
                } else {
                    echo '<span style="color:red;">Sin asignar</span>';
                }
                break;

            case 'status_prop':
                $st = get_post_meta( $post_id, '_dueno_status_propiedad', true );
                if($st == 'Disponible') echo '<span style="color:green; font-weight:bold;">🟢 '.$st.'</span>';
                elseif($st == 'Vendida' || $st == 'Alquilada') echo '<span style="color:red; font-weight:bold;">🔴 '.$st.'</span>';
                elseif($st == 'En Proceso') echo '<span style="color:orange; font-weight:bold;">🟡 '.$st.'</span>';
                else echo $st;
                break;

            case 'check_ia':
                $fecha = get_post_meta( $post_id, '_dueno_fecha_check', true );
                $res = get_post_meta( $post_id, '_dueno_consultado', true );
                if($fecha) echo '📅 ' . date('d/m', strtotime($fecha));
                if($res) echo '<br><small>'.mb_strimwidth($res, 0, 30, '...').'</small>';
                break;
        }
    }

    // --- PROPIEDADES (EXTRA ÚTIL) ---
    
    public function set_propiedad_columns( $columns ) {
        $columns['precio'] = '💰 Precio';
        $columns['ubicacion'] = '📍 Zona';
        $columns['dueno_link'] = '👤 Dueño';
        return $columns;
    }

    public function render_propiedad_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'precio':
                $v = get_post_meta($post_id, '_inmo_venta_precio', true);
                $a = get_post_meta($post_id, '_inmo_alquiler_precio', true);
                if($v) echo 'V: $'.$v.'<br>';
                if($a) echo 'A: $'.$a;
                break;
            case 'ubicacion':
                echo get_post_meta($post_id, '_inmo_ubicacion_ref', true);
                break;
            case 'dueno_link':
                $did = get_post_meta($post_id, '_inmo_dueno_id', true);
                if($did) echo '<a href="'.get_edit_post_link($did).'">'.get_the_title($did).'</a>';
                break;
        }
    }
}

new Inmo_Columns();