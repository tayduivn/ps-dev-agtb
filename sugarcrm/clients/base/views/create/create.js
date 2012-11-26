({
    extendsFrom: 'RecordView',

    // button states
    STATE: {
        CREATE: 'create',
        SAVE: 'save',
        EDIT: 'edit',
        DUPLICATE: 'duplicate'
    },

    checkDuplicates: false,

    /**
     * Initialize the view and prepare the model with default button metadata
     * for the current layout.
     */
    initialize: function(options) {
        _.extend(this.events, {
            'click [name=save_button]': 'save',
            'click [name=cancel_button]': 'cancel',
            'click [name=save_create_button]': 'saveAndCreate',
            'click [name=save_view_button]': 'saveAndView',
            'click [name=restore_button]': 'restoreModel'
        });

        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        app.view.views.RecordView.prototype._renderHtml.call(this);
        this.setTitle(app.lang.get('LBL_CREATE_BUTTON_LABEL', this.module) + ' ' + this.moduleSingular);
        this.setButtonAsCreate();
    },

    save: function() {
        //TODO: do not save if we need to check for duplicates
        if (!this.$('[name=save_button]').hasClass('disabled') && !this.checkDuplicates) {
            this.model.save({}, {
                success: _.bind(function() {
                    //TODO: close pushdown modal instead
                    app.navigate(this.context, this.model, 'record');
                }, this)
            });
        }
    },

    cancel: function() {
        this.context.trigger("drawer:hide");
        if (this.context.parent)
            this.context.parent.trigger("drawer:hide");
    },

    bindDataChange: function() {
        app.view.views.RecordView.prototype.bindDataChange.call(this);

        this.model.on("change", function() {
            if (this.model.isValid(undefined, true)) {
                this.setButtonAsSave();
            } else {
                this.setButtonAsCreate();
            }
        }, this);
    },

    /**
     * Handle click on save and create another link
     */
    saveAndCreate: function() {
//        this.context.trigger('quickcreate:saveAndCreate');
    },

    /**
     * Handle click on save and view link
     */
    saveAndView: function() {
//        this.context.trigger('quickcreate:saveAndView');
    },

    /**
     * Handle click on restore to original link
     */
    restoreModel: function() {
//        this.context.trigger('quickcreate:restore');
    },

    /**
     * Change button to Create
     */
    setButtonAsCreate: function() {
        this.setButtonStates(this.STATE.CREATE);
    },

    /**
     * Change button to Ignore Duplicate and Save
     */
    setButtonAsIgnoreDuplicate: function() {
        this.setButtonStates(this.STATE.DUPLICATE);
    },

    /**
     * Change button to Edit
     */
    setButtonAsEdit: function() {
        this.setButtonStates(this.STATE.EDIT);
    },

    /**
     * Change button to Save
     */
    setButtonAsSave: function() {
        this.setButtonStates(this.STATE.SAVE);
    },

    /**
     * Change the behavior of buttons depending on the state that they should be in
     * @param state
     */
    setButtonStates: function(state) {
        var $buttons = {
            save:        this.$("[name=save_button]"),
            saveAndNew:  this.$("[name=save_create_button]"),
            saveAndView: this.$("[name=save_view_button]"),
            cancel:      this.$("[name=cancel]"),
            undo:        this.$("[name=restore_button]")
        };

        switch (state) {
            case this.STATE.CREATE:
                $buttons.save
                    .text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module))
                    .addClass("disabled")
                    .removeClass('btn-primary');
                $buttons.saveAndNew.toggleClass('hide', true);
                $buttons.saveAndView.toggleClass('hide', true);
                $buttons.undo.toggleClass('hide', true);
                break;
            case this.STATE.SAVE:
                $buttons.save
                    .text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module))
                    .removeClass("disabled")
                    .addClass('btn-primary');
                $buttons.saveAndNew.toggleClass('hide', false);
                $buttons.saveAndView.toggleClass('hide', false);
                $buttons.undo.toggleClass('hide', true);
                break;
            case this.STATE.EDIT:
                break;
            case this.STATE.DUPLICATE:
                break;
            default:
                $buttons.save
                    .text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module))
                    .addClass("disabled")
                    .removeClass('btn-primary');
                $buttons.saveAndNew.toggleClass('hide', true);
                $buttons.saveAndView.toggleClass('hide', true);
                $buttons.undo.toggleClass('hide', true);
                break;
        }
    }

})