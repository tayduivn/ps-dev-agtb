//TODO DEPRICATED SEE METADATAMANAGER FOR NEW CALL

(function (app) {
    //var privateVars;
    app.augment('sugarFieldManager',
        (function () {
            var instance;

            function init(args) {
                instance = new SugarFieldManager();
                _.bindAll(instance);
                return instance;
            }

            function SugarFieldManager() {
                return {
                    //TODO move this to global cache
                    fieldsObj:{},
                    fieldsHash:'',
                    fieldTypeMap:{
                        varchar:"text",
                        name:"text",
                        text:"textarea"
                    },
                    fieldHandlers: {},

                    get : function(def){
                        return new app.SugarField(def);
                    }

                };
            }

            return instance || init();
        }()));

    //SugarFields are obects that will listen to models and massage the data and handle events on the field
    app.augment("sugarFields", (function(){
        function Base(def, model){
            model.on("change:" + def.name, function(model, value) {
                console.log(value);
            });
        }

        function Name(def, model){
            var newValue = "";
            model.on("change:" + def.name, function(model, value) {
                if (value != newValue) {
                    newValue = model.first_name + " " + model.last_name;
                    model.set(def.name, newValue);
                }
            });
        }

    }));
}(SUGAR.App));