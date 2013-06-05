describe('favorite field', function() {

    var app;
    var model;
    var field;

    var moduleName;
    var metadata;

    beforeEach(function() {

        moduleName = 'Accounts';
        metadata = {
            fields: {
                name: {
                    name: "name",
                    vname: "LBL_NAME",
                    type: "varchar",
                    len: 255,
                    comment: "Name of this bean"
                }
            },
            favoritesEnabled: true,
            views: [],
            layouts: [],
            _hash: "bc6fc50d9d0d3064f5d522d9e15968fa"
        };

        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addModule(moduleName, metadata);
        SugarTest.testMetadata.set();
        app.data.declareModel(moduleName, metadata);

        model = app.data.createBean(moduleName, {
            id:'123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });

        field = SugarTest.createField('base', 'toggle_favorite', 'favorite', 'detail');
        field.model = model;

    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        model = null;
        field = null;

        moduleName = null;
        metadata = null;
    });

    it("should not render and log error if the module has no favorites enabled", function() {

        var error = sinon.spy(app.logger, 'error');

        var loadTemplate = sinon.stub(field, '_loadTemplate', function() {
            this.template = function() {
                return '<i class="icon-favorite"></i>';
            };
        });

        metadata.favoritesEnabled = false;
        app.data.declareModel(moduleName, metadata);

        field.model = model;
        field.render();
        expect(loadTemplate.called).toBeFalsy();
        expect(error.calledOnce).toBeTruthy();

        error.restore();
        loadTemplate.restore();
    });

    it("should not render doesnt not have id", function() {

        var loadTemplate = sinon.stub(field, '_loadTemplate', function() {
            this.template = function() {
                return '<i class="icon-favorite"></i>';
            };
        });

        app.data.declareModel(moduleName, metadata);
        delete model.attributes.id;
        field.model = model;
        field.render();
        expect(loadTemplate.called).toBeFalsy();

        loadTemplate.restore();
    });

    describe("toggle favorite", function() {
        var templateFavoriteIsActive   = '<i class="icon-favorite active"></i>',
            templateFavoriteIsInactive = '<i class="icon-favorite"></i>',
            loadTemplateStub,
            isFavStub,
            favStub;

        beforeEach(function() {
            isFavStub = sinon.stub(field.model, 'isFavorite', function() {
                return this.fav;
            });

            favStub = sinon.stub(field.model, 'favorite', function() {
                this.fav = !this.fav;
                return true;
            });
        });

        afterEach(function() {
            loadTemplateStub.restore();
            favStub.restore();
            isFavStub.restore();
        });

        it('should favorite an unfavorite record', function() {
            loadTemplateStub = sinon.stub(field, '_loadTemplate', function() {
                this.template = function() {
                    return templateFavoriteIsInactive;
                };
            });

            model.fav   = false;
            field.model = model;
            field.render();

            field.$('.icon-favorite').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(field.$('.icon-favorite').hasClass('active')).toBeTruthy();
        });

        it('should unfavorite a favorite record', function() {
            loadTemplateStub = sinon.stub(field, '_loadTemplate', function() {
                this.template = function() {
                    return templateFavoriteIsActive;
                };
            });

            model.fav   = true;
            field.model = model;
            field.render();

            field.$('.icon-favorite').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(field.$('.icon-favorite').hasClass('active')).toBeFalsy();
        });

        it('should log error if unable to favorite or unfavorite record', function() {
            var errorSpy = sinon.spy(app.logger, 'error');

            loadTemplateStub = sinon.stub(field, '_loadTemplate', function() {
                this.template = function() {
                    return templateFavoriteIsInactive;
                };
            });

            isFavStub.restore();
            isFavStub = sinon.stub(field.model, 'isFavorite', function() {
                return false;
            });

            favStub.restore();
            favStub = sinon.stub(field.model, 'favorite', function() {
                return false;
            });

            field.model = model;
            field.render();

            field.$('.icon-favorite').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(errorSpy.calledOnce);

            errorSpy.restore();
        });

        describe("trigger 'favorite:active' on context", function() {
            var triggerSpy;

            beforeEach(function() {
                triggerSpy = sinon.spy(field.context, "trigger");
            });

            afterEach(function() {
                triggerSpy.restore();
            });

            it("Should trigger the favorite:active event on the context when favorite an unfavorite record.", function() {
                loadTemplateStub = sinon.stub(field, '_loadTemplate', function() {
                    this.template = function() {
                        return templateFavoriteIsInactive;
                    };
                });

                model.fav   = false;
                field.model = model;
                field.render();

                field.$(".icon-favorite").trigger("click");
                expect(triggerSpy.calledWithExactly("favorite:active")).toBeTruthy();
            });

            it("Should not trigger the favorite:active event on the context when unfavorite a favorite record.", function() {
                loadTemplateStub = sinon.stub(field, '_loadTemplate', function() {
                    this.template = function() {
                        return templateFavoriteIsActive;
                    };
                });

                model.fav   = true;
                field.model = model;
                field.render();

                field.$(".icon-favorite").trigger("click");
                expect(triggerSpy.neverCalledWith("favorite:active")).toBeTruthy();
            });
        });
    });

    it('should format accordingly with favorite status on bean', function() {

        field.model = model;
        var isFavStub = sinon.stub(field.model, 'isFavorite', function() {
            return this.fav;
        });

        field.model.fav = false;
        expect(field.format()).toBeFalsy();
        expect(isFavStub.calledOnce);

        field.model.fav = true;
        expect(field.format()).toBeTruthy();
        expect(isFavStub.calledOnce);

        isFavStub.restore();
    });
});
