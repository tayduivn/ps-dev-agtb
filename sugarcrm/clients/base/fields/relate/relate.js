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
    events: {
        'keyup .chzn-search input': 'throttleSearch'
    },
    allow_single_deselect: true,
    minChars: 1,
    _previousTerm: null,
    fieldTag: 'select.chzn-select',
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {
        _.bindAll(this);
        this.minChars = options.def.minChars || this.minChars;
        app.view.Field.prototype.initialize.call(this, options);
        this.optionsTemplateC = app.template.getField(this.type, "options");
    },
    /**
     * Renders relate field
     */
    _render: function() {
        var self = this;
        var result = app.view.Field.prototype._render.call(this);
        if(this.tplName === 'edit') {
            this.$(this.fieldTag).not(".chzn-done").chosen({
                allow_single_deselect: self.allow_single_deselect,
                no_results_text: app.lang.get("LBL_SEARCH_FOR")
            }).change(function(evt){
                    var selected = $(evt.currentTarget).find(':selected'),
                        value = selected.text(),
                        id = selected.val(),
                        searchmore = selected.data("searchmore"),
                        empty = selected.data("empty");
                    if(searchmore || empty) {
                        self.beforeSearchMore();
                        $(evt.currentTarget).val('');
                        self.setValue({id: '', value: ''});
                        self.view.layout.trigger("drawer:selection:fire", {
                            components: [{
                                layout : 'selection-list',
                                context: {
                                    module: self.getSearchModule()
                                }
                            }]
                        }, self.setValue);
                    } else {
                        self.setValue({id: id, value: value});
                    }
                }).on("liszt:updated", function(evt) {
                    var selected = $(evt.currentTarget).find(':selected'),
                        value = selected.text(),
                        id = selected.val();

                    self.setValue({id: id, value: value, silent: true});
                });
        } else if(this.tplName === 'disabled') {
            this.$(this.fieldTag).attr("disabled", "disabled").not(".chzn-done").chosen();
        }
        return result;
    },
    beforeSearchMore: function() {},
    setValue: function(model) {
        var silent = model.silent || false;
        this.model.set(this.def.id_name, model.id, {silent: silent});
        this.model.set(this.def.name, model.value, {silent: silent});
    },
    /**
     * Throttles search ajax
     * @param {Object} e event object
     * @param {Integer} interval interval to throttle
     */
    throttleSearch: function(evt) {
        var term = evt.currentTarget.value;
        if(this._previousTerm != term) {
            this.search(term);
        };
    },
    onSearchSuccess: function(data) {
        var self = this,
            chosen_select = this.$(this.fieldTag).not(":disabled");
        chosen_select.children().not(":first").remove();
        self.selectOptions = data.models;
        var options = self.optionsTemplateC(self);
        chosen_select.append(options);
        chosen_select.trigger("liszt:updated");
    },
    getSearchModule: function() {
        return this.def.module;
    },
    /**
     * Searches for related field
     * @param event
     */
    search: _.debounce(function(term) {
        var self = this,
            searchModule = this.getSearchModule(),
            chosen_select = this.$(this.fieldTag).not(":disabled"),
            chosen_search_input = self.$(self.fieldTag + " + .chzn-container-active .chzn-search input"),
            params = {
                limit: 3
            };
        this._previousTerm = term;
        if(!_.isUndefined(term) && term) {
            params.q = term;
        }

        if(term.length >= this.minChars) {
            chosen_select.children().not(":first").not(":selected").remove();
            var adv_search = app.lang.get("LBL_LOADING"),
                opt_adv_search = new Option(adv_search, '');
            $(opt_adv_search).html(adv_search).appendTo(chosen_select);

            chosen_select.trigger("liszt:updated");
            chosen_search_input.val(term);

            self.search_collection = app.data.createBeanCollection(searchModule);
            self.search_collection.fetch({
                context: self,
                params: params,
                success: function(data) {
                    self.onSearchSuccess.call(self, data);
                },
                complete: function() {
                    chosen_search_input.val(term);
                },
                error: function() {
                    app.logger.error("Unable to fetch the bean collection.");
                    chosen_select.children().not(":first").remove();
                    var adv_search = app.lang.get("LBL_SEARCH_UNAVAILABLE"),
                        opt_adv_search = new Option(adv_search, '');
                    $(opt_adv_search).html(adv_search).appendTo(chosen_select);
                }
            });
        }
    }, app.config.requiredElapsed || 500)
})