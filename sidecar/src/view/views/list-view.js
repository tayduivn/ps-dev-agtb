(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    app.view.views.ListView = app.view.View.extend({

        events: {
            'click [class*="orderBy"]': 'setOrderBy',
            'click .search': 'showSearch',
            'click [rel=tooltip]': 'fixTooltip',
            'mouseenter tr': 'showActions',
            'mouseleave tr': 'hideActions',
            'click [name=show_more_button]': 'showMoreRecords',
            'click [name=show_more_button_back]': 'showPreviousRecords',
            'click [name=show_more_button_forward]': 'showNextRecords'
        },
        render : function() {
            app.view.View.prototype.render.call(this);
            app.events.on('treeview:node_select', this.handleTreeNodeSelect, this);
        },
        handleTreeNodeSelect : function(json_data) {
            console.log(json_data);
        },
        bind: function(context) {
            var collection = context.get("collection");
            _.each(collection.models, function(model) {
                var tr = this.$el.find('tr[name="' + model.module + '_' + model.get("id") + '"]');
                _.each(model.attributes, function(value, field) {
                    var el = tr.find('input[name="' + field + '"],span[name="' + field + '"]');
                    if (el.length > 0) {
                        //Bind input to the model
                        el.on("change", function(ev) {
                            model.set(field, el.val());
                        });
                        //And bind the model to the input
                        model.on("change:" + field, function(model, value) {
                            if (el[0].tagName.toLowerCase() == "input")
                                el.val(value); else
                                el.html(value);
                        });
                    }
                }, this);
            }, this);
        },
        /**
         * Sets order by on collection and view
         * @param {Object} event jquery event object
         */
        setOrderBy: function(event) {
            //set on this obj and not the prototype
            this.orderBy = this.orderBy || {};

            //mapping for css
            var orderMap = {
                "desc": "_desc",
                "asc": "_asc"
            };

            //TODO probably need to check if we can sort this field from metadata
            var collection = this.context.get('collection');
            var fieldName = this.$(event.target).data('fieldname');

            if (!collection.orderBy) {
                collection.orderBy = {
                    field: "",
                    direction: ""
                };
            }

            var nOrder = "desc";

            // if same field just flip
            if (fieldName == collection.orderBy.field) {
                if (collection.orderBy.direction == "desc") {
                    nOrder = "asc";
                }
                collection.orderBy.direction = nOrder;
            } else {
                collection.orderBy.field = fieldName;
                collection.orderBy.direction = "desc";
            }

            // set it on the view
            this.orderBy.field = fieldName;
            this.orderBy.direction = orderMap[collection.orderBy.direction];

            // refetch the collection
            collection.fetch();
        },
        showSearch: function() {
            var searchEl = '.search';
            $(searchEl).toggleClass('active');
            $(searchEl).parent().parent().parent().find('.dataTables_filter').toggle();
            $(searchEl).parent().parent().parent().find('.form-search').toggleClass('hide');
            return false;
        },
        fixTooltip: function() {
            console.log("click on a tooltip");
            $(".tooltip").hide();
        },
        showActions: function(e) {
            $(e.currentTarget).children("td").children("span").children(".btn-group").show();
        },
        hideActions: function(e) {
            $(e.currentTarget).children("td").children("span").children(".btn-group").hide();
        },

        showMoreRecords: function() {
            var self = this;
            app.alert.show('show_more_records', {level:'process', title:'Loading'});
            app.controller.context.state.collection.paginate({add: true,
                success: function() {
                    app.alert.dismiss('show_more_records');
                    self.render();
                    window.scrollTo(0, document.body.scrollHeight);
                }
            });
        },
        showPreviousRecords: function() {
            var self = this;
            app.alert.show('show_previous_records', {level:'process', title:'Loading'});
            app.controller.context.state.collection.paginate({page: -1,
                success: function() {
                    app.alert.dismiss('show_previous_records');
                    self.render();
                }
            });
        },
        showNextRecords: function() {
            var self = this;
            app.alert.show('show_next_records', {level:'process', title:'Loading'});
            app.controller.context.state.collection.paginate({
                success: function() {
                    app.alert.dismiss('show_next_records');
                    self.render();
                }
            });
        },

        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
            }
        }

    });

})(SUGAR.App);
