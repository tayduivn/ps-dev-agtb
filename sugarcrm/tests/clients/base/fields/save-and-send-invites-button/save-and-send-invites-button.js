describe('View.Fields.Base.SaveAndSendInvitesButtonField', function() {
    var app, event, field, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'save-and-send-invites-button');
        SugarTest.testMetadata.set();

        field = SugarTest.createField(
            'base',
            'save_button',
            'save-and-send-invites-button',
            'edit',
            undefined,
            'Meetings',
            undefined,
            undefined,
            'Meetings'
        );

        sandbox = sinon.sandbox.create();

        event = $.Event('click');
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('when the save button is clicked', function() {
        it('should not show the alert when the button is disabled', function() {
            var spy = sandbox.spy(app.alert, 'show');
            sandbox.stub(field, 'preventClick').returns(false);
            field.rowActionSelect(event);
            expect(spy).not.toHaveBeenCalled();
        });

        it('should show the alert when the button is not disabled', function() {
            var spy = sandbox.spy(app.alert, 'show');
            sandbox.stub(field, 'preventClick').returns(true);
            field.rowActionSelect(event);
            expect(spy).toHaveBeenCalled();
        });
    });

    describe('when the yes button is clicked', function() {
        describe('when the invitees field is not dirty', function() {
            var spy;

            beforeEach(function() {
                spy = sandbox.spy(field, 'propagateEvent');
            });

            it('should set send_invites=true and trigger the event when there is no invitees field', function() {
                field.model.unset('invitees');
                field.handleYes(event);
                expect(field.model.get('send_invites')).toBe(true);
                expect(spy).toHaveBeenCalled();
            });

            it('should set send_invites=true and trigger the event when the invitees field is not dirty', function() {
                field.model.set('invitees', {isDirty: sandbox.stub().returns(false)});
                field.handleYes(event);
                expect(field.model.get('send_invites')).toBe(true);
                expect(spy).toHaveBeenCalled();
            });
        });

        describe('when the invitees field is dirty', function() {
            var stub;

            beforeEach(function() {
                var invitees;

                // stub it so no AJAX requests are made
                stub = sandbox.stub(app.api, 'call');

                field.model.module = 'Meetings';
                field.model.set('_module', 'Meetings');
                field.model.set('id', '1234');

                // just need an object with Backbone events
                invitees = app.data.createBean('Contacts', {name: 'Foo Bar'});
                invitees.isDirty = sandbox.stub().returns(true);
                field.model.set('invitees', invitees);
            });

            it('should trigger the event and call the send_invites API after sync', function() {
                // fast-forward by immediately triggering the sync event
                sandbox.stub(field, 'propagateEvent', function(event) {
                    field.model.get('invitees').trigger('sync');
                });

                field.handleYes(event);
                expect(stub).toHaveBeenCalled();
                expect(stub.args[0][0]).toEqual('update');
                expect(stub.args[0][1]).toMatch(/.*rest\/v10\/Meetings\/1234\/send_invites/);
            });

            it('should not attempt to call the send_invites API more than once on sync', function() {
                // attempt to trigger save to be called twice
                sandbox.stub(field, 'propagateEvent', function(event) {
                    var invitees = field.model.get('invitees');
                    invitees.trigger('sync');
                    invitees.trigger('sync');
                });

                field.handleYes(event);
                expect(stub.calledOnce).toBe(true);
            });
        });
    });

    describe('when the no button is clicked', function() {
        it('should set send_invites=false and trigger the event', function() {
            var spy = sandbox.spy(field, 'propagateEvent');
            field.handleNo(event);
            expect(field.model.get('send_invites')).toBe(false);
            expect(spy).toHaveBeenCalled();
        });
    });
});
