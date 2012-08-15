/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        'click [name=save_button]': 'saveModel'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("subnav:save", null, this);
        this.context.on("subnav:save", this.saveModel, this);
        this.model.on("error:validation", this.handleValidationError, this);
    },
    saveModel: function() {
        var self = this;

        // TODO we need to dismiss this in global error handler
        app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});
        this.model.save(null, {
            success: function() {
                app.alert.dismiss('save_edit_view');
                self.app.navigate(self.context, self.model, 'detail');
            },
            fieldsToValidate: this.getFields(this.module)
        });
    },
    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);
        if (this.model.id) {
            this.model.on("change", function() {
                if (this.context.get('subnavModel')) {
                    this.context.get('subnavModel').set({
                        'title': app.lang.get('LBL_EDIT_BUTTON', this.module),
                        'meta': this.meta,
                        'fields': this.fields
                    });
                }
            }, this);
        } else {
            if (this.context.get('subnavModel')) {
                this.context.get('subnavModel').set({
                    'title': app.lang.get('LBL_NEW_FORM_TITLE', this.module),
                    'meta': this.meta,
                    'fields': this.fields
                });
            }
        }
    },

    handleValidationError:function (errors) {
        var self = this;

        _.each(errors, function (fieldErrors, fieldName) {
            //retrieve the field by name
            var field = self.getField(fieldName);
            if (field) {
                var controlGroup = field.$el.parents('.control-group:first');

                if (controlGroup) {
                    //Clear out old messages
                    controlGroup.find('.add-on').remove();
                    controlGroup.find('.help-block').html("");

                    controlGroup.addClass("error");
                    controlGroup.find('.controls').addClass('input-append');
                    _.each(fieldErrors, function (errorContext, errorName) {
                        controlGroup.find('.help-block').append(self.app.error.getErrorString(errorName, errorContext));
                    });
                    controlGroup.find('.controls input:last').after('<span class="add-on"><i class="icon-exclamation-sign"></i></span>');
                }
            }
        });
    }
})
