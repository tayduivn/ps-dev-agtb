(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('Merge', ['view'], {

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
                if (!models.length ||
                    models.length < this._minRecordsToMerge ||
                    models.length > this._maxRecordsToMerge) {
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
                }
                return true;
            },

            /**
             * Check access for models selected for merge.
             *
             * @param {Data.Collection} Merge Collection to check access for merge.
             * @return {Array} Models with access.
             */
            validateModelsForMerge: function(mergeCollection) {
                var result = [];
                _.each(mergeCollection.models, function(model) {
                    var hasAccess = _.every(['view', 'edit', 'delete'], function(acl) {
                        return app.acl.hasAccessToModel(acl, model);
                    });
                    if (hasAccess) {
                        result.push(model);
                    }
                }, this);
                return result;
            }
        });
    });
})(SUGAR.App);
