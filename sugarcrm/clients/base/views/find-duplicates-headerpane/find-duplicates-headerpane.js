/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
        'click a[name=merge_duplicates_button]:not(".disabled")': 'mergeDuplicatesClicked'
    },

    plugins: ['MergeDuplicates'],

    /**
     * Wait for the mass_collection to be set up so we can add listener
     */
    bindDataChange: function() {
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'bindDataChange'});
        this.on('mergeduplicates:complete', this.mergeComplete, this);
        this.context.on('change:mass_collection', this.addMassCollectionListener, this);
    },

    /**
     * {@inheritDoc}
     * Dispose safe for mass_collection
     */
    unbindData: function() {
        var massCollection = this.context.get('mass_collection');

        if (massCollection) {
            massCollection.off(null, null, this);
        }
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Set up add/remove listener on the mass_collection so we can enable/disable the merge button
     */
    addMassCollectionListener: function() {
        var massCollection = this.context.get('mass_collection');
        massCollection.on('add remove', this.toggleMergeButton, this);
    },

    /**
     * Enable the merge button when a duplicate has been checked
     * Disable when all are unchecked
     */
    toggleMergeButton: function() {
        var disabled;
        if (this.context.get('mass_collection').length > 0) {
            disabled = false;
        } else {
            disabled = true;
        }
        this.$("[name='merge_duplicates_button']").toggleClass('disabled', disabled);
    },

    /**
     * Cancel and close the drawer
     */
    cancel: function() {
        app.drawer.close();
    },

    /**
     * Close the current drawer window by passing merged primary record
     * once merge process is complete.
     *
     * @param {Backbone.Model} primaryRecord Primary Record.
     */
    mergeComplete: function(primaryRecord) {
        app.drawer.closeImmediately(true, primaryRecord);
    },

    /**
     * Merge records handler.
     */
    mergeDuplicatesClicked: function() {
        this.mergeDuplicates(this.context.get('mass_collection'), this.collection.dupeCheckModel);
    }
})
