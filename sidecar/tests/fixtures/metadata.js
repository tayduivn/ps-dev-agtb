var fixtures = typeof(fixtures) == "object" ? fixtures : {};

fixtures.metadata = {
    _hash: '2q34aasdfwrasdfse',
    "modules": {
        "Cases": {
            '_hash': '12345678910',
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                "case_number": {
                    "name": "case_number",
                    "type": "float",
                    round: 2,
                    precision: 2,
                    number_group_seperator: ",",
                    decimal_seperator: "."
                },
                "name": {
                    "name": "name",
                    "type": "varchar"
                },
                "description": {
                    "name": "description",
                    "type": "text"
                },
                "type": {
                    "name": "type",
                    "type": "varchar"
                },
                "status": {
                    "name": "status",
                    "type": "enum",
                    "options": [
                        {"key": "s1", "value": "s1"},
                        {"key": "s2", "value": "s2"},
                        {"key": "s3", "value": "s3"}
                    ]
                },
                "priority": {
                    "name": "priority",
                    "type": "enum",
                    "multi": true,

                    "options": [
                        {"key": "c1", "value": "c1"},
                        {"key": "c2", "value": "c2"},
                        {"key": "c3", "value": "c3"}
                    ]
                },
                "date_entered": {
                    "name": "date_entered",
                    "type": "datetimecombo"
                },
                "created_by": {
                    "name": "created_by",
                    "type": "varchar"
                },
                "date_modified": {
                    "name": "date_modified",
                    "type": "datetimecombo"
                },
                "modified_user_id": {
                    "name": "modified_user_id",
                    "type": "varchar"
                },
                "leradio_c": {
                    "name": "leradio_c",
                    "type": "radioenum",
                    "options": "Elastic_boost_options" // Temporary, TODO: Pull from app list strings
                }
            },
            "relationships": {
            },
            "views": {
                "editView": {
                    "buttons": [
                        {
                            name: "save_button",
                            type: "button",
                            label: "Save",
                            value: "save",
                            primary: true,
                            events: {
                                click: "function(){ var self = this; " +
                                    "this.model.save(null, {success:" +
                                    "function(){self.app.navigate(self.context, self.model, 'detail');}" +
                                    "});" +
                                    "}"
                            }
                        },
                        {
                            name: "cancel_button",
                            type: "button",
                            label: "Cancel",
                            value: "cancel",
                            route: {
                                action: "detail",
                                module: "Cases"
                            },
                            primary: false
                        }
                    ],
                    "panels": [
                        {
                            "label": "Details",
                            "fields": [
                                {name: "case_number", label: "Case Number", "class": "foo"},
                                {name: "name", label: "Name"},
                                {name: "status", label: "Status"},

                                {name: "priority", label: "Priority"},
                                {name: "description", label: "Description"},
                                {name: "date_modified", label: "Modifed Date"},
                                {name: "leradio_c", label: "LeRadio"}
                            ]
                        }
                    ]

                },
                "detailView": {
                    "buttons": [
                        {
                            name: "edit_button",
                            type: "button",
                            label: "Edit",
                            value: "edit",
                            route: {
                                action: "edit"
                            },
                            primary: true
                        }
                    ],
                    "panels": [
                        {
                            "label": "Details",
                            "fields": [
                                {name: "case_number", label: "Case Number", "class": "foo"},
                                {name: "name", label: "Name"},
                                {name: "status", label: "Status"},

                                {name: "priority", label: "Priority"},
                                {name: "description", label: "Description"},
                                {name: "date_modified", label: "Modifed Date"},
                                {name: "leradio_c", label: "LeRadio"}
                            ]
                        }
                    ]
                },
                "quickCreate": {

                },
                //This is stored in a listviewdefs variable on the server, but its inconsistent with the rest of the app
                "listView": {
                    "buttons": [
                        {
                            name: "show_more_button",
                            type: "button",
                            label: "Show More",
                            class: "loading wide",
                            events: {
                                click: "function(){ var self = this; " +
                                    "this.context.state.collection.paginate({add:true, success:function(){window.scrollTo(0,document.body.scrollHeight);}});" +
                                    "}"
                            }
                        }
                    ],
                    "listNav": [
                        {
                            name: "show_more_button_back",
                            type: "navElement",
                            icon: "icon-plus",
                            label: " ",
                            route: {
                                action: "create",
                                module: "Cases"
                            }
                        },
                        {
                            name: "show_more_button_back",
                            type: "navElement",
                            icon: "icon-chevron-left",
                            label: " ",
                            events: {
                                click: "function(){ var self = this; " +
                                    "this.context.state.collection.paginate({page:-1, success:function(){}});" +
                                    "}"
                            }
                        },
                        {
                            name: "show_more_button_forward",
                            type: "navElement",
                            icon: "icon-chevron-right",
                            label: " ",
                            events: {
                                click: "function(){ var self = this; " +
                                    "this.context.state.collection.paginate({success:function(){}});" +
                                    "}"
                            }
                        }
                    ],
                    "panels": [
                        {
                            "label": "LBL_PANEL_1",
                            "fields": [
                                {name: "case_number", label: "Case Number", "class": "foo"},
                                {name: "name", label: "Name"},
                                {name: "status", label: "Status"},
                                {name: "priority", label: "priority"},
                                {name: "date_modified", label: "Modifed Date"},

                                {type: "sugarField_actionsLink", label: "Actions"}
                            ]
                        }
                    ]
                },
                //Subpanel layout defs
                "subpanelView": {

                }
            },
            //Layouts map an action to a lyout that defines a set of views and how to display them
            //Different clients will get different layouts for the same actions
            "layouts": {
                "edit": {
                    //Default layout is a single view
                    "type": "simple",
                    "components": [
                        {view: "editView"}
                    ]
                },
"detail": {
    "type": "rows",
    "components": [
        {view: "detailView"},
        {view: "subpanelView"}
    ]
},
                "list": {
                    //Default layout is a single view
                    "type": "simple",
                    "components": [
                        {view: "listView"}
                    ]
                },
                //Example of a sublayout. Two columns on the top and one view below that
                "sublayout": {
                    "type": "rows",
                    "components": [
                        {"layout": {
                            "type": "columns",
                            "components": [
                                {view: "editView"},
                                {view: "detailView"}
                            ]
                        }},
                        {"view": "subpanelView"}
                    ]
                },
                //Layout with context switch. Edit view with related detail view
                "complexlayout": {
                    "type": "columns",
                    "components": [
                        {"view": "editView"},
                        {
                            "view": "detailView",
                            //Name of link to pull the new context from, In this case a single account
                            "context": "accounts"
                        }
                    ]
                },
                //Layout that references another layout
                "detailplus": {
                    "type": "fluid",
                    "components": [
                        {view: "subpanelView",
                            size: 2},
                        {layout: "edit",
                            size: 6},
                        {layout: "detail",
                            size: 3}
                    ]
                }
            }
        },
        "Contacts": {
            '_hash': '12345678910',
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                "first_name": {
                    "name": "first_name",
                    "type": "varchar"
                },
                "last_name": {
                    "name": "last_name",
                    "type": "varchar"
                },
                "phone_work": {
                    "name": "phone_work",
                    "type": "varchar"
                },
                "email1": {
                    "name": "email1",
                    "type": "varchar"
                },
                "full_name": {
                    "name": "full_name",
                    "type": "varchar",
                    "concat": ["first_name", "last_name"]
                }
            },
            "relationships": {
            },
            "views": {
                "editView": {
                    "buttons": [
                        {
                            name: "save_button",
                            type: "button_save",
                            label: "Save",
                            value: "save",
                            primary: true
                        },
                        {
                            name: "cancel_button",
                            type: "button",
                            label: "Cancel",
                            value: "cancel",
                            route: {
                                action: "detail",
                                module: "Contacts"
                            },
                            events: {
                                //click : "SUGAR.App.myExtension.callback",
                                //drag: "",
                                foo: 'function(e){console.log(this)}'
                            },
                            primary: false
                        }
                    ],
                    "panels": [
                        {
                            "label": "Details",
                            "fields": [
                                {name: "first_name", label: "First Name", "class": "foo"},
                                {name: "last_name", label: "Last Name"},
                                {name: "phone_work", label: "Phone"},
                                {name: "email1", label: "Email"}
                            ]
                        }
                    ]

                },
                "detailView": {
                    "buttons": [
                        {
                            name: "edit_button",
                            type: "button",
                            label: "Edit",
                            value: "edit",
                            route: {
                                action: "edit"
                            },
                            primary: true
                        }
                    ],
                    "panels": [
                        {
                            "label": "Details",
                            "fields": [
                                {name: "first_name", label: "First Name"},
                                {name: "last_name", label: "Last Name"},
                                {name: "phone_work", label: "Phone"},
                                {name: "email1", label: "Email"},
                                {type: "sugarField_primaryAddress", label: "Address"}
                            ]
                        }
                    ]
                },
                "quickCreate": {

                },
                //This is stored in a listviewdefs variable on the server, but its inconsistent with the rest of the app
                "listView": {
                    "panels": [
                        {
                            "label": "LBL_PANEL_1",
                            "fields": [
                                {name: "first_name", label: "First Name"},
                                {name: "last_name", label: "Last Name"},
                                {name: "email1", label: "Email"},
                                {name: "phone_work", label: "Phone"},
                                {type: "sugarField_actionsLink", label: "Actions"}
                            ]
                        }
                    ]
                },
                //Subpanel layout defs
                "subpanelView": {

                }
            },
            //Layouts map an action to a lyout that defines a set of views and how to display them
            //Different clients will get different layouts for the same actions
            "layouts": {
                "edit": {
                    //Default layout is a single view
                    "type": "simple",
                    "components": [
                        {view: "editView"}
                    ]
                },
                "detail": {
                    "type": "rows",
                    "components": [
                        {view: "detailView"},
                        {view: "subpanelView"}
                    ]
                },
                "list": {
                    //Default layout is a single view
                    "type": "simple",
                    "components": [
                        {view: "listView"}
                    ]
                },
                //Example of a sublayout. Two columns on the top and one view below that
                "sublayout": {
                    "type": "rows",
                    "components": [
                        {"layout": {
                            "type": "columns",
                            "components": [
                                {view: "editView"},
                                {view: "detailView"}
                            ]
                        }},
                        {"view": "subpanelView"}
                    ]
                },
                //Layout with context switch. Edit view with related detail view
                "complexlayout": {
                    "type": "columns",
                    "components": [
                        {"view": "editView"},
                        {
                            "view": "detailView",
                            //Name of link to pull the new context from, In this case a single account
                            "context": "accounts"
                        }
                    ]
                },
                //Layout that references another layout
                "detailplus": {
                    "type": "fluid",
                    "components": [
                        {view: "subpanelView",
                            size: 2},
                        {layout: "edit",
                            size: 6},
                        {layout: "detail",
                            size: 3}
                    ]
                }
            }
        },
        "Home": {
            '_hash': '12345678910',
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
                                    " var args={password:this.model.get(\"password\"), username:this.model.get(\"username\")}; this.app.sugarAuth.login(args, {success:" +
                                    "function(){console.log(\"logged in successfully!\");self.app.navigate('', self.model); }" +
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
                    "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n"},
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
            "events": {},
            controller: "{" +
                "render : function(){" +
                "this.app.sugarField.base.prototype.render.call(this);" +
                "}," +
                "customCallback : function(){}" +
                "}"
        },
        "float": {
            "views": {
                "detailView": {
                    "type": "basic",
                    "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n"},
                "editView": {
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
            controller: "{" +
                "unformat:function(value){\n" +
                "  value = SUGAR.App.utils.unformatNumberString(value, this.number_group_seperator, this.decimal_seperator, false);\n" +
                "return value\n" +
                "}," +
                "format:function(value){\n" +
                " value = SUGAR.App.utils.formatNumber(value, this.round, this.precision, this.number_group_seperator, this.decimal_seperator);\n" +
                "return value\n" +
                "}" +
                "}"
        },
        "datetime": {
            "views": {
                "detailView": {
                    "type": "basic",
                    "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n"
                },
                "editView": {
                    "type": "basic",
                    "template": "<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> " +
                        "<input type=\"text\" class=\"input-xlarge datepicker\" value=\"{{value}}\">  <p class=\"help-block\">" +
                        "<\/p> <\/div>"
                },
                "default": {
                    "type": "basic",
                    "template": "<span name=\"{{name}}\">{{value}}</span>"
                }
            },
            controller: "{" +
                "render:function(value){\n" +
                " app.sugarField.base.prototype.render.call(this);//call proto render\n" +
                "  	$(function() {" +
                "$( \".datepicker\" ).datepicker({" +
                "showOn: \"button\"," +
                "buttonImage: \"../lib/jquery-ui/css/smoothness/images/calendar.gif\"," +
                "buttonImageOnly: true," +
                "dateFormat: \"yy-mm-dd\"" +
                "});" +
                "});\n" +
                "}," +
                "unformat:function(value){\n" +
                "return value\n" +
                "}," +
                "format:function(value){\n" +
                "return value\n" +
                "},\n" +
                "}"
        },
        "datetimecombo": {
            "views": {
                "detailView": {
                    "type": "basic",
                    "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value.dateTime}}</span>\n"},
                "editView": {
                    "type": "basic",
                    "template": "<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> " +
                        "<input type=\"text\" class=\"input-xlarge datepicker\" value=\"{{value.date}}\"> " +
                        "<select class=\"date_time_hours\">{{#each timeOptions.hours}}<option value=\"{{this.value}}\" {{in this.key ..\/value.hours \"selected\"}}>{{this.key}}</option>{{/each}}</select>" +
                        " : " +
                        "<select class=\"date_time_minutes\">{{#each timeOptions.minutes}}<option value=\"{{this.value}}\"{{in this.key ..\/value.minutes \"selected\"}}>{{this.key}}</option>{{/each}}</select>" +
                        " " +
                        "{{#if this.amPm}}<select class=\"date_time_ampm\">{{#each timeOptions.amPm}}<option value=\"{{this.value}}\" {{in this.key ..\/value.amPm \"selected\"}}>{{this.key}}</option>{{/each}}</select>{{/if}}" +
                        " <p class=\"help-block\">" +
                        "<\/p> <\/div>"
                },
                "default": {
                    "type": "basic",
                    "template": "<span name=\"{{name}}\">{{value.dateTime}}</span>"
                }
            },
            controller: "{" +
                "render:function(value){\n" +
                " app.sugarField.base.prototype.render.call(this);//call proto render\n" +
                "  	$(function() {" +
                "$( \".datepicker\" ).datepicker({" +
                "showOn: \"button\"," +
                "buttonImage: \"../lib/jquery-ui/css/smoothness/images/calendar.gif\"," +
                "buttonImageOnly: true" +
                "});" +
                "});\n" +
                "}," +
                "unformat:function(value){\n" +
                "return value\n" +
                "}," +
                "format:function(value){\n" +
                "var jsDate = app.utils.date.parse(value);\n" +
                "jsDate = app.utils.date.roundTime(jsDate);\n" +
                "value = {\n" +
                "dateTime: value,\n" +
                "//TODO Account for user prefs\n" +
                "date: app.utils.date.format(jsDate, 'Y-m-d'),\n" +
                "time: app.utils.date.format(jsDate, 'h:i:s'),\n" +
                "hours: app.utils.date.format(jsDate, 'H'),\n" +
                "minutes: app.utils.date.format(jsDate, 'i'),\n" +
                "seconds: app.utils.date.format(jsDate, 's'),\n" +
                "amPm: app.utils.date.format(jsDate, 'H') < 12 ? 'am' : 'pm',\n" +
                "};\n" +
                "return value\n" +
                "},\n" +
                "timeOptions:{" +
                "    hours:[{key:\"00\",value:\"00\"},{key:\"01\",value:\"01\"},{key:\"02\",value:\"02\"},{key:\"03\",value:\"03\"},{key:\"04\",value:\"04\"}," +
                "        {key:\"05\",value:\"05\"},{key:\"06\",value:\"06\"},{key:\"07\",value:\"07\"},{key:\"08\",value:\"08\"},{key:\"09\",value:\"09\"}," +
                "        {key:\"10\",value:\"10\"},{key:\"11\",value:\"11\"},{key:\"12\",value:\"12\"},{key:\"13\",value:\"13\"},{key:\"14\",value:\"14\"}," +
                "        {key:\"15\",value:\"15\"},{key:\"16\",value:\"16\"},{key:\"17\",value:\"17\"},{key:\"18\",value:\"18\"},{key:\"19\",value:\"19\"}," +
                "        {key:\"20\",value:\"20\"},{key:\"21\",value:\"21\"},{key:\"22\",value:\"22\"},{key:\"23\",value:\"23\"}" +
                "            ]," +
                "    minutes:[{key:\"00\",value:\"00\"},{key:\"15\",value:\"15\"},{key:\"30\",value:\"30\"},{key:\"45\",value:\"45\"}]," +
                "    amPm:[{key:\"am\",value:\"am\"}, {key:\"pm\",value:\"pm\"}]" +
                "}," +
                "bindDomChange: function (model, fieldName) {\n" +
                "var self = this\n" +
                "var date = this.$el.find('input');\n" +

                "var hour = this.$el.find('.date_time_hours');\n" +
                "var minute = this.$el.find('.date_time_minutes');\n" +
                "date.on('change', function(ev) {\n" +
                "model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() +':'+ minute.val()+':00'));\n" +
                "});\n" +
                " hour.on('change', function(ev) {\n" +
                "model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() +':'+ minute.val()+':00'));\n" +
                "});\n" +
                "minute.on('change', function(ev) {\n" +
                "model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() +':'+ minute.val()+':00'));\n" +
                "});\n" +
                "}\n" +
                "}"
        },
        "integer": {
            "views": {
                "detailView": {
                    "type": "basic",
                    "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n"},
                "editView": {
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
            controller: "{" +
                "unformat:function(value){\n" +
                " value = SUGAR.App.utils.formatNumber(value, 1, 0, \"\", \".\");\n" +
                "return value\n" +
                "}," +
                "format:function(value){\n" +
                " value = SUGAR.App.utils.formatNumber(value, 1, 0, this.number_group_seperator, \".\");\n" +
                "return value\n" +
                "}" +
                "}"
        },

        "enum": {
            "views": {
                "detailView": {
                    "type": "basic",
                    "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n"},
                "editView": {
                    "type": "basic",
                    "template": "<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> " +
                        "<select name=\"{{name}}\" {{#if multi}} multiple {{/if}}>{{#each options}}<option value=\"{{{this.key}}}\" {{in this.key ..\/value \"SELECTED\"}}>{{this.value}}</option>{{/each}}</select>  <p class=\"help-block\">" +
                        "<\/p> <\/div>"
                },
                "default": {
                    "type": "basic",
                    "template": "<span name=\"{{name}}\">{{value}}</span>"
                }
            },

            controller: "{" +
                "fieldType:\"select\",\n" +
                "render:function(){" +
                "   var result = this.app.sugarField.base.prototype.render.call(this);" +
                "   $(this.fieldType + \"[name=\" + this.name + \"]\").chosen();" +
                "   $('select').chosen();" +
                "   return result;" +

                "}" +
                "" +
                "\n}\n"

        },

        radioenum: {
            views: {
                detailView: {
                    template: "<h3>{{label}}</h3><span name=\"{{name}}\">{{value}}</span>\n"
                },
                editView: {
                    template: "<div class=\"controls\"><label class=\"control-label\">{{label}}<\/label>" +
                        "{{#each options}}<label><input type=\"radio\" name=\"{{../name}}\" value=\"{{this}}\" {{eq this ..\/value \"SELECTED\"}}>{{this}}</label>{{/each}}"
                }
            }
        },
        "checkbox": {
            "views": {
                "detailView": {
                    "type": "basic",
                    "template": "<h3>{{label}}<\/h3><span name=\"{{name}}\"><input type=\"checkbox\" class=\"checkbox\"{{#if value}} checked{{/if}} disabled></span>\n"},
                "editView": {
                    "type": "basic",
                    "template": "<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> " +
                        "<input type=\"checkbox\" class=\"checkbox\"{{#if value}} checked{{/if}}> <p class=\"help-block\">" +
                        "<\/p> <\/div>"
                }
            },
            controller: "{\n" +
                "unformat:function(value){\n" +
                "  value = this.el.children[0].children[1].checked ? \"1\" : \"0\";\n" +
                "  return value\n" +
                "},\n" +
                "format:function(value){\n" +
                "  value = (value==\"1\") ? true : false;\n" +
                "  return value\n" +
                "}\n" +
                "}"
        },
        "password": {
            "editView": {
                "type": "basic",
                "template": "\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                    "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                    "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>"},
            "loginView": {
                "type": "basic",
                "template": "\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                    "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                    "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>"}
        },
        "button": {
            "default": {
                "type": "basic",
                "template": "<a href=\"{{#if route}}#{{buildRoute context model route.action route.options}}" +
                    "{{else}}javascript:void(0){{/if}}\" class=\"btn {{class}} {{#if primary}}btn-primary{{/if}}\">" +
                    "{{#if icon}}<i class=\"{{icon}}\"><\/i>{{/if}}{{label}}<\/a>\n"
            }
        },
        "navElement": {
            "default": {
                "type": "basic",
                "template": "<a href=\"{{#if route}}#{{buildRoute context model route.action route.options}}" +
                    "{{else}}javascript:void(0){{/if}}\" class=\"{{class}}\">" +
                    "{{#if icon}}<i class=\"{{icon}}\"><\/i>{{/if}}{{label}}<\/a>\n"
            }
        },
        "textarea": {
            "detailView": {
                "type": "basic",
                "template": "<label class=\"control-label\">{{label}}<\/label>{{value}}\n"},
            "editView": {
                "type": "basic",
                "template": "<label class=\"control-label\">{{label}}<\/label><textarea class=\"input-xlarge\" id=\"textarea\" rows=\"3\">{{value}}</textarea>"}
        },
        "sugarField_actionsLink": {
            "default": {
                "template": "<div class=\"btn-group pull-right\"><a class=\"btn\" href=\"#\" data-toggle=\"dropdown\">Actions<span class=\"caret\"><\/span><\/a>" +
                    "<ul class=\"dropdown-menu\"> <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\"><i class=\"icon-list-alt\"><\/i>Details<\/a><\/li> " +
                    "  <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/edit\"><i class=\"icon-pencil\"><\/i> Edit<\/a><\/li>  " +
                    " <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/delete\"><i class=\"icon-trash\"><\/i> Delete<\/a><\/li> <\/ul>     <\/div>"
            }
        },
        "sugarField_fullName": {
            "default": {
                "template": "{{{getfieldvalue model \"first_name\"}}} {{{getfieldvalue model \"last_name\"}}}"
            },
            "detailView": {
                "template": "<h2>{{{getfieldvalue model \"first_name\"}}} {{{getfieldvalue model \"last_name\"}}}<\/h2>"
            }
        },
        "sugarField_primaryAddress": {
            "detailView": {
                "template": "<h3>{{label}}<\/h3>{{{getfieldvalue model \"primary_address_street\"}}}<br> {{{getfieldvalue model \"primary_address_city\"}}}," +
                    " {{{getfieldvalue model \"primary_address_postalcode\"}}} {{{getfieldvalue model \"primary_address_country\"}}}"
            }
        },
        "sugarField_buttonSave": {
            "default": {
                "template": "<button class=\"btn btn-primary\" href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/save\">{{label}}<\/button>"
            }
        },
        "sugarField_buttonCancelSave": {
            "default": {
                "template": "<a class=\"btn btn-primary\" href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/save\">Save<\/a><a class=\"btn btn-primary\" href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\">Cancel<\/a>"
            }
        }

    },
    'viewTemplates': {
        "detailView": "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a> {{name}}</h3>" +
            "<form name='{{name}}' class='well'>" +
            "{{#each meta.buttons}}" +
            "{{sugar_field ../context ../this ../model}}" +
            "{{/each}}" +
            "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            "<h4>{{label}}</h4>" +
            "{{#each fields}}" +
            "<div>{{sugar_field ../../context ../../this ../../model}}</div>" +
            "{{/each}}" +
            "</div>" +
            "{{/each}}</form>",
        "editView": "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a> {{name}}</h3>" +
            "<form name='{{name}}' class='well'>" +
            "{{#each meta.buttons}}" +
            "{{sugar_field ../context ../this ../model}}" +
            "{{/each}}" +
            "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            "<h4>{{label}}</h4>" +
            "{{#each fields}}" +
            "<div>{{sugar_field ../../context ../../this ../../model}}</div>" +
            "{{/each}}" +
            "</div>" +
            "{{/each}}</form>",
        "loginView": "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a>&nbsp</h3>" +
            "<form name='{{name}}' class='well'>" +
            "{{#each meta.panels}}" +
            '<div class="{{../name}} panel">' +
            "<h4>{{label}}</h4>" +
            "{{#each fields}}" +
            "<div>{{sugar_field ../../context ../../this ../../model}}</div>" +
            "{{/each}}" +
            "</div>" +
            "{{/each}}" + "{{#each meta.buttons}}" +
            "{{sugar_field ../context ../this ../model}}" +
            "{{/each}}" + "</form>",
        "subpanelView": "",
        "listView": '<div class="span12 container-fluid subhead">' +
            '<h3>{{context.state.module}}</h3>' +
            "{{#each meta.panels}}" +
            '<div class="{{../name}}">' +
            '<table class="table table-striped"><thead><tr>' +
            '{{#each fields}}' +
            '<th width="{{width}}%">{{label}}</th>' +
            '{{/each}}' +
            '</tr></thead><tbody>' +
            '{{#each ../context.state.collection.models}}' +
            '<tr name="{{module}}_{{attributes.id}}">' +
            '{{#each ../fields}}' +
            // SugarField requires the current context, field name, and the current bean in the context
            // since we are pulling from the collection rather than the default bean in the context
            '<td class="dblclick">{{sugar_field ../../../context ../../../this ../this}}</td>' +
            '{{/each}}' +
            '</tr>' +
            '{{/each}}' +
            '</tbody></table>' +
            '{{/each}}' +
            "{{#each meta.buttons}}" +
            "{{sugar_field ../context ../this ../model}}" +
            "{{/each}}" +
            "<ul class=\"nav nav-pills pull-right actions\">{{#each meta.listNav}}" +
            '<li>' +
            "{{sugar_field ../context ../this ../model}}" +
            '</li>' +
            "{{/each}}" +
            '{{#if context.state.collection.page}}<li><div class=\"page_counter\"><small>Page {{context.state.collection.page}}</small></div></li>{{/if}}' +
            '</ul>' +
            "</div>"
    },
    appListStrings: {
        "campainglog_target_type_dom":{
        "Contacts":"Contacts",
        "Users":"Users",
        "Prospects":"Targets",
        "Leads":"Leads",
        "Accounts":"Accounts"
        },
        "merge_operators_dom":{
        "like":"Contains",
        "exact":"Exactly",
        "start":"Starts With"
        },
        "custom_fields_importable_dom":{
        "true":"Yes",
        "false":"No",
        "required":"Required"
        },
        "Elastic_boost_options":[
        "Disabled",
        "Low Boost",
        "Medium Boost",
        "High Boost"
        ],
        "custom_fields_merge_dup_dom":[
        "Disabled",
        "Enabled",
        "In Filter",
        "Default Selected Filter",
        "Filter Only"
        ],
        "navigation_paradigms":{
        "m":"Modules",
        "gm":"Grouped Modules"
        },
        "contract_status_dom":{
        "notstarted":"Not Started",
        "inprogress":"In Progress",
        "signed":"Signed"
        },
        "contract_payment_frequency_dom":{
        "monthly":"Monthly",
        "quarterly":"Quarterly",
        "halfyearly":"Half yearly",
        "yearly":"Yearly"
        },
        "contract_expiration_notice_dom":{
        "1":"1 Day",
        "3":"3 Days",
        "5":"5 Days",
        "7":"1 Week",
        "14":"2 Weeks",
        "21":"3 Weeks",
        "31":"1 Month"
        },
        "oc_status_dom":{
        "":"",
        "Active":"Active",
        "Inactive":"Inactive"
        },
        "projects_priority_options":{
        "high":"High",
        "medium":"Medium",
        "low":"Low"
        },
        "projects_status_options":{
        "notstarted":"Not Started",
        "inprogress":"In Progress",
        "completed":"Completed"
        },
        "chart_strings":{
        "expandlegend":"Expand Legend",
        "collapselegend":"Collapse Legend",
        "clickfordrilldown":"Click for Drilldown",
        "drilldownoptions":"Drill Down Options",
        "detailview":"More Details...",
        "piechart":"Pie Chart",
        "groupchart":"Group Chart",
        "stackedchart":"Stacked Chart",
        "barchart":"Bar Chart",
        "horizontalbarchart":"Horizontal Bar Chart",
        "linechart":"Line Chart",
        "noData":"Data not available",
        "print":"Print",
        "pieWedgeName":"sections"
        }
    },
    appStrings: {
        DATA_TYPE_DUE: "Due:",
        DATA_TYPE_MODIFIED: "Modified:",
        DATA_TYPE_SENT: "Sent:",
        DATA_TYPE_START: "Start:",
        DEFAULT: "Basic",
        ERROR_EXAMINE_MSG: "  Please examine the error message below:",
        ERROR_FULLY_EXPIRED: "Your company's license for SugarCRM has expired for more than 7 days and needs to be brought up to date. Only admins may login.",
        ERROR_JS_ALERT_SYSTEM_CLASS: "System",
        ERROR_JS_ALERT_TIMEOUT_MSG_1: "Your session is about to timeout in 2 minutes. Please save your work.",
        ERROR_JS_ALERT_TIMEOUT_MSG_2: "Your session has timed out.",
        ERROR_JS_ALERT_TIMEOUT_TITLE: "Session Timeout",
        ERROR_LICENSE_EXPIRED: "Your company's license for SugarCRM needs to be updated. Only admins may login",
        ERROR_LICENSE_VALIDATION: "Your company's license for SugarCRM needs to be validated. Only admins may login",
        ERROR_MISSING_COLLECTION_SELECTION: "Empty required field",
        ERROR_NOTIFY_OVERRIDE: "Error: ResourceObserver->notify() needs to be overridden.",
        ERROR_NO_RECORD: "Error retrieving record.  This record may be deleted or you may not be authorized to view it.",
        ERROR_TYPE_NOT_VALID: "Error. This type is not valid.",
        ERROR_UNABLE_TO_RETRIEVE_DATA: "Error: Unable to retrieve data for {0} Connector.  The service may currently be inaccessible or the configuration settings may be invalid.  Connector error message: ({1}).",
        ERR_ADDRESS_KEY_NOT_SPECIFIED: "Please specify 'key' index in displayParams attribute for the Meta-Data definition",
        ERR_AJAX_LOAD: "An error has occured:",
        ERR_AJAX_LOAD_FAILURE: "There was an error processing your request, please try again at a later time.",
        ERR_AJAX_LOAD_FOOTER: "If this error persists, please have your administrator disable Ajax for this module",
        ERR_BLANK_PAGE_NAME: "Please enter a page name."
    }
};