xdescribe('cache', function () {
    var app = SUGAR.App;
    beforeEach(function () {
    });

    afterEach(function () {
        //Reset the cache after every test
        app.cache.cutAll()
    });

    it('should store strings', function () {
        var value = "This is a test.";
        var key = "testKey";
        app.cache.set(key, value);
        expect(app.cache.get(key)).toEqual(value);
    });

    it('should store objects', function () {
        var value = {foo: "test", bar:{more:"a"}};
        var key = "testKey";
        app.cache.set(key, value);
        expect(app.cache.get(key)).toEqual(value);
    });

    it('should store functions', function () {
        var func = function(){return "Hello World"};
        var key = "testKey";
        app.cache.set(key, func);
        expect(app.cache.get(key)()).toEqual(func());
    });

    it('should store DOM elements', function () {
        var el = document.createElement("div");
        el.id = "testID";
        el.className = "Test";
        var key = "testKey";
        app.cache.set(key, el);
        //Ensure it is an element
        expect(app.cache.get(key) instanceof HTMLElement).toBeTruthy();
        //And it has all the expected properties
        expect(app.cache.get(key).id).toEqual(el.id);
        expect(app.cache.get(key).className).toEqual(el.className);

    });

    it('should append values', function () {
        var value = "Hello";
        var key = "testKey";
        app.cache.set(key, value);
        expect(app.cache.get(key)).toEqual(value);

        app.cache.add(key, " World");
        expect(app.cache.get(key)).toEqual("Hello World");
    });


    it('should remove values', function () {
        var value = "Hello";
        var key = "testKey";
        app.cache.set(key, value);
        expect(app.cache.get(key)).toEqual(value);

        app.cache.cut(key);
        expect(app.cache.get(key)).toBeUndefined();
    });


    it('should remove all values', function () {
        var value = "Hello";
        var key = "testKey";
        var key2 = "testKey2";
        app.cache.set(key, value);
        app.cache.set(key2, value);
        expect(app.cache.get(key)).toEqual(value);
        expect(app.cache.get(key2)).toEqual(value);

        app.cache.cutAll();
        expect(app.cache.get(key)).toBeUndefined();
        expect(app.cache.get(key2)).toBeUndefined();
    });


});