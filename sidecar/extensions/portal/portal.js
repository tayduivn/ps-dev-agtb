(function(app) {
    var base_metadata = {
        _hash: '',
        "modules": {
            "Login": {
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
                        "meta": {
                            "buttons": [
                                {
                                    name: "login_button",
                                    type: "button",
                                    label: "Login",
                                    value: "login",
                                    primary: true,
                                    events: {
                                        click: "function(){ var self = this; " +
                                            "$('#content').hide(); " +
                                            "app.alert.show('login', {level:'process', title:'Loading', autoclose:false}); " +
                                            "var args={password:this.model.get(\"password\"), username:this.model.get(\"username\")}; " +
                                            "this.app.login(args, null, {error:function(){ app.alert.dismiss('login'); $('#content').show();" +
                                            "console.log(\"login failed!\");},  success:" +
                                            "function(){console.log(\"logged in successfully!\"); $(\".navbar\").show();" +
                                            "$(\"body\").attr(\"id\", \"\"); var app = self.app; " +
                                            "app.events.on('app:sync:complete', function() { " +
                                            "app.alert.dismiss('login'); $('#content').show();" +
                                            "}); " +
                                            "app.sync(" +
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
                    }
                },
                //Layouts map an action to a lyout that defines a set of views and how to display them
                //Different clients will get different layouts for the same actions
                "layouts": {
                    "login": {
                        "meta": {
                            //Default layout is a single view
                            "type": "simple",
                            "components": [
                                {view: "loginView"}
                            ]
                        }
                    }
                }
            }
        },
        'sugarFields': {
            "text": {
                "views": {
                    "detailView": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n",
                    "editView": "<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> " +
                            "<input type=\"text\" class=\"input-xlarge\" value=\"{{value}}\">  <p class=\"help-block\">" +
                            "<\/p> <\/div>",
                    "loginView":"<div class=\"controls\"><label class=\"hide\">{{label}}<\/label> " +
                            "<input type=\"text\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\">  <p class=\"help-block\">" +
                            "<\/p> <\/div>",
                    "default": "<span name=\"{{name}}\">{{value}}</span>"
                },
                "events": {
                },
                controller: "{" +
                    "render : function(){" +
                    "this.app.view.Field.prototype.render.call(this);" +
                    "if (!SUGAR.App.api.isAuthenticated()) { $(\".navbar\").hide(); $(\"body\").attr(\"id\", \"portal\"); }" +
                    "}}"
            },
            "password": {
                "views": {
                    "editView":"\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                            "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                            "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>",
                    "loginView": "<div class=\"control-group\">" +
                                                "<label class=\"hide\">{{label}}</label>" +
                                                "<input type=\"password\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\">" +
                                                "<p class=\"help-block\"><a href=\"#\" rel=\"popoverTop\" data-content=\"You need to contact your Sugar Admin to reset your password.\" data-original-title=\"Forgot Your Password?\">Forgot password?</a></p>" +
                                                "</div>"
                }
            },
            "button": {
                "views": {
                    "default":"<a href=\"{{#if route}}#{{buildRoute context model route.action route.options}}" +
                            "{{else}}javascript:void(0){{/if}}\" class=\"btn {{class}} {{#if primary}}btn-primary{{/if}}\">" +
                            "{{#if icon}}<i class=\"{{icon}}\"><\/i>{{/if}}{{label}}<\/a>\n"
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
                "<div>{{field ../../context ../../this ../../model}}</div>" +
                "{{/each}}" +
                "</div>          \n" +
                "{{/each}}" +
                "<div class=\"modal-footer\">\n" +
                "{{#each meta.buttons}}" +
                "{{field ../context ../this ../model}}" +
                "{{/each}}" +
                "</div>\n" +
                "</div>                             \n" +
                "</div>\n" +
                "</div>         \n" +
                "</form>",
            "header": "<div class=\"navbar navbar-fixed-top\">\n    <div class=\"navbar-inner\">\n      <div class=\"container-fluid\">\n        <a class=\"cube\" href=\"#\" rel=\"tooltip\" data-original-title=\"Dashboard\"></a>\n        <div class=\"nav-collapse\">\n          <ul class=\"nav\" id=\"moduleList\">\n              {{#each moduleList}}\n              <li {{{eq this ../currentModule \"class=\\\"active\\\"\" \"\"}}}>\n                <a href=\"#{{this}}\">{{this}}</a>\n              </li>\n              {{/each}}\n          </ul>\n          <ul class=\"nav pull-right\" id=\"userList\">\n            <li class=\"divider-vertical\"></li>\n            <li class=\"dropdown\">\n              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Current User <b class=\"caret\"></b></a>\n              <ul class=\"dropdown-menu\">\n                <li><a href=\"#logout\">Log Out</a></li>\n              </ul>\n            </li>\n            <li class=\"divider-vertical\"></li>\n     <li class=\"dropdown\" id=\"createList\">\n              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\"><i class=\"icon-plus icon-md\"></i> <b class=\"caret\"></b></a>\n              <ul class=\"dropdown-menu\">\n                  {{#each createListLabels}}\n                                <li>\n                                  <a href=\"#{{this}}/create\">{{this}}</a>\n                                </li>\n                                {{/each}}\n              </ul>\n            </li>\n          </ul>\n          <div id=\"searchForm\">\n            <form class=\"navbar-search pull-right\" action=\"\">\n              <input type=\"text\" class=\"search-query span3\" placeholder=\"Search\" data-provide=\"typeahead\" data-items=\"10\" >\n              <a href=\"\" class=\"btn\"><i class=\"icon-search\"></i></a>\n                <a href=\"#adminSearch\" class=\"pull-right advanced\" data-toggle=\"modal\" rel=\"tooltip\" title=\"Advanced Search Options\" id=\"searchAdvanced\"><i class=\"icon-cog\"></i></a>\n            </form>\n\n          </div>\n        </div><!-- /.nav-collapse -->\n      </div>\n    </div><!-- /navbar-inner -->\n  </div>",
            "subnav": "<div class=\"subnav\">" +
                "<div class=\"btn-toolbar pull-left\">" +
                "<h1>{{fieldWithName context this null \"name\"}}</h1>" +
                "</div>" +
                "<div class=\"btn-toolbar pull-right\">" +
                "<div class=\"btn-group\">" +
                "{{#each meta.buttons}}" +
                "{{field ../context ../this ../model}}  " +
                "{{/each}}" +
                "</div>" +
                "</div>" +
                "</div>"
        }
    };
    app.events.on("app:init", function() {
        app.metadata.set(base_metadata);
        app.data.declareModels(base_metadata);
    });

    app.view.Field=app.view.Field.extend({
        /**
         * Handles how validation errors are appended to the fields dom element
         *
         * By default errors are appended to the dom into a .help-block class if present
         * and the .error class is added to any .control-group elements in accordance with
         * bootstrap.
         *
         * @param {Object} errors hash of validation errors
         */
        handleValidationError: function(errors) {
            var self = this;

            this.$('.control-group').addClass("error");
            this.$('.help-block').html("");

            _.each(errors, function(errorContext, errorName) {
                self.$('.help-block').append("<br>"+app.error.getErrorString(errorName,errorContext));
            });
        }
    });

})(SUGAR.App);

