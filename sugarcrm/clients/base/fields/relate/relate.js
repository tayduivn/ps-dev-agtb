({
    events: {
        'keyup .chzn-search input': 'throttleSearch'
    },
    allow_single_deselect: true,
    minChars: 1,
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {
        _.bindAll(this);
        app.view.Field.prototype.initialize.call(this, options);
        this.optionsTemplateC = app.template.getField(this.type, "options");
    },
    /**
     * Renders relate field
     */
    _render: function() {
        var self = this;
        var result = app.view.Field.prototype._render.call(this);
        if(this.action == 'edit') {
            this.$(".chzn-select").not(".chzn-done").chosen({
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
                }).on("liszt:updated", function() {
                    self.setValue({id: '', value: ''});
                });
        }
        return result;
    },
    setValue: function(model) {
        this.model.set(this.def.id_name, model.id, {silent: true});
        this.model.set(this.def.name, model.value, {silent: true});
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
    onSearchSuccess: function() {},
    /**
     * Searches for related field
     * @param event
     */
    search: _.debounce(function(term) {
        var self = this,
            searchModule = this.def.module,
            chosen_select = this.$(".chzn-select").not(":disabled"),
            params = {
                limit: 3
            };
        this._previousTerm = term;
        if(!_.isUndefined(term) && term) {
            params.q = term;
        }
        var _success = function(data) {
            chosen_select.children().not(":first").remove();
            self.selectOptions = data.models;
            var options = self.optionsTemplateC(self);
            chosen_select.append(options);
            chosen_select.trigger("liszt:updated");
            self.$(".chzn-search input").val(term);
        };

        if(term.length >= this.minChars) {
            chosen_select.children().not(":first").not(":selected").remove();
            var adv_search = app.lang.get("LBL_LOADING"),
                opt_adv_search = new Option(adv_search, '');
            $(opt_adv_search).html(adv_search).appendTo(chosen_select);

            chosen_select.trigger("liszt:updated");
            self.$(".chzn-search input").val(term);

            self.search_collection = app.data.createBeanCollection(searchModule);
            self.search_collection.fetch({
                params: params,
                success: _success,
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