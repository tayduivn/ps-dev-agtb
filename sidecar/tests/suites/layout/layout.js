describe("Layout", function() {

    it('creates views', function () {
        expect(SUGAR.App.layout.get({
            view : "EditView",
            module: "Contacts"
        })).not.toBe(null);
    });

    it('creates layouts', function () {
        expect(SUGAR.App.layout.get({
            layout : "edit",
            module: "Contacts"
        })).not.toBe(null);
    });

    it('creates views', function () {
            expect(SUGAR.App.layout.get({
                view : "EditView",
                module: "Contacts"
            })).not.toBe(null);
        });
});

describe("Layout.View", function(){
    var syncResult, layout, html;
    //Fake the cache
    SUGAR.App.cache = SUGAR.App.cache || {
        get:function (key) {
            var parts = key.split(".");
            if (parts.length == 1){
                return fixtures[parts[0]]
            }

            return fixtures[parts[0]][parts[1]];
        }
    };
    //Fake template manager
    SUGAR.App.template = SUGAR.App.template || {
        get:function (key) {
            return Handlebars.compile(fixtures.templates[key]);
        }
    };
    //Fake a field list
    SUGAR.App.sugarFieldsSync = function (that, callback){
        var ajaxResponse = sugarFieldsFixtures;
        var result= callback(that, ajaxResponse);
        return result;
    };
    syncResult= SUGAR.App.sugarFieldManager.getInstance().syncFields();

    var App = SUGAR.App.init({el: "#sidecar"});
    //Need a sample Bean
    this.bean = new App.Bean({
        first_name: "Foo",
        last_name: "Bar"
    });
    this.collection = new App.BeanCollection([this.bean]);
    //Setup a context
    App.context = {
        module:"Contacts",
        model: this.bean,
        collection : this.collection
    };


    it('renders edit views', function(){
        layout = App.layout.get({
            context : App.context,
            view:"EditView"
        });
        layout.render();
        html = layout.$el.html();
        expect(html).toContain('EditView');
        expect(layout.$el).toContain('input=[value="Foo"]');
    })

    it('renders detail views', function(){
        layout = App.layout.get({
            context : App.context,
            view:"DetailView"
        });
        layout.render();
        html = layout.$el.html();
        expect(html).toContain('DetailView');
    })

})