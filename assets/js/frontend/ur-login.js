jQuery(function ($) {
    $(document).on('click', '#user_ajax_login_submit', function (e) {
        e.preventDefault();
        var username = $('#username').val();
        var password = $('#password').val();
        var url = ur_login_params.ajax_url + '?action=ur_login&security=' + ur_login_params.ur_login_form_save_data;
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                username: username,
                password: password,
            },
            success: function (data) {
                console.log(data);
            }
        })

    });

    $(function () {
        
    })
});