jQuery(function ($) {
    $(document).on('click', '#user_ajax_login_submit', function (e) {
        e.preventDefault();
        var username = $('#username').val();
        var password = $('#password').val();
        var rememberme = $('#rememberme').val();
        var url = ur_login_params.ajax_url + '?action=user_registration_ajax_login_submit&security=' + ur_login_params.ur_login_form_save_nonce;
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                username: username,
                password: password,
                rememberme: rememberme
            },
            success: function (res) {

                if (res.success == false) {
                    $('#user-registration')
                        .find(".user-registration-error")
                        .remove();
                    $('#user-registration').append('<ul class="user-registration-error">' + res.data + '</ul>');

                } else {
                    document.location.href = ur_login_params.redirecturl;
                }
            }
        });
    });
});