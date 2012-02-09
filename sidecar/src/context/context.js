(function(app) {
    function Context(obj, data) {
        return {
            state: {
                url: null,
                module: null,
                collection: null,
                model: null
            },

            get: function() {

            },

            set: function() {

            },

            reset: function() {

            },

            fire: function() {

            },

            /**
             * Takes parameters from another source and stores their state.
             *
             * Note: This function should be called everytime a new route routed.
             * @param obj
             */
            init: function() {

            }
        };
    }

    app.augment("context", {
        getContext: function(obj, data) {
            return new Context(obj, data);
        }
    });
})(SUGAR.App);