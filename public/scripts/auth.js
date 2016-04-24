function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

$(function () {
    function showAuthError(msg) {
        if (msg == '') {
            $('#divAuthError').modal('hide');
        } else {
            $('.message', '#divAuthError').html(msg);
            $('#divAuthError').modal('show');
        }
    }

    $('#loginDialog').on('hidden.bs.modal', function() {
    }).on('shown.bs.modal', function() {
        $('input[name=email]', this).focus();
    });

    $('#btnLogin').click(function () {
        var $form = $(this).closest("form"),
            $username = $('input[name=email]', $form),
            $password = $('input[name=password]', $form);

        if (!isEmail($username.val())) {
            showAuthError('用户名必须是您的电子邮箱');
            return;
        }

        if ($password.val().trim() == '') {
            showAuthError('密码不能为空');
            return;
        }

        ajaxSubmit($form, {
            beforeSend: function (xhr) {
                $('#divLoginIndicator').show();
            },
            success: function (resp) {
                $('#divLoginIndicator').hide();
                if (resp.code != 0) {
                    showAuthError(resp.message);
                } else { // login success
                    //$('#loginDialog').modal('hide');
                    window.location.href = '/web/sponsorships';
                }
            },
            statusCode: {
                500: function () {
                    showAuthError('服务器异常，请稍后重试。');
                }
            },
            error: function () {
                $('#divLoginIndicator').hide();
            }
        });
    });

    $('#registerCaptcha').click(function () {
        $("#captcha-img").attr("src", "/captcha?" + Math.random());
        $("#captcha-input").val("");
    });

    $('#btnRegister').click(function () {
        var $form = $(this).closest("form"),
            $name = $('input[name=name]', $form),
            $email = $('input[name=email]', $form),
            $password = $('input[name=password]', $form),
            $confirmpassword = $('input[name=confirmpassword]', $form),
            $captcha = $('input[name=captcha]', $form);

        if ($captcha.val() == '') {
            showAuthError('验证码不能为空');
            return;
        }
        if ($name.val() == '') {
            showAuthError('赞助商名称不能为空');
            return;
        }
        if ($password.val() != $confirmpassword.val()) {
            showAuthError('密码填写不一致');
            return;
        }
        if (!isEmail($email.val())) {
            showAuthError('电子邮箱格式不正确'+$email.val());
            return;
        }
        if ($password.val().trim() == '') {
            showAuthError('密码不能为空');
            return;
        }
        ajaxSubmit($form, {
            beforeSend: function (xhr) {
                $('#divRegisterIndicator').show();
            },
            success: function (resp) {
                $('#divRegisterIndicator').hide();
                if (resp.code != 0) {
                    showAuthError(resp.message);
                } else { // login success
                    window.location.href = '/';
                }
            },
            statusCode: {
                500: function () {
                    showAuthError('服务器异常，请稍后重试。');
                }
            },
            error: function () {
                $('#divRegisterIndicator').hide();
            }
        });
    });

    $('#btnRegisterLink').click(function(){
        $('#loginDialog').hide();
        $('#registerDialog').show();
    });

    $('#btnLoginLink').click(function(){
        $('#loginDialog').show();
        $('#registerDialog').hide();
    });

});