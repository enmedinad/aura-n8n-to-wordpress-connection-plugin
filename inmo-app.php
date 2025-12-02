<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_Chat_Widget {

    // 🔴 URL de n8n (Asegúrate que sea la correcta /webhook/ o /webhook-test/)
    private $n8n_webhook = 'https://n8n.srv1048574.hstgr.cloud/webhook/chat-inmobiliario'; 

    public function __construct() {
        add_action( 'wp_footer', array( $this, 'render_widget' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_inmo_chat_proxy', array( $this, 'chat_proxy_handler' ) );
        add_action( 'wp_ajax_nopriv_inmo_chat_proxy', array( $this, 'chat_proxy_handler' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_script( 'jquery' );
    }

    public function chat_proxy_handler() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $response = wp_remote_post( $this->n8n_webhook, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => json_encode($data), 
            'method'  => 'POST', 
            'timeout' => 45
        ));

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => 'Error de conexión con n8n' ) );
        } else {
            echo wp_remote_retrieve_body( $response );
        }
        wp_die();
    }

    public function render_widget() {
        // URL para el JS
        $proxy_url = admin_url('admin-ajax.php?action=inmo_chat_proxy');
        ?>
        
        <div id="inmo-widget-container">
            <div id="inmo-launcher"><span class="icon">💬</span></div>
            
            <div id="inmo-window">
                <div class="inmo-header">
                    <div class="agent-info">
                        <div class="avatar">🤖</div>
                        <div class="name">Asistente Inmobiliario<br><small>En línea</small></div>
                    </div>
                    <div id="inmo-close">✕</div>
                </div>
                
                <div id="inmo-messages">
                    <div class="msg bot">Hola 👋, soy la IA de Inmouru. ¿Buscas comprar o alquilar hoy?</div>
                </div>

                <div class="inmo-input-zone">
                    <input type="text" id="inmo-input" placeholder="Escribe aquí..." autocomplete="off">
                    <button id="inmo-send">➤</button>
                </div>
            </div>
        </div>

        <style>
            #inmo-widget-container { position: fixed; bottom: 20px; right: 20px; z-index: 999999; font-family: 'Segoe UI', sans-serif; }
            #inmo-launcher { width: 60px; height: 60px; background: #25D366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: transform 0.3s; }
            #inmo-launcher:hover { transform: scale(1.1); }
            #inmo-window { display: none; width: 350px; height: 500px; background: white; border-radius: 15px; position: absolute; bottom: 80px; right: 0; box-shadow: 0 5px 25px rgba(0,0,0,0.2); flex-direction: column; overflow: hidden; border: 1px solid #eee; }
            .inmo-header { background: #009a22; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
            .agent-info { display: flex; gap: 10px; align-items: center; }
            .avatar { width: 35px; height: 35px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
            .name { font-size: 14px; font-weight: bold; line-height: 1.2; }
            .name small { font-weight: normal; opacity: 0.8; font-size: 12px; }
            #inmo-close { cursor: pointer; font-size: 18px; opacity: 0.7; }
            #inmo-messages { flex: 1; height: 345px; background: #E5DDD5; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
            .msg { max-width: 80%; padding: 8px 12px; border-radius: 8px; font-size: 14px; line-height: 1.4; word-wrap: break-word; }
            .msg.bot { background: white; align-self: flex-start; border-top-left-radius: 0; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
            .msg.user { background: #DCF8C6; align-self: flex-end; border-top-right-radius: 0; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
            .msg.system { align-self: center; font-size: 11px; color: #888; background: rgba(255,255,255,0.8); padding: 2px 8px; border-radius: 10px; margin: 5px 0; }
            .inmo-input-zone { background: #f0f0f0; padding: 10px; display: flex; gap: 10px; }
            #inmo-input { flex: 1; border: none; padding: 10px; border-radius: 20px; outline: none; }
            #inmo-send { background: #009a22; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }
            
            /* Cards dentro del chat */
            .inmo-mini-card { background: white; border-radius: 8px; overflow: hidden; margin-top: 5px; border: 1px solid #ddd; width: 100%; max-width: 250px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: block; text-decoration: none; color: inherit; }
            .inmo-card-img { width: 100%; height: 120px; background-size: cover; background-position: center; }
            .inmo-card-data { padding: 10px; }
            .inmo-card-price { color: #009a22; font-weight: bold; font-size: 15px; }
            .inmo-card-title { font-size: 13px; font-weight: 600; margin: 3px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #333; }
            .inmo-card-btn { display: block; text-align: center; background: #f0f0f0; color: #333; text-decoration: none; padding: 5px; font-size: 12px; margin-top: 5px; border-radius: 4px; font-weight: 600; }
        </style>

        <script type="text/javascript">
        
        // A. DEFINIR FUNCIÓN GLOBALMENTE PRIMERO (Evita el ReferenceError)
        window.triggerInmoChat = function(msg) {
            // Intenta usar jQuery si está cargado
            if (typeof jQuery !== 'undefined') {
                var $ = jQuery;
                if ( $('#inmo-window').is(':hidden') ) {
                    $('#inmo-window').fadeIn(200);
                }
                setTimeout(function(){
                    $('#inmo-input').val(msg);
                    $('#inmo-send').click();
                }, 300);
            } else {
                console.error("jQuery aún no ha cargado.");
            }
        };

        // B. LÓGICA DEL CHAT (DENTRO DE DOCUMENT READY)
        jQuery(document).ready(function($){
            var proxyUrl = '<?php echo $proxy_url; ?>';
            
            // Abrir/Cerrar
            $('#inmo-launcher, #inmo-close').click(function(){
                $('#inmo-window').fadeToggle(200);
            });

            // Agregar mensaje al historial
            function addMsg(html, type) {
                $('#inmo-messages').append('<div class="msg '+type+'">'+html+'</div>');
                var div = document.getElementById("inmo-messages");
                div.scrollTop = div.scrollHeight;
            }

            // Renderizar tarjetas de propiedades
            function renderCards(props) {
                if(!props || props.length === 0) return;
                var cardsHtml = '';
                props.forEach(function(p){
                    var title = p.title || "Propiedad";
                    var price = p.precio || p.price || "Consultar";
                    var img = p.imagen || 'https://via.placeholder.com/300x200?text=Sin+Foto';
                    var link = p.link || '/?post_type=propiedad&p=' + (p.id || p.ID); // Fallback

                    cardsHtml += `
                    <a href="${link}" class="inmo-mini-card">
                        <div class="inmo-card-img" style="background-image: url('${img}');"></div>
                        <div class="inmo-card-data">
                            <div class="inmo-card-price">$ ${price}</div>
                            <div class="inmo-card-title">${title}</div>
                            <span class="inmo-card-btn">Ver Detalles</span>
                        </div>
                    </a>`;
                });
                addMsg(cardsHtml, 'bot');
            }

            // Enviar mensaje a N8N
            function send() {
                var txt = $('#inmo-input').val().trim();
                if(txt === '') return;

                addMsg(txt.replace(/\n/g, '<br>'), 'user');
                $('#inmo-input').val('');
                $('#inmo-messages').append('<div class="msg system typing">Escribiendo...</div>');

                $.ajax({
                    url: proxyUrl, method: 'POST', contentType: 'application/json',
                    data: JSON.stringify({ message: txt, sessionId: 'sess_'+Math.floor(Math.random()*9999) }),
                    success: function(res) {
                        $('.typing').remove();
                        // 1. Texto
                        var text = res.output || res.text;
                        if(text) addMsg(text.replace(/\n/g, '<br>'), 'bot');
                        else if(!res.properties) addMsg("Error: Respuesta vacía de IA.", 'system');
                        
                        // 2. Propiedades
                        if(res.properties && Array.isArray(res.properties)) renderCards(res.properties);
                    },
                    error: function(err) {
                        $('.typing').remove();
                        addMsg('Error de conexión.', 'system');
                        console.log(err);
                    }
                });
            }

            $('#inmo-send').click(send);
            $('#inmo-input').keypress(function(e){ if(e.which==13) send(); });
        });
        </script>
        <?php
    }
}

new Inmo_Chat_Widget();