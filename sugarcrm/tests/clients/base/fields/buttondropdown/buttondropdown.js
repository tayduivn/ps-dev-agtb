describe("Dropdown button field", function() {
    var sinonSandbox, field;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('buttondropdown', 'field', 'base', 'detail');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'buttondropdown');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();

        field = SugarTest.createField('base', 'my_button_dropdown', 'buttondropdown', 'detail', {
            "type":"buttondropdown",
            "name":"main_dropdown",
            "buttons":[{
                "name":"one",
                "label":"one",
                "primary":true,
                "showOn":"view"
            }, {
                "name":"two",
                "label":"two",
                "showOn":"edit"
            }, {
                "name":"three",
                "label":"three"
            }]
        });
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
    });

    describe('Render', function() {
        it("Should render 1 buttondropdown and 3 buttons", function() {
            var buttons = 0,
                dropdowns = 0;

            field.render();

            _.each(field.view.fields, function(field) {
                if (field.type === 'button') {
                    buttons++;
                }
                if (field.type === 'buttondropdown') {
                    dropdowns++;
                }
            });

            expect(dropdowns).toBe(1);
            expect(buttons).toBe(3);
        });

        it("Should only render the first button as a primary button", function() {
            field.render();

            expect(field.view.getField('one').$('a.btn').hasClass('btn-primary')).toBe(true);
            expect(field.view.getField('two').$('a.btn').hasClass('btn-primary')).toBe(false);
            expect(field.view.getField('three').$('a.btn').hasClass('btn-primary')).toBe(false);
        });

        it("Should wrap dropdown options buttons with 'li' html tag", function() {
            field.render();

            expect(field.view.getField('one').$('li a').length).toBe(0);
            expect(field.view.getField('two').$('li a').length).toBe(1);
            expect(field.view.getField('three').$('li a').length).toBe(1);
        });
    });
});