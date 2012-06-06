(function(app) {
    app.baseMetadata = {
        _hash: '',
        "modules": {
            "Login": {
                "fields": {
                    "username": {
                        "name": "username",
                        "type": "varchar",
                        required: true
                    },
                    "password": {
                        "name": "password",
                        "type": "password",
                        required: true
                    },
                    "url": {
                        "name": "url",
                        "type": "url",
                        required: true
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
