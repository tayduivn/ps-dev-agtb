describe("Base.Field.ActionMenu", function() {
    var app,
        field,
        Account,
        layout,
        $checkBox,
        triggerStub;

    beforeEach(function() {
        app = SugarTest.app;
        layout = {trigger: function(){}, off: function(){}};
        var def = {};
        SugarTest.loadComponent("base", "view", "list");
        SugarTest.loadComponent("base", "view", "flex-list");
        field = SugarTest.createField('base', 'actionmenu', 'actionmenu', 'recordlist', def);
        field.view.layout = layout;

    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field._loadTemplate = null;
        field = null;
        Account = null;
    });

    describe('disable the alert when selecting all', function() {
        var dataProvider = [
            {
                message:  'should disable the alert when the disable_select_all_alert option is true',
                disable:  true,
                expected: true
            },
            {
                message:  'should not disable the alert when the disable_select_all_alert option is false',
                disable:  false,
                expected: false
            },
            {
                message:  'should not disable the alert when the disable_select_all_alert option is undefined',
                disable:  undefined,
                expected: false
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var def = {};
                if (!_.isUndefined(data.disable)) {
                    def.disable_select_all_alert = data.disable;
                }
                field = SugarTest.createField('base', 'actionmenu', 'actionmenu', 'list', def);
                expect(field.def.disable_select_all_alert).toBe(data.expected);
            });
        });

        it('should not trigger "list:alert:*" if the disable_select_all_alert option is true', function() {
            var spyOnTrigger,
                def = {disable_select_all_alert: true};
            field = SugarTest.createField('base', 'actionmenu', 'actionmenu', 'list', def);
            field.view.layout = layout;
            spyOnTrigger = sinon.spy(field.view.layout, 'trigger');
            field.toggleSelectAll();
            expect(spyOnTrigger).not.toHaveBeenCalled();
            spyOnTrigger.restore();
        });
    });

    describe('toggleSelect:', function() {
        beforeEach(function() {
            triggerStub = sinon.collection.stub(field.context, 'trigger');
        });
        it('should trigger a "mass_collection:add" event', function() {
            field.toggleSelect(true);
            expect(triggerStub).toHaveBeenCalledWith('mass_collection:add');

        });
        it('should trigger a "mass_collection:remove" event', function() {
            field.toggleSelect(false);
            expect(triggerStub).toHaveBeenCalledWith('mass_collection:remove');
        });
    });

    describe('checkAll:', function() {
        beforeEach(function() {
            triggerStub = sinon.collection.stub(field.context, 'trigger');
            $checkBox = '<input type="checkbox" name="check">';
            field.$el.append($checkBox);
        });
        it('should trigger a "mass_collection:add:all" event', function() {
            field.$(field.fieldTag).prop('checked', true);
            field.checkAll();
            expect(triggerStub).toHaveBeenCalledWith('mass_collection:add:all');
        });

        it('should trigger a "mass_collection:remove:all" event', function() {
            field.$(field.fieldTag).prop('checked', false);
            field.checkAll();
            expect(triggerStub).toHaveBeenCalledWith('mass_collection:remove:all');
        });
    });

    describe('check:', function() {
        beforeEach(function() {
            $checkBox = '<input type="checkbox" name="check">';
            field.$el.append($checkBox);
        });

        using('checkbox state', [true, false], function(state) {
            it('should call toggleSelect', function() {
                var toggleSelectStub = sinon.collection.stub(field, 'toggleSelect');
                field.$(field.fieldTag).prop('checked', state);
                field.check();

                expect(toggleSelectStub).toHaveBeenCalledWith(state);
            });
        });
    });

    it('should create action button components on the list header', function() {
        var def = {
            'buttons' : [
                {
                    'name' : 'test_button',
                    'type' : 'button',
                    'events' : {
                        'click' : 'function() { this.callback = "stuff executed"; }',
                        'blur [name=test_button]' : 'function() { this.callback = "blur executed"; }'
                    }
                }
            ]
        };

        field = SugarTest.createField("base","actionmenu", "actionmenu", "list-header", def);
        field._loadTemplate = function() { this.template = function(){ return '<a href="javascript:void(0);"></a>'}; };
        var MassCollection = app.BeanCollection.extend();
        var massCollection = new MassCollection();
        massCollection.add({id: '1', name: 'toto'}, {id: '2', name: 'tata'}, {id: '3', name: 'titi'});
        field.context.set('mass_collection', massCollection);
        field.getPlaceholder();

        expect(def.buttons.length).toBe(field.fields.length);
        expect(_.where(_.pluck(def.buttons, 'events'), {
            'click' : 'function() { this.callback = "stuff executed"; }',
            'blur [name=test_button]' : 'function() { this.callback = "blur executed"; }'
        })).not.toBeEmpty();
    });
});
