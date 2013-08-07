({
    /**
     * User Profile wizard page for the FirstLoginWizard
     * @class View.Views.UserWizardPageView
     * @alias SUGAR.App.view.views.UserWizardPageView
     */
    extendsFrom: "WizardPageView",
    /**
     * @override
     * @param options
     */
    initialize: function(options){
        //Load the default wizard page template, if you want to.
        options.template = app.template.getView("wizard-page");
        app.view.invokeParent(this, {type: 'view', name: 'wizard-page', method: 'initialize', args:[options]});
        this.fieldsToValidate = this._fieldsToValidate(this.options.meta);
    },
    /**
     * @override
     * @returns {boolean}
     */
    isPageComplete: function(){
        return this.areAllRequiredFieldsNonEmpty;
    },
    /**
     * Listen to changes on required fields. If all required fields contain one or more characters
     * we update the `this.areAllRequiredFieldsNonEmpty` flag and, if status changed from before we
     * call `this.updateButtons` which will enable/disable next button as appropriate.
     * @param {Object} evt the event
     */
    checkIfPageComplete: function(evt) {
        var anyEmpty = false,
            self = this,
            last = this.areAllRequiredFieldsNonEmpty;

        //Check required fields on page to see if they have at least one character. Checks
        //only required <input> elements since profile page's required fields are such.
        this.$('[data-fieldname] input').each(function(index, el) {
            if (self.$(el).hasClass('required')) {
                var val = $.trim(self.$(el).val());
                if (!val.length > 0) {
                    anyEmpty = true;
                }
            }
        });
        this.areAllRequiredFieldsNonEmpty = !anyEmpty;
        // Update buttons if applicable and something's actually changed
        if (!last || last !== this.areAllRequiredFieldsNonEmpty) {
            this.updateButtons();
        }
    },
    /**
     * Prepares HTTP payload
     * @return {Object} Payload with fields we want to update
     * @private
     */
    _prepareRequestPayload: function() {
        var payload = {},
            self = this,
            fields = _.keys(this.fieldsToValidate);
        _.each(fields, function(key) {
            payload[key] = self.model.get(key);
        })
        return payload;
    },
    /**
     * Called before we allow user to proceed to next wizard page. Does the validation and profile update.
     * @param {Function} callback The callback to call once HTTP request is completed.
     * @override
     */
    beforeNext: function(callback) {
        var self = this;
        this.clearValidationErrors();
        this.model.doValidate(this.fieldsToValidate,
            _.bind(function(isValid) {
                if (isValid) {
                    var payload = self._prepareRequestPayload();
                    app.alert.show('wizardprofile', {level: 'process', title: app.lang.getAppString('LBL_LOADING'), autoClose: false});
                    app.user.updateProfile(payload, function(err) {
                        app.alert.dismiss('wizardprofile');
                        if (err) {
                            app.logger.debug("Wizard profile update failed: " + err);
                            callback(false);
                        } else {
                            app.logger.debug("Wizard profile updated successfully!");
                            callback(true);
                        }
                    });
                } else {
                    callback(false);
                }
            }, self)
        );
    }

})
