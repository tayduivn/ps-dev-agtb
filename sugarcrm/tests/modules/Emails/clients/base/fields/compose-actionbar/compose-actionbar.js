describe("Emails.fields.compose-actionbar", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'compose-actionbar', 'Emails');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.testMetadata.set();

        field = SugarTest.createField("base", "compose-actionbar", "compose-actionbar", "edit", null, "Emails", null, null, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        field = null;
    });

    describe("getPlaceholder", function() {
        it("should return empty span if no def", function() {
            var $result;
            field.def = {};
            $result = $(field.getPlaceholder().toString());
            expect($result.find('span').length).toBe(0); //no field placeholders
        });

        it("should return actiondropdown placeholder if an actiondropdown type is in the list", function() {
            var $result;
            field.def = {
                'buttonSections': [
                    {
                        'name': 'my_dropdown',
                        'type': 'actiondropdown',
                        'buttons': [
                            {
                                'name': 'foo',
                                'type': 'button',
                                'primary': true
                            },
                            {
                                'name': 'bar',
                                'type': 'button'
                            }
                        ]
                    }
                ]
            };
            $result = $(field.getPlaceholder().toString());
            expect(field.fields.length).toBe(1);
            expect(field.fields[0].type).toBe('actiondropdown');
            expect($result.find('.actions').length).toBe(1); //action dropdown
            expect($result.find('.actions>span').length).toBe(1); //foo button placeholder
            expect($result.find('.dropdown-menu>li').length).toBe(1); //bar button placeholder
        });

        it("should return default placeholder if no type on button section", function() {
            var $result;
            field.def = {
                'buttonSections': [
                    {
                        'name': 'my_dropdown',
                        'buttons': [
                            {
                                'name': 'foo',
                                'type': 'button',
                                'primary': true
                            },
                            {
                                'name': 'bar',
                                'type': 'button'
                            }
                        ]
                    }
                ]
            };
            $result = $(field.getPlaceholder().toString());
            expect(field.fields.length).toBe(2);
            expect($result.find('.actions').length).toBe(1);
            expect($result.find('span').length).toBe(2); //2 placeholders for buttons
        });

        it("should two button sections if two in the def", function() {
            var $result, $sections;
            field.def = {
                'buttonSections': [
                    {
                        'name': 'my_dropdown',
                        'buttons': [
                            {
                                'name': 'foo',
                                'type': 'button',
                                'primary': true
                            }
                        ]
                    },
                    {
                        'name': 'my_dropdown2',
                        'buttons': [
                            {
                                'name': 'bar',
                                'type': 'button'
                            },
                            {
                                'name': 'baz',
                                'type': 'button'
                            }
                        ]
                    }
                ]
            };
            $result = $(field.getPlaceholder().toString());
            expect(field.fields.length).toBe(3); //3 buttons total
            $sections = $result.find('.actions');
            expect($sections.length).toBe(2); //2 placeholders
            expect($sections.filter(':eq(0)').find('span').length).toBe(1); //1 button in first section
            expect($sections.filter(':eq(1)').find('span').length).toBe(2); //2 buttons in second section
        });
    });

    describe("handleButtonClick", function() {
        var $button1, $button2, $button3, triggerStub, triggerCode;

        beforeEach(function() {
            field.$el = $('<div></div>');
            $button1 = $('<a name="button1" data-event="foo">button1</a> ');
            field.$el.append($button1.get(0).outerHTML);
            $button2 = $('<a name="button2">button2</a> ');
            field.$el.append($button2.get(0).outerHTML);
            $button3 = $('<a id="noname">button3</a> ');
            field.$el.append($button3.get(0).outerHTML);

            triggerStub = sinon.stub(field.view.context, 'trigger', function(code) {
                triggerCode = code;
            });
        });

        afterEach(function() {
            triggerCode = null;
            triggerStub.restore();
        });

        it("should fire event specified by data-event if there", function() {
            var event = {'currentTarget': $button1.get(0)};
            field.handleButtonClick(event);
            expect(triggerCode).toEqual('foo');
        });

        it("should fire event specifying the name if no data-event", function() {
            var event = {'currentTarget': $button2.get(0)};
            field.handleButtonClick(event);
            expect(triggerCode).toEqual('actionbar:button2:clicked');
        });

        it("should fire default event if no data-event or name", function() {
            var event = {'currentTarget': $button3.get(0)};
            field.handleButtonClick(event);
            expect(triggerCode).toEqual('actionbar:button:clicked');
        });
    });
});
