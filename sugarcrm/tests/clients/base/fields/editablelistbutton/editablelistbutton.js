describe("BaseEditablelistbuttonField", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'editablelistbutton');

        field = SugarTest.createField("base","editablelistbutton", "editablelistbutton");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });
    it('should be able to trigger filtering to the filterpanel layout.', function() {
        //Fake layouts
        field.view = new Backbone.View();
        field.view.layout = new Backbone.View();
        field.view.layout.layout = new Backbone.View();
        field.view.layout.layout.name = 'filterpanel';
        //Fake quicksearch field
        var $input = $('<input>').addClass('search-name').val('query test');
        $('<div>').addClass('search').append($input[0]).appendTo(field.view.layout.layout.$el);
        //Fake original filter def
        var origFilterDef = [{field1: { $equals: 'value1'}}, {field2: { $starts: 'value2'}}];
        field.collection.origFilterDef = origFilterDef;
        var triggerStub = sinon.stub(field.view.layout.layout, 'trigger');
        //Call the method
        field._refreshListView();

        expect(triggerStub).toHaveBeenCalled();
        expect(triggerStub).toHaveBeenCalledWith('filter:apply', 'query test', origFilterDef);
        triggerStub.restore();
    });
});
