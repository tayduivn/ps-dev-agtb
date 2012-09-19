({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('quickcreate:clear', this.clear, this);
        this.context.on('quickcreate:validateModel', this.validateModel, this);
        /*
        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            if (true || this.editMode) {
                this.$(".record-save-prompt").show();
            }

            this.previousModelState = this.model.previousAttributes();
        }, this);
*/
    },

    render: function() {
        var totalFieldCount = 0;

        _.each(this.meta.panels, function(panel) {
            var columns = (panel.columns) || 2,
                count = 0,
                rows = [],
                row = [];

            _.each(panel.fields, function(field) {
                var maxSpan;

                if (_.isUndefined(panel.labels)) {
                    panel.labels = true;
                }
                //8 for span because we are using a 2/3 ratio between field span and label span with a max of 12
                maxSpan = (panel.labels) ? 8 : 12;

                if (_.isUndefined(field.span)) {
                    field.span = Math.floor(maxSpan / columns);
                }

                //4 for label span because we are using a 1/3 ratio between field span and label span with a max of 12
                if (_.isUndefined(field.labelSpan)) {
                    field.labelSpan = Math.floor(4 / columns);
                }

                totalFieldCount++;
                field.index = totalFieldCount;
                row.push(field);

                if (count % columns == columns - 1) {
                    rows.push(row);
                    row = [];
                }

                count++;
            }, this);

            panel.grid = rows;
        }, this);

        app.view.View.prototype.render.call(this);
    },

    // Overloaded functions
    _renderHtml: function() { // Use original original
        app.view.View.prototype._renderHtml.call(this);
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
    },

    /**
     * Clears out field values
     */
    clear: function() {
        this.model.clear();
        this.model.set(this.model._defaults);
    },

    validateModel: function(success, failure) {
        if(this.model.isValid(this.getFields(this.module))) {
            success();
        } else {
            failure();
        }
    }
})



