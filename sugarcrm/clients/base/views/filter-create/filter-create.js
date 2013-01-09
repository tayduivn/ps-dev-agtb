({
    events: {
        'click a.filter-close': 'triggerClose',
        'click a.addme': 'addRow'
    },

    rowTemplate: Handlebars.compile('<article class="filter-body" id="filter_row_new">' +
'     <div class="row-fluid">' +
'       <div class="filter-field controls span3">' +
'         <select name="field" class="field_name chzn-select chzn-inherit-width" data-placeholder="Select field name..."> ' +
'             <option value=""></option> ' +
'             {{#each filterFields}}' +
'                 <option data-type="{{type}}" value="{{name}}">{{string}}</option>' +
'             {{/each}}' +
'         </select>' +
'       </div>' +
'       <div class="filter-operator hide controls span3"> ' +
'         <select name="operator" class="operator chzn-select chzn-inherit-width" data-placeholder="Select operator...">' +
'         </select>' +
'       </div>' +
'       <div class="filter-value hide controls span4">' +
'       </div>' +
'       <div class="filter-actions span2">' +
'         <a class="removeme btn btn-invisible hide btn-dark"><i class="icon-minus"></i></a>' +
'         <a class="updateme btn btn-invisible hide btn-dark"><i class="icon-refresh"></i></a>' +
'         <a class="addme btn btn-invisible disabled btn-dark"><i class="icon-plus"></i></a>' +
'       </div>' +
'     </div>' +
'   </article>'),

    filterOperatorMap: {
        'enum': ['is', 'is not'],
        'varchar': ['matches', 'does not match', 'contains', 'does not contains', 'starts with', 'does not start with', 'ends with', 'does not end with'],
        'name': ['matches', 'does not match', 'contains', 'does not contains', 'starts with', 'does not start with', 'ends with', 'does not end with'],
        'currency': ['is equal to', 'is greater than', 'is greater than or equal to', 'is less than', 'is less than or equal to'],
        'int': ['is equal to', 'is greater than', 'is greater than or equal to', 'is less than', 'is less than or equal to'],
        'double': ['is equal to', 'is greater than', 'is greater than or equal to', 'is less than', 'is less than or equal to'],
        'datetime': ['datetime operator'],
        'base': ['fall through to this case']
    },

    initialize: function(opts) {
        var self = this;
        this.title = app.controller.context.get('module');
        app.view.View.prototype.initialize.call(this, opts);
    },

    render: function() {
        app.view.View.prototype.render.call(this);
        this.fields = app.metadata.getModule(this.title).fields;
        _.each(this.fields, function(value, key) {
            var el = $("<option />").attr('value', key).text(app.lang.getAppString(value.vname));
            self.$('#filter_row_new select.field_name').append(el);
        });
    },

    addRow: function(e) {
    },

    triggerClose: function() {
        this.layout.trigger("filter:create:close:fire");
    }
})
