jQuery(function ($) {
    $(document).on('click', '#user_ajax_login_submit', function (e) {
        e.preventDefault();
        var username = $('#username').val();
        var password = $('#password').val();
        // var nonce = $('#user-registration-login-nonce').val()
        var url = ur_login_params.ajax_url + '?action=user_registration_login_submit&security=' + ur_login_params.ur_login_form_save_data;
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                username: username,
                password: password,
                // nonce:user-registration-login,
            },
            success: function (res) {
                $('#status').text(res.data.message);
                if (res.data.loggedin == true) {
                    document.location.href = ur_login_params.redirecturl;
                }
            }
        })

    });
});