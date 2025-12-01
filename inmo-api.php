<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_API {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_api_fields' ) );
    }

    public function register_api_fields() {
        
        // 1. Exponer Datos de la Propiedad (Precio, Operación, M2, etc.)
        register_rest_field( 'propiedad', 'datos_propiedad', array(
            'get_callback' => array( $this, 'get_propiedad_meta' ),
            'schema'       => null,
        ));

        // 2. Exponer Datos del Dueño vinculado (Nombre y Teléfono)
        register_rest_field( 'propiedad', 'datos_dueno', array(
            'get_callback' => array( $this, 'get_dueno_info' ),
            'schema'       => null,
        ));

        // 3. Exponer Taxonomías legibles (Texto en lugar de IDs)
        register_rest_field( 'propiedad', 'listados_texto', array(
            'get_callback' => array( $this, 'get_tax_names' ),
            'schema'       => null,
        ));
    }

    // --- CALLBACKS ---

    // Devuelve los campos personalizados limpios
    public function get_propiedad_meta( $object ) {
        $post_id = $object['id'];
        
        return array(
            'precio'         => get_post_meta( $post_id, '_inmo_precio', true ),
            'tipo_operacion' => get_post_meta( $post_id, '_inmo_tipo_operacion', true ),
            'm2'             => get_post_meta( $post_id, '_inmo_m2', true ), // Asumiendo que agregamos este campo
            'dormitorios'    => get_post_meta( $post_id, '_inmo_dormitorios', true ),
            'nota_interna'   => get_post_meta( $post_id, '_inmo_nota_interna', true ),
            // Agrega aquí cualquier otro meta field que crees en metaboxes
        );
    }

    // Busca al dueño y devuelve sus datos de contacto
    public function get_dueno_info( $object ) {
        $post_id = $object['id'];
        $dueno_id = get_post_meta( $post_id, '_inmo_dueno_id', true );

        if ( ! $dueno_id ) {
            return null; // No hay dueño asignado
        }

        $dueno_post = get_post( $dueno_id );
        if ( ! $dueno_post ) return null;

        return array(
            'id'       => $dueno_id,
            'nombre'   => $dueno_post->post_title,
            'telefono' => get_post_meta( $dueno_id, '_dueno_telefono', true ),
            'status'   => get_post_meta( $dueno_id, '_dueno_status_propiedad', true ),
        );
    }

    // Devuelve los nombres de Amenities y Condiciones (Ej: ["Piscina", "Garage"])
    // Esto ayuda a la IA de n8n a entender mejor el contexto sin buscar IDs
    public function get_tax_names( $object ) {
        $post_id = $object['id'];
        
        $amenities = wp_get_post_terms( $post_id, 'amenities', array( 'fields' => 'names' ) );
        $condiciones = wp_get_post_terms( $post_id, 'condiciones_dueno', array( 'fields' => 'names' ) );

        return array(
            'amenities'   => $amenities,
            'condiciones' => $condiciones
        );
    }
}

new Inmo_API();