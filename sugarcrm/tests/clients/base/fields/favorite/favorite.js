describe('favorite field', function() {

    describe('view', function() {
        var app;
        var model;
        var field;
        var dm;

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
            }

            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addModuleDefinition(moduleName, metadata);
            SugarTest.testMetadata.set();

            app = SugarTest.app;
            dm = app.data;

            dm.declareModel(moduleName, metadata);
            model = dm.createBean(moduleName, {
                name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
            });

            app.view.Field.prototype._renderHtml = function() {
            };

            field = SugarTest.createField('base', 'toggle_favorite', 'favorite', 'detail');
            field.model = model;

        });

        afterEach(function() {
            SugarTest.testMetadata.dispose();
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
            SugarTest.testMetadata.addModuleDefinition(moduleName, metadata);
            SugarTest.testMetadata.set();

            field.model = model;
            field.render();
            field._renderHtml();
            expect(loadTemplate.called).toBeFalsy();
            expect(error.calledOnce).toBeTruthy();

            error.restore();
            loadTemplate.restore();
        });

        it('should favorite an unfavorite record', function() {

            sinon.stub(field, '_loadTemplate', function() {
                this.template = function() {
                    return '<i class="icon-favorite"></i>';
                };
            });

            model.fav = false;
            var isFavStub = sinon.stub(field.model, 'isFavorite', function() {
                return this.fav;
            });
            var favStub = sinon.stub(field.model, 'favorite', function() {
                this.fav = !this.fav;
                return true;
            });

            field.model = model;
            field.render();
            field._renderHtml();

            field.$('.icon-favorite').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(field.$('.icon-favorite').hasClass('active')).toBeTruthy();

            field._loadTemplate.restore();
            favStub.restore();
            isFavStub.restore();
        });

        it('should unfavorite a favorite record', function() {

            sinon.stub(field, '_loadTemplate', function() {
                this.template = function() {
                    return '<i class="icon-favorite active"></i>';
                };
            });

            model.fav = true;
            var isFavStub = sinon.stub(field.model, 'isFavorite', function() {
                return this.fav;
            });
            var favStub = sinon.stub(field.model, 'favorite', function() {
                this.fav = !this.fav;
                return true;
            });

            field.model = model;
            field.render();
            field._renderHtml();

            field.$('.icon-favorite').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(field.$('.icon-favorite').hasClass('active')).toBeFalsy();

            field._loadTemplate.restore();
            favStub.restore();
            isFavStub.restore();
        });

        it('should log error if unable to favorite or unfavorite record', function() {

            sinon.stub(field, '_loadTemplate', function() {
                this.template = function() {
                    return '<i class="icon-favorite"></i>';
                };
            });

            var isFavStub = sinon.stub(field.model, 'isFavorite', function() {
                return false;
            });
            var favStub = sinon.stub(field.model, 'favorite', function() {
                return false;
            });
            var error = sinon.spy(app.logger, 'error');

            field.model = model;
            field.render();
            field._renderHtml();

            field.$('.icon-favorite').trigger('click');
            expect(favStub.calledOnce);
            expect(isFavStub.calledOnce);
            expect(error.calledOnce);

            field._loadTemplate.restore();
            favStub.restore();
            isFavStub.restore();
            error.restore();
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
});
