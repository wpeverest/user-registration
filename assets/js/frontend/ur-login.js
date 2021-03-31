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
                console.log(res);
                $('#user-registration .user-registration-error').text(res.data);
                if (res.data.loggedin == true) {
                    document.location.href = ur_login_params.redirecturl;
                }
            }
        })

    });
});