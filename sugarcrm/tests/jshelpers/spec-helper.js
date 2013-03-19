
beforeEach(function(){
    if (!(SugarTest.clock && SugarTest.clock.restore))
    {
        SugarTest.clock = sinon.useFakeTimers();
    }

    //Mock throttle to prevent the need to actually wait.
    //(underscore throttle uses dates to enforce waits outside of the normal setTimeout function
    if (!_.throttle.restore)
    {
        sinon.stub(_, "throttle", function(f, t) {
            return function(){
                var self = this,
                    args = arguments;
                f.apply(self, args);
            };
        });
    }

});

afterEach(function() {
    SugarTest.clock.restore();
    _.throttle.restore();
});
