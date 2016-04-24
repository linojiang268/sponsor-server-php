@extends('layouts.main')
@section('styles')
    <link href="/css/welcome/index.css" rel="stylesheet">
@endsection
@section('scripts')
    <script src="/scripts/auth.js"></script>
@endsection

@section('content')
<div class="section-body">
</div>

<div class="modal fade" id="divAuthError" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">错误</h4>
            </div>
            <div class="modal-body text-danger">
                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                <span class="message"></span>                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">确定</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@if (Auth::user() == null)
    <div style="display: block;" id="loginDialog">
        <div class="modal-dialog" style="width:400px;"  role="document">
            <div>
                <div class="modal-header">
                    <h4 class="modal-title">
                        登录
                        <a href="javascript:{}" id="btnRegisterLink" style="margin-left: 290px;">注册</a>
                    </h4>
                </div>
                <form action="/web/login" id="login-form" method="post" class="form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label" for="username">用户名</label>
                            <input type="email" name="email" class="form-control" placeholder="电子邮箱" />
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password">密码</label>
                            <input type="password" name="password" class="form-control" placeholder="密码" />
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" checked="checked" name="remember"> 下次自动登陆
                            </label>
                        </div>
                        {!! csrf_field() !!}
                    </div>
                    <div class="modal-footer">
                        <span id="divLoginIndicator" style="display: none;"></span>
                        <button type="button" id="btnLogin" class="btn btn-primary">登录</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="registerDialog" style="display: none;">
        <div class="modal-dialog" style="width:400px;" role="document">
            <div>
                <div class="modal-header">
                    {{--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>--}}
                    <h4 class="modal-title">
                        注册
                        <a href="javascript:{}" id="btnLoginLink" style="margin-left: 290px;">登录</a>
                    </h4>
                </div>
                <form action="/web/register" id="register-form" method="post" class="form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label" for="username">赞助商名称</label>
                            <input type="text" name="name" class="form-control" placeholder="赞助商名称" />
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="email">电子邮箱</label>
                            <input type="email" name="email" class="form-control" placeholder="电子邮箱" />
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password">密码</label>
                            <input type="password" name="password" class="form-control" placeholder="密码" />
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="confirmpassword">确认密码</label>
                            <input type="password" name="confirmpassword" class="form-control" placeholder="密码" />
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="captcha" style="display: block;">验证码</label>
                            <input type="text" name="captcha" id="captcha-input" style=" height: 34px; padding: 6px 12px; font-size: 14px; line-height: 1.42857143; color: #555; background-color: #fff; background-image: none; border: 1px solid #ccc; border-radius: 4px; -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075); box-shadow: inset 0 1px 1px rgba(0,0,0,.075); -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s; -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s; transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
" placeholder="验证码" />
                            <a href="javascript:{}" id="registerCaptcha"><img src="/captcha" id="captcha-img" style="margin-left:30px;" /></a>
                        </div>

                        {!! csrf_field() !!}
                    </div>

                    <div class="modal-footer">
                        <span id="divRegisterIndicator" style="display: none;"></span>
                        <button type="button" id="btnRegister" class="btn btn-primary">注册</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection