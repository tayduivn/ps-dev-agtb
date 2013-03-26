(function(){

var underscoreDelayFunctions = ["throttle", "debounce"];

beforeEach(function(){
    if (!(SugarTest.clock && SugarTest.clock.restore))
    {
        SugarTest.clock = sinon.useFakeTimers();
    }

    //Mock throttle and debounce to prevent the need to actually wait.
    //(underscore throttle uses dates to enforce waits outside of the normal setTimeout function
    _.each(underscoreDelayFunctions, function(func){
        if (!_[func].restore)
        {
            sinon.stub(_, func, function(f, t) {
                return function(){
                    var self = this,
                        args = arguments;
                    f.apply(self, args);
                };
            });
        }
    });
});

afterEach(function() {
    SugarTest.clock.restore();
    _.each(underscoreDelayFunctions, function(func){
        if(_[func].restore)
            _[func].restore();
    });
});

})();