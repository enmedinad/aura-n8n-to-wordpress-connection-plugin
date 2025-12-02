<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inmo_App_Frontend {

    // 🔴 PEGA AQUÍ TU WEBHOOK DE N8N
    private $n8n_webhook = 'https://n8n.srv1048574.hstgr.cloud/webhook-test/chat-inmobiliario'; 

    public function __construct() {
        add_shortcode( 'inmo_app', array( $this, 'render_app' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        // Registramos scripts pero solo los cargamos si se usa el shortcode (optimización)
        wp_register_script( 'inmo-app-js', false );
        wp_register_style( 'inmo-app-css', false );
    }

    public function render_app() {
        wp_enqueue_script( 'jquery' );
        
        // HTML DE LA APP
        ob_start();
        ?>
        <div class="inmo-app-container">
            
            <div class="inmo-col-chat">
                <div class="inmo-chat-header">
                    <h3>🤖 Asistente Inmobiliario</h3>
                    <span class="status-dot"></span> En línea
                </div>
                
                <div id="inmo-messages" class="inmo-messages-area">
                    <div class="msg bot">
                        Hola, soy la IA de Inmouru. ¿Buscas comprar, alquilar o vender una propiedad hoy?
                    </div>
                </div>

                <div class="inmo-input-area">
                    <input type="text" id="inmo-input" placeholder="Ej: Busco alquiler en Pocitos, 2 dorm..." autocomplete="off">
                    <button id="inmo-send">Enviar ➤</button>
                </div>
            </div>

            <div class="inmo-col-results">
                <div class="results-header">
                    <h3>📋 Propiedades Destacadas</h3>
                </div>
                <div id="inmo-results-grid" class="inmo-grid">
                    <div class="empty-state">
                        <p>👋 Las recomendaciones de la IA aparecerán aquí.</p>
                    </div>
                </div>
            </div>

        </div>

        <style>
            .inmo-app-container { display: flex; flex-wrap: wrap; gap: 20px; background: #f4f4f4; padding: 20px; border-radius: 12px; font-family: 'Segoe UI', sans-serif; min-height: 600px; }
            
            /* Chat Column */
            .inmo-col-chat { flex: 1; min-width: 300px; background: #fff; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
            .inmo-chat-header { background: #2271b1; color: white; padding: 15px; display: flex; align-items: center; gap: 10px; }
            .inmo-chat-header h3 { margin: 0; font-size: 1.1em; color: white; }
            .status-dot { width: 10px; height: 10px; background: #00ff44; border-radius: 50%; display: inline-block; box-shadow: 0 0 5px #00ff44; }
            
            .inmo-messages-area { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; background: #f9f9f9; max-height: 500px; }
            .msg { padding: 12px 16px; border-radius: 12px; max-width: 85%; line-height: 1.4; font-size: 15px; }
            .msg.bot { background: #eef2f5; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; }
            .msg.user { background: #2271b1; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
            .msg.system { background: transparent; color: #888; font-size: 0.85em; text-align: center; align-self: center; font-style: italic; }

            .inmo-input-area { padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px; background: white; }
            #inmo-input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; transition: 0.3s; }
            #inmo-input:focus { border-color: #2271b1; }
            #inmo-send { background: #2271b1; color: white; border: none; padding: 0 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
            #inmo-send:hover { background: #1a5c8e; }

            /* Results Column */
            .inmo-col-results { flex: 1.5; min-width: 300px; display: flex; flex-direction: column; }
            .results-header h3 { margin: 0 0 15px 0; color: #444; }
            .inmo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; overflow-y: auto; max-height: 550px; }
            
            /* Cards */
            .inmo-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: 0.3s; border: 1px solid #eee; }
            .inmo-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
            .inmo-card-img { height: 140px; background: #ddd; background-size: cover; background-position: center; }
            .inmo-card-body { padding: 12px; }
            .inmo-card-price { color: #2271b1; font-weight: bold; font-size: 1.1em; }
            .inmo-card-title { font-size: 0.95em; font-weight: 600; margin: 5px 0; color: #333; }
            .inmo-card-meta { font-size: 0.85em; color: #777; display: flex; gap: 10px; }
            .empty-state { text-align: center; color: #999; margin-top: 50px; grid-column: 1 / -1; }
        </style>

        <script type="text/javascript">
        jQuery(document).ready(function($){
            var webhookUrl = '<?php echo $this->n8n_webhook; ?>';
            var $chatBox = $('#inmo-messages');

            function addMsg(text, type) {
                var cleanText = text.replace(/\n/g, "<br>");
                $chatBox.append('<div class="msg '+type+'">'+cleanText+'</div>');
                $chatBox.scrollTop($chatBox[0].scrollHeight);
            }

            // Simular carga de tarjeta (Para probar sin N8N al inicio)
            function renderProperties(props) {
                var $grid = $('#inmo-results-grid');
                $grid.empty();
                
                if(!props || props.length === 0) {
                    $grid.html('<div class="empty-state">No se encontraron propiedades exactas.</div>');
                    return;
                }

                props.forEach(function(p) {
                    var html = `
                    <div class="inmo-card">
                        <div class="inmo-card-img" style="background-image: url('${p.img || 'https://via.placeholder.com/300'}');"></div>
                        <div class="inmo-card-body">
                            <div class="inmo-card-price">${p.price}</div>
                            <div class="inmo-card-title">${p.title}</div>
                            <div class="inmo-card-meta">
                                <span>🛏️ ${p.dorm}</span>
                                <span>🚿 ${p.banos}</span>
                            </div>
                            <a href="${p.link}" target="_blank" style="display:block; margin-top:10px; text-align:center; background:#f0f6fc; color:#2271b1; padding:5px; text-decoration:none; border-radius:4px; font-size:0.9em;">Ver Detalles</a>
                        </div>
                    </div>
                    `;
                    $grid.append(html);
                });
            }

            $('#inmo-send').click(function(){
                var txt = $('#inmo-input').val().trim();
                if(txt === '') return;

                addMsg(txt, 'user');
                $('#inmo-input').val('');
                addMsg('Pensando...', 'system');

                // CONEXIÓN CON N8N
                $.ajax({
                    url: webhookUrl,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ message: txt, sessionId: 'sess_'+Math.floor(Math.random()*10000) }),
                    success: function(res) {
                        $('.msg.system').remove();
                        
                        // 🔍 DEBUG: Mira la consola del navegador (F12) para ver qué llegó
                        console.log("Respuesta recibida de n8n:", res); 

                        // Intentamos encontrar el texto en varias variables posibles
                        var botText = res.output || res.text || res.response || res.message;

                        // Si es un objeto complejo (error raro), lo convertimos a texto
                        if (typeof botText === 'object') {
                            botText = JSON.stringify(botText);
                        }

                        // Si encontramos texto, lo mostramos
                        if (botText) {
                            addMsg(botText, 'bot');
                        } else {
                            // Si llegó vacío, avisamos
                            addMsg("Recibí una respuesta vacía de la IA. Revisa la consola (F12).", 'system');
                        }
                        
                        // Renderizar propiedades si vienen
                        if(res.properties && Array.isArray(res.properties)) {
                            renderProperties(res.properties);
                        }
                    },
                    error: function() {
                        $('.msg.system').remove();
                        addMsg('Error de conexión con el servidor IA.', 'system');
                    }
                });
            });

            $('#inmo-input').keypress(function(e){ if(e.which==13) $('#inmo-send').click(); });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}

new Inmo_App_Frontend();