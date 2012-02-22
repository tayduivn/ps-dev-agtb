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

            ;

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

                        // loop over fields and set them in the result
                        for (field in fields) {

                            name = fields[field]['name'];

                            if (fields[field]['view']) {
                                view = fields[field]['view'];
                            }

                            if (!(result[name])) {
                                result[name] = {}; // pre allocate the field in results
                            }

                            fresult = this.getField(name, view);
                            if (view) {
                                result[name][fields[field]['view']] = fresult;
                            } else {
                                result[name] = fresult;
                            }

                        }
                        //return results
                        return result;
                    },


                    /**
                     * Gets sugarField from cache
                     *
                     * @param  object that follows {fname:"xyz", view:"editView"}
                     * @return obj of sugar fields stored by fieldname.viewtype
                     */
                    getField:function (name, view) {
                        // init results
                        var result = {};

                        name = this.fieldTypeMap[name] || name;

                        // assign fields to results if set
                        if (view && this.fieldsObj[name] && this.fieldsObj[name][view]) {
                            result = this.fieldsObj[name][view];
                            // fall back to default if field for this view doesnt exist
                        } else if (this.fieldsObj[name] && this.fieldsObj[name]['default']) {
                            result = this.fieldsObj[name]['default'];
                        } else {
                            result = {error:name + ": No such field in field cache."};
                        }
                        if (result.template && !result.templateC) {
                            result.templateC = app.template.get(name + ":" + view);
                            if (!result.templateC)
                                result.templateC = app.template.compile(result.template, name + ":" + view);
                        }
                        //return result
                        return result;
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
                    }
                };
            };

            return instance || init();
            ;
        }()))
}(SUGAR.App));