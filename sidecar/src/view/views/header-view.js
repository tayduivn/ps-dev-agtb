(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    app.view.views.HeaderView = app.view.View.extend({
        events: {
        },
      render: function(){
            this.moduleList = this.moduleList || _.toArray(app.metadata.getModuleList());
           app.view.View.prototype.render.call(this);
      }
    });

})(SUGAR.App);