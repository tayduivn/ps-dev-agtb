/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    allow_single_deselect: true,
    minChars: 1,
    fieldTag: 'input.select2',
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function (options) {
        _.bindAll(this);
        this.minChars = options.def.minChars || this.minChars;
        this.bwcLink = options.def.bwcLink;//false is a perfectly valid value for this boolean metadata property!
        app.view.Field.prototype.initialize.call(this, options);
        var populateMetadata = app.metadata.getModule(this.getSearchModule());

        if (_.isEmpty(populateMetadata)) {
            return;
        }
        _.each(this.def.populate_list, function (target, source) {
            if (_.isUndefined(populateMetadata.fields[source])) {
                app.logger.error('Fail to populate the related attributes: attempt to access undefined key - ' + this.getSearchModule() + '::' + source);
            }
        }, this);
    },
    bindKeyDown: function (callback) {
        this.$('input').on("keydown.record", {field: this}, callback);
    },
    focus: function () {
        this.$(this.fieldTag).select2('open');
    },
    /**
     * Renders relate field
     */
    _render: function () {
        var self = this;

        var result = app.view.Field.prototype._render.call(this);

        if (this.tplName === 'edit') {

            this.$(this.fieldTag).select2({
                width: '100%',
                initSelection: function (el, callback) {
                    var $el = $(el),
                        id = $el.data('id'),
                        text = $el.val();
                    callback({id: id, text: text});
                },
                formatInputTooShort: function () {
                    return '';
                },
                formatSearching: function () {
                    return app.lang.get("LBL_LOADING", self.module);
                },
                placeholder: this.getPlaceHolder(),
                allowClear: self.allow_single_deselect,
                minimumInputLength: self.minChars,
                query: self.search
            }).on("open",function () {
                    var plugin = $(this).data('select2');
                    if (!plugin.searchmore) {
                        plugin.searchmore = $('<ul class="select2-results">')
                            .append(
                                $('<li class="select2-result">')
                                    .append($('<div/>').addClass('select2-result-label').html(app.lang.get('LBL_SEARCH_FOR_MORE')))
                                    .mousedown(function () {
                                        plugin.opts.element.trigger($.Event("searchmore"));
                                        plugin.close();
                                    })
                            );
                        plugin.dropdown.append(plugin.searchmore);
                    }
                }).on("searchmore",function () {
                    $(this).select2("close");
                    app.drawer.open({
                        layout: 'selection-list',
                        context: {
                            module: self.getSearchModule(),
                            fields: _.union(['id', 'name'], _.keys(self.def.populate_list || {}))
                        }
                    }, self.setValue);
                }).on("change", function (e) {
                    var id = e.val,
                        plugin = $(this).data('select2'),
                        value = (id) ? plugin.selection.find("span").text() : '',
                        collection = plugin.context,
                        model = collection.get(id),
                        attributes = {
                            id: model.id,
                            value: model.get('name')
                        };

                    _.each(model.attributes, function (value, field) {
                        if (app.acl.hasAccessToModel('view', model, field)) {
                            attributes[field] = attributes[field] || model.get(field);
                        }
                    });
                    self.setValue(attributes);
                });
        } else if (this.tplName === 'disabled') {
            this.$(this.fieldTag).attr("disabled", "disabled").select2();
        }
        return result;
    },

    //First checks if there's a bwcLink on the field (the bwcLink property allows overriding which module will be used;
    //e.g. we can point an assigned_user_name (Users) to an Employees detail view). If no bwcLink exists, we "fallback"
    //checking top level of the related module's meta for isBwcEnabled. For either of these cases, we create bwc route.
    //If, for some reason, the meta value for bwcLink is explicitly set to false, isBwcEnabled will be ignored.
    buildRoute: function (module, idName) {
        var moduleMeta = app.metadata.getModule(module) || {};//fallback so we don't clutter tests with stubs ;)
        if (this.bwcLink || (this.def.bwcLink !== false && moduleMeta.isBwcEnabled)) {
            this.href = '#' + app.bwc.buildRoute(module, idName, 'DetailView');
        } else {
            //Normal Sidecar route
            this.href = '#' + app.router.buildRoute(module, idName);
        }
    },
    //Derived controllers can override these if related module and id in another place
    _buildRoute: function () {
        var module, idName;
        module = this._getRelateModule();
        idName = this._getRelateId();
        this.buildRoute(module, idName);
    },
    _getRelateModule: function () {
        return this.def.module;
    },
    _getRelateId: function () {
        return this.model.get(this.def.id_name);
    },
    format: function (value) {
        this._buildRoute();
        return value;
    },
    setValue: function (model) {
        if (model) {
            var silent = model.silent || false;
            this.model.set(this.def.id_name, model.id, {silent: silent});
            this.model.set(this.def.name, model.value, {silent: silent});

            var newData = {},
                self = this;
            _.each(this.def.populate_list, function (target, source) {
                source = _.isNumber(source) ? target : source;
                if (!_.isUndefined(model[source]) && app.acl.hasAccessToModel('edit', this.model, target)) {
                    newData[target] = model[source];
                }
            }, this);

            if (!_.isEmpty(newData)) {
                var message = app.lang.get(self.def.populate_confirm || 'NTC_OVERWRITE_POPULATED_DATA_CONFIRM', this.getSearchModule()) +
                    '<br/><br/>';
                _.each(newData, function (value, field) {
                    var def = this.model.fields[field];
                    message += app.lang.get(def.label || def.vname || field, this.module) + ': ' + value + '<br/>';
                }, this);
                message += '<br/>';

                app.alert.show('overwrite_confirmation', {
                    level: 'confirmation',
                    messages: message,
                    onConfirm: function () {
                        self.model.set(newData);
                    }
                });
            }
        }
    },
    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect refresh the select2 plugin
     */
    bindDomChange: function () {
    },
    getSearchModule: function () {
        return this.def.module;
    },
    getPlaceHolder: function () {
        var module,
            moduleString = app.lang.getAppListStrings('moduleListSingular');

        if (!moduleString[this.getSearchModule()]) {
            app.logger.error("Module '" + this.getSearchModule() + "' doesn't have singular translation.");
            // graceful fallback
            module = this.getSearchModule().toLocaleLowerCase();
        } else {
            module = moduleString[this.getSearchModule()].toLocaleLowerCase();
        }
        return app.lang.get('LBL_SEARCH_SELECT_MODULE', this.module, {
            module: module
        });
    },

    getFilterDef: function(searchTerm, context) {
        var searchFilter = [], returnFilter = [];
        // We allow searching on both 'name' and 'id' (e.g. if user is searching for
        // 'MyBug 12345', both terms '12', or, 'MyB' need to produce a match)
        var fieldNames = _.union(['id', 'name'], _.keys(this.def.populate_list || {}));
        if (searchTerm) {
            _.each(fieldNames, function(name) {
                var o = {};
                o[name] = {'$starts': searchTerm};
                searchFilter.push(o);
            });
            returnFilter[0] = {'$or' : searchFilter};
        }
        return returnFilter;
    },

    /**
     * Searches for related field
     * @param event
     */
    search: _.debounce(function (query) {
        var term = query.term || '',
            self = this, searchCollection, filterDef,
            searchModule = this.getSearchModule(),
            params = {},
            limit = self.def.limit || 5;

        searchCollection = query.context || app.data.createBeanCollection(searchModule);

        if (query.context) {
            params.offset = searchCollection.next_offset;
        }
        filterDef = self.getFilterDef(term, self);
        params.filter = JSON.parse(JSON.stringify(filterDef));

        searchCollection.fetch({
            //Don't show alerts for this request
            showAlerts: false,
            update: true,
            remove: _.isUndefined(params.offset),
            context: self,
            params: params,
            limit: limit,
            success: function (data) {
                var fetch = {results: [], more: data.next_offset > 0, context: searchCollection};
                if (fetch.more) {
                    var plugin = self.$(self.fieldTag).data("select2"),
                        height = plugin.searchmore.children("li:first").children(":first").outerHeight(),
                    //0.2 makes scroll not to touch the bottom line which avoid fetching next record set
                        maxHeight = height * (limit - .2);
                    plugin.results.css("max-height", maxHeight);
                } else {

                }
                _.each(data.models, function (model, index) {
                    if (params.offset && index < params.offset) {
                        return;
                    }
                    fetch.results.push({
                        id: model.id,
                        text: model.get('name')
                    });
                });
                query.callback(fetch);
            },
            error: function () {
                query.callback({results: []});
                app.logger.error("Unable to fetch the bean collection.");
            }
        });
    }, app.config.requiredElapsed || 500)

})
