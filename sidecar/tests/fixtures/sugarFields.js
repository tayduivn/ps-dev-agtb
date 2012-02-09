var sugarFieldsFixtures = {
    "fieldsData":{
        "text":{
            "DetailView":{
                "type":"basic",
                "template":"<label class=\"control-label\">{{label}}<\/label>{{value}}"
            },
            "EditView":{
                "type":"basic",
                "template":"<div class=\"control-group\">\n        <label class=\"control-label\" for=\"input01\">{{label}}<\/label>\n\n        <div class=\"controls\">\n            <input type=\"text\" class=\"input-xlarge\" id=\"\" value=\"{{value}}\">\n\n            <p class=\"help-block\">{{help}}<\/p>\n        <\/div>\n    <\/div>"
            }
        },
        "password":{
            "EditView":{
                "type":"basic",
                "template":"<script type=\"text\/html\" id=\"password_EditView\">\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{field.label}}<\/label>\n\n        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{field.value}}\">\n\n            <p class=\"help-block\">{{field.help}}<\/p>\n        <\/div>\n    <\/div>\n<\/script>"
            }
        },
        "button_save":{
            "default":{
                "type":"basic",
                "template":"<script type=\"text\/html\" id=\"button_save\">\n    <button type=\"submit\" class=\"btn btn-primary\" value={{field.value}}>{{field.label}}<\/button>\n<\/script>"
            }
        },
        "textarea":{
            "DetailView":{
                "type":"basic",
                "template":"<script type=\"text\/html\" id=\"textarea_DetailView\">\n    <label class=\"control-label\">{{field.label}}<\/label>{{field.value}}\n<\/script>"
            },
            "EditView":{
                "type":"basic",
                "template":"<script type=\"text\/html\" id=\"textarea_DetailView\">\n    <label class=\"control-label\">{{field.label}}<\/label>{{field.value}}\n<\/script>"
            }
        }
    }, "fieldsHash":"asq345awaf3asf3"}
var sugarFieldsGetFieldsResponse = {
    "text":{
        "EditView":{
            "type":"basic",
            "template":"<script type=\"text/html\" id=\"text_EditView\">\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input01\">{{field.label}}</label>\n\n        <div class=\"controls\">\n            <input type=\"text\" class=\"input-xlarge\" id=\"\" value=\"{{field.value}}\">\n\n            <p class=\"help-block\">{{field.help}}</p>\n        </div>\n    </div>\n</script>"
        },
        "DetailView":{
            "type":"basic",
            "template":"<script type=\"text/html\" id=\"text_DetailView\">\n    <label class=\"control-label\">{{field.label}}</label>{{field.value}}\n</script>"
        }
    },
    "password":{
        "EditView":{
            "type":"basic",
            "template":"<script type=\"text/html\" id=\"password_EditView\">\n    <div class=\"control-group\">\n        <label class=\"control-label\" for=\"input02\">{{field.label}}</label>\n\n        <div class=\"controls\">\n            <input type=\"password\" class=\"input-xlarge\" id=\"\" value=\"{{field.value}}\">\n\n            <p class=\"help-block\">{{field.help}}</p>\n        </div>\n    </div>\n</script>"
        }
    },
    "button_save":{
        "default":{
            "type":"basic",
            "template":"<script type=\"text/html\" id=\"button_save\">\n    <button type=\"submit\" class=\"btn btn-primary\" value={{field.value}}>{{field.label}}</button>\n</script>"
        }
    },
    "textarea":{
        "EditView":{
            "type":"basic",
            "template":"<script type=\"text/html\" id=\"textarea_DetailView\">\n    <label class=\"control-label\">{{field.label}}</label>{{field.value}}\n</script>"
        },
        "DetailView":{
            "type":"basic",
            "template":"<script type=\"text/html\" id=\"textarea_DetailView\">\n    <label class=\"control-label\">{{field.label}}</label>{{field.value}}\n</script>"
        }
    },
    "asdfasd":{
        "error":"No such field in field cache."
    }
}