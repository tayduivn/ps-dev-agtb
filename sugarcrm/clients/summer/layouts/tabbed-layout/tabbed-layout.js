/**
 * Layout that places components using bootstrap fluid layout divs
 * @class View.Layouts.ListLayout
 * @extends View.FluidLayout
 */
({
    firstIsActive :false,
    initialize:function (options) {
        _.bindAll(this);
        var div = $('<div/>').addClass('tabbable');
        div.append($('<ul/>').addClass('nav nav-tabs'));
        div.append($('<div/>').addClass('tab-content'));

        this.$el.append(div);
        app.view.Layout.prototype.initialize.call(this, options);
    },
    /**
     * Places a view's element on the page. This shoudl be overriden by any custom layout types.
     * @param {View.View} comp
     * @protected
     * @method
     */
    _placeComponent:function (comp, def) {
        var id = _.uniqueId('record-bottom');
        // All components of this layout will be placed within the
        // innermost container div.
        var tabbable = this.$el.find('.tabbable');
        var nav = $('<li/>');
        if(!this.firstIsActive)nav.addClass('active');
        nav.html('<a href="#' + id + '" onclick="return false;" data-toggle="tab">' + def.label + '</a>')
        tabbable.find('.nav').append(nav);
        var content = $('<div/>').addClass('tab-pane').attr('id', id).html(comp.el);
        if(!this.firstIsActive)content.addClass('active');
        this.firstIsActive = true;
        tabbable.find('.tab-content').append(content);


    }

})