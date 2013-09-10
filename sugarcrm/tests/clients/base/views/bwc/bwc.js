describe('Base.View.Bwc', function() {
    var view, app;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'Documents', 'bwc', null, null);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
    });

    describe('Handling iframe URLs', function() {
		
    	it('Add frame mark to URL', function() {
    		var withMark = view._addIframeMark('/sugar7/index.php?module=Administration&action=Home'); 
    		expect(withMark).toBe('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		withMark = view._addIframeMark('/sugar7/index.php'); 
    		expect(withMark).toBe('/sugar7/index.php?bwcFrame=1');
    	});
    	
    	it('Remove frame mark from URL', function() {
    		var noMark = view._rmIframeMark('/sugar7/index.php?module=Administration&action=Home&bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = view._rmIframeMark('/sugar7/index.php?bwcFrame=1');
    		expect(noMark).toBe('/sugar7/index.php?'); 
    		noMark = view._rmIframeMark('/sugar7/index.php?module=Administration&bwcFrame=1&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = view._rmIframeMark('/sugar7/index.php?module=Administration&action=Home');
    		expect(noMark).toBe('/sugar7/index.php?module=Administration&action=Home'); 
    		noMark = view._rmIframeMark('/sugar7/index.php');
    		expect(noMark).toBe('/sugar7/index.php'); 
    	});
    });
    
    describe('Warning unsaved changes', function() {
        var alertShowStub;
        beforeEach(function() {
            sinon.collection.stub(app.router, 'navigate', function() {});
            alertShowStub = sinon.collection.stub(app.alert, 'show');
            sinon.collection.stub(Backbone.history, 'getFragment');
        });

        afterEach(function() {
            alertShowStub.restore();
        });

        it('serialize form elements', function() {
            var form = $('<form>' +
                '<input name="name" value="test">' +
                '<input name="phone_number" value="121-1213-456">' +
                '<input type="checkbox" name="check1" value="c">' +
                '<input type="checkbox" name="check1" value="d" checked>' +
                '<input type="radio" name="radio1" value="1">' +
                '<input type="radio" name="radio1" value="0" checked>' +
                '<select name="select1">' +
                '<option value="blah1">Boo1</option>' +
                '<option value="blah2" selected>Boo2</option>' +
                '</select>' +
                '<textarea name="text1">raw data set</textarea>' +
                '</form>').get(0);
            var actual = view.serializeObject(form);
            expect(actual.name).toBe('test');
            expect(actual.phone_number).toBe('121-1213-456');
            expect(actual.radio1).toBe('0');
            expect(actual.select1).toBe('blah2');
            expect(actual.check1).toBe('d');
            expect(actual.text1).toBe('raw data set');

            //Assign new value changing by jQuery
            $(form).find('[name=name]').val('new test value');
            $(form).find('[name=select1]').val('blah1');

            //Assign new value changing by JS
            form.phone_number.value = '999-888-1200';
            var actual2 = view.serializeObject(form);
            expect(actual2.name).toBe('new test value');
            expect(actual2.phone_number).toBe('999-888-1200');
            expect(actual2.radio1).toBe(actual.radio1);
            expect(actual2.select1).toBe('blah1');
            expect(actual2.check1).toBe(actual.check1);
            expect(actual2.text1).toBe(actual.text1);
        });

        it('should ignore unsavedchange logic when current view does not contain form data', function() {
            var emptyForm = $('<div>' +
                '<a href="javascript:void(0);"></a>' +
                '<h1>Title foo</h1>' +
                '</div>').get(0);
            sinon.collection.stub(view.$el, 'get', function() {
                return {
                    contentWindow: {
                        EditView: emptyForm
                    }
                };
            });
            var bwcWindow = view.$el.get(0).contentWindow,
                attributes = view.serializeObject(bwcWindow.EditView);
            view.resetBwcModel(attributes);
            expect(_.isEmpty(view.bwcModel.attributes)).toBe(true);
            expect(view.hasUnsavedChanges()).toBe(false);
        });

        it('warn unsaved changes on bwc iframe', function() {
            var form = $('<form>' +
                '<input name="name" value="test">' +
                '<input name="phone_number" value="121-1213-456">' +
                '</form>').get(0);
            view.resetBwcModel({module: 'Document'});
            sinon.collection.stub(view.$el, 'get', function() {
                return {
                    contentWindow: {
                        EditView: form
                    }
                };
            });
            expect(view.hasUnsavedChanges()).toBe(true);
            var bwcWindow = view.$el.get(0).contentWindow,
                attributes = view.serializeObject(bwcWindow.EditView);
            //reset to the current changed form
            view.resetBwcModel(attributes);
            expect(view.hasUnsavedChanges()).toBe(false);
            //change the value once again
            form.phone_number.value = '408-888-8888';
            expect(view.hasUnsavedChanges()).toBe(true);
        });
    });
});
