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
     * @return {String} hash key.
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
     * @param {Boolean} isUseful Flag of useful or not useful.
     */
    vote: function(isUseful) {
        if (this.isVoted()) {
            return;
        }
        var field = isUseful ? 'useful' : 'notuseful';
        var votes = app.user.lastState.get(this.getLastStateKey()) || {};

        votes[this.model.id] = isUseful ? this.KEY_USEFUL : this.KEY_NOT_USEFUL;
        app.user.lastState.set(this.getLastStateKey(), votes);

        this.model.set(field, parseInt(this.model.get(field), 10) + 1);
        this.model.save();
        this.voted = true;
        this.votedUseful = isUseful;
        this.votedNotUseful = !isUseful;
        if (!this.disposed) {
            this.render();
        }
    },

    /**
     * Check voted state.
     *
     * @return {Boolean}
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
