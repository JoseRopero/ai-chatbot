<?php
/**
 * Plugin Name: AI Chatbot Plugin
 * Description: Chatbot usando OpenAI API.
 * Version: 2.1
 * Author: Jose Manuel Ropero
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/chatbot-api.php';

function ai_chatbot_shortcode() {
    return '<div id="ai-chatbot-container">
                <input type="text" id="chat-input" placeholder="Escribe tu pregunta..." />
                <button id="send-btn">Enviar</button>
                <div id="chat-output"></div>
            </div>';
}
add_shortcode('ai_chatbot', 'ai_chatbot_shortcode');

function ai_chatbot_enqueue_assets() {
    wp_enqueue_script('ai-chatbot-js', plugins_url('/js/ai-chatbot.js', __FILE__), array('jquery'), '2.1', true);
    wp_enqueue_style('ai-chatbot-css', plugins_url('/css/ai-chatbot.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'ai_chatbot_enqueue_assets');

function ai_chatbot_add_admin_menu() {
    add_options_page(
        'AI Chatbot Settings',
        'AI Chatbot',
        'manage_options',
        'ai-chatbot',
        'ai_chatbot_options_page'
    );
}
add_action('admin_menu', 'ai_chatbot_add_admin_menu');

function ai_chatbot_options_page() {
    ?>
    <div class="wrap">
        <h1>AI Chatbot Settings</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('ai_chatbot_options_group');
                do_settings_sections('ai-chatbot');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

function ai_chatbot_settings_init() {
    register_setting('ai_chatbot_options_group', 'ai_chatbot_openai_api_key', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ));

    add_settings_section(
        'ai_chatbot_section',
        __('OpenAI Configuration', 'ai-chatbot'),
        '__return_false',
        'ai-chatbot'
    );

    add_settings_field(
        'ai_chatbot_field_api_key',
        __('OpenAI API Key', 'ai-chatbot'),
        'ai_chatbot_field_api_key_cb',
        'ai-chatbot',
        'ai_chatbot_section'
    );
}
add_action('admin_init', 'ai_chatbot_settings_init');

function ai_chatbot_field_api_key_cb() {
    $api_key = get_option('ai_chatbot_openai_api_key');
    ?>
    <div style="margin-bottom: 15px;">
        <p>Ingresa tu propia <strong>API Key de OpenAI</strong>. Puedes obtenerla desde <a href="https://platform.openai.com/account/api-keys" target="_blank">aquí</a>.</p>
    </div>
    <?php
    if ($api_key) {
        $masked_key = substr($api_key, 0, 4) . str_repeat('*', strlen($api_key) - 8) . substr($api_key, -4);
        ?>
        <div style="margin-bottom: 10px;">
            <label for="ai_chatbot_field_api_key_display"><strong>API Key Actual:</strong></label><br />
            <input type="text" id="ai_chatbot_field_api_key_display" value="<?php echo esc_attr($masked_key); ?>" size="50" disabled />
        </div>
        <div style="margin-bottom: 15px;">
            <label for="ai_chatbot_field_api_key_new"><strong>Actualizar API Key:</strong></label><br />
            <input type="password" id="ai_chatbot_field_api_key_new" name="ai_chatbot_openai_api_key" value="" size="50" placeholder="Ingresa nueva API Key para actualizar" />
        </div>
        <?php
    } else {
        ?>
        <div style="margin-bottom: 15px;">
            <input type="password" id="ai_chatbot_field_api_key_new" name="ai_chatbot_openai_api_key" value="" size="50" placeholder="Ingresa tu API Key de OpenAI" />
        </div>
        <?php
    }
}







