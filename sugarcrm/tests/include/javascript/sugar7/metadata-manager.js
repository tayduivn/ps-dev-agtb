/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Sugar7.Metadata', function() {
    var app, sandbox;

    beforeEach(function() {
        var mock;

        app = SugarTest.app;

        sandbox = sinon.sandbox.create();

        mock = sandbox.mock(app.metadata);
        mock.expects('getRelationship').withArgs('meetings_users').returns({rhs_module: 'Users'});
        mock.expects('getRelationship').withArgs('meetings_contacts').returns({rhs_module: 'Contacts'});
        mock.expects('getRelationship').withArgs('meetings_leads').returns({rhs_module: 'Leads'});
        mock.expects('getRelationship').withArgs('meetings_foo').returns({});
        mock.expects('getModule').returns({
            fields: {
                name: {
                    name: 'name',
                    // not a link
                    type: 'varchar'
                },
                users: {
                    name: 'users',
                    relationship: 'meetings_users',
                    type: 'link'
                },
                contacts: {
                    name: 'contacts',
                    relationship: 'meetings_contacts',
                    type: 'link'
                },
                leads: {
                    name: 'leads',
                    relationship: 'meetings_leads',
                    type: 'link'
                },
                targets: {
                    // has no name
                    name: '',
                    relationship: 'meetings_targets',
                    type: 'link'
                }
            }
        });
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
    });

    describe('when you need the names of the RHS modules from links in the LHS vardef', function() {
        it('should return three pairs', function() {
            var pairs = app.metadata.getRHSModulesForLinks('Meetings', ['users', 'contacts', 'leads']);
            expect(_.size(pairs)).toBe(3);
            expect(pairs.users).toEqual('Users');
            expect(pairs.contacts).toEqual('Contacts');
            expect(pairs.leads).toEqual('Leads');
        });

        it('should not include fields without a name', function() {
            var pairs = app.metadata.getRHSModulesForLinks('Meetings', ['targets']);
            expect(_.size(pairs)).toBe(0);
        });

        it('should not include fields that are not links', function() {
            var pairs = app.metadata.getRHSModulesForLinks('Meetings', ['name']);
            expect(_.size(pairs)).toBe(0);
        });

        it('should not include fields that are not found in the LHS vardef', function() {
            var pairs = app.metadata.getRHSModulesForLinks('Meetings', ['foo']);
            expect(_.size(pairs)).toBe(0);
        });
    });
});
