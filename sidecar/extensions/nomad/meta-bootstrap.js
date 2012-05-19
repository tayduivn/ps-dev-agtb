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

    app.events.on("app:init", function() {
        app.metadata.set(base_metadata);
        app.data.declareModels(base_metadata);

        // Example of a custom route: URL hash, route name, route handler
        app.router.route("foo", "foo", function() {
            app.logger.debug("Handling route foo!!!");
        });
    });

})(SUGAR.App);
