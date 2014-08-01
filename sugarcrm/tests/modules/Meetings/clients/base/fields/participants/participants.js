describe('View.Fields.Base.Meetings.ParticipantsField', function() {
    var app, context, field, fieldDef, fixture, model, module, participants, sandbox;

    module = 'Meetings';

    participants = [
        {_module: 'Users', id: '1', name: 'Jim Brennan', accept_status_meetings: 'accept', delta: 0},
        {_module: 'Users', id: '2', name: 'Will Weston', accept_status_meetings: 'decline', delta: 0},
        {_module: 'Contacts', id: '3', name: 'Jim Gallardo', accept_status_meetings: 'tentative', delta: 0},
        {_module: 'Leads', id: '4', name: 'Sallie Talmadge', accept_status_meetings: 'none', delta: 0}
    ];

    fieldDef = {module_list: ['Users', 'Contacts', 'Leads']};

    fixture = {
        _hash: '12345678910',
        fields: {
            id: {
                name: 'id',
                type: 'id'
            },
            first_name: {
                name: 'first_name',
                type: 'varchar',
                len: 20
            },
            last_name: {
                name: 'last_name',
                type: 'varchar'
            },
            full_name: {
                name: 'full_name',
                type: 'varchar',
                concat: ['first_name', 'last_name']
            }
        }
    };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.updateModuleMetadata('Users', _.extend({}, fixture, {isBwcEnabled: true}));
        SugarTest.testMetadata.updateModuleMetadata('Contacts', {isBwcEnabled: false});
        SugarTest.testMetadata.updateModuleMetadata('Leads', _.extend({}, fixture, {isBwcEnabled: false}));
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'detail', module);
        SugarTest.loadHandlebarsTemplate('participants', 'field', 'base', 'edit', module);
        SugarTest.loadComponent('base', 'field', 'participants', module);
        SugarTest.declareData('base', module, true, false);
        SugarTest.loadPlugin('EllipsisInline');
        SugarTest.loadPlugin('LinkField');
        SugarTest.loadPlugin('Tooltip');
        SugarTest.testMetadata.set();
        app.data.declareModelClass('Users', null, 'base', fixture);
        app.data.declareModelClass('Leads', null, 'base', fixture);
        SugarTest.app.data.declareModels();

        context = app.context.getContext({module: module});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.api, 'call', function(method, url, data, callbacks, options) {
            if (callbacks.success) {
                callbacks.success({});
            }
        });
    });

    afterEach(function() {
        sandbox.restore();

        if (field) {
            field.dispose();
        }

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('accessing the field value', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context,
                true
            );
            // skip rendering
            field.model.off('change:invitees');
        });

        it('should return the collection', function() {
            field.model.get('invitees').reset(participants);
            expect(field.getFieldValue().length).toBe(participants.length);
        });

        it('should throw an exception when the field value is the wrong type', function() {
            field.model.set('invitees', 'foo');
            expect(function() {
                var value = field.getFieldValue();
            }).toThrow();
        });
    });

    describe('when the participants field is in detail mode', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context,
                true
            );
            field.getFieldValue().reset(participants);
        });

        it('should render one row for each participant', function() {
            field.render();
            expect(field.$('div.row.participant').length).toBe(participants.length);
        });

        it('should preview the participant when the preview button is clicked', function() {
            var previewBtn, spy;
            spy = sandbox.spy();
            app.events.on('preview:render', spy, this);
            field.render();
            previewBtn = field.$('button[data-action=previewRow]:not(.disabled)').first();
            previewBtn.click();
            expect(spy).toHaveBeenCalled();
            app.events.off('preview:render', this);
        });
    });

    describe('when the participants field is in edit mode', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'edit',
                fieldDef,
                module,
                model,
                context,
                true
            );
            field.action = 'edit';
            field.getFieldValue().reset(participants);
        });

        it('should hide the select initially', function() {
            field.render();
            expect(field.$('[name=newRow]').css('display')).toEqual('none');
        });

        it('should only have one plus button', function() {
            field.render();
            expect(field.$('button[data-action=addRow]').length).toBe(1);
        });

        it('should show the select and hide the plus button when the plus button is clicked', function() {
            field.render();
            field.$('button[data-action=addRow]').click();
            expect(field.$('[name=newRow]').css('display')).toEqual('table');
            expect(field.$('button[data-action=addRow]').css('display')).toEqual('none');
        });

        it("should hide the select and show the plus button when the select row's minus button is clicked", function() {
            field.render();
            field.$('button[data-action=addRow]').click();
            field.$('button[data-action=removeRow]').last().click();
            expect(field.$('[name=newRow]').css('display')).toEqual('none');
            expect(field.$('button[data-action=addRow]').css('display')).not.toEqual('none');
        });

        it("should remove a participant when that participant's minus button is clicked", function() {
            var spy = sandbox.spy(field.getFieldValue(), 'remove');
            field.render();
            field.$('button[data-action=removeRow]').first().click();
            expect(spy).toHaveBeenCalled();
            expect(field.$('div.row.participant').length).toBe(participants.length - 1);
        });

        it("should disable a participant's delete button when the participant is the current user", function() {
            app.user.id = '1';
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=1]').hasClass('disabled')).toBe(true);
        });

        it("should disable a participant's delete button when the participant is the assigned user", function() {
            field.model.set('assigned_user_id', '1');
            field.render();
            expect(field.$('button[data-action=removeRow][data-id=1]').hasClass('disabled')).toBe(true);
        });

        it('should add a participant when a new participant is selected', function() {
            var spy = sandbox.spy(field.getFieldValue(), 'add');
            field.render();
            field.getFieldElement().select2('data', {
                id: '5',
                text: 'George Walton',
                attributes: {_module: 'Contacts', id: '5', name: 'George Walton'}
            }, true);
            expect(spy).toHaveBeenCalled();
            expect(field.$('div.row.participant').length).toBe(participants.length + 1);
        });

        it('should search for more participants and add them to the options', function() {
            var data, query;

            query = {
                term: 'George',
                callback: sandbox.spy()
            };

            sandbox.stub(field.getFieldValue(), 'search', function(options) {
                var records = app.data.createMixedBeanCollection([
                    {_module: 'Contacts', id: '5', name: 'George Walton'}
                ]);
                options.success(records);
                options.complete();
            });

            field.search(query);
            data = query.callback.getCall(0).args[0];
            expect(data.more).toBe(false);
            expect(data.results.length).toBe(1);
        });

        it('should not include participants that are already invited', function() {
            var query = {
                term: 'Jim',
                callback: sandbox.spy()
            };

            sandbox.stub(field.getFieldValue(), 'search', function(options) {
                var records = app.data.createMixedBeanCollection([
                    {_module: 'Users', id: '1', name: 'Jim Brennan'},
                    {_module: 'Contacts', id: '3', name: 'Jim Gallardo'},
                    {_module: 'Leads', id: '6', name: 'Jim Long'}
                ]);
                options.success(records);
                options.complete();
            });

            field.search(query);
            expect(query.callback.getCall(0).args[0].results.length).toBe(1);
        });

        it('should produce no results when an exception is thrown', function() {
            var query = {
                term: 'Jim',
                callback: sandbox.spy()
            };

            sandbox.stub(field, 'getFieldValue').throws();
            field.search(query);
            expect(query.callback.getCall(0).args[0].results.length).toBe(0);
        });
    });

    describe('formatting the view model for render', function() {
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'invitees',
                'participants',
                'detail',
                fieldDef,
                module,
                model,
                context,
                true
            );
        });

        it('should return an empty array when an exception is thrown', function() {
            field.getFieldValue().reset(participants);
            sandbox.stub(field, 'getFieldValue').throws();
            expect(field.format(undefined).length).toBe(0);
        });

        it('should only return participants whose deltas are greater than -1', function() {
            var collection = field.getFieldValue();
            collection.reset(participants);
            collection.add([
                {_module: 'Contacts', id: '5', name: 'George Walton'},
                {_module: 'Contacts', id: '6', name: 'Jim Long'}
            ]);
            collection.remove(['2', '4']);
            expect(collection.length).toBe(participants.length + 2);
            expect(field.format(undefined).length).toBe(participants.length);
        });

        it('should set the last property to true for only the final participant', function() {
            var isLast;
            field.getFieldValue().reset(participants);
            isLast = _.findWhere(field.format(undefined), {last: true});
            expect(isLast.name).toEqual('Sallie Talmadge');
        });

        it('should only include an avatar property when the participant has a picture field', function() {
            var hasAvatar;
            field.getFieldValue().reset([
                {_module: 'Contacts', id: '5', name: 'George Walton', picture: '5'},
                {_module: 'Contacts', id: '6', name: 'Jim Long'}
            ]);
            hasAvatar = _.filter(field.format(undefined), function(participant) {
                return !_.isUndefined(participant.avatar);
            });
            expect(hasAvatar.length).toBe(1);
        });

        it('should set the accept status appropriately', function() {
            var collection, formatted;
            collection = field.getFieldValue();
            collection.reset(participants);
            collection.add([{_module: 'Contacts', id: '5', name: 'George Walton', accept_status_meetings: ''}]);
            formatted = field.format(undefined);
            expect(formatted[0].accept_status.label).toEqual('LBL_RESPONSE_ACCEPT');
            expect(formatted[0].accept_status.css_class).toEqual('success');
            expect(formatted[1].accept_status.label).toEqual('LBL_RESPONSE_DECLINE');
            expect(formatted[1].accept_status.css_class).toEqual('important');
            expect(formatted[2].accept_status.label).toEqual('LBL_RESPONSE_TENTATIVE');
            expect(formatted[2].accept_status.css_class).toEqual('warning');
            expect(formatted[3].accept_status.label).toEqual('LBL_RESPONSE_NONE');
            expect(formatted[3].accept_status.css_class).toEqual('');
            expect(formatted[4].accept_status.label).toEqual('LBL_RESPONSE_NONE');
            expect(formatted[4].accept_status.css_class).toEqual('');
        });
    });
});
