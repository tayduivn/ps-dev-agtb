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
