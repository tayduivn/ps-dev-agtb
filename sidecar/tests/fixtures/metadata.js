/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 1/31/12
 * Time: 12:26 PM
 * To change this template use File | Settings | File Templates.
 */

fixtures = typeof(fixtures) == "object" ? fixtures : {};
fixtures.metadata = {
    _hash: '2q34aasdfwrasdfse',
    "modules": {
        "Cases": {
            '_hash': '12345678910',
            "primaryBean": "Case",
            "beans": {
                "Case": {
                    "fields": {
                        "id": {
                            "name": "id",
                            "type": "id"
                        },
                        "case_number": {
                            "name": "case_number",
                            "type": "varchar"
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
                            "type": "varchar"
                        },
                        "date_entered": {
                            "name": "date_entered",
                            "type": "varchar"
                        },
                        "created_by": {
                            "name": "created_by",
                            "type": "varchar"
                        },
                        "date_modified": {
                            "name": "date_modified",
                            "type": "varchar"
                        },
                        "modified_user_id": {
                            "name": "modified_user_id",
                            "type": "varchar"
                        }
                    },
                    "relationships": {

                    }
                }
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
                                {name: "name", label: "Name"},
                                {name: "status", label: "Status"},
                                {name: "description", label: "Description"}

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
                                {name: "description", label: "Description"}

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
                                    "console.log(this); this.context.state.collection.paginate({add:true, success:function(){console.log(\"in paginate success\");window.scrollTo(0,document.body.scrollHeight);}});" +
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
                                    "console.log(this); this.context.state.collection.paginate({page:-1, success:function(){console.log(\"in paginate success\");}});" +
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
                                    "console.log(this); this.context.state.collection.paginate({success:function(){console.log(\"in paginate success\");}});" +
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
            "primaryBean": "Contact",
            "beans": {
                "Contact": {
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

                    }
                }
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
            "primaryBean": "Home",
            "beans": {
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
                    }
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
            "events": {}
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
        "detailView" :
               "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a> {{name}}</h3>" +
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
           "editView" :
               "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a> {{name}}</h3>" +
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
           "loginView" :
               "<h3 class=\"view_title\"><a href='#{{context.state.module}}'>{{context.state.module}}</a>&nbsp</h3>" +
               "<form name='{{name}}' class='well'>" +
               "{{#each meta.panels}}" +
                   '<div class="{{../name}} panel">' +
                   "<h4>{{label}}</h4>" +
                   "{{#each fields}}" +
                       "<div>{{sugar_field ../../context ../../this ../../model}}</div>" +
                   "{{/each}}" +
                   "</div>" +
               "{{/each}}"+        "{{#each meta.buttons}}" +
                           "{{sugar_field ../context ../this ../model}}" +
                       "{{/each}}"+"</form>",
           "subpanelView" :
               "",
           "listView" :
                   '<div class="span12 container-fluid subhead">'+
                       '<h3>{{context.state.module}}</h3>' +
               "{{#each meta.panels}}" +
                   '<div class="{{../name}}">' +
                   '<table class="table table-striped"><thead><tr>' +
                   '{{#each fields}}' +
                       '<th width="{{width}}%">{{label}}</th>' +
                   '{{/each}}' +
                   '</tr></thead><tbody>' +
                   '{{#each ../context.state.collection.models}}' +
                       '<tr name="{{beanType}}_{{attributes.id}}">' +
                       '{{#each ../fields}}' +
                           // SugarField requires the current context, field name, and the current bean in the context
                           // since we are pulling from the collection rather than the default bean in the context
                           '<td class="dblclick">{{sugar_field ../../../context ../../../this ../this}}</td>' +
                       '{{/each}}' +
                       '</tr>' +
                   '{{/each}}' +
                   '</tbody></table>'+
               '{{/each}}'+
               "{{#each meta.buttons}}" +
                       "{{sugar_field ../context ../this ../model}}" +
               "{{/each}}"+
                   "<ul class=\"nav nav-pills pull-right actions\">{{#each meta.listNav}}" +
                               '<li>'  +
                               "{{sugar_field ../context ../this ../model}}" +
                               '</li>'+
                           "{{/each}}"+
                               '{{#if context.state.collection.page}}<li><div class=\"page_counter\"><small>Page {{context.state.collection.page}}</small></div></li>{{/if}}'+
                               '</ul>'+
                   "</div>"
    }
};