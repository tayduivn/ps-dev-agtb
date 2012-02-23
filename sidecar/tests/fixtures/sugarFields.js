var sugarFieldsFixtures = {
    "fieldsList":["text", "password", "button_save", "textarea", "textarea"],
    "fieldsData":{
        "text":{
            "detailView":{
                "type":"basic",
                "template":"<label class=\"control-label\">{{label}}<\/label><span name=\"{{name}}\">{{value}}</span>\n"},
            "editView":{
                "type":"basic",
                "template":" <div class=\"control-group\">\n" +
                    "        <label class=\"control-label\" for=\"input01\">{{label}}<\/label>\n\n" +
                    "        <div class=\"controls\">\n            <input type=\"text\" class=\"input-xlarge\" name=\"{{name}}\" value=\"{{value}}\">\n\n" +
                    "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>\n"
            },
            "default":{
                "type":"basic",
                "template":"<span name=\"{{name}}\">{{value}}</span>"
            }},
        "password":{
            "editView":{
                "type":"basic",
                "template":"\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{label}}<\/label>\n\n" +
                    "        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n" +
                    "            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>"}},
        "button_save":{
            "default":{
                "type":"basic", "template":"<button type=\"submit\" class=\"btn btn-primary\" value={{value}}>{{label}}<\/button>\n"}},
        "textarea":{
            "detailView":{
                "type":"basic",
                "template":"<label class=\"control-label\">{{label}}<\/label>{{value}}\n"},
            "editView":{
                "type":"basic",
                "template":"<label class=\"control-label\">{{label}}<\/label>{{value}}\n"}
        },
        "sugarField_actionsLink":{
            "default":{
                "template":"<div class=\"btn-group\"><a class=\"btn\" href=\"#\" data-toggle=\"dropdown\">Actions<span class=\"caret\"><\/span><\/a>"+
                    "<ul class=\"dropdown-menu\"> <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/\"><i class=\"icon-list-alt\"><\/i>Details<\/a><\/li> "+
                    "  <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/edit\"><i class=\"icon-pencil\"><\/i> Edit<\/a><\/li>  "+
                    " <li><a href=\"#{{model.module}}\/{{{getfieldvalue model \"id\"}}}\/delete\"><i class=\"icon-trash\"><\/i> Delete<\/a><\/li> <\/ul>     <\/div>"
            }
        },
        "sugarField_fullName":{
            "default":{
                "template":"{{{getfieldvalue model \"first_name\"}}} {{{getfieldvalue model \"last_name\"}}}"
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