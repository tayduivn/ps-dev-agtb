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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    plugins: ['Prettify'],
    className: 'row-fluid',

    initialize: function(options) {
        var self = this,
            request = {
                file: '',
                keys: [],
                page: {},
                page_data: {},
                parent_link: '',
                section: {},
                section_page: false
            },
            main;

        this._super('initialize', [options]);

        // trigger initial close of side bar
        app.events.trigger('app:dashletPreview:close');

        // load up the styleguide css if not already loaded
        //TODO: cleanup styleguide.css and add to main file
        if ($('head #styleguide_css').length === 0) {
            $('<link>')
                .attr({
                    rel: 'stylesheet',
                    href: 'styleguide/assets/css/styleguide.css',
                    id: 'styleguide_css'
                })
                .appendTo('head');
        }

        // load page_data index from metadata (defined in layout/docs.php)
        request.page_data = app.metadata.getLayout(this.module, 'docs').page_data;
        // page_name defined in router
        request.file = this.context.get('page_name');
        if (!_.isUndefined(request.file) && !_.isEmpty(request.file)) {
            request.keys = request.file.split('-');
        }
        if (request.keys.length) {
            // get page content variables from page_data
            if (request.keys[0] === 'index') {
                if (request.keys.length > 1) {
                    // this is a section index call
                    request.section = request.page_data[request.keys[1]];
                } else {
                    // this is the home index call
                    request.section = request.page_data[request.keys[0]];
                }
                request.section_page = true;
                request.file = 'index';
            } else if (request.keys.length > 1) {
                // this is a section page call
                request.section = request.page_data[request.keys[0]];
                request.page = request.section.pages[request.keys[1]];
                request.parent_link = '-' + request.keys[0];
                window.prettyPrint && prettyPrint();
            } else {
                // this is a general page call
                request.section = request.page_data[request.keys[0]];
            }
        }

        // load up the page view into the component array
        main = this.getComponent('main-pane');
        main._addComponentsFromDef([{
            view: 'docs-' + request.file,
            context: {
                module: 'Styleguide',
                skipFetch: true,
                request: request
            }
        }]);

        this.render();
    },

    _placeComponent: function(component) {
        if (component.meta.name) {
            this.$("." + component.meta.name).append(component.$el);
        }
    }
})
