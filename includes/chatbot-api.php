<?php
add_action('rest_api_init', function () {
    register_rest_route('ai-chatbot/v1', '/get-response', array(
        'methods' => 'POST',
        'callback' => 'ai_chatbot_get_response',
        'permission_callback' => '__return_true',
    ));
});

function ai_chatbot_get_response($request) {
    $params = $request->get_json_params();
    $user_message = sanitize_text_field($params['message']);

    // Verificar si hay una nueva API Key
    if (isset($params['new_api_key']) && !empty($params['new_api_key'])) {
        $new_api_key = sanitize_text_field($params['new_api_key']);
        update_option('ai_chatbot_openai_api_key', $new_api_key);
    }

    $api_key = get_option('ai_chatbot_openai_api_key');

    if (empty($api_key)) {
        return new WP_Error('no_api_key', 'API Key not set.', array('status' => 500));
    }

    $body = json_encode(array(
        'model' => 'gpt-3.5-turbo',
        'messages' => array(
            array('role' => 'user', 'content' => $user_message)
        ),
        'max_tokens' => 50,
        'temperature' => 0.5,
    ));

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'body'    => $body,
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'timeout' => 30,
    ));

    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'API request failed.', array('status' => 500));
    }

    $result = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($result['error'])) {
        return new WP_Error('api_error', $result['error']['message'], array('status' => 500));
    }

    if (isset($result['choices'][0]['message']['content'])) {
        return rest_ensure_response(trim($result['choices'][0]['message']['content']));
    }

    return new WP_Error('invalid_response', 'Invalid API response.', array('status' => 500));
}





