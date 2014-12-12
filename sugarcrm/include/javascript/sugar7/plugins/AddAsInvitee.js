/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {

    app.events.on('app:init', function() {
        /**
         * Add As Invitee plugin manages listening for changes to the parent field
         * on event type records (Calls & Meetings) and adds the person record as
         * an invitee on the call/meeting.
         *
         * This plugin listens for changes to the parent field, but waits to do
         * this until render to accommodate create scenario where data may have been
         * pre-populated without triggering a change event.
         *
         * In create scenario, a change listener is added immediately on render, but
         * for record edit view, we need to wait until after sync so we can detect
         * a change event accurately.
         *
         * This plugin is built to enhance {@link View.Views.Base.RecordView}
         * and its descendants.
         */
        app.plugins.register('AddAsInvitee', ['view'], {
            onAttach: function() {
                this.once('render', function() {
                    if (this.isCreateWithParent()) {
                        this.handleParentChange(this.model);
                    }

                    if (this.model.isNew()) {
                        this.addParentChangeListener();
                    } else {
                        this.model.once('sync', function() {
                            this.addParentChangeListener();
                        }, this);
                    }
                }, this);
            },

            /**
             * Check if currently creating a record with the parent record
             * already pre-populated
             *
             * @return {Boolean}
             */
            isCreateWithParent: function() {
                return this.model.isNew() && !_.isUndefined(this.model.get('parent_name'));
            },

            /**
             * Add a listener for parent field changes
             */
            addParentChangeListener: function() {
                this.model.on('change:parent_name', this.handleParentChange, this);
            },

            /**
             * If parent field changes, check if it is a possible invitee add as an invitee
             *
             * @param {Object} model
             */
            handleParentChange: function(model) {
                var parent = app.data.createBean(model.get('parent_type'), {
                    id: model.get('parent_id'),
                    name: model.get('parent_name')
                });
                if (this.isPossibleInvitee(parent)) {
                    this.turnOffAutoInviteParent();
                    this.addAsInvitee(parent);
                }
            },

            /**
             * Person is a possible invitee if it has an id, is one of the possible invitee
             * modules and is not already in the invitee list
             *
             * @param {Object} person
             * @return {Boolean}
             */
            isPossibleInvitee: function(person) {
                var inviteeModuleList = ['Leads', 'Contacts'],
                    invitees = this.model.get('invitees');

                return (!_.isEmpty(person.id) &&
                    _.contains(inviteeModuleList, person.module) &&
                    !_.isUndefined(invitees) &&
                    _.isUndefined(invitees.get(person.id)));
            },

            /**
             * Tell the server not to auto invite parent field because we are managing
             * this behavior on the front end. This is a flag sent to the server to
             * maintain the mobile app behavior which auto invites the parent record
             */
            turnOffAutoInviteParent: function() {
                this.model.setDefault('auto_invite_parent', false);
            },

            /**
             * Add the given person as an invitee
             *
             * @param {Object} person
             */
            addAsInvitee: function(person) {
                this.model.get('invitees').add(person);
            }
        });
    });
})(SUGAR.App);
