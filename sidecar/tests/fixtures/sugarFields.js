var sugarFieldsFixtures = {
    "fieldsList":["text", "password", "button_save", "textarea", "textarea"],
    "fieldsData":{
        "text":{
            "templates" : {
                "detailView":{
                    "type":"basic",
                    "template":"<h3>{{label}}<\/h3><span name=\"{{name}}\">{{value}}</span>\n"
                },
                "editView":{
                    "type":"basic",
                    "template":"<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> "+
                        "<input type=\"text\" class=\"input-xlarge\" value=\"{{value}}\">  <p class=\"help-block\">"+
                        "<\/p> <\/div>"
                },
                "loginView":{
                    "type":"basic",
                    "template":"<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> "+
                        "<input type=\"text\" class=\"input-xlarge\" value=\"{{value}}\">  <p class=\"help-block\">"+
                        "<\/p> <\/div>"
                },
                "default":{
                    "type":"basic",
                    "template":"<span name=\"{{name}}\">{{value}}</span>"
                }
            }
        },
        "password":{
            templates : {
                "editView":"\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                    "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                    "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>",
                "loginView":"\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                            "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                            "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>"
            }
        },
        "button":{
            templates : {
                "default":"<a href=\"{{#if route}}#{{buildRoute context model route.action route.options}}" +
                        "{{else}}javascript:void(0){{/if}}\" class=\"btn {{class}} {{#if primary}}btn-primary{{/if}}\">"+
                        "{{#if icon}}<i class=\"{{icon}}\"><\/i>{{/if}}{{label}}<\/a>\n"
            },
            controller: "{" +
                    "render:function(){" +
                        "console.log('button render called!');this.prototype.render.call(this)" +
                    "}" +
                "}"
        },
        "navElement":{
            templates : {
                "default":"<a href=\"{{#if route}}#{{buildRoute context model route.action route.options}}" +
                        "{{else}}javascript:void(0){{/if}}\" class=\"{{class}}\">"+
                        "{{#if icon}}<i class=\"{{icon}}\"><\/i>{{/if}}{{label}}<\/a>\n"
            }
        },
        "textarea":{
            templates : {
                "detailView":"<label class=\"control-label\">{{label}}<\/label>{{value}}\n",
                "editView":"<label class=\"control-label\">{{label}}<\/label>" +
                           "<textarea class=\"input-xlarge\" id=\"textarea\" rows=\"3\">{{value}}</textarea>"
            }
        },
        "url": {
            "detailView": {
                "template": "<a href=\"http://{{value}}\">{{value}}</a>"
            },
            "editView": {
                "template":"<div class=\"controls\"><label class=\"control-label\" for=\"input01\">{{label}}<\/label> "+
                    "<input placeholder=\"www.website.com\" type=\"text\" class=\"input-xlarge\" value=\"{{value}}\">  <p class=\"help-block\">"+
                    "<\/p> <\/div>"
            }
        },
        "email": {
            "detailView": {
                "template": ""
            },
            "editView": {
                "template": ""
            }
        },
        "radioenum": {
            templates: {
                detailView: "<label class=\"control-label\">{{label}}<\/label>{{value}}\n",
                editView: "Mah Field{{value}}{{#each options}}<input type='radio' name='{{name}}' value='{{value}}'><label>{{value}}</label>{{/each}}"
            }
        },
        "image": {
            "detailView": {
                "template":""
            },
            "editView": {
                "template": ""
            },
            "listView": {
                "template": ""
            }
        },
        "sugarField_actionsLink":{
            templates : {
                "default":"<div class=\"btn-group pull-right\"><a class=\"btn\" href=\"#\" data-toggle=\"dropdown\">" +
                          "Actions<span class=\"caret\"><\/span><\/a>"+
                          "<ul class=\"dropdown-menu\"> <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\"><i class=\"icon-list-alt\"><\/i>Details<\/a><\/li> "+
                        "  <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/edit\"><i class=\"icon-pencil\"><\/i> Edit<\/a><\/li>  "+
                        " <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/delete\"><i class=\"icon-trash\"><\/i> Delete<\/a><\/li> <\/ul>     <\/div>"
            }
        },
        "sugarField_fullName":{
            "default":{
                "template":"{{{getfieldvalue model \"first_name\"}}} {{{getfieldvalue model \"last_name\"}}}"
            },
            "detailView":{
                "template":"<h2>{{{getfieldvalue model \"first_name\"}}} {{{getfieldvalue model \"last_name\"}}}<\/h2>"
            }
        },
        "sugarField_primaryAddress":{
            "detailView":{
                "template":"<h3>{{label}}<\/h3>{{{getfieldvalue model \"primary_address_street\"}}}<br> {{{getfieldvalue model \"primary_address_city\"}}},"+
                    " {{{getfieldvalue model \"primary_address_postalcode\"}}} {{{getfieldvalue model \"primary_address_country\"}}}"
            }
        },
        "sugarField_buttonSave":{
            "default":{
                "template":"<button class=\"btn btn-primary\" href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/save\">{{label}}<\/button>"
            }
        },
        "sugarField_buttonCancelSave":{
            "default":{
                "template":"<a class=\"btn btn-primary\" href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/save\">Save<\/a><a class=\"btn btn-primary\" href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\">Cancel<\/a>"
            }
        }

    },
    "fieldsHash":"asq345awaf3asf3"
}
var sugarFieldsGetFieldsResponse = {
    "text":{

        "editView":{

            "type":"basic",
            "template":"<label class=\"control-label\" id=\"{{view.name}}_{{name}}\">{{label}}</label>{{value}}<script>alert('foo')</script>\n",
            "script":"insert_javascript_here"
        },
        "detailView":{
            "type":"basic", "template":"<label class=\"control-label\">{{label}}</label>{{value}}\n"}}, "password":{
        "editView":{
            "type":"basic", "template":"\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}</label>\n\n        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n            <p class=\"help-block\">{{help}}</p>\n        </div>\n    </div>"}}, "button_save":{
        "default":{
            "type":"basic", "template":"<button type=\"submit\" class=\"btn btn-primary\" value={{value}}>{{label}}</button>\n"}}, "textarea":{
        "editView":{
            "type":"basic", "template":"<label class=\"control-label\">{{label}}</label>{{value}}\n"}, "detailView":{
            "type":"basic", "template":"<label class=\"control-label\">{{label}}</label>{{value}}\n"}}, "asdfasd":{
        "asdf":{
            "error":"No such field in field cache."}}};