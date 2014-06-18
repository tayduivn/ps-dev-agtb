/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

describe("Sugar7 utils", function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) { eval(d) });
    });

    afterEach(function() {

    });

    describe("getSubpanelCollection()", function() {
        it("should return the proper subpanel collection", function() {
            var ctx = {};
            ctx.children = [];

            var mdl = new Backbone.Model(),
                targetMdl = new Backbone.Model();

            targetMdl.set({id: 'targetMdl'});
            mdl.set({module: 'Test'});

            var col = new Backbone.Collection();
            col.add(targetMdl);

            mdl.set({collection: col});
            ctx.children.push(mdl);

            var targetCol = app.utils.getSubpanelCollection(ctx, 'Test');

            expect(targetCol.models[0].get('id')).toEqual('targetMdl');
        });
    });
    
    describe('Handling iframe URLs', function() {
		
    	it('Add frame mark to URL', function() {
    		var withMark = app.utils.addIframeMark('/sugar7/index.php?module=Administration&action=Home'); 
    		expect(withMark).toBe('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    		withMark = app.utils.addIframeMark('/sugar7/index.php?bwcFrame=1'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    	});
    	
    	it('Remove frame mark from URL', function() {
    		var noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&bwcFrame=1&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php?module=Administration&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = app.utils.rmIframeMark('/sugar7/index.php');
    		expect(noMark).toBe('/sugar7/index.php'); 
    	});
    });

    describe('getRecordName', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model();
        });
        it('should get document_name for Documents module', function() {
            model.module = 'Documents';
            model.set({
                document_name: 'Awesome Document',
                name: 'document.zip'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Document');
        });
        it('get full_name when available', function() {
            model.module = 'Contacts';
            model.set({
                full_name: 'Awesome Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('build full name based on first name and last name', function() {
            model.module = 'Users';
            model.set({
                first_name: 'Awesome',
                last_name: 'Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Awesome Name');
        });
        it('get name otherwise', function() {
            model.module = 'Leads';
            model.set({
                name: 'Simple Name'
            });
            expect(app.utils.getRecordName(model)).toEqual('Simple Name');
        });
    });

    var name = 'module';
    using('query strings',
        [
            ['?module=asdf', 'asdf'],
            ['?asdf=asdf&module=asdf&module=zxcv', 'zxcv'],
            ['?asdf=asdf&module=zxcv&modtrwer=zxcv', 'zxcv'],
            ['?xcvb=asdf&asdf=asdf&ryuit=zxcv', '']
        ],
        function (value, result) {
            it('should be able to get parameters', function () {
                var testResult = app.utils.getWindowLocationParameterByName(name, value);
                expect(result).toEqual(testResult);
            });
        });

    describe('getSelectedUsersReportees', function() {
        describe('as manager', function() {
            var user;
            beforeEach(function() {
                user = {
                    is_manager: true,
                    id: 'test_id'
                };
            });

            afterEach(function() {
                sinon.collection.restore();
                delete user;
            });

            it('will make an xhr call with status equal to active', function() {
                var post_args = undefined;
                sinon.collection.stub(app.api, 'call', function(type, url, args) {
                    post_args = args;
                });
                app.utils.getSelectedUsersReportees(user, {});
                expect(app.api.call).toHaveBeenCalled();
                expect(post_args).not.toBeUndefined();
                expect(post_args.filter[0].status).toEqual('Active');
            });
        });
    });
});
