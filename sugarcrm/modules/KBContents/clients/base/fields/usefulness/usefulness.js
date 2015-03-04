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

({
    events: {
        'click [data-action=useful]': 'usefulClicked',
        'click [data-action=notuseful]': 'notusefulClicked'
    },

    /**
     * {@inheritDoc}
     *
     * This field doesn't support `showNoData`.
     */
    showNoData: false,

    plugins: [],

    KEY_USEFUL: 'u',
    KEY_NOT_USEFUL: 'n',

    voted: false,
    votedUseful: false,
    votedNotUseful: false,

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        if (!this.model.has('useful')) {
            this.model.set('useful', 0);
        }
        if (!this.model.has('notuseful')) {
            this.model.set('notuseful', 0);
        }
    },

    /**
     * Build the state key for usefulness vote.
     *
     * @return {string} hash key.
     */
    getLastStateKey: function() {
        if (this._lastStateKey) {
            return this._lastStateKey;
        }
        this._lastStateKey = app.user.lastState.key('usefulness', this.view);
        return this._lastStateKey;
    },

    /**
     * The vote for useful or not useful.
     *
     * @param {boolean} isUseful Flag of useful or not useful.
     */
    vote: function(isUseful) {
        if (this.isVoted()) {
            return;
        }
        var action = isUseful ? 'useful' : 'notuseful';
        var url = app.api.buildURL(this.model.module, action, {
            id: this.model.id
        });
        var callbacks = app.data.getSyncCallbacks('update', this.model, {
            success: _.bind(function() {
                var votes = app.user.lastState.get(this.getLastStateKey()) || {};
                votes[this.model.id] = isUseful ? this.KEY_USEFUL : this.KEY_NOT_USEFUL;
                app.user.lastState.set(this.getLastStateKey(), votes);

                this.voted = true;
                this.votedUseful = isUseful;
                this.votedNotUseful = !isUseful;

                if (!this.disposed) {
                    this.render();
                }
            }, this)
        });

        app.api.call('update', url, null, callbacks);
    },

    /**
     * Check voted state.
     *
     * @return {boolean}
     */
    isVoted: function() {
        if (!this.voted) {
            var votes = app.user.lastState.get(this.getLastStateKey()) || {};
            if (_.has(votes, this.model.id) &&
                _.indexOf([this.KEY_USEFUL, this.KEY_NOT_USEFUL], votes[this.model.id]) !== -1
            ) {
                this.voted = true;
                this.votedUseful = (votes[this.model.id] == this.KEY_USEFUL);
                this.votedNotUseful = (votes[this.model.id] == this.KEY_NOT_USEFUL);
            }
        }
        return this.voted;
    },

    /**
     * Handler to vote useful when icon clicked.
     */
    usefulClicked: function() {
        this.vote(true);
    },

    /**
     * Handler to vote not useful when icon clicked.
     */
    notusefulClicked: function() {
        this.vote(false);
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        this.isVoted();
        this._super('_render');
        return this;
    }
})
