(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('list-column-ellipsis', ['view'], {

            events:{
                'click th.morecol':'toggleDropdown',
                'click th.morecol li a':'toggleColumn'
            },

            /**
             * Manually toggle the dropdown on cell click
             * @param {Object}(optional) event jquery event object
             */
            toggleDropdown:function (event) {
                var self = this;
                var $dropdown = self.$('.morecol > div');
                if ($dropdown.length > 0 && _.isFunction($dropdown.dropdown)) {
                    $dropdown.toggleClass('open');
                    if (event) event.stopPropagation();
                    $('html').one('click', function () {
                        $dropdown.removeClass('open');
                    });
                }
            },
            /**
             * Toggle the 'visible' state of an available field
             * @param {Object} event jquery event object
             */
            toggleColumn:function (event) {
                if (!event) return;
                event.stopPropagation();

                var $li = this.$(event.currentTarget).closest('li'),
                    column = $li.data('fieldname');

                this._fields.visible = [];
                this._fields.available = [];

                _.each(this._fields.options, function(fieldMeta){
                    if(fieldMeta.name === column) {
                        fieldMeta.selected = !fieldMeta.selected;
                    }
                    if(fieldMeta.selected) {

                        this._fields.visible.push(fieldMeta);
                    } else {
                        this._fields.available.push(fieldMeta);
                    }
                }, this);

                this.render();
                this.toggleDropdown();
            },

            onAttach:function (component, plugin) {
                this.before('render', function () {
                    var lastActionColumn = _.last(this.rightColumns);
                    if (lastActionColumn) lastActionColumn.isColumnDropdown = true;
                }, null, this);
            }
        });
    });
})(SUGAR.App);
