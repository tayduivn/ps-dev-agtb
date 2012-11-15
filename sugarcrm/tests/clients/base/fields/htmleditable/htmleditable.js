describe("htmleditable", function() {

    describe("edit view", function() {
        var field, stub;

        beforeEach(function() {
            var $textarea = $('<textarea class="htmleditable"></textarea>');
            field = SugarTest.createField("base","html_email", "htmleditable", "edit");
            stub = sinon.stub(field, "_getHtmlEditableField", function(){
                return $textarea;
            });
        });

        afterEach(function() {
            stub.restore();
            field = undefined;
        });

        it("should render edit view not readonly view", function() {
            var edit = sinon.spy(field, '_renderEdit');
            var view = sinon.spy(field, '_renderView');

            field.render();

            expect(edit.calledOnce).toBeTruthy();
            expect(view.called).toBeFalsy();

            edit.restore();
            view.restore();
        });

        it("should give access to wysihtml5 editor", function() {
            field.render();

            expect(field._getWysiHtml5Editor()).toBeDefined();
        });

        it("should initialize wysihtml5 editor when it doesn't exist", function() {
            var wysihtml5Spy = sinon.spy($.fn, 'wysihtml5');

            field._getWysiHtml5Editor();

            expect(wysihtml5Spy.calledOnce).toBeTruthy();

            wysihtml5Spy.restore();
        });

        it("should not initialize wysihtml5 editor if it already exists", function() {
            var wysihtml5Spy = sinon.spy($.fn, 'wysihtml5');

            field._getWysiHtml5Editor();
            field._getWysiHtml5Editor();

            expect(wysihtml5Spy.calledOnce).toBeTruthy();

            wysihtml5Spy.restore();
        });

        it("setting a value to the model should also set the editor with that value", function() {
            var expectedValue = 'foo';
            var setEditorContentSpy;

            field.render();
            setEditorContentSpy = sinon.spy(field, '_setEditorContent');
            field.model.set(field.name, expectedValue);

            expect(setEditorContentSpy.withArgs(expectedValue).calledOnce).toBeTruthy();

            setEditorContentSpy.restore();
        });
    });

    describe("readonly view", function() {
        var field, stub;

        beforeEach(function() {
            var $textarea = $('<iframe class="htmleditable"></iframe>');
            field = SugarTest.createField("base","html_email", "htmleditable", "detail");
            stub = sinon.stub(field, "_getHtmlEditableField", function(){
                return $textarea;
            });
        });

        afterEach(function() {
            stub.restore();
            field = undefined;
        });

        it("should render read view not edit view", function() {
            var edit = sinon.spy(field, '_renderEdit');
            var view = sinon.spy(field, '_renderView');

            field.render();

            expect(edit.called).toBeFalsy();
            expect(view.calledOnce).toBeTruthy();

            edit.restore();
            view.restore();
        });

        it("should not return wysihtml5 editor", function() {
            var wysihtml5Spy = sinon.spy(field, '_getWysiHtml5Editor');

            field.render();

            expect(wysihtml5Spy.called).toBeFalsy();

            wysihtml5Spy.restore();
        });

        it("should set the value to the iframe if the model is changed", function() {
            var mock, expectedValue = 'foo';

            field.render();

            mock = sinon.mock(field);
            mock.expects('_setIframeContent').once().withArgs(expectedValue);

            field.model.set(field.name, expectedValue);

            mock.verify();
            mock.restore();
        });
    });

});
