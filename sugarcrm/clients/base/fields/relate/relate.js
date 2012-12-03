({
    events: {
        'keyup .chzn-search input': 'throttleSearch'
    },
    allow_single_deselect: true,
    minChars: 3,
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
                        $(evt.currentTarget).val('');
                        $(this).trigger("liszt:updated");
                        //TODO: inline Modal for selection
                    } else {
                        self.setValue({id: id, value: value});
                    }
                }).on("liszt:updated", function(evt) {
                    var selected = $(evt.currentTarget).find(':selected'),
                        value = selected.text(),
                        id = selected.val();

                    self.setValue({id: id, value: value});
                });
        }
        return result;
    },
    bindDataChange: function() {
        if (this.model) {
            var self = this;
            this.model.on("change:" + this.name, function() {
                if(self.tplName !== 'edit') {
                    self.render();
                }
            }, this);
        }
    },
    setValue: function(model) {
        this.model.set(this.def.id_name, model.id);
        this.model.set(this.def.name, model.value);
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
            chosen_search_input = self.$(self.fieldTag + " + .chzn-container .chzn-search input"),
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