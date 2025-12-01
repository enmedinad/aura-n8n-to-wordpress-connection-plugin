<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_API {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_api_fields' ) );
    }

    public function register_api_fields() {
        
        // 1. Exponer Datos Completos de la Propiedad
        register_rest_field( 'propiedad', 'datos_propiedad', array(
            'get_callback' => array( $this, 'get_propiedad_meta' ),
            'schema'       => null,
        ));

        // 2. Exponer Datos del Dueño (Nombre y Teléfono)
        register_rest_field( 'propiedad', 'datos_dueno', array(
            'get_callback' => array( $this, 'get_dueno_info' ),
            'schema'       => null,
        ));

        // 3. Exponer Taxonomías legibles (Texto)
        register_rest_field( 'propiedad', 'listados_texto', array(
            'get_callback' => array( $this, 'get_tax_names' ),
            'schema'       => null,
        ));
    }

    // --- CALLBACKS ---

    public function get_propiedad_meta( $object ) {
        $post_id = $object['id'];
        
        // Recuperar Galería (IDs) y convertirlos a URLs
        $galeria_ids = get_post_meta( $post_id, '_inmo_galeria', true );
        $galeria_urls = array();
        
        if ( ! empty( $galeria_ids ) ) {
            $ids_array = explode( ',', $galeria_ids );
            foreach ( $ids_array as $img_id ) {
                $url = wp_get_attachment_url( $img_id );
                if ( $url ) $galeria_urls[] = $url;
            }
        }

        return array(
            // Información Básica
            'tipo_propiedad' => get_post_meta( $post_id, '_inmo_tipo_propiedad', true ),
            'tipo_operacion' => get_post_meta( $post_id, '_inmo_tipo_operacion', true ),
            'precio'         => get_post_meta( $post_id, '_inmo_precio', true ), // (Si usas el campo legacy)
            // Ficha Técnica
            'm2'             => get_post_meta( $post_id, '_inmo_m2', true ),
            'dormitorios'    => get_post_meta( $post_id, '_inmo_dormitorios', true ),
            'banos'          => get_post_meta( $post_id, '_inmo_banos', true ),
            'habitaciones'   => get_post_meta( $post_id, '_inmo_habitaciones', true ),
            'gastos_comunes' => get_post_meta( $post_id, '_inmo_gastos_comunes', true ),
            // Arrays (Checkboxes)
            'garantias'      => get_post_meta( $post_id, '_inmo_garantias', true ) ?: [],
            'documentacion'  => get_post_meta( $post_id, '_inmo_documentacion', true ) ?: [],
            // Nota Interna y Galería
            'nota_interna'   => get_post_meta( $post_id, '_inmo_nota_interna', true ),
            'galeria_urls'   => $galeria_urls, // Devuelve array de strings ["http://...", "http://..."]
        );
    }

    public function get_dueno_info( $object ) {
        $post_id = $object['id'];
        $dueno_id = get_post_meta( $post_id, '_inmo_dueno_id', true );

        if ( ! $dueno_id ) return null;

        $dueno_post = get_post( $dueno_id );
        if ( ! $dueno_post ) return null;

        return array(
            'id'       => $dueno_id,
            'nombre'   => $dueno_post->post_title,
            'telefono' => get_post_meta( $dueno_id, '_dueno_telefono', true ),
            // Podríamos agregar aquí el email si lo guardamos en el futuro
        );
    }

    public function get_tax_names( $object ) {
        $post_id = $object['id'];
        
        // Obtener nombres de las taxonomías (Categorías)
        $amenities = wp_get_post_terms( $post_id, 'amenities', array( 'fields' => 'names' ) );
        $condiciones = wp_get_post_terms( $post_id, 'condiciones_dueno', array( 'fields' => 'names' ) );

        return array(
            'amenities'   => $amenities,    // Ej: ["Piscina", "Garage"]
            'condiciones' => $condiciones   // Ej: ["Sin Mascotas", "Pareja Sola"]
        );
    }
}

new Inmo_API();