(function(app) {
    var base_metadata = {
        _hash: '',
        "modules": {
            "Login": {
                "fields": {
                    "username": {
                        "name": "username",
                        "type": "varchar",
                        "required": true
                    },
                    "password": {
                        "name": "password",
                        "type": "password",
                        "required": true
                    }
                },
                "views": {
                    "loginView": {
                        "meta": {
                            "buttons": [
                                {
                                    name: "login_button",
                                    type: "button",
                                    label: "Log In",
                                    'class': "login-submit",
                                    value: "login",
                                    primary: true,
                                    events: {
                                        click: "function(){ var self = this; " +
                                            "if(this.model.isValid()) {" +
                                            "$('#content').hide(); " +
                                            "app.alert.show('login', {level:'process', title:'Loading', autoclose:false}); " +
                                            "var args={password:this.model.get(\"password\"), username:this.model.get(\"username\")}; " +
                                            "this.app.login(args, null, {error:"+
                                            "function(){ app.alert.dismiss('login'); $('#content').show();" +
                                            "console.log(\"login failed!\");},  success:" +
                                            "function(){console.log(\"logged in successfully!\"); $(\".navbar\").show();" +
                                            "$(\"body\").attr(\"id\", \"\"); var app = self.app; " +
                                            "app.events.on('app:sync:complete', function() { " +
                                            "app.alert.dismiss('login'); $('#content').show();" +
                                            "}); " +
                                            "}" +
                                            "});" +
                                            "}" +
                                            "}"
                                    }
                                },
                                {
                                    name: "signup_button",
                                    type: "button",
                                    label: "Sign Up",
                                    value: "signup",
                                    'class': 'pull-left',
                                    events: {
                                        click: "function(){ " +
                                            "app.router.navigate('#signup');" +
                                            "app.router.start();" +
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
                        },
                        controller: "{" +
                            "render: function(data) { " +
                            "if (app.config && app.config.logoURL) {" +
                            "this.logoURL=app.config.logoURL" +
                            "}" +
                            "app.view.View.prototype.render.call(this);" +
                            "if (!SUGAR.App.api.isAuthenticated()) { $(\".navbar\").hide(); $(\"footer\").hide(); }" +
                            "return this;" +
                            "}" +
                            "}"
                    }
                },
                "layouts": {
                    "login": {
                        "meta": {
                            //Default layout is a single view
                            "type": "simple",
                            "components": [
                                {view: "loginView"}
                            ]
                        }
                    },
                    "signup": {
                        "meta": {
                            //Default layout is a single view
                            "type": "simple",
                            "components": [
                                {view: "signupView"}
                            ]
                        }
                    }
                }
            },
            "Error": {
                "views": {
                    "errorView": {
                        "meta": {},
                        "template":  
                            "<div class='container-fluid'>" +
                                    "<div class='row-fluid'>" +
                                        "<div class='span7'>" +
                                            "<div class='card2'>" +
                                                "<div class='row-fluid'>" +
                                                    "<div class='span4'><h1>{{ this.model.attributes.type}}</h1></div>" +
                                                    "<div class='span8'>" +
                                                        "<p><strong>{{ this.model.attributes.title }}</strong><br>" +
                                                        "{{ this.model.attributes.message }}</p>" +
                                                    "</div>" +
                                                "</div>" +
                                            "</div>" +
                                        "</div>" +
                                    "</div>" +
                                "</div>",
                        controller: "{" +
                            "initialize: function(options) { " +
                                "app.view.View.prototype.initialize.call(this, options);" +
                            "}," +
                            "render: function(data) { " +
                                "var self = this, attributes = {};" +
                                "if(this.context.get('errorType')) {" +
                                    "attributes = this.getErrorAttributes(); " +
                                    "this.model.set(attributes); " +
                                "}" +
                                "app.view.View.prototype.render.call(this);" +
                            "}," +
                            "getErrorAttributes: function() {" +
                                "var attributes = {}; "+
                                "if(this.context.get('errorType') ==='404') {" +
                                    "attributes = {" +
                                        "title: 'HTTP: 404 Not Found'," +
                                        "type: '404'," +
                                        "message: \"We're sorry but the resource you asked for cannot be found.\"" +
                                    "};" +
                                "} else if(this.context.get('errorType') ==='500') { " +
                                    "attributes = {" +
                                        "title: 'HTTP: 500 Internal Server Error'," + 
                                        "type: '500'," +
                                        "message: 'There was an error on the server. Please contact technical support.'" +
                                    "};" +
                                "} else {" +
                                    "attributes = { " +
                                        "title: 'Unknown Error', " +
                                        "type: 'Unknown'," +
                                        "message: 'Unknown error.'" +
                                    "};" +
                                "} " +
                                "return attributes;" +
                            "}" +
                        "}"
                    }
                },
                "layouts": {
                    "error": {
                        "meta": {
                            "type": "simple",
                            "components": [
                                {view: "errorView"}
                            ]
                        }
                    }
                }
            },
            "Signup": {
                "fields": {
                    "first_name": {
                        "name": "first_name",
                        "type": "varchar",
                        "required": true
                    },
                    "last_name": {
                        "name": "last_name",
                        "type": "varchar",
                        "required": true
                    },
                    "email": {
                        "name": "email",
                        "type": "email",
                        "required": true
                    },
                    "phone_work": {
                        "name": "phone_work",
                        "type": "phone"
                    },
                    "state": {
                        "name": "state",
                        "type": "enum",
                        "options": "state_dom"
                    },
                    "country": {
                        "name": "country",
                        "type": "enum",
                        "options": "countries_dom",
                        "required": true
                    },
                    "company": {
                        "name": "company",
                        "type": "varchar",
                        "required": true
                    },
                    "jobtitle": {
                        "name": "jobtitle",
                        "type": "varchar"
                    },
                    "hr1": {
                        "name": "hr1",
                        "type": "hr"
                    }
                },
                "views": {
                    "signupView": {
                        "meta": {
                            "buttons": [
                                {     
                                    name: "cancel_button",
                                    type: "button",
                                    label: "Cancel",
                                    value: "signup",
                                    primary: false,
                                    events: {
                                        click: "function(){" +
                                            "app.router.goBack();" +
                                            "}"
                                    } 
                                },
                                {
                                    name: "signup_button",
                                    type: "button",
                                    label: "Sign Up",
                                    value: "signup",
                                    primary: true,
                                    events: {
                                        click: "" +
                                            "function(){ var self = this; " +
                                            "var oEmail = this.model.get(\"email\");" +
                                            "if (oEmail) {" +
                                            "   this.model.set({\"email\": [{\"email_address\":oEmail}]}, {silent: true});" +
                                            "}" +
                                            "var validFlag = this.model.isValid();" +
                                            " this.model.set({\"email\":oEmail}, {silent: true});" +
                                            "   if(validFlag) {" +
                                            "   $('#content').hide(); " +
                                            "   app.alert.show('signup', {level:'process', title:'Registering', autoClose:false}); " +
                                            "   var contactData={" +
                                            "       first_name:this.model.get(\"first_name\"), " +
                                            "       last_name:this.model.get(\"last_name\")," +
                                            "       email:this.model.get(\"email\")," +
                                            "       phone_work:this.model.get(\"phone_work\")," +
                                            "       state:this.model.get(\"state\")," +
                                            "       country:this.model.get(\"country\")," +
                                            "       company:this.model.get(\"company\")," +
                                            "       jobtitle:this.model.get(\"jobtitle\")" +
                                            "   }; " +
                                            "   this.app.api.signup(contactData, null, " +
                                            "   {" +
                                            "       error:function(){ app.alert.dismiss('signup'); $('#content').show(); },  " +
                                            "       success:function(){" +
                                            "           app.alert.dismiss('signup');" +
                                            "           $(\".modal-footer\").hide();" +
                                            "           $(\".modal-body\").html('<div class=\"alert alert-success tleft\">" +
                                            "               <p><strong>Thank you for signing up!</strong></p><p>" +
                                            "               A customer service representative will contact you shortly to configure your account.</p>" +
                                            "               </div>" +
                                            "           ');" +
                                            "           $('#content').show();" +
                                            "       }" +
                                            "   });" +
                                            "   }" +
                                            "}"
                                    }
                                }
                            ],
                            "panels": [
                                {
                                    "label": "Login",
                                    "fields": [
                                        {name: "first_name", label: "First name"},
                                        {name: "last_name", label: "Last name"},
                                        {name: "hr1", label: ""},
                                        {name: "email", label: "Email"},
                                        {name: "phone_work", label: "(###) ###-#### (optional)"},
                                        {name: "country", label: "Country"},
                                        {name: "state", label: "State"},
                                        {name: "hr1", label: ""},
                                        {name: "company", label: "Company"},
                                        {name: "jobtitle", label: "Job title (optional)"}
                                    ]
                                }
                            ]
                        },
                        controller: "{" +
                            "stateField: function() { return this.$el.find('select[name=state]'); }," +
                            "countryField: function() { return this.$el.find('select[name=country]'); }," +
                            "toggleStateField: function() {" +
                            "if (this.countryField().val()=='USA') {" +
                            "this.stateField().parent().show();" +
                            "} else {" +
                            "this.stateField().parent().hide();" +
                            "this.context.attributes.model.attributes.state = undefined;" +
                            "}" +
                            "}," +
                            "render: function(data) { " +
                            "var that  = this;" +
                            "app.view.View.prototype.render.call(this);" +
                            "that.toggleStateField();" +
                            "this.countryField().on(\"change\", function(ev) { that.render(); });" +
                            "return this;" +
                            "}" +
                            "}"
                    }
                },
                //Layouts map an action to a lyout that defines a set of views and how to display them
                //Different clients will get different layouts for the same actions
                "layouts": {
                    "signup": {
                        "meta": {
                            //Default layout is a single view
                            "type": "simple",
                            "components": [
                                {view: "signupView"}
                            ]
                        }
                    }
                }
            }
        },
        'fields': {
            "text": {
                "templates": {
                    "loginView": "<div class=\"control-group\"><label class=\"hide\">{{label}}<\/label> " +
                        "<div class=\"controls\">\n" +
                        "<input type=\"text\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\"></div>  <p class=\"help-block\">" +
                        "<\/p> <\/div>",
                    "signupView": "<div class=\"control-group\"><label class=\"hide\">{{label}}<\/label> " +
                        "<div class=\"controls\">\n" +
                        "<input type=\"text\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\"></div>  <p class=\"help-block\">" +
                        "<\/p> <\/div>"
                }
            },
            "password": {
                "templates": {
                    "loginView": "<div class=\"control-group\">" +
                        "<label class=\"hide\">{{label}}</label>" +
                        "<div class=\"controls\">\n" +
                        "<input type=\"password\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\">\n  <\/div>\n" +
                        "<p class=\"help-block\"></p>" +
                        "</div>"
                }
            },
            "button": {
                "templates": {
                    "default": "<a href=\"{{#if def.route}}#{{buildRoute context model def.route.action def.route.options}}" +
                        "{{else}}javascript:void(0){{/if}}\" class=\"btn {{def.class}} {{#if def.primary}}btn-primary{{/if}}\">" +
                        "{{#if def.icon}}<i class=\"{{def.icon}}\"><\/i>{{/if}}{{label}}<\/a>\n"
                }
            },
            "hr": {
                "templates": {
                    "default": "<hr>\n"
                }
            },
            "enum": {
                "templates": {
                    "signupView": "<div class=\"control-group\"><label class=\"hide\" for=\"input01\">{{label}}<\/label> " +
                        "<select data-placeholder=\"{{label}}\" name=\"{{name}}\"><option value=\"\" selected></option>{{#eachOptions def.options}}<option value=\"{{{this.key}}}\" {{#has this.key ../value}}selected{{/has}}>{{this.value}}</option>{{/eachOptions}}</select>  <p class=\"help-block\">" +
                        "<\/p> <\/div>",
                    "default": ""
                },
                controller: "{" +
                    "fieldTag:\"select\",\n" +
                    "render:function(){" +
                    "   this.app.view.Field.prototype.render.call(this);" +
                    "   this.$('select').chosen();" +
                    "   return this;" +
                    "}\n" +
                    "}"
            },
            "email": {
                "templates": {
                    "loginView": "<div class=\"control-group\"><label class=\"hide\">{{label}}<\/label> " +
                        "<div class=\"controls\">\n" +
                        "<input type=\"text\" class=\"center textField\" value=\"{{value}}\" placeholder=\"{{label}}\"></div>  <p class=\"help-block\">" +
                        "<\/p> <\/div>",
                    "signupView": "<div class=\"control-group\"><label class=\"hide\">{{label}}<\/label> " +
                        "<div class=\"controls\">\n" +
                        "<input type=\"text\" class=\"center textField\" value=\"{{value}}\" placeholder=\"{{label}}\"></div>  <p class=\"help-block\">" +
                        "<\/p> <\/div>"
                }
            },
            "phone": {
                "templates": {
                    "loginView": "<div class=\"control-group\"><label class=\"hide\">{{label}}<\/label> " +
                        "<div class=\"controls\">\n" +
                        "<input type=\"text\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\"></div>  <p class=\"help-block\">" +
                        "<\/p> <\/div>",
                    "signupView": "<div class=\"control-group\"><label class=\"hide\">{{label}}<\/label> " +
                        "<div class=\"controls\">\n" +
                        "<input type=\"text\" class=\"center\" value=\"{{value}}\" placeholder=\"{{label}}\"></div>  <p class=\"help-block\">" +
                        "<\/p> <\/div>"
                }
            }
        },
        'views': {
            "loginView": {
                templates: {
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
                        "<div>{{field ../../this ../../model}}</div>" +
                        "{{/each}}" +
                        "<p class=\"help-block\"><a rel=\"popoverTop\" data-content=\"You need to contact your Sugar Admin to reset your password.\" data-original-title=\"Forgot Your Password?\">Forgot password?</a></p>" +
                        "</div>          \n" +
                        "{{/each}}" +
                        "<div class=\"modal-footer\">\n" +
                        "{{#each meta.buttons}}" +
                        "{{field ../this ../model}}" +
                        "{{/each}}" +
                        "</div>\n" +
                        "</div>                             \n" +
                        "</div>\n" +
                        "</div>         \n" +
                        "</form>"
                }
            },
            "header": {
                templates: {
                    "header": "<div class=\"navbar navbar-fixed-top\">\n    <div class=\"navbar-inner\">\n      <div class=\"container-fluid\">\n        <a class=\"cube\" href=\"#\" rel=\"tooltip\" title=\"Dashboard\"></a>\n        <div class=\"nav-collapse\">\n          <ul class=\"nav\" id=\"moduleList\">\n              {{#each moduleList}}\n              <li {{#eq this ../module}}class=\"active\"{{/eq}}>\n                <a href=\"#{{this}}\">{{this}}</a>\n              </li>\n              {{/each}}\n          </ul>\n          <ul class=\"nav pull-right\" id=\"userList\">\n            <li class=\"divider-vertical\"></li>\n            <li class=\"dropdown\">\n              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Current User <b class=\"caret\"></b></a>\n              <ul class=\"dropdown-menu\">\n                <li><a href=\"#profile\">Profile</a></li>\n               <li><a href=\"#logout\">Log Out</a></li>\n              </ul>\n            </li>\n            <li class=\"divider-vertical\"></li>\n     <li class=\"dropdown\" id=\"createList\">\n              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\"><i class=\"icon-plus icon-md\"></i> <b class=\"caret\"></b></a>\n              <ul class=\"dropdown-menu\">\n                  {{#each createListLabels}}\n                                <li>\n                                  <a href=\"#{{this.module}}/create\">{{this.label}}</a>\n                                </li>\n                                {{/each}}\n              </ul>\n            </li>\n          </ul>\n          <div id=\"searchForm\">\n            <form class=\"navbar-search pull-right\" action=\"\">\n      <input type=\"text\" class=\"search-query span3\" placeholder=\"Search\" data-provide=\"typeahead\" data-items=\"10\" >\n              <a href=\"\" class=\"btn\"><i class=\"icon-search\"></i></a>\n      </form>\n\n          </div>\n        </div><!-- /.nav-collapse -->\n      </div>\n    </div><!-- /navbar-inner -->\n  </div>"
                }
            },
            "footer": {
                templates: {
                    "footer": "<footer>\n" +
                    "    <div class=\"row-fluid\">\n" +
                    "        <div class=\"span3\"><a href=\"\" class=\"logo\">SugarCRM</a></div>\n" +
                    "        <div class=\"span9\">\n" +
                    "            <div class=\"btn-toolbar pull-right\">\n" +
                    "                <div class=\"btn-group\">\n" +
                    "                    <a title=\"Activity View Tour\" class=\"btn\" id=\"tour\"><i class=\"icon-road\"></i>\n" +
                    "                        Tour</a>\n" +
                    "                </div>\n" +
                    "                <div class=\"btn-group\">\n" +
                    "                    <a id=\"print\" class=\"btn\"><i class=\"icon-print\"></i> Print</a>\n" +
                    "                    <a id=\"top\" class=\"btn\"><i class=\"icon-arrow-up\"></i> Top</a>\n" +
                    "                </div>\n" +
                    "            </div>\n" +
                    "        </div>\n" +
                    "    </div>\n" +
                    "</footer>\n" +
                    "\n" +
                    "<!-- Tour Guide -->\n" +
                    "<div class=\"modal hide\" id=\"systemTour\">\n" +
                    "  <div class=\"modal-header\">\n" +
                    "    <a class=\"close\" data-dismiss=\"modal\">?</a>\n" +
                    "    <h3>Tour the Portal</h3>\n" +
                    "  </div>\n" +
                    "  <div class=\"modal-body\">\n" +
                    "    <p>The primary actions to get things done in the portal.</p>\n" +
                    "  </div>\n" +
                    "  <div class='pointsolight'>\n" +
                    "  <div id=\"tourCube\" class=\"tourSee\"><span>Dashboard</span></div>\n" +
                    "  <div id=\"tourCreate\" class=\"tourSee\"><span>Quick create</span></div>\n" +
                    "  <div id=\"tourUser\" class=\"tourSee\"><span>User admin</span></div>\n" +
                    "  <div id=\"tourModules\" class=\"tourSee\"><span>Modules</span></div>\n" +
                    "  <div id=\"tourUSearch\" class=\"tourSee\"><span>Universal search</span></div>\n" +
                    "  <div id=\"tourSort\" class=\"tourSee\"><span>Sort columns</span></div>\n" +
                    "  <div id=\"tourRefine\" class=\"tourSee\"><span>Filter<br>items</span></div>\n" +
                    "  <div id=\"tourAdd\" class=\"tourSee\"><span>Add<br>item</span></div>\n" +
                    "  </div>\n" +
                    "</div>\n"
                }
            },
            "signupView": {
                templates: {
                    "signupView": "<form name='{{name}}'>" +
                        "<div class=\"container welcome\">\n" +
                        "<div class=\"row\">\n" +
                        "<div class=\"span4 offset4 thumbnail\">\n" +
                        "<div class=\"modal-header tcenter\">\n" +
                        "<h2 class=\"brand\">SugarCRM</h2>\n" +
                        "</div>\n" +
                        "{{#each meta.panels}}" +
                        "<div class=\"modal-body tcenter\">\n" +
                        "{{#each fields}}\n" +
                        "{{field ../../this ../../model}}" +
                        "{{/each}}" +
                        "</div>          \n" +
                        "{{/each}}" +
                        "<div class=\"modal-footer\">\n" +
                        "{{#each meta.buttons}}" +
                        "{{field ../this ../model}}" +
                        "{{/each}}" +
                        "</div>\n" +
                        "</div>                             \n" +
                        "</div>\n" +
                        "</div>         \n" +
                        "</form>"
                }
            },
            "subnav": {
                "templates": {
                    "subnav": "<div class=\"subnav\">" +
                        "<div class=\"btn-toolbar pull-left\">" +
                        "<h1>{{fieldWithName this \"name\"}}</h1>" +
                        "</div>" +
                        "<div class=\"btn-toolbar pull-right\">" +
                        "<div class=\"btn-group\">" +
                        "{{#each meta.buttons}}" +
                        "{{field ../this ../model}}  " +
                        "{{/each}}" +
                        "</div>" +
                        "</div>" +
                        "</div>"
                }
            },
            "errorView": {
                "templates": {
                    "errorView": "<div class='container-fluid'>" +
                            "<div class='row-fluid'>" +
                                "<div class='span7'>" +
                                    "<div class='card2'>" +
                                        "<div class='row-fluid'>" +
                                            "<div class='span4'><h1>{{ this.model.attributes.type}}</h1></div>" +
                                            "<div class='span8'>" +
                                                "<p><strong>{{ this.model.attributes.title }}</strong><br>" +
                                                "{{ this.model.attributes.message }}</p>" +
                                            "</div>" +
                                        "</div>" +
                                    "</div>" +
                                "</div>" +
                            "</div>" +
                        "</div>"
                }
            }
        },
        "appListStrings": {
            "state_dom": {
                "AL": "Alabama",
                "AK": "Alaska",
                "AZ": "Arizona",
                "AR": "Arkansas",
                "CA": "California",
                "CO": "Colorado",
                "CT": "Connecticut",
                "DE": "Delaware",
                "DC": "District Of Columbia",
                "FL": "Florida",
                "GA": "Georgia",
                "HI": "Hawaii",
                "ID": "Idaho",
                "IL": "Illinois",
                "IN": "Indiana",
                "IA": "Iowa",
                "KS": "Kansas",
                "KY": "Kentucky",
                "LA": "Louisiana",
                "ME": "Maine",
                "MD": "Maryland",
                "MA": "Massachusetts",
                "MI": "Michigan",
                "MN": "Minnesota",
                "MS": "Mississippi",
                "MO": "Missouri",
                "MT": "Montana",
                "NE": "Nebraska",
                "NV": "Nevada",
                "NH": "New Hampshire",
                "NJ": "New Jersey",
                "NM": "New Mexico",
                "NY": "New York",
                "NC": "North Carolina",
                "ND": "North Dakota",
                "OH": "Ohio",
                "OK": "Oklahoma",
                "OR": "Oregon",
                "PA": "Pennsylvania",
                "RI": "Rhode Island",
                "SC": "South Carolina",
                "SD": "South Dakota",
                "TN": "Tennessee",
                "TX": "Texas",
                "UT": "Utah",
                "VT": "Vermont",
                "VA": "Virginia ",
                "WA": "Washington",
                "WV": "West Virginia",
                "WI": "Wisconsin",
                "WY": "Wyoming"
            },
            "countries_dom": {
                ABU_DHABI: "ABU DHABI",
                ADEN: "ADEN",
                AFGHANISTAN: "AFGHANISTAN",
                ALBANIA: "ALBANIA",
                ALGERIA: "ALGERIA",
                AMERICAN_SAMOA: "AMERICAN SAMOA",
                ANDORRA: "ANDORRA",
                ANGOLA: "ANGOLA",
                ANTARCTICA: "ANTARCTICA",
                ANTIGUA: "ANTIGUA",
                ARGENTINA: "ARGENTINA",
                ARMENIA: "ARMENIA",
                ARUBA: "ARUBA",
                AUSTRALIA: "AUSTRALIA",
                AUSTRIA: "AUSTRIA",
                AZERBAIJAN: "AZERBAIJAN",
                BAHAMAS: "BAHAMAS",
                BAHRAIN: "BAHRAIN",
                BANGLADESH: "BANGLADESH",
                BARBADOS: "BARBADOS",
                BELARUS: "BELARUS",
                BELGIUM: "BELGIUM",
                BELIZE: "BELIZE",
                BENIN: "BENIN",
                BERMUDA: "BERMUDA",
                BHUTAN: "BHUTAN",
                BOLIVIA: "BOLIVIA",
                BOSNIA: "BOSNIA",
                BOTSWANA: "BOTSWANA",
                BOUVET_ISLAND: "BOUVET ISLAND",
                BRAZIL: "BRAZIL",
                BRITISH_ANTARCTICA_TERRITORY: "BRITISH ANTARCTICA TERRITORY",
                BRITISH_INDIAN_OCEAN_TERRITORY: "BRITISH INDIAN OCEAN TERRITORY",
                BRITISH_VIRGIN_ISLANDS: "BRITISH VIRGIN ISLANDS",
                BRITISH_WEST_INDIES: "BRITISH WEST INDIES",
                BRUNEI: "BRUNEI",
                BULGARIA: "BULGARIA",
                BURKINA_FASO: "BURKINA FASO",
                BURUNDI: "BURUNDI",
                CAMBODIA: "CAMBODIA",
                CAMEROON: "CAMEROON",
                CANADA: "CANADA",
                CANAL_ZONE: "CANAL ZONE",
                CANARY_ISLAND: "CANARY ISLAND",
                CAPE_VERDI_ISLANDS: "CAPE VERDI ISLANDS",
                CAYMAN_ISLANDS: "CAYMAN ISLANDS",
                CEVLON: "CEVLON",
                CHAD: "CHAD",
                CHANNEL_ISLAND_UK: "CHANNEL ISLAND UK",
                CHILE: "CHILE",
                CHINA: "CHINA",
                CHRISTMAS_ISLAND: "CHRISTMAS ISLAND",
                COCOS_KEELING_ISLAND: "COCOS (KEELING) ISLAND",
                COLOMBIA: "COLOMBIA",
                COMORO_ISLANDS: "COMORO ISLANDS",
                CONGO: "CONGO",
                CONGO_KINSHASA: "CONGO KINSHASA",
                COOK_ISLANDS: "COOK ISLANDS",
                COSTA_RICA: "COSTA RICA",
                CROATIA: "CROATIA",
                CUBA: "CUBA",
                CURACAO: "CURACAO",
                CYPRUS: "CYPRUS",
                CZECH_REPUBLIC: "CZECH REPUBLIC",
                DAHOMEY: "DAHOMEY",
                DENMARK: "DENMARK",
                DJIBOUTI: "DJIBOUTI",
                DOMINICA: "DOMINICA",
                DOMINICAN_REPUBLIC: "DOMINICAN REPUBLIC",
                DUBAI: "DUBAI",
                ECUADOR: "ECUADOR",
                EGYPT: "EGYPT",
                EL_SALVADOR: "EL SALVADOR",
                EQUATORIAL_GUINEA: "EQUATORIAL GUINEA",
                ESTONIA: "ESTONIA",
                ETHIOPIA: "ETHIOPIA",
                FAEROE_ISLANDS: "FAEROE ISLANDS",
                FALKLAND_ISLANDS: "FALKLAND ISLANDS",
                FIJI: "FIJI",
                FINLAND: "FINLAND",
                FRANCE: "FRANCE",
                FRENCH_GUIANA: "FRENCH GUIANA",
                FRENCH_POLYNESIA: "FRENCH POLYNESIA",
                GABON: "GABON",
                GAMBIA: "GAMBIA",
                GEORGIA: "GEORGIA",
                GERMANY: "GERMANY",
                GHANA: "GHANA",
                GIBRALTAR: "GIBRALTAR",
                GREECE: "GREECE",
                GREENLAND: "GREENLAND",
                GUADELOUPE: "GUADELOUPE",
                GUAM: "GUAM",
                GUATEMALA: "GUATEMALA",
                GUINEA: "GUINEA",
                GUYANA: "GUYANA",
                HAITI: "HAITI",
                HONDURAS: "HONDURAS",
                HONG_KONG: "HONG KONG",
                HUNGARY: "HUNGARY",
                ICELAND: "ICELAND",
                IFNI: "IFNI",
                INDIA: "INDIA",
                INDONESIA: "INDONESIA",
                IRAN: "IRAN",
                IRAQ: "IRAQ",
                IRELAND: "IRELAND",
                ISRAEL: "ISRAEL",
                ITALY: "ITALY",
                IVORY_COAST: "IVORY COAST",
                JAMAICA: "JAMAICA",
                JAPAN: "JAPAN",
                JORDAN: "JORDAN",
                KAZAKHSTAN: "KAZAKHSTAN",
                KENYA: "KENYA",
                KOREA: "KOREA",
                KOREA_SOUTH: "KOREA, SOUTH",
                KUWAIT: "KUWAIT",
                KYRGYZSTAN: "KYRGYZSTAN",
                LAOS: "LAOS",
                LATVIA: "LATVIA",
                LEBANON: "LEBANON",
                LEEWARD_ISLANDS: "LEEWARD ISLANDS",
                LESOTHO: "LESOTHO",
                LIBYA: "LIBYA",
                LIECHTENSTEIN: "LIECHTENSTEIN",
                LITHUANIA: "LITHUANIA",
                LUXEMBOURG: "LUXEMBOURG",
                MACAO: "MACAO",
                MACEDONIA: "MACEDONIA",
                MADAGASCAR: "MADAGASCAR",
                MALAWI: "MALAWI",
                MALAYSIA: "MALAYSIA",
                MALDIVES: "MALDIVES",
                MALI: "MALI",
                MALTA: "MALTA",
                MARTINIQUE: "MARTINIQUE",
                MAURITANIA: "MAURITANIA",
                MAURITIUS: "MAURITIUS",
                MELANESIA: "MELANESIA",
                MEXICO: "MEXICO",
                MOLDOVIA: "MOLDOVIA",
                MONACO: "MONACO",
                MONGOLIA: "MONGOLIA",
                MOROCCO: "MOROCCO",
                MOZAMBIQUE: "MOZAMBIQUE",
                MYANAMAR: "MYANAMAR",
                NAMIBIA: "NAMIBIA",
                NEPAL: "NEPAL",
                NETHERLANDS: "NETHERLANDS",
                NETHERLANDS_ANTILLES: "NETHERLANDS ANTILLES",
                NETHERLANDS_ANTILLES_NEUTRAL_ZONE: "NETHERLANDS ANTILLES NEUTRAL ZONE",
                NEW_CALADONIA: "NEW CALADONIA",
                NEW_HEBRIDES: "NEW HEBRIDES",
                NEW_ZEALAND: "NEW ZEALAND",
                NICARAGUA: "NICARAGUA",
                NIGER: "NIGER",
                NIGERIA: "NIGERIA",
                NORFOLK_ISLAND: "NORFOLK ISLAND",
                NORWAY: "NORWAY",
                OMAN: "OMAN",
                OTHER: "OTHER",
                PACIFIC_ISLAND: "PACIFIC ISLAND",
                PAKISTAN: "PAKISTAN",
                PANAMA: "PANAMA",
                PAPUA_NEW_GUINEA: "PAPUA NEW GUINEA",
                PARAGUAY: "PARAGUAY",
                PERU: "PERU",
                PHILIPPINES: "PHILIPPINES",
                POLAND: "POLAND",
                PORTUGAL: "PORTUGAL",
                PORTUGUESE_TIMOR: "PORTUGUESE TIMOR",
                PUERTO_RICO: "PUERTO RICO",
                QATAR: "QATAR",
                REPUBLIC_OF_BELARUS: "REPUBLIC OF BELARUS",
                REPUBLIC_OF_SOUTH_AFRICA: "REPUBLIC OF SOUTH AFRICA",
                REUNION: "REUNION",
                ROMANIA: "ROMANIA",
                RUSSIA: "RUSSIA",
                RWANDA: "RWANDA",
                RYUKYU_ISLANDS: "RYUKYU ISLANDS",
                SABAH: "SABAH",
                SAN_MARINO: "SAN MARINO",
                SAUDI_ARABIA: "SAUDI ARABIA",
                SENEGAL: "SENEGAL",
                SERBIA: "SERBIA",
                SEYCHELLES: "SEYCHELLES",
                SIERRA_LEONE: "SIERRA LEONE",
                SINGAPORE: "SINGAPORE",
                SLOVAKIA: "SLOVAKIA",
                SLOVENIA: "SLOVENIA",
                SOMALILIAND: "SOMALILIAND",
                SOUTH_AFRICA: "SOUTH AFRICA",
                SOUTH_YEMEN: "SOUTH YEMEN",
                SPAIN: "SPAIN",
                SPANISH_SAHARA: "SPANISH SAHARA",
                SRI_LANKA: "SRI LANKA",
                ST_KITTS_AND_NEVIS: "ST. KITTS AND NEVIS",
                ST_LUCIA: "ST. LUCIA",
                SUDAN: "SUDAN",
                SURINAM: "SURINAM",
                SW_AFRICA: "SW AFRICA",
                SWAZILAND: "SWAZILAND",
                SWEDEN: "SWEDEN",
                SWITZERLAND: "SWITZERLAND",
                SYRIA: "SYRIA",
                TAIWAN: "TAIWAN",
                TAJIKISTAN: "TAJIKISTAN",
                TANZANIA: "TANZANIA",
                THAILAND: "THAILAND",
                TONGA: "TONGA",
                TRINIDAD: "TRINIDAD",
                TUNISIA: "TUNISIA",
                TURKEY: "TURKEY",
                UGANDA: "UGANDA",
                UKRAINE: "UKRAINE",
                UNITED_ARAB_EMIRATES: "UNITED ARAB EMIRATES",
                UNITED_KINGDOM: "UNITED KINGDOM",
                UPPER_VOLTA: "UPPER VOLTA",
                URUGUAY: "URUGUAY",
                US_PACIFIC_ISLAND: "US PACIFIC ISLAND",
                US_VIRGIN_ISLANDS: "US VIRGIN ISLANDS",
                USA: "USA",
                UZBEKISTAN: "UZBEKISTAN",
                VANUATU: "VANUATU",
                VATICAN_CITY: "VATICAN CITY",
                VENEZUELA: "VENEZUELA",
                VIETNAM: "VIETNAM",
                WAKE_ISLAND: "WAKE ISLAND",
                WEST_INDIES: "WEST INDIES",
                WESTERN_SAHARA: "WESTERN SAHARA",
                YEMEN: "YEMEN",
                ZAIRE: "ZAIRE",
                ZAMBIA: "ZAMBIA",
                ZIMBABWE: "ZIMBABWE"
            }
        },
        "appStrings": {
            ERROR_FIELD_REQUIRED: "Error. This field is required.",
            ERROR_EMAIL: "Error. Invalid Email Address: {{#each this}}{{this}} {{/each}}"
        }
    };

    // Add custom events here for now
    app.events.on("app:init", function() {
        app.metadata.set(base_metadata);
        app.data.declareModels();
        
        // Load dashboard route.
        app.router.route("", "dashboard", function() {
            app.controller.loadView({
                layout: "dashboard"
            });
        });

        // Load the search results route.
        app.router.route("search/:query", "search", function(query) {
            app.controller.loadView({
                module: "Search",
                layout: "search",
                query: query
            });
        });

        // Load the profile
        app.router.route("profile", "profile", function() {
            app.controller.loadView({
                layout: "profile"
            });
        });
        // Loadds profile edit
        app.router.route("profile/edit", "profileedit", function() {
            app.controller.loadView({
                layout: "profileedit"
            });
        });
    });

    var oRoutingBefore = app.routing.before;
    app.routing.before = function(route, args) {
        var dm, nonModuleRoutes;
        nonModuleRoutes = [
            "search",
            "error",
            "profile",
            "profileedit"
        ];

        app.logger.debug("Loading route. " + (route?route:'No route or undefined!'));

        if(!oRoutingBefore.call(this, route, args)) return false;

        function alertUser(msg) {
            // TODO: Error messages should later be put in lang agnostic app strings. e.g. also in layout.js alert.
            msg = msg || "At minimum, you need to have the 'Home' module enabled to use this application.";

            app.alert.show("no-sidecar-access", {
                level: "error",
                title: "Error",
                messages: [msg]
            });
        }

        // Handle index case - get default module if provided. Otherwise, fallback to Home if possible or alert.
        if(route === 'index') {
            dm = typeof(app.config) !== undefined && app.config.defaultModule ? app.config.defaultModule : null;
            if (dm && app.metadata.getModule(dm) && app.acl.hasAccess('read', dm)) {
                app.router.list(dm);
            } else if(app.acl.hasAccess('read', 'Home')) {
                app.router.index();
            } else {
                alertUser();
                return false;
            }
        // If route is NOT index, and NOT in non module routes, check if module (args[0]) is loaded and user has access to it.
        } else if(!_.include(nonModuleRoutes, route) && args[0] && !app.metadata.getModule(args[0]) || !app.acl.hasAccess('read', args[0])) {
            app.logger.error("Module not loaded or user does not have access. ", route);
            alertUser("Issue loading "+args[0]+" module. Please try again later or contact support.");
            return false;
        } 
        return true;
    };

    app.view.Field = app.view.Field.extend({
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

            // For each error add to error help block
            this.$('.controls').addClass('input-append');
            _.each(errors, function(errorContext, errorName) {
                self.$('.help-block').append(app.error.getErrorString(errorName, errorContext));
            });

            // Remove previous exclamation then add back.
            this.$('.add-on').remove();
            this.$('.controls').find('input').after('<span class="add-on"><i class="icon-exclamation-sign"></i></span>');
        }
    });

    app.Controller = app.Controller.extend({
        loadView: function(params) {
            var self = this;
            // TODO: Will it ever happen: app.config == undefined?
            // app.config should always be present because the logger depends on it
            if (_.isUndefined(app.config) || (app.config && app.config.appStatus == 'offline')) {
                var callback = function(data) {
                    var params = {
                        module: "Login",
                        layout: "login",
                        create: true
                    };
                    app.Controller.__super__.loadView.call(self, params);
                    app.alert.show('appOffline', {
                        level: "error",
                        title: 'Error',
                        messages: 'Sorry the application is not available at this time. Please contact the site administrator.',
                        autoclose: false
                    });
                };

                app.logout({success: callback, error: callback});
                return;
            }
            app.Controller.__super__.loadView.call(this, params);
        }
    });

    var _rrh = {
        /**
         * Handles `signup` route.
         */
        signup: function() {
            app.logger.debug("Route changed to signup!");
            app.controller.loadView({
                module: "Signup",
                layout: "signup",
                create: true
            });
        }
    };

    app.events.on("app:init", function() {
        // Register portal specific routes
        app.router.route("signup", "signup", _rrh.signup);
    });

})(SUGAR.App);
