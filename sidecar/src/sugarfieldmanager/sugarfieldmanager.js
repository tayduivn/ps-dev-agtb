/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 2/2/12
 * Time: 5:20 PM
 * To change this template use File | Settings | File Templates.
 */
(function (app) {
    //var privateVars;
    app.augment('sugarFieldManager',
        (function () {
        var instance;

        function init(args) {
            instance = new SugarFieldManager();
            _.bind(instance.handleResponse, instance);
            return instance
        };

        function SugarFieldManager() {
            return {
                //TODO move this to global cache
                fieldsObj:{},
                fieldsHash:'',


                /**
                 * Retrieves sugarFields and stores them internally
                 *
                 * @return bool
                 */
                syncFields:function () {
                    // call api field sync with current field hash
                    //TODO put real api call
                    var that = this;
                    result = SUGAR.App.sugarFieldsSync(that, this.handleResponse);
                    return result;
                },

                /**
                 * Callback handles API response for sugarfields
                 *
                 * @param  obj that sugarfield manager object
                 * @param  obj response response from sugarFields
                 * @return bool
                 */
                handleResponse:function (that, response) {
                    // if we got something set fields and list
                    if (response.fieldsHash != that.fieldsHash) {
                        that.fieldsObj = response.fieldsData;
                        that.fieldsHash = response.fieldsHash;
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
                    var result = {};
                    var fname = "";
                    var hasView = false;

                    // loop over fields and set them in the result
                    for (field in fields) {
                        fname = fields[field].name;

                        if (fields[field].view) {
                            hasView = true;
                        }

                        if (!(result[fname])) {
                            result[fname] = {}; // pre allocate the field in results
                        }

                        // assign fields to results if set
                        if (hasView && this.fieldsObj[fname] && this.fieldsObj[fname][fields[field].view]) {
                            result[fname][fields[field].view] = this.fieldsObj[fname][fields[field].view];

                        // fall back to default if field for this view doesnt exist
                        } else if (this.fieldsObj[fname] && this.fieldsObj[fname]['default']) {
                            result[fname]['default'] = this.fieldsObj[fname]['default'];
                        } else {
                            result[fname] = {error:"No such field in field cache."};
                        }

                    }
                    //return results
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

        return {
            getInstance:function (args) {
                return instance || init(args);
            }
        };
    }()))
}(SUGAR.App));