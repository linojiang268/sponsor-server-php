<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sponsorship - @yield('title')</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    @yield('styles')
    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container" style="border-bottom: 1px solid #e5e5e5;">
        <div class="navbar-header">
          <a href="/" class="navbar-brand">赞助证</a>
        </div>
        <ul class="nav navbar-nav navbar-right">
        @if (Auth::user() == null)
          {{--<li><a href="#" class="btn" data-toggle="modal" data-target="#loginDialog" data-backdrop="static" data-keyboard="false">登录</a></li>--}}
          {{--<li><a href="#" class="btn" data-toggle="modal" data-target="#registerDialog" data-backdrop="static" data-keyboard="false">注册</a></li>--}}
       @else
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
              {{ Auth::user()->name }}
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu">
              {{--<li><a href="/profile">个人资料</a></li>--}}
              {{--<li role="separator" class="divider"></li>--}}
              <li><a href="/web/logout">退出</a></li>
            </ul>
          </li>
        @endif
        </ul>
    </div>

    <div class="container">
        @yield('content')
    </div>
    <script src="/scripts/jquery.min.js"></script>
    <script src="/scripts/bootstrap.min.js"></script>
    <script src="/scripts/lib.js"></script>
    @yield('scripts')
    </body>
</html>