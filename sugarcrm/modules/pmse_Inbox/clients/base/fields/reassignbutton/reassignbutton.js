({
    extendsFrom: 'RowactionField',
    initialize: function (options) {
        this._super("initialize", [options]);
        this.type = 'rowaction';
    },

    _render: function () {
        var value=this.model.get('cas_status');
//        if(value==='TODO'){
        if(/TODO/.test(value)){
            this._super("_render");
        } else {
            this.hide();
        }
    },

    bindDataChange: function () {
        if (this.model) {
            this.model.on("change", this.render, this);
        }
    }
})
