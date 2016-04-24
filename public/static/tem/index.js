(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var Header = React.createClass({displayName: "Header",

    render: function() {
        return (
           React.createElement("header", null, 
                    React.createElement("nav", {className: "navbar navbar-default"}, 
                        React.createElement("div", {className: "container-fluid"}, 
                                React.createElement("div", {className: "navbar-header"}, 
                                    React.createElement("a", {className: "navbar-brand", href: "#"}, 
                                        "cooperation witness"
                                    )
                                )
                        )
                    )
          )
        );
    }

});

module.exports = Header;

},{}],2:[function(require,module,exports){
var Index = require('../page/index/main.jsx');

ReactDOM.render(React.createElement(Index, null),document.getElementById('container'));

},{"../page/index/main.jsx":6}],3:[function(require,module,exports){
var Contents = React.createClass({displayName: "Contents",

    render: function() {
        return (
            React.createElement("div", {className: "section-body"}, 
                React.createElement("p", {className: "logo-text"}, "Cooperation witness")
            )
        );
    }

});

module.exports = Contents;

},{}],4:[function(require,module,exports){
var Error = React.createClass({displayName: "Error",
	render: function() {
		var errorMess = this.props.error || '';
		return (
			React.createElement("div", {className: "error-mess"}, 
				 errorMess 
			)
		);
	}

});

module.exports = Error;

},{}],5:[function(require,module,exports){
var Error = require("./errorTip.jsx");
var Login = React.createClass({displayName: "Login",
      getInitialState: function(){
        return {
          error:""
        };
      },
	loginBtnHandler: function(){
		$loginPanel = $('.loginPanel');
		$loginPanel.fadeIn('fast');
	},
	closeHandler: function(){
		$closeBtn = $('#loginClose');
		$('.loginPanel').fadeOut('fast');
	},
      loginToList: function(){
           var server = H.server,
            _this = this;
            var params = {
              email: $('#username').val(),
              password: $('#pass').val(),
              _token: $('input[name="_token"]').val()
            };
            this.setState({error:' '});

            if ( !params.email ) {
              this.setState({error:"邮箱不能为空。"});
              return;
            }
            if ( !params.password ) {
              this.setState({error:"密码不能为空。"});
              return;
            }

            server.login(params , function(resp){
              if ( resp.code == 0 ) {
                _this.setState({error: resp.message });
                setTimeout(function(){
                   window.location.href="list";
                 }, 800);
              } else {
                _this.setState({error: resp.message});
                return false;
              }
            });
          return false; 
    },
	render: function(){
		return (
		      React.createElement("div", null, 
                    React.createElement("button", {type: "button", id: "loginBtn", className: "btn btn-success loginBtn", onClick: this.loginBtnHandler}, "登录"), 
                    React.createElement("div", {className: "loginPanel form-w", style: {display:"none"}}, 
                     React.createElement("span", {className: "closeBtn", id: "loginClose", onClick: this.closeHandler}, "x"), 
                        React.createElement("h3", null, "登录"), 
                        React.createElement(Error, {error: this.state.error}), 
                        React.createElement("form", null, 
                        React.createElement("div", {className: "form-group"}, 
                            React.createElement("input", {type: "text", className: "form-control", id: "username", placeholder: "邮箱"})
                          ), 
                          React.createElement("div", {className: "form-group"}, 
                            React.createElement("input", {type: "password", className: "form-control", id: "pass", placeholder: "密码"})
                          ), 
                          React.createElement("div", {className: "checkbox login-checkbox"}, 
                            React.createElement("label", null, 
                              React.createElement("input", {type: "checkbox"}), " 下次自动登录"
                            )
                          ), 
                         /*<button type="submit" className="btn btn-default btn-block">提交</button>*/
                          React.createElement("a", {href: "javascript:;", className: "btn btn-default btn-block", onClick: this.loginToList}, "提交")
                        )
                    )
               )
		);
	}
});

module.exports = Login;

},{"./errorTip.jsx":4}],6:[function(require,module,exports){
var PageIntro = require('./pageintro.jsx'),
      Header = require('../../components/header.jsx'),
      Contents = require('./contents.jsx'),
      Login = require('./login.jsx'),
      Regist = require('./regist.jsx');

var Index = React.createClass({displayName: "Index",
	componentDidMount: function(){
		var $body = $('#index');
		//$body.height($(window).height());
	},
	render: function(){
		return (
			React.createElement("div", {id: "index", className: "index-w jpanel", ref: "idw"}, 
				React.createElement(Header, null), 
				React.createElement(Contents, null), 
				React.createElement(Login, null), 
				React.createElement(Regist, null)
			)
		);
	}
});

module.exports = Index;

},{"../../components/header.jsx":1,"./contents.jsx":3,"./login.jsx":5,"./pageintro.jsx":7,"./regist.jsx":8}],7:[function(require,module,exports){
var PageIntro = React.createClass({displayName: "PageIntro",
	render: function () {
		return(
			React.createElement("div", {id: "section-index", className: "section-index"})
		);
	}
});
module.exports = PageIntro;

},{}],8:[function(require,module,exports){
var Error = require("./errorTip.jsx");
var Regist = React.createClass({displayName: "Regist",
      getInitialState: function(){
        return {
          error:''
        }
      },
	registBtnHandler: function(){
		$registPanel = $('.registPanel');
		$registPanel.fadeIn('fast');
	},
	closeHandler: function(){
		$closeBtn = $('#registClose');
		$('.registPanel').fadeOut('fast');
	},
      registToList: function(){
        var server = H.server,
            _this = this;
        var params = {
          name: $('#sname').val(),
          password: $('#spass').val(),
          email: $('#email').val(),
          captcha: $('#captcha-input').val(),
          _token: $('input[name="_token"]').val()
        };
        this.setState({error:' '});
        if ( !params.name ) {
          this.setState({error:"赞助方名称不能为空。"});
          return;
        }
        if ( !params.password ) {
          this.setState({error:"密码不能为空。"});
          return;
        }
        if ( !H.isEmail( params.email )) {
          this.setState({error:"邮箱错误。"});
          return;
        }
        if ( !params.captcha ) {
          this.setState({error:"验证码不能为空。"});
          return;
        }
        if ( params.password != $('#confirmpass').val() ) {
          this.setState({error:"两次输入的密码不同，请重新输入"});
          return;
        }
        server.regist(params , function(resp){
          if ( resp.code == 0 ) {
            _this.setState({error: resp.message });
            setTimeout(function(){
               window.location.href="list";
             }, 800);
          } else {
            _this.setState({error: resp.message});
            _this.captchaRefresh();
            return false;
          }
        });
          return false;
      },
      captchaChange: function(){
        this.captchaRefresh();
        this.setState({error:""});
      },
      captchaRefresh: function(){
        $(".captcha-loading").show();
        $("#captcha-img").attr("src","/captcha?"+Math.random());
        $("#captcha-input").val("");
      },
      componentDidMount: function(){
        $("#captcha-img")[0].onload = function(){
          console.log("captcha has loaded.");
          $(".captcha-loading").hide();
        }
      },
	render: function  () {
		return (
		      React.createElement("div", null, 
                    React.createElement("button", {type: "button", className: "btn btn-success registBtn", onClick: this.registBtnHandler}, "注册"), 
                    React.createElement("div", {className: "registPanel form-w", style: {display:"none"}}, 
                        React.createElement("span", {className: "closeBtn", id: "registClose", onClick: this.closeHandler}, "x"), 
                        React.createElement("h3", null, "注册coopreration witness"), 
                        React.createElement("form", null, 
                          React.createElement(Error, {error: this.state.error}), 
                          React.createElement("div", {className: "form-group"}, 
                            React.createElement("input", {type: "text", className: "form-control", id: "sname", placeholder: "赞助方名称"})
                          ), 
                          React.createElement("div", {className: "form-group"}, 
                            React.createElement("input", {type: "text", className: "form-control", id: "email", placeholder: "邮箱"})
                          ), 
                          React.createElement("div", {className: "form-group"}, 
                            React.createElement("input", {type: "password", className: "form-control", id: "spass", placeholder: "密码"})
                          ), 
                          React.createElement("div", {className: "form-group"}, 
                            React.createElement("input", {type: "password", className: "form-control", id: "confirmpass", placeholder: "确认密码"})
                          ), 
                          React.createElement("div", {className: "form-group captcha-w"}, 
                            React.createElement("input", {type: "text", className: "form-control captcha-input", id: "captcha-input", placeholder: "验证码"}), 
                            React.createElement("img", {className: "captcha-img", id: "captcha-img", src: "/captcha", alt: ""}), 
                            React.createElement("div", {className: "captcha-loading"}, React.createElement("img", {src: "/static/assets/images/loading.gif"})), 
                            React.createElement("a", {href: "javascript:;", className: "changed-btn", onClick: this.captchaChange}, "换一换")
                          ), 
                          React.createElement("a", {href: "javascript:;", className: "btn btn-default btn-block", onClick: this.registToList}, "提交")
                        )
                    )
               )
		);
	}
});


module.exports = Regist;

},{"./errorTip.jsx":4}]},{},[2]);
