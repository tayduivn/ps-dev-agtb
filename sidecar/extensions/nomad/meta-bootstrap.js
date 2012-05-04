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
                                        {name: "username", label: "Username"},
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
                            "type": "simple",
                            "components": [
                                {view: "login"}
                            ]
                        }
                    }
                }
            }
        }
    };
    app.cache.init();
    app.metadata.set(base_metadata);
    app.data.declareModels(base_metadata);
})(SUGAR.App);
