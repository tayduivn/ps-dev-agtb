describe("Quickcreate", function() {

    beforeEach(function() {
        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate('quickcreate-list', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('quickcreate', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('quickcreateactions', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('edit', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');

        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'view', 'edit');
        SugarTest.loadComponent('base', 'view', 'alert');
        SugarTest.loadComponent('base', 'view', 'quickcreate-alert');
        SugarTest.loadComponent('base', 'view', 'quickcreate-list');
        SugarTest.loadComponent('base', 'view', 'quickcreate');
        SugarTest.loadComponent('base', 'view', 'quickcreateactions');

        SugarTest.testMetadata.addViewDefinition('quickcreate-list', {
            panels: [{
                fields: [{
                    name: "first_name",
                    orderBy: "first_name"
                }, {
                    name: "last_name"
                }, {
                    name: "phone_work"
                }]
            }]
        });

        SugarTest.testMetadata.addViewDefinition('quickcreate', {
            type: "edit",
            panels: [{
                fields: [{
                    name: "first_name",
                    type: "text"
                }, {
                    name: "last_name",
                    type: "text",
                    duplicate_merge: "default"
                }, {
                    name: "phone_work",
                    type: "text",
                    duplicate_merge: "default"
                }]
            }]
        });

        SugarTest.testMetadata.addViewDefinition('quickcreateactions', {
            buttons: [{
                name: "restore_button",
                type: "button",
                label: "LBL_RESTORE",
                css_class: "hide btn-invisible btn-link"
            }, {
                name: "save_view_button",
                type: "button",
                label: "LBL_SAVE_AND_VIEW",
                css_class: "btn-invisible btn-link"
            }, {
                name: "save_create_button",
                type: "button",
                label: "LBL_SAVE_AND_CREATE_ANOTHER",
                css_class: "btn-invisible btn-link"
            }, {
                name: "cancel_button",
                type: "button",
                label: "LBL_CANCEL_BUTTON_LABEL",
                value: "cancel",
                css_class: "btn-invisible btn-link"
            }, {
                name: "save_button",
                type: "button",
                label: "LBL_SAVE_BUTTON_LABEL",
                value: "save",
                css_class: "btn-primary"
            }]
        });

        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
    });

    var initializeLayout = function() {
        var layout = SugarTest.createLayout('base', 'Contacts', 'quickcreate', {
            "type": "fluid",
            "components": [
                {"view":"quickcreate-alert"},
                {"view":"quickcreate-list"},
                {"view":"quickcreate"},
                {"view":"quickcreateactions"}
            ]
        });

        return layout;
    };

    describe('Render', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            delete layout;
        });

        it("should display three fields in the quickcreate form", function() {
            layout.render();

            expect(layout.$el.find('input').size()).toEqual(3);
        });

        it("should have four columns on the quickcreate-list table", function() {
            layout.render();

            expect(layout.$el.find('.dataTables_filter th').size()).toEqual(4);
        });

        it("should have the quickcreate-list table with no data", function() {
            layout.render();

            expect(layout.$el.find('.dataTables_filter td.dataTables_empty').size()).toEqual(1);
        });

        it("should have five buttons", function() {
            layout.render();

            expect(layout.$el.find('[name=cancel_button]').size()).toEqual(1);
            expect(layout.$el.find('[name=save_button]').size()).toEqual(1);
            expect(layout.$el.find('[name=save_create_button]').size()).toEqual(1);
            expect(layout.$el.find('[name=save_view_button]').size()).toEqual(1);
            expect(layout.$el.find('[name=restore_button]').size()).toEqual(1);
        });

        it("should have no alerts", function() {
            layout.render();

            expect(layout.$el.find('.alert').size()).toEqual(0);
        });
    });

    describe('Cancel', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            delete layout;
        });

        it("should close modal when cancel button is clicked", function() {
            var flag = false,
                stub = sinon.stub(layout, 'close', function() {
                    flag = true;
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=cancel_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'Modal should have been closed but timeout expired', 1000);

            runs(function() {
                expect(stub.calledOnce).toBeTruthy();

                stub.restore();
            });
        });
    });

    describe('Save', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            delete layout;
        });

        it("should save data when save button is clicked, form data are valid, and no duplicates are found", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    options.success(layout.collection);
                }),
                saveModelStub = sinon.stub(layout, 'saveModel', function() {
                    flag = true;
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'Save should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledOnce).toBeTruthy();
                expect(fetchStub.calledOnce).toBeTruthy();
                expect(saveModelStub.calledOnce).toBeTruthy();

                saveModelStub.restore();
                isValidStub.restore();
                fetchStub.restore();
            });
        });

        it("should close modal once save is complete", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    options.success(layout.collection);
                }),
                saveModelStub = sinon.stub(layout, 'saveModel', function(success) {
                    success();
                }),
                closeStub = sinon.stub(layout, 'close', function() {
                    flag = true;
                    return;
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'close should have been called but timeout expired', 1000);

            runs(function() {
                expect(closeStub.calledOnce).toBeTruthy();

                saveModelStub.restore();
                isValidStub.restore();
                fetchStub.restore();
                closeStub.restore();
            });
        });

        it("should not save data when save button is clicked but form data are invalid", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    flag = true;
                    return false;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    options.success(layout.collection);
                }),
                saveModelStub = sinon.stub(layout, 'saveModel', function() {
                    return;
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'isValid should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledOnce).toBeTruthy();
                expect(fetchStub.called).toBeFalsy();
                expect(saveModelStub.called).toBeFalsy();

                saveModelStub.restore();
                isValidStub.restore();
                fetchStub.restore();
            });
        });

        it("should not save data when save button is clicked but duplicates are found", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    flag = true;
                    layout.collection.push(new Backbone.Model({
                        test: '123'
                    }));
                    options.success(layout.collection);
                }),
                saveModelStub = sinon.stub(layout, 'saveModel', function() {
                    return;
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'fetch should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledOnce).toBeTruthy();
                expect(fetchStub.calledOnce).toBeTruthy();
                expect(saveModelStub.called).toBeFalsy();

                saveModelStub.restore();
                isValidStub.restore();
                fetchStub.restore();
            });
        });

        it("should change the save button label and hide other save buttons when duplicates are found", function() {
            var flag = false,
                saveCreateSelector, saveViewSelector,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    flag = true;
                    layout.collection.push(new Backbone.Model({
                        test: '123'
                    }));
                    options.success(layout.collection);
                }),
                hide = sinon.spy($.fn, 'hide');

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_button]').click();
            });
            waitsFor(function() {
                return flag;
            }, 'fetch should have been called but timeout expired', 1000);
            runs(function() {
                expect(layout.$el.find('[name=save_button]').text()).toEqual('LBL_IGNORE_DUPLICATE_AND_SAVE');
                expect(hide).toHaveBeenCalled();
                saveCreateSelector = _.filter(hide.thisValues, function(thisValue) { return thisValue.selector === "[name=save_create_button]" })
                saveViewSelector = _.filter(hide.thisValues, function(thisValue) { return thisValue.selector === "[name=save_view_button]" })
                expect(saveCreateSelector).toBeDefined();
                expect(saveViewSelector).toBeDefined();

                isValidStub.restore();
                fetchStub.restore();
            });
        });

        xit("should display an alert when duplicates are found", function() {
            var flag = false,
                restoreAndCallShowAlert = function(args) {
                    alertShowStub.restore();
                    layout.getComponent('quickcreate-alert').show(arguments);
                },
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    layout.collection.push(new Backbone.Model({
                        test: '123'
                    }));
                    options.success(layout.collection);
                }),
                alertShowStub = sinon.stub(layout.getComponent('quickcreate-alert'), 'show', function() {
                    flag = true;
                    restoreAndCallShowAlert(arguments);
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'show should have been called but timeout expired', 1000);

            runs(function() {
                expect(alertShowStub.calledOnce).toBeTruthy();
                expect(layout.$el.find('.alert').size()).toEqual(1);

                isValidStub.restore();
                fetchStub.restore();
            });
        });

        it("should highlight user key fields when duplicates are found", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    layout.collection.push(new Backbone.Model({
                        test: '123'
                    }));
                    options.success(layout.collection);
                }),
                quickcreateView = layout.getComponent('quickcreate'),
                triggerStub = sinon.stub(layout.context, 'trigger', function(eventKey) {
                    switch (eventKey) {
                        case 'quickcreate:validateModel':
                            quickcreateView.validateModel(arguments[1]);
                            break;
                        case 'quickcreate:highlightDuplicateFields':
                            quickcreateView.highlightDuplicateFields(arguments[1], function() {
                                flag = true;
                            });
                            break;
                        case 'quickcreate:save':
                            layout.save();
                            break;
                        default:
//                            console.log(eventKey + ' event trigger ignored');
                            break;
                    }
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'highlightDuplicateFields should have been called but timeout expired', 1000);

            runs(function() {
                expect(layout.getComponent('quickcreate').$el.find('.warning').size()).toEqual(2);

                isValidStub.restore();
                fetchStub.restore();
                triggerStub.restore();
            });
        });
    });

    describe('Ignore Duplicate and Save', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            delete layout;
        });

        it("should save data and not run duplicate check", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    flag = true;
                    layout.collection.push(new Backbone.Model({
                        test: '123'
                    }));
                    options.success(layout.collection);
                }),
                saveModelStub = sinon.stub(layout, 'saveModel', function(success) {
                    success();
                }),
                closeStub = sinon.stub(layout, 'close', function() {
                    flag = true;
                    return;
                });

            layout.render();

            runs(function() {
                expect(layout.skipDupCheck()).toBeFalsy();
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'fetch should have been called but timeout expired', 1000);

            runs(function() {
                flag = false;
                expect(layout.skipDupCheck()).toBeTruthy();
                layout.$el.find('[name=save_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'close should have been called but timeout expired', 1000);

            runs(function() {
                expect(isValidStub.calledTwice).toBeTruthy();
                expect(fetchStub.calledOnce).toBeTruthy();
                expect(saveModelStub.calledOnce).toBeTruthy();
                expect(closeStub.calledOnce).toBeTruthy();

                saveModelStub.restore();
                isValidStub.restore();
                fetchStub.restore();
                closeStub.restore();
            });
        });
    });

    describe('Save and Create Another', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            delete layout;
        });

        it("should save, clear out the form, but not close the modal", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    options.success(layout.collection);
                }),
                saveModelStub = sinon.stub(layout, 'saveModel', function(success) {
                    success();
                }),
                closeStub = sinon.stub(layout, 'close', function() {
                    return;
                }),
                clearStub = sinon.stub(layout.model, 'clear', function() {
                    flag = true;
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_create_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'clear should have been called but timeout expired', 1000);

            runs(function() {
                expect(saveModelStub.calledOnce).toBeTruthy();
                expect(closeStub.called).toBeFalsy();
                expect(clearStub.calledOnce).toBeTruthy();

                saveModelStub.restore();
                isValidStub.restore();
                fetchStub.restore();
                closeStub.restore();
                clearStub.restore();
            });
        });
    });

    describe('Save and View', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            delete layout;
        });

        it("should save, close the modal, and navigate to the detail view", function() {
            var flag = false,
                isValidStub = sinon.stub(layout.model, 'isValid', function() {
                    return true;
                }),
                fetchStub = sinon.stub(layout.collection, 'fetch', function(options) {
                    options.success(layout.collection);
                }),
                saveModelStub = sinon.stub(layout, 'saveModel', function(success) {
                    success();
                }),
                closeStub = sinon.stub(layout, 'close', function() {
                    return;
                }),
                navigateStub = sinon.stub(SugarTest.app, 'navigate', function() {
                    flag = true;
                });

            layout.render();

            runs(function() {
                layout.$el.find('[name=save_view_button]').click();
            });

            waitsFor(function() {
                return flag;
            }, 'navigate should have been called but timeout expired', 1000);

            runs(function() {
                expect(saveModelStub.calledOnce).toBeTruthy();
                expect(closeStub.calledOnce).toBeTruthy();
                expect(navigateStub.calledOnce).toBeTruthy();

                saveModelStub.restore();
                isValidStub.restore();
                fetchStub.restore();
                closeStub.restore();
                navigateStub.restore();
            });
        });
    });

});
