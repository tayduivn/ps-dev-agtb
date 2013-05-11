({
    events: {
        'click [name="record-close"]': 'closeClicked',
        'click [name="record-close-new"]': 'closeNewClicked'
    },
    extendsFrom: 'ButtonField',
    initialize: function (options) {
        app.view.invokeParent(this, {type: 'field', name: 'button', method:'initialize', args:[options]});
        this.type = 'button';
    },
    closeClicked: function () {
        this._close(false);
    },
    closeNewClicked: function () {
        this._close(true);
    },
    _render: function () {
        if (this.model.get('status') === 'Completed') {
            this.hide();
        } else {
            app.view.invokeParent(this, {type: 'field', name: 'button', method: '_render'});
        }
    },
    _close: function (createNew) {
        var self = this;

        this.model.set('status', 'Completed');
        this.model.save({}, {
            success: function () {
                app.alert.show('close_task_success', {level: 'success', autoClose: true, title: app.lang.get('LBL_TASK_CLOSE_SUCCESS', self.module)});
                if (createNew) {
                    var module = app.metadata.getModule(self.model.module);
                    var prefill = app.data.createBean(self.model.module);
                    prefill.copy(self.model);

                    if (module.fields.status && module.fields.status['default']) {
                        prefill.set('status', module.fields.status['default']);
                    } else {
                        prefill.unset('status');
                    }

                    app.drawer.open({
                        layout: 'create-actions',
                        context: {
                            create: true,
                            model: prefill
                        }
                    }, function () {
                        if (self.parent) {
                            self.parent.render();
                        } else {
                            self.render();
                        }
                    });
                }
            },
            error: function (error) {
                app.alert.show('close_task_error', {level: 'error', autoClose: true, title: app.lang.getAppString('ERR_AJAX_LOAD')});
                app.logger.error('Failed to close a task. ' + error);

                // we didn't save, revert!
                self.model.revertAttributes();
            }
        });
    },
    bindDataChange: function () {
        if (this.model) {
            this.model.on("change:status", this.render, this);
        }
    }
})
