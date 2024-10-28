jQuery(document).ready(function($) {
    $('#send-btn').on('click', function() {
        var userMessage = $('#chat-input').val().trim();
        if (userMessage === "") {
            alert("Please enter a message.");
            return;
        }

        $('#chat-output').append('<p><strong>You:</strong> ' + userMessage + '</p>');
        $('#chat-output').append('<p><strong>Bot:</strong> <em>Loading...</em></p>');
        $('#send-btn').attr('disabled', true);

        $.ajax({
            url: '/wp-json/ai-chatbot/v1/get-response',
            type: 'POST',
            data: JSON.stringify({ 
                message: userMessage,
                new_api_key: $('input[name="ai_chatbot_openai_api_key"]').val()
            }),
            contentType: 'application/json',
            success: function(response) {
                $('#chat-output').children().last().html('<strong>Bot:</strong> ' + response);
                $('#send-btn').attr('disabled', false);
                $('#chat-input').val('');
            },
            error: function(jqXHR) {
                var msg = 'Unable to get response.';
                if (jqXHR.status === 500) {
                    msg = 'Server error. Please try again later.';
                }
                $('#chat-output').children().last().html('<strong>Error:</strong> ' + msg);
                $('#send-btn').attr('disabled', false);
            }
        });
    });

    $('#chat-input').keypress(function(e) {
        if (e.which == 13) {
            $('#send-btn').click();
            return false;
        }
    });
});







