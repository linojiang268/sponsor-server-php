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
var User = React.createClass({displayName: "User",
	logout: function () {
		window.location.href = "index.html";
	},
	render: function() {
		return (
			React.createElement("div", {className: "user-w"}, 
				React.createElement("div", {className: "btn-group"}, 
				  React.createElement("button", {type: "button", className: "btn btn-default dropdown-toggle", "data-toggle": "dropdown", "aria-haspopup": "true", "aria-expanded": "false"}, 
				    "HC ", React.createElement("span", {className: "caret"})
				  ), 
				  React.createElement("ul", {className: "dropdown-menu"}, 
				    React.createElement("li", null, React.createElement("a", {href: "#"}, "我的资料")), 
				    React.createElement("li", null, React.createElement("a", {href: "#", onClick: this.logout}, "退出登录"))
				  )
				)		
			)
		);
	}

});

module.exports = User;

},{}],3:[function(require,module,exports){
var SponsorPublish = require('../page/sponsorPublish/main.jsx');

ReactDOM.render(React.createElement(SponsorPublish, null),document.getElementById('container'));

},{"../page/sponsorPublish/main.jsx":4}],4:[function(require,module,exports){
var Header = require('../../components/header.jsx'),
    User = require('../../components/user.jsx'),
    Title = require('./title.jsx'),
    PublishForm = require('./publishForm.jsx');

var SponsorPublish = React.createClass({displayName: "SponsorPublish",
    render: function(){
        return (
            React.createElement("div", {id: "sponsorPublish", className: "sponsorPublish-w jpanel", ref: "idw"}, 
                React.createElement(Header, null), 
                React.createElement(User, null), 
                React.createElement(Title, null), 
                React.createElement(PublishForm, null)
            )
        );
    }
});

module.exports = SponsorPublish;

},{"../../components/header.jsx":1,"../../components/user.jsx":2,"./publishForm.jsx":5,"./title.jsx":6}],5:[function(require,module,exports){
var PublishForm = React.createClass({displayName: "PublishForm",

	render: function() {
		return (
			React.createElement("div", {className: "form-w publish-form"}, 
				React.createElement("form", {className: "form-horizontal"}, 
					React.createElement("div", {className: "form-group"}, 
						React.createElement("label", {htmlFor: "name", className: "col-sm-2 control-label"}, "项目", React.createElement("i", {className: "cred"}, "*")), 
						React.createElement("div", {className: "col-sm-10"}, 
							React.createElement("input", {type: "text", className: "form-control p-name", id: "name", placeholder: "项目名"})
						)
					), 
					React.createElement("div", {className: "form-group"}, 
						React.createElement("label", {className: "col-sm-2 control-label"}, "申请日期", React.createElement("i", {className: "cred"}, "*")), 
						React.createElement("div", {className: "col-sm-10"}, 
							React.createElement("input", {type: "text", className: "form-control form-control-inline", id: "begin_time", placeholder: "选择起始日期"}), " ~ ", 
							React.createElement("input", {type: "text", className: "form-control form-control-inline", id: "end_time", placeholder: "选择结束日期"})
						)
					), 
					React.createElement("div", {className: "form-group"}, 
						React.createElement("label", {htmlFor: "condition", className: "col-sm-2 control-label"}, "申请条件"), 
						React.createElement("textarea", {name: "condition", id: "condition", cols: "55", rows: "4", placeholder: "请简要描述申请此赞助需要满足的条件，供社团参与评估。"})
					), 
					React.createElement("div", {className: "form-group"}, 
						React.createElement("label", {htmlFor: "intro", className: "col-sm-2 control-label"}, "简介 ", React.createElement("i", {className: "cred"}, "*")), 
						React.createElement("textarea", {name: "intro", id: "intro", cols: "55", rows: "4", placeholder: "请简要描述可提供的赞助。"})
					), 
					React.createElement("div", {className: "form-group"}, 
						React.createElement("label", {className: "col-sm-2 control-label"}), 
						React.createElement("a", {href: "javascript:;", id: "saveBtn", className: "btn btn-default"}, "保存为草稿"), 
						React.createElement("a", {href: "javascript:;", id: "publishBtn", className: "btn btn-default publishBtn"}, "发布")
					)
				)
			)
		);
	}

});

module.exports = PublishForm;

},{}],6:[function(require,module,exports){
var Title = React.createClass({displayName: "Title",

	render: function() {
		return (
			React.createElement("div", {className: "c-title-w"}, 
				React.createElement("h3", {className: "c-title"}, "发布新赞助"), 
				React.createElement("a", {className: "toListBtn", href: "sponsorList.html"}, "我的赞助")
			)
		);
	}

});

module.exports = Title;

},{}]},{},[3]);
