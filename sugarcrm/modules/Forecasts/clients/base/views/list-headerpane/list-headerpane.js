/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    /**
     * Who is my parent
     */
    extendsFrom: 'HeaderpaneView',
    plugins: ['FieldErrorCollection'],

    /**
     * Boolean if the Save button should be disabled or not
     */
    saveBtnDisabled: true,

    /**
     * Boolean if the Save button should be disabled or not
     */
    commitBtnDisabled: true,

    /**
     * Boolean if any fields in the view have errors or not
     */
    fieldHasErrorState: false,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super("initialize", [options]);

        this.on('render', function() {
            this.getField('save_draft_button').setDisabled();
            // this is a hacky way to add the class but it needs to be done for proper spacing
            this.getField('save_draft_button').$el.addClass('btn-group');
            this.getField('commit_button').setDisabled();
        }, this);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.context.on('change:selectedUser', function(model, changed) {
            this.title = changed.full_name;
            if (!this.disposed) {
                this.render();
            }
        }, this);

        this.context.on('plugin:fieldErrorCollection:hasFieldErrors', function(collection, hasErrors) {
            this.fieldHasErrorState = hasErrors;
            this.setButtonStates();
        }, this)

        this.context.on('button:print_button:click', function() {
            window.print();
        }, this);

        this.context.on('forecasts:worksheet:is_dirty', function(worksheet_type, is_dirty) {
            this.saveBtnDisabled = !is_dirty;
            this.commitBtnDisabled = !is_dirty;
            this.setButtonStates();
        }, this);

        this.context.on('button:commit_button:click button:save_draft_button:click', function() {
            this.saveBtnDisabled = true;
            this.commitBtnDisabled = true;
            this.setButtonStates();
        }, this);

        this.context.on('forecasts:worksheet:saved', function(totalSaved, worksheet_type, wasDraft){
            if(wasDraft === true) {
                this.commitBtnDisabled = false;
                this.setButtonStates();
            }
        }, this);

        this.context.on('forecasts:worksheet:needs_commit', function(worksheet_type) {
            this.commitBtnDisabled = false;
            this.setButtonStates();
        }, this);

        this._super("bindDataChange");
    },

    /**
     * Sets the Save Button and Commit Button to enabled or disabled
     */
    setButtonStates: function() {
        // fieldHasErrorState trumps the disabled flags, but when it's cleared
        // revert back to whatever states the buttons were in
        if (this.fieldHasErrorState) {
            this.getField('save_draft_button').setDisabled(true);
            this.getField('commit_button').setDisabled(true);
        } else {
            this.getField('save_draft_button').setDisabled(this.saveBtnDisabled);
            this.getField('commit_button').setDisabled(this.commitBtnDisabled);
        }
    },

    /**
     * @inheritdoc
     */
    _renderHtml: function() {
        var user = this.context.get('selectedUser') || app.user.toJSON();
        this.title = this.title || user.full_name;

        this._super("_renderHtml");
    }
})
