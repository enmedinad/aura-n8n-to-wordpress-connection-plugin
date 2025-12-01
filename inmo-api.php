<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_API {
    public function __construct() { add_action( 'rest_api_init', array( $this, 'register_api_fields' ) ); }

    public function register_api_fields() {
        register_rest_field( 'propiedad', 'datos_propiedad', array( 'get_callback' => array( $this, 'get_propiedad_meta' ), 'schema' => null ));
        register_rest_field( 'propiedad', 'datos_dueno', array( 'get_callback' => array( $this, 'get_dueno_info' ), 'schema' => null ));
    }

    public function get_propiedad_meta( $object ) {
        $id = $object['id'];
        
        // Convertir Galería IDs a URLs
        $galeria_ids = get_post_meta( $id, '_inmo_galeria', true );
        $urls = [];
        if($galeria_ids) {
            foreach(explode(',', $galeria_ids) as $gid) { $u = wp_get_attachment_url($gid); if($u) $urls[] = $u; }
        }

        return array(
            'ubicacion_referencia' => get_post_meta( $id, '_inmo_ubicacion_ref', true ), // Solo enviamos la ref, no la real
            'gastos_comunes'       => get_post_meta( $id, '_inmo_gastos_comunes', true ),
            'gastos_incluidos'     => get_post_meta( $id, '_inmo_gastos_incluidos', true ),
            
            // Zona 1
            'dormitorios' => get_post_meta( $id, '_inmo_dormitorios', true ),
            'banos'       => get_post_meta( $id, '_inmo_banos', true ),
            'cocina'      => get_post_meta( $id, '_inmo_cocina_tipo', true ),
            'calefaccion' => get_post_meta( $id, '_inmo_calefaccion_tipo', true ),
            
            // Zona 2
            'm2_total'    => get_post_meta( $id, '_inmo_sup_total', true ),
            'estado'      => get_post_meta( $id, '_inmo_estado', true ),
            'cochera'     => get_post_meta( $id, '_inmo_cochera_tipo', true ),
            
            // Venta
            'en_venta'      => get_post_meta( $id, '_inmo_venta_activa', true ),
            'precio_venta'  => get_post_meta( $id, '_inmo_venta_precio', true ),
            
            // Alquiler
            'en_alquiler'     => get_post_meta( $id, '_inmo_alquiler_activo', true ),
            'precio_alquiler' => get_post_meta( $id, '_inmo_alquiler_precio', true ),
            'mascotas'        => get_post_meta( $id, '_inmo_cond_mascota', true ),
            'garantias'       => get_post_meta( $id, '_inmo_garantias', true ) ?: [],

            'galeria_urls' => $urls
        );
    }

    public function get_dueno_info( $object ) {
        $did = get_post_meta( $object['id'], '_inmo_dueno_id', true );
        if(!$did) return null;
        $dp = get_post($did);
        return $dp ? ['nombre' => $dp->post_title, 'telefono' => get_post_meta($did, '_dueno_telefono', true)] : null;
    }
}
new Inmo_API();