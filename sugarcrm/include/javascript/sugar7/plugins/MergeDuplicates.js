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
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('MergeDuplicates', ['view'], {

            /**
             * Minimum number of records for merging.
             *
             * @property
             */
            _minRecordsToMerge: 2,

            /**
             * Maximum number of records for merging.
             *
             * @property
             */
            _maxRecordsToMerge: 5,

            /**
             * Merge records handler.
             *
             * @param {Backbone.Collection} mergeCollection Set of merging records.
             * @param {Backbone.Model} primaryRecord (Optional) Default Primary Model.
             */
            mergeDuplicates: function(mergeCollection, primaryRecord) {
                if (_.isEmpty(mergeCollection)) {
                    return;
                }
                var primaryRecordId = null;
                if (!_.isEmpty(primaryRecord)) {
                    mergeCollection.add(primaryRecord, {silent: true});
                    primaryRecordId = primaryRecord.id;
                }
                var models = this.validateModelsForMerge(mergeCollection);

                if (this.validateSize(models) === false) {
                    return;
                }

                if (!this.triggerBefore('mergeduplicates', models)) {
                    return;
                }
                
                app.drawer.open({
                    layout: 'merge-duplicates',
                    context: {
                        primaryRecord: primaryRecord ? primaryRecord : null,
                        selectedDuplicates: models
                    }
                }, _.bind(function(refresh, primaryRecord) {
                    if (refresh) {
                        this.trigger('mergeduplicates:complete', primaryRecord);
                        mergeCollection.reset();
                    } else {
                        mergeCollection.remove(primaryRecordId);
                    }
                }, this));
            },

            /**
             * Check size for models selected for merge.
             *
             * @param {Array} models Array of merging record set.
             * @return {Boolean} True only if it contains valid size of collection.
             */
            validateSize: function(models) {
                var isValidSize = models.length && models.length >= this._minRecordsToMerge &&
                    models.length <= this._maxRecordsToMerge;

                if (isValidSize) {
                    return true;
                }

                var msg = app.lang.get('TPL_MERGE_INVALID_NUMBER_RECORDS',
                    this.module,
                    {
                        minRecords: this._minRecordsToMerge,
                        maxRecords: this._maxRecordsToMerge
                    }
                );

                app.alert.show('invalid-record-count', {
                    level: 'error',
                    messages: msg,
                    autoClose: true
                });

                return false;
            },

            /**
             * Check access for models selected for merge.
             *
             * @param {Data.Collection} Merge Collection to check access for merge.
             * @return {Array} Models with access.
             */
            validateModelsForMerge: function(mergeCollection) {
                return _.filter(mergeCollection.models, function(model) {
                    return _.every(['view', 'edit', 'delete'], function(acl) {
                        return app.acl.hasAccessToModel(acl, model);
                    });
                }, this);
            }
        });
    });
})(SUGAR.App);
