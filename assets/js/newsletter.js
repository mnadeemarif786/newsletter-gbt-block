jQuery(document).ready(function($) {
    $('.newsletter-form .submit-btn').on('click', function(e) {
        e.preventDefault();
        const $form = $(this).closest('.newsletter-form');
        const name = $form.find('input[name="user_name"]').val().trim();
        const email = $form.find('input[name="user_email"]').val().trim();
        const $response = $form.find('.response-msg');
        $response.text('').removeClass('error success');
        if (!name || !email) {
            $response.text('Both name and email are required.').addClass('error');
            return;
        }
        const emailPattern = /^[^@]+@[^@]+\.[^@]+$/;
        if (!emailPattern.test(email)) {
            $response.text('Please enter a valid email address.').addClass('error');
            return;
        }
        $.ajax({
            type: 'POST',
            url: newsletter_ajax.ajaxurl,
            data: {
                action: 'submit_gtb_newsletter',
                _ajax_nonce: newsletter_ajax._ajax_nonce,
                name: name,
                email: email
            },
            success: function(response) {
                if (response.success) {
                    $response.text(response.data.message).addClass('success');
                    $form.find('input').val('');
                } else {
                    $response.text(response.data.message).addClass('error');
                }
            },
            error: function() {
                $response.text('Something went wrong. Please try again.').addClass('error');
            }
        });
    });
});
