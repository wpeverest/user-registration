
(function ($) {
    var login_validator = $('.user-registration-form-login');
    console.log(login_validator);
    if (login_validator.length) {
        login_validator.validate({
            rules: {
                username: {
                    required: true
                }
            }
        });
    }
})(jQuery);