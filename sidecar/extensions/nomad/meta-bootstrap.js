(function(app) {
    app.baseMetadata = {
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
                    },
                    "url": {
                        "name": "url",
                        "type": "url"
                    }
                },
                "views": {
                    "login": {
                        "meta": {
                            "panels": [
                                {
                                    "label": "Login",
                                    "fields": [
                                        {name: "username", label: "Username", placeholder: "Username or Email"},
                                        {name: "password", label: "Password"},
                                        {name: "url", label: "URL"}
                                    ]
                                }
                            ]
                        }
                    }
                },
                "layouts": {
                    "login": {
                        "meta": {
                            //Default layout is a single view
                            "type": "login",
                            "components": [
                                {view: "login"}
                            ]
                        }
                    }
                }
            }
        }
    };

})(SUGAR.App);
