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
    allow_single_deselect: true,
    minChars: 1,
    fieldTag: 'input.select2',
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {
        _.bindAll(this);
        this.minChars = options.def.minChars || this.minChars;
        this.bwcLink = options.def.bwcLink;//false is a perfectly valid value for this boolean metadata property!
        app.view.Field.prototype.initialize.call(this, options);
    },
    /**
     * Renders relate field
     */
    _render: function() {
        var self = this, moduleMeta;

        //First checks if there's a bwcLink on the field (the bwcLink property allows overriding which module will be used;
        //e.g. we can point an assigned_user_name (Users) to an Employees detail view). If no bwcLink exists, we "fallback"
        //checking top level of the related module's meta for isBwcEnabled. For either of these cases, we create bwc route.
        //If, for some reason, the meta value for bwcLink is explicitly set to false, isBwcEnabled will be ignored.
        moduleMeta = app.metadata.getModule(this.def.module) || {};//fallback so we don't clutter tests with stubs ;)
        if (this.bwcLink || (this.def.bwcLink !== false && moduleMeta.isBwcEnabled)) {
            this.route = app.bwc.buildRoute(this.def.module, this.model.get(this.def.id_name), 'DetailView');
        } else {
            // Use normal sidecar route
            this.route = '#' + app.router.buildRoute(this.def.module, this.model.get(this.def.id_name));
        }

        var result = app.view.Field.prototype._render.call(this);

        if(this.tplName === 'edit') {

            this.$(this.fieldTag).select2({
                    width: '100%',
                    initSelection: function(el, callback) {
                        var $el = $(el),
                            id = $el.data('id'),
                            text = $el.val();
                        callback({id: id, text: text});
                    },
                    formatInputTooShort: function() {
                        return '';
                    },
                    formatSearching: function() {
                        return app.lang.get("LBL_LOADING", self.module);
                    },
                    placeholder: this.getPlaceHolder(),
                    allowClear: self.allow_single_deselect,
                    minimumInputLength: self.minChars,
                    query: self.search
                }).on("open", function() {
                    var plugin = $(this).data('select2');
                    if(!plugin.searchmore) {
                        plugin.searchmore = $('<ul class="select2-results">')
                            .append(
                            $('<li class="select2-result">')
                                .append($('<div/>').addClass('select2-result-label').html(app.lang.get('LBL_SEARCH_FOR_MORE')))
                                .mousedown(function() {
                                    plugin.opts.element.trigger($.Event("searchmore"));
                                    plugin.close();
                                })
                        );
                        plugin.dropdown.append(plugin.searchmore);
                    }
                }).on("searchmore", function() {
                    $(this).select2("close");
                    self.setValue({id: '', value: ''});
                    app.drawer.open({
                        layout : 'selection-list',
                        context: {
                            module: self.getSearchModule()
                        }
                    }, self.setValue);
                }).on("change", function(e) {
                    var id = e.val,
                        plugin = $(this).data('select2'),
                        value = (id) ? plugin.selection.find("span").text() : '';
                    self.setValue({id: e.val, value: value});
                });
        } else if(this.tplName === 'disabled') {
            this.$(this.fieldTag).attr("disabled", "disabled").select2();
        }
        return result;
    },
    setValue: function(model) {
        if (model) {
            var silent = model.silent || false;
            this.model.set(this.def.id_name, model.id, {silent: silent});
            this.model.set(this.def.name, model.value, {silent: silent});
        }
    },
    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect refresh the select2 plugin
     */
    bindDomChange: function() {
    },
    getSearchModule: function() {
        return this.def.module;
    },
    getPlaceHolder: function() {
        var module,
            moduleString = app.lang.getAppListStrings('moduleListSingular');

        if (!moduleString[this.getSearchModule()]) {
            app.logger.error("Module '" + this.getSearchModule() + "' doesn't have singular translation.");
            // graceful fallback
            module = this.getSearchModule().toLocaleLowerCase();
        }
        else {
            module = moduleString[this.getSearchModule()].toLocaleLowerCase();
        }
        return app.lang.get('LBL_SEARCH_SELECT_MODULE', this.module, {
            module: module
        });
    },
    /**
     * Searches for related field
     * @param event
     */
    search: _.debounce(function(query) {
        var term = query.term,
            self = this,
            searchModule = this.getSearchModule(),
            params = {},
            limit = self.def.limit || 5;

        if(!_.isUndefined(term) && term) {
            params.q = term;
        }

        var search_collection = query.context || app.data.createBeanCollection(searchModule);

        if(query.context) {
            params.offset = search_collection.next_offset
        }
        search_collection.fetch({
            context: self,
            params: params,
            limit: limit,
            success: function(data) {
                var fetch = {results:[], more: data.next_offset > 0, context: search_collection};
                if(fetch.more) {
                    var plugin = self.$(self.fieldTag).data("select2"),
                        height = plugin.searchmore.children("li:first").children(":first").outerHeight(),
                        //0.2 makes scroll not to touch the bottom line which avoid fetching next record set
                        maxHeight = height * (limit - .2);
                    plugin.results.css("max-height", maxHeight);
                } else {

                }
                _.each(data.models, function(model){
                    fetch.results.push({
                        id: model.id,
                        text: model.get('name')
                    })
                });
                query.callback(fetch);
            },
            error: function() {
                query.callback({results:[]});
                app.logger.error("Unable to fetch the bean collection.");
            }
        });

    }, app.config.requiredElapsed || 500)
})
