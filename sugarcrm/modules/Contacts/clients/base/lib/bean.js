//BEGIN SUGARCRM flav=ent ONLY
/**
 * Extensions to the Contacts sidecar bean.
 * Included by JSGroupings.php
 */
(function(app) {
    app.events.on("app:sync:complete", function(){

        var contactsClass = app.data.getBeanClass("Contacts");
        /**
         * Need to return undefined since validation for Contact is asynchronous.
         * Validation result is passed with 'validation:complete' event.
         * @returns {undefined} Undefined when portal_name has changed, otherwise a boolean indicating if model is valid or not
         */
        contactsClass.prototype.isValid = function(){
            app.data.beanModel.prototype.isValid.apply(this, arguments);
            return;
        };

        /**
         * Custom validation needed for Contacts beans when changing portal_name field.  We need to do a uniqueness
         * check to make sure no two contacts have the same portal_name since it is the user id for Portal.
         *
         * Model validation is deferred, actual validation result is passed with 'validation:complete' event.
         */
        contactsClass.prototype._doValidate = function() {
            var self = this,
                errors = {},
                origArgs = arguments,
                skip = false;
            if(_.isUndefined(this.get("id"))){
                // If new and portal_name is not set, skip checking portal_name
                if(!this.has("portal_name") || this.get("portal_name") === ""){
                    skip = true;
                }
            } else {
                // If not new and portal name has not changed since last sync, skip checking portal_name
                if(_.isUndefined(this.changedAttributes(this._syncedAttributes)["portal_name"])){
                    skip = true;
                }
            }
            errors = app.data.beanModel.prototype._doValidate.apply(self, origArgs);
            if(skip){
                _.defer(function(errors, self){
                    completeValidation(errors, self);
                }, errors, self);
                return;
            }

            // portal_name was changed
            var currentName = self.get("portal_name");
            var alertOptions = {
                title: app.lang.get("LBL_VALIDATING"),
                level: "process"
            };
            app.alert.show('validation', alertOptions);
            app.api.records('read', 'Contacts', null, {
                filter: [
                    {
                        portal_name: currentName
                    }
                ]
            }, {
                success: function(data){
                    if(data.records && data.records.length > 0){
                        /**
                         * If there is more than one Contact with this portal_name
                         * or the found record is not the same as the current one,
                         *   then we have a duplicate.
                         */
                        if(data.records.length > 1 || data.records[0].id != self.get("id")){
                            errors['portal_name'] = {
                                ERR_EXISTING_PORTAL_USERNAME: ''
                            };
                        }
                    }
                },
                error: function(){
                    errors['portal_name'] = {
                        ERR_PORTAL_NAME_CHECK: ''
                    };
                },
                /**
                 * After check is done, close alert and trigger the completion of the validation to the editor
                 */
                complete: function(){
                    completeValidation(errors, self);
                }
            });
            // When using delayed validation, isValid method should return undefined so that editor
            // can attach a listener for validation:complete event
            return;
        };
        var completeValidation = function(errors, self){
            app.alert.dismiss('validation');
            // Trigger events for all error fields
            self._processValidationErrors(errors);
            if(_.isEmpty(errors)){
                self.trigger('validation:success');
            }
            self.trigger('validation:complete', _.isEmpty(errors));
        };
    });



})(SUGAR.App);
//END SUGARCRM flav=ent ONLY
