({
    events: {
        'click [name=save_button]': 'save',
        'click [name=cancel_button]': 'cancel',
        'click [name=save_create_button]': 'saveAndCreate',
        'click [name=save_view_button]': 'saveAndView',
        'click [name=restore_button]': 'restoreModel'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.context.on('quickcreate:actions:setButtonAsIgnoreDuplicate', this.setButtonAsIgnoreDuplicate, this);
        this.context.on('quickcreate:actions:setButtonAsSave', this.setButtonAsSave, this);
        this.context.on('quickcreate:actions:setButtonAsEdit', this.setButtonAsEdit, this);

        this.context.on('quickcreate:restore', this.setButtonAsSave, this);
        this.context.on('quickcreate:edit', this.setButtonAsEdit, this);
    },
    
    restoreModel: function() {
        this.context.trigger('quickcreate:restore');
    },
    
    /**
     * Handle click on save button
     */
    save: function() {
        this.context.trigger('quickcreate:save');
    },

    /**
     * Handle click on cancel button
     */
    cancel: function() {
        this.context.trigger('quickcreate:cancel');
    },

    /**
     * Handle click on save and create another button
     */
    saveAndCreate: function() {
        this.context.trigger('quickcreate:saveAndCreate');
    },

    /**
     * Handle click on save and view button
     */
    saveAndView: function() {
        this.context.trigger('quickcreate:saveAndView');
    },

    /**
     * Change button to Ignore Duplicate and Save
     */
    setButtonAsIgnoreDuplicate: function() {
        this.initializeButtons({ duplicate: true });
    },

    /**
     * Change button to Edit
     */
    setButtonAsEdit: function() {
        this.initializeButtons({ edit: true });
    },

    /**
     * Change button to Save
     */
    setButtonAsSave: function() {
        this.initializeButtons();
    },

    /**
     * Set states for action buttons
     * @param options
     */
    initializeButtons: function(options) {
        options = options || {};
        
        if ( !this.$buttons ) {
            this.$buttons = {
                save:        this.$("[name=save_button]"),
                saveAndNew:  this.$("[name=save_create_button]"),
                saveAndView: this.$("[name=save_view_button]"),
                cancel:      this.$("[name=cancel]"),
                undo:        this.$("[name=restore_button]")
            };
        }
        var $buttons = this.$buttons;

        if ( options.duplicate === true ) {
            $buttons.save
                    .text(app.lang.get('LBL_IGNORE_DUPLICATE_AND_SAVE', this.module));
            $buttons.saveAndNew.hide();
            $buttons.saveAndView.hide();
        } else {
            $buttons.save
                    .text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module));
            $buttons.saveAndNew.show();
            $buttons.saveAndView.show();

            if ( options.edit === true ) {
                $buttons.undo.show();
            } else {
                $buttons.undo.hide();
            }
        }
    }
})
