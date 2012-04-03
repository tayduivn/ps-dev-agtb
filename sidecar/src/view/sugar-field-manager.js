//TODO DEPRICATED SEE METADATAMANAGER FOR NEW CALL

(function (app) {

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
                    fieldTypeMap : {
                        varchar: "text",
                        name: "text",
                        text: "textarea"
                    },

                    get : function(params){
                        var meta, controller;
                        var type = params.def && params.def.type ? params.def && params.def.type : false;
                        var fClass = this.fieldTypeMap[type] ? this.fieldTypeMap[type] : type;
                        if (type && app.sugarField[fClass])
                            return new app.sugarField[fClass](params);

                        meta = app.metadata.get({sugarField:{type: type}});
                        controller = meta.controller;
                        if (controller) {
                            try {
                                var obj = eval("(" + controller + ")");
                                if (typeof (obj) == "object"){
                                    app.sugarField[fClass] = app.sugarField.base.extend(obj);
                                }
                                return new app.sugarField[fClass](params);
                            } catch(e) {
                                app.logger.error("invalid field controller " + fClass + " : " + controller);
                            }
                        }

                        return new app.sugarField.base(params);
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