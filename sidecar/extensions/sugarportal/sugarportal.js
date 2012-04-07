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
                                        "this.app.sugarAuth.login(args, {success:" +
                                        "function(){console.log(\"logged in successfully!\");var app = self.app; app.sync(" +
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
                        "template": "<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> " +
                            "<input type=\"text\" class=\"input-xlarge\" value=\"{{value}}\">  <p class=\"help-block\">" +
                            "<\/p> <\/div>"
                    },
                    "default": {
                        "type": "basic",
                        "template": "<span name=\"{{name}}\">{{value}}</span>"
                    }
                },
                "events": {
                }
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
                        "template": "\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                            "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                            "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>"
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
            "loginView": "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a>&nbsp</h3>" +
                "<form name='{{name}}' class='well'>" +
                "{{#each meta.panels}}" +
                '<div class="{{../name}} panel">' +
                "<h4>{{label}}</h4>" +
                "{{#each fields}}" +
                "<div>{{sugarField ../../context ../../this ../../model}}</div>" +
                "{{/each}}" +
                "</div>" +
                "{{/each}}" + "{{#each meta.buttons}}" +
                "{{sugarField ../context ../this ../model}}" +
                "{{/each}}" + "</form>"
        }
    }
    //if (_.isEmpty(app.metadata.get())) {
        app.metadata.set(base_metadata);
        app.data.declareModels(base_metadata);
        app.template.load(base_metadata);
    //}
})
    (SUGAR.App);
