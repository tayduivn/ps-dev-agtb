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
/**
 * Layout for the global search results page.
 *
 * @class View.Layouts.Base.SearchLayout
 * @alias SUGAR.App.view.layouts.BaseSearchLayout
 * @extends View.Layout
 */
({
    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.collection.query = this.context.get('searchTerm') || '';

        this.context.on('change:searchTerm', function(context, searchTerm) {
            //TODO: collection.fetch shouldn't need a query to be passed. Will
            // be fixed by SC-3973.
            this.context.set('searchTerm', searchTerm);
            this.collection.fetch({query: searchTerm});
        }, this);

        this.collection.on('sync', function(collection, data) {
            var isCollection = (collection instanceof App.BeanCollection);
            if (!isCollection) {
                return;
            }
            this.formatRecords(collection);
//            collection.facets = data.facets;
//            this.context.set('facets', data.facets);
        }, this);
    },

    /**
     * Formats models returned by the globalsearch api.
     *
     * @param {Data.BeanCollection} collection The collection of models to format.
     */
    formatRecords: function(collection) {
        collection.each(function(model) {
            if (model.formatted) {
                return;
            }
            var module = app.metadata.getModule(model.get('_module'));
            var highlights = _.map(model.get('_highlights'), function(val, key) {
                return {
                    name: key,
                    value: new Handlebars.SafeString(val),
                    label: module.fields[key].vname,
                    link: true,
                    highlighted: true
                };
            });
            model.set('_highlights', highlights);

            //FIXME: We shouldn't do that because it only applies for person
            // object, SC-4196 will fix it.
            if (!model.get('name')) {
                var name = model.get('first_name') + ' ' + model.get('last_name');
                model.set('name', name);
            }
            // We add a flag here so that when the user clicks on
            // `More results...` we won't reformat the existing ones.
            model.formatted = true;
        });
    }
})
