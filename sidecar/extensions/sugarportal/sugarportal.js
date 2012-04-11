(function(app) {
    var base_metadata = {
        _hash: '',
        "modules": {
            "Home": {
                "fields": {
                    "username": {
                        "name": "username",
                        "type": "varchar"
                    },
                    "password": {
                        "name": "password",
                        "type": "password"
                    }
                },
                "views": {
                    "loginView": {
                        "buttons": [
                            {
                                name: "login_button",
                                type: "button",
                                label: "Login",
                                value: "login",
                                primary: true,
                                events: {
                                    click: "function(){ var self = this; " +
                                        " var args={password:this.model.get(\"password\"), username:this.model.get(\"username\")}; " +
                                        "this.app.api.login(args, null, {error:function(){console.log(\"login failed!\");},  success:" +
                                        "function(){console.log(\"logged in successfully!\"); $(\".navbar\").show(); $(\"body\").attr(\"id\", \"\"); var app = self.app; app.sync(" +
                                        "function(){console.log(\"sync success firing\");}); }" +
                                        "});" +
                                        "}"
                                }
                            }
                        ],
                        "panels": [
                            {
                                "label": "Login",
                                "fields": [
                                    {name: "username", label: "Username"},
                                    {name: "password", label: "Password"}
                                ]
                            }
                        ]

                    }

                },
                //Layouts map an action to a lyout that defines a set of views and how to display them
                //Different clients will get different layouts for the same actions
                "layouts": {
                    "login": {
                        //Default layout is a single view
                        "type": "simple",
                        "components": [
                            {view: "loginView"}
                        ]
                    }
                }
            }
        },
        'sugarFields': {
            "text": {
                "views": {
                    "detailView": {
                        "type": "basic",
                        "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n"
                    },
                    "editView": {
                        "type": "basic",
                        "template": "<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> " +
                            "<input type=\"text\" class=\"input-xlarge\" value=\"{{value}}\">  <p class=\"help-block\">" +
                            "<\/p> <\/div>"
                    },
                    "loginView": {
                        "type": "basic",
                        "template": "<div class=\"controls\">" +
                            "<label for=\"loginUsername\" class=\"hide\">{{label}}</label>" +
                            "<input type=\"text\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\"><p class=\"help-block\">{{help}}</p>" +
                        "</div>"
                    },
                    "default": {
                        "type": "basic",
                        "template": "<span name=\"{{name}}\">{{value}}</span>"
                    }
                },
                "events": {
                },
                controller: "{" +
                    "render : function(){" +
                    "this.app.sugarField.base.prototype.render.call(this);" +
                    "if (!SUGAR.App.api.isAuthenticated()) { $(\".navbar\").hide(); $(\"body\").attr(\"id\", \"portal\"); }" +
                    "}}"
            },
            "password": {
                "views": {
                    "editView": {
                        "type": "basic",
                        "template": "\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                            "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                            "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>"
                    },
                    "loginView": {
                        "type": "basic",
                        "template":  "<div class=\"control-group\">" +
                            "<label class=\"hide\">{{label}}</label>" +
                            "<input type=\"password\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\">" +
                            "<p class=\"help-block\"><a href=\"#\" rel=\"popoverTop\" data-content=\"You need to contact your Sugar Admin to reset your password.\" data-original-title=\"Forgot Your Password?\">Forgot password?</a></p>" +
                            "</div>"
                    }
                }
            },
            "button": {
                "views": {
                    "default": {
                        "type": "basic",
                        "template": "<a href=\"{{#if route}}#{{buildRoute context model route.action route.options}}" +
                            "{{else}}javascript:void(0){{/if}}\" class=\"btn {{class}} {{#if primary}}btn-primary{{/if}}\">" +
                            "{{#if icon}}<i class=\"{{icon}}\"><\/i>{{/if}}{{label}}<\/a>\n"
                    }
                }
            }
        },
        'viewTemplates': {
            "loginView": "<form name='{{name}}'>" +
                "<div class=\"container welcome\">\n" +
                "<div class=\"row\">\n" +
                "<div class=\"span4 offset4 thumbnail\">\n" +
                "<div class=\"modal-header tcenter\">\n" +
                "<h2 class=\"brand\">SugarCRM</h2>\n" +
                "</div>\n" +
                "{{#each meta.panels}}" +
                "<div class=\"modal-body tcenter\">\n" +
                "{{#each fields}}\n" +
                "<div>{{sugarField ../../context ../../this ../../model}}</div>" +
                "{{/each}}" +
                "</div>          \n" +
                "{{/each}}" +
                "<div class=\"modal-footer\">\n" +
                "{{#each meta.buttons}}" +
                "{{sugarField ../context ../this ../model}}" +
                "{{/each}}" +
                "</div>\n" +
                "</div>                             \n" +
                "</div>\n" +
                "</div>         \n" +
                "</form>"
        }
    }
    //if (_.isEmpty(app.metadata.get())) {
    app.metadata.set(base_metadata);
    app.data.declareModels(base_metadata);
    app.template.load(base_metadata);
    //}
})
    (SUGAR.App);
