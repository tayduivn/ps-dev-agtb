module.exports = function(config) {
    config.set({
        basePath: '../',
        frameworks: [
            'jasmine'
        ],
        plugins: [
            'karma-chrome-launcher',
            'karma-coverage',
            'karma-firefox-launcher',
            'karma-jasmine',
            'karma-junit-reporter',
            'karma-phantomjs-launcher'
        ],
        proxies: {
            '/clients': '/base/clients',
            '/fixtures': '/base/tests/fixtures',
            '/include': '/base/include',
            '/modules': '/base/modules'
        },
        reportSlowerThan: 500,
        browserDisconnectTimeout: 5000,
        browserDisconnectTolerance: 5
    });
};
