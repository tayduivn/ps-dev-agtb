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
                "type":"basic",
                "template":"<button type=\"submit\" class=\"btn btn-primary\" value={{value}}>{{label}}<\/button>\n"}},
        "button":{
            "default":{
                "type":"basic",
                "template":"<button {{#if primary}}class=\"btn btn-primary\"{{/if}} value=\"{{value}}\" " +
                    "{{#if route}}onclick=\"" +
                        "SUGAR.App.router.navigate('{{buildRoute context route.action model route}}', {trigger: true}); return false;\"" +
                    "{{/if}}>{{label}}<\/button>\n"
            }
        },
        "textarea":{
            "detailView":{
                "type":"basic",
                "template":"<label class=\"control-label\">{{label}}<\/label>{{value}}\n"},
            "editView":{
                "type":"basic",
                "template":"<label class=\"control-label\">{{label}}<\/label>{{value}}\n"}
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