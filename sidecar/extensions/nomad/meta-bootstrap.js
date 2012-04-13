(function(app) {
    var base_metadata = {
        _hash: '',
        "modules": {
            "Home": {
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

                },
                "layouts": {
                    "login": {
                        //Default layout is a single view
                        "type": "simple",
                        "components": [
                            {view: "login"}
                        ]
                    }
                }
            }
        }
    };
    app.cache.init();
    app.metadata.set(base_metadata);
    app.data.declareModels(base_metadata);
})(SUGAR.App);