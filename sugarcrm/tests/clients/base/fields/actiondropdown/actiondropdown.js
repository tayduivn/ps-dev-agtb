describe('Base.Field.Actiondropdown', function() {

    var app, field, view, moduleName = 'Contacts';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        app = SugarTest.app;
        SugarTest.testMetadata.addViewDefinition('record', {
            "type": "record",
            "panels":[
                {
                    "name":"panel_header",
                    "placeholders":true,
                    "header":true,
                    "labels":false,
                    fields: [
                        {
                            "name" : "dropdown",
                            "type":"actiondropdown",
                            "label":"",
                            "buttons": [
                                {
                                    "type" : "button",
                                    "name" : "test1"
                                },
                                {
                                    "type" : "button",
                                    "name" : "test2"
                                }
                            ]
                        }
                    ]

                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'view', 'editable');
        SugarTest.loadComponent('base', 'view', 'record');
        var context = SugarTest.app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView("base", moduleName, 'record', null, context);
        view.createMode = false;
        view.render();
        field = view.getField('dropdown');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    it('should render button html nested on the buttons', function() {
        _.each(field.fields, function(button){
            var actualPlaceholderCount = field.$el.find("span[sfuuid='" + button.sfId + "']").length;
            expect(actualPlaceholderCount).toBe(1);
        });
    });

    it('should populate proper dropdown list when a nested button is hidden', function() {

        expect(field.fields.length).toBeGreaterThan(1);


        var button = field.fields[1];
        var actualPlaceholderCount = field.$(".dropdown-menu").find("span[sfuuid='" + button.sfId + "']").length;
        expect(actualPlaceholderCount).toBe(1);

        //second button should be at the primary position when the first one is hidden
        field.fields[0].hide();
        expect(field.fields[0].$el.is(":hidden")).toBe(true);
        actualPlaceholderCount = field.$(".dropdown-menu").find("span[sfuuid='" + button.sfId + "']").length;
        expect(actualPlaceholderCount).toBe(0);

        //the button position should be restored when the first one is shown once again
        field.fields[0].show();
        actualPlaceholderCount = field.$(".dropdown-menu").find("span[sfuuid='" + button.sfId + "']").length;
        expect(actualPlaceholderCount).toBe(1);
    });
});
