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
                    //TODO move this to global cache
                    fieldsObj:{},
                    fieldsHash:'',
                    fieldTypeMap:{
                        varchar:"text",
                        name:"text",
                        text:"textarea"
                    },
                    fieldHandlers: {},

                    /**
                     * Retrieves sugarFields and stores them internally
                     *
                     * @return bool
                     */
                    syncFields:function () {
                        // call api field sync with current field hash
                        //TODO put real api call
                        var that = this;
                        var callbacks = {success: this.handleResponse, error: this.handleResponse};
                        return SUGAR.Api.getInstance().getSugarFields(this.fieldsHash, callbacks);
                    },

                    /**
                     * Callback handles API response for sugarfields
                     *
                     * @param  obj that sugarfield manager object
                     * @param  obj response response from sugarFields
                     * @return bool
                     */
                    handleResponse:function (response) {
                        // if we got something set fields and list
                        if (instance && response.fieldsHash != instance.fieldsHash) {
                            instance.fieldsObj = response.fieldsData;
                            instance.fieldsHash = response.fieldsHash;
                            return true;
                        } else {
                            return false;
                        }
                    },

                    /**
                     * Gets sugarFields from cache
                     *
                     * @param  array array of field objects that follow {fname:"xyz", view:"editView"}
                     * @return obj of sugar fields stored by fieldname.viewtype
                     */
                    getFields:function (fields) {

                        // init results
                        var fresult = {};
                        var result = {};
                        var name = "";
                        var view = "";
                        var field;

                        // loop over fields and set them in the result
                        for (field in fields) {

                            name = fields[field].name;

                            if (fields[field].view) {
                                view = fields[field].view;
                            }

                            if (!(result[name])) {
                                result[name] = {}; // pre allocate the field in results
                            }

                            fresult = this.getField(name, view);
                            if (view) {
                                result[name][fields[field].view] = fresult;
                            } else {
                                result[name] = fresult;
                            }

                        }
                        //return results
                        return result;
                    },


                    /**
                     * Gets sugarField from cache
                     * @param name string name of field to retrieve
                     * @param view string optional name of view the field widget is going to be used on
                     * @return obj of sugar fields stored by fieldname.viewtype
                     */
                    getField:function (name, view) {
                        // init results
                        var result = {}, field;

                        name = this.fieldTypeMap[name] || name;

                        if (this.fieldsObj[name])
                        {
                            field = this.fieldsObj[name].views || this.fieldsObj[name] ;

                            // assign fields to results if set
                            if (view && field[view]) {
                                result = field[view];
                                // fall back to default if field for this view doesnt exist
                            } else if (field['default']) {
                                result = field['default'];
                            } else {
                                result = {error:name + ": No such field in field cache."};
                            }
                            if (result.template && !result.templateC) {
                                result.templateC = app.template.get(name + ":" + view);
                                if (!result.templateC)
                                    result.templateC = app.template.compile(result.template, name + ":" + view);
                            }
                        } else
                            result = {error:name + ": No such field in field cache."};
                        //return result
                        return result;
                    },

                    get : function(def){
                        return new app.SugarField(def);
                    },

                    /**
                     * Get an object that contains the setter and getter functions for this type of it exists
                     * @param name
                     */
                    getFieldHandler:function(name) {
                        name = this.fieldTypeMap[name] || name;
                        if (this.fieldsObj[name] && this.fieldsObj[name].handler){
                            if (typeof(this.fieldsObj[name].handler) == "string") {
                                try {
                                    this.fieldsObj[name].handler = eval(this.fieldsObj[name].handler);
                                }catch(e){
                                    app.logger.error(e);
                                }
                            }

                            return this.fieldsObj[name].handler;
                        }

                        return null;
                    },

                    /**
                     * Resets sugarFieldManager internal state to blank
                     *
                     * @return bool
                     */
                    reset:function () {
                        //reset all my internal variables
                        this.fieldsObj = {};
                        this.fieldsHash = '';

                        return true;
                    },

                    getFieldClass : {

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