describe('Browser Notification', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    using('various browser scenarios', [
        {
            browser: 'chrome',
            supportsNotification: true,
            permission: 'granted',
            shouldShowNotification: true
        }, {
            browser: 'chrome',
            supportsNotification: true,
            permission: 'denied',
            shouldShowNotification: false
        }, {
            browser: 'firefox',
            supportsNotification: true,
            permission: 'granted',
            shouldShowNotification: true
        }, {
            browser: 'firefox',
            supportsNotification: true,
            permission: 'denied',
            shouldShowNotification: false
        }, {
            browser: 'safari',
            supportsNotification: true,
            permission: 'granted',
            shouldShowNotification: true
        }, {
            browser: 'safari',
            supportsNotification: true,
            permission: 'denied',
            shouldShowNotification: false
        }, {
            browser: 'ie',
            supportsNotification: false,
            permission: 'unsupported',
            shouldShowNotification: false
        }
    ], function(options) {
        it('should ' + (options.shouldShowNotification ? '' : 'not ') + 'show desktop notification in '
            + options.browser + ' when permission is ' + options.permission, function() {

            var origNotification = window.Notification,
                showUsingBrowserAlertStub;

            if (options.supportsNotification) {
                window.Notification = sinon.stub();
                window.Notification.requestPermission = function(callback) {
                    callback(options.permission);
                };
            } else {
                window.Notification = undefined;
            }

            showUsingBrowserAlertStub = sinon.stub(app.browserNotification, '_showUsingBrowserAlert');

            app.browserNotification.show('foo');

            if (window.Notification) {
                expect(window.Notification.calledOnce).toBe(options.shouldShowNotification);
            } else {
                expect(showUsingBrowserAlertStub.calledOnce).toBe(true);
            }

            window.Notification = origNotification;
            showUsingBrowserAlertStub.restore();
        });
    });
});
