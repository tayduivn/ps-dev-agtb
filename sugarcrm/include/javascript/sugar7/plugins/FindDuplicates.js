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
        var createDuplicateCollection = function(dupeCheckModel, module) {
            var collection = app.data.createBeanCollection(module || this.module),
                collectionSync = collection.sync;
            _.extend(collection, {
                /**
                 * Duplicate check model.
                 *
                 * @property
                 */
                dupeCheckModel: dupeCheckModel,

                /**
                 * {@inheritDoc}
                 *
                 * Override endpoint in order to fetch custom api.
                 */
                sync: function(method, model, options) {
                    options = options || {};
                    if (_.isEmpty(model.filterDef)) {
                        options.endpoint = _.bind(this.endpoint, this);
                    }
                    collectionSync(method, model, options);
                },

                /**
                 * {@inheritDoc}
                 *
                 * Custom endpoint for duplicate check.
                 */
                endpoint: function(method, model, options, callbacks) {
                    //Dupe Check API requires POST
                    var url = app.api.buildURL(this.module, 'duplicateCheck');
                    var data = app.data.getEditableFields(this.dupeCheckModel);
                    return app.api.call('create', url, data, callbacks);
                }
            });
            return collection;
        };

        app.plugins.register('FindDuplicates', ['view'], {
            /**
             * {@inheritDoc}
             *
             * Bind the find duplicate button handler.
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    this.context.on('button:find_duplicates_button:click', this.findDuplicatesClicked, this);
                });
            },

            /**
             * Handles the click event, and open the duplicate list view in the drawer.
             */
            findDuplicatesClicked: function() {
                this.findDuplicates(this.model);
            },

            /**
             * Open duplicate list view on drawer with duplicates collection.
             *
             * @param {Backbone.Model} dupeCheckModel Duplicate check model.
             */
            findDuplicates: function(dupeCheckModel) {
                app.drawer.open({
                    layout: 'find-duplicates',
                    context: {
                        layoutName: 'records',
                        dupelisttype: 'dupecheck-list-multiselect',
                        collection: this.createDuplicateCollection(dupeCheckModel),
                        model: app.data.createBean(this.module)
                    }
                }, _.bind(function(refresh, primaryRecord) {
                    if (refresh && dupeCheckModel.id === primaryRecord.id) {
                        app.router.refresh();
                    } else if (refresh) {
                        app.navigate(this.context, primaryRecord);
                    }
                }, this));
            },

            /**
             * Create Duplicates list collection.
             *
             * @param {Backbone.Model} dupeCheckModel Duplicate check model.
             * @return {Backbone.Collection} Duplicate check collection.
             */
            createDuplicateCollection: createDuplicateCollection,

            /**
             * {@inheritDoc}
             *
             * Clean up associated event handlers.
             */
            onDetach: function(component, plugin) {
                this.context.off('button:find_duplicates_button:click', this.findDuplicatesClicked, this);
            }
        });

        app.plugins.register('FindDuplicates', ['layout'], {
            /**
             * Create Duplicates list collection.
             *
             * @param {Backbone.Model} dupeCheckModel Duplicate check model.
             * @return {Backbone.Collection} Duplicate check collection.
             */
            createDuplicateCollection: createDuplicateCollection
        });
    });
})(SUGAR.App);
