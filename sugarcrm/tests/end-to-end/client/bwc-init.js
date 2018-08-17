/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
'use strict';

(() => {
    window.seedbed = {};

    // check if seedbed already initialized
    if (window.__e2e) {
        return;
    }

    // The following object can be used to store various data not accessible via
    // GUI.
    // Such data is accessed by tests to do verification, clean-up, etc.
    const e2e = {
        initialized: false,

        // Files created during a test run
        files: [],

        // Records created during a test run
        create: [],

        // Records updated during a test run
        update: [],

        // Track all requests made by the app during a test run
        requests: [],

        // Faked requests
        fakedRequests: [],

        timeouts: {
            ids: {},
            counter: 0,
        },

        routerCounter: 0,

        // Unhandled js exceptions
        errors: [],

        // Animation info
        animationCounter: 0,

        // Active requests info
        requestsInfo: {
            activeReqCount: 0,
            activeOfflineRequests: 0,
        },

        // Layout info
        layoutInfo: {
            renderInfo: [],
        },

        // indicates if the language has been changed
        languageChanged: false,

        // e2e client exception
        e2eException: null,
    };

    window.__e2e = e2e;

    if (window.$) {
        $(window).on('error', err => {
            e2e.errors.push(`${err.message}, ${err.filename}, ${err.lineno}`);
        });

        $(document).on('ajaxSend', () => {
            e2e.requestsInfo.activeReqCount++;
        });

        $(document).on('ajaxComplete', () => {
            if (e2e.requestsInfo.activeReqCount > 0) {
                e2e.requestsInfo.activeReqCount--;
            }
        });
    }

    YAHOO.util.Connect.startEvent.subscribe(() => {
        e2e.requestsInfo.activeReqCount++;
    });

    YAHOO.util.Connect.completeEvent.subscribe(() => {
        if (e2e.requestsInfo.activeReqCount > 0) {
            e2e.requestsInfo.activeReqCount--;
        }
    });

    $(() => {
        e2e.initialized = true;

        // Track css animation
        const originAnimate = $.fn.anim;

        const animationStart = function(duration) {
            e2e.animationCounter++;

            window.setTimeout(() => {
                if (e2e.animationCounter > 0) {
                    e2e.animationCounter--;
                }
                // eslint-disable-next-line no-magic-numbers
            }, duration * 1000 + 25);
        };

        $.fn.anim = function anim(properties, duration, ease, callback, delay) {
            /* eslint-disable max-len */
            // uncomment following 2 lines for slow animation with logging:
            // duration = 4;
            // console.log($(this).get(0).className + ' ' + $(this).get(0).id + ' ' + duration);
            /* eslint-enable max-len */

            const context = originAnimate.call(
                this,
                properties,
                duration,
                ease,
                callback,
                delay
            );

            // doesn't count marker animation in waitForApp
            if (_.isEmpty(this) || $(this).get(0).id === 'marker') {
                return context;
            }

            animationStart(
                duration,
                `${$(this).get(0).className} ${$(this).get(0).id}`
            );

            return context;
        };

        const originToggleClass = $.fn.toggleClass;

        $.fn.toggleClass = function toggleClass(...args) {
            const [name] = args;
            const context = originToggleClass.apply(this, args);
            let duration =
                $(this).css('-webkit-transition-duration') ||
                $(this).css('transition-duration');

            if (!_.isEmpty(duration) && parseFloat(duration) > 0) {
                duration = parseFloat(duration);

                animationStart(duration, name);
            }

            return context;
        };

        const originAddClass = $.fn.addClass;

        $.fn.addClass = function addClass(...args) {
            const [name] = args;
            const context = originAddClass.apply(this, args);
            let duration =
                $(this).css('-webkit-transition-duration') ||
                $(this).css('transition-duration');

            if (!_.isEmpty(duration) && parseFloat(duration) > 0) {
                duration = parseFloat(duration);

                animationStart(duration, name);
            }

            return context;
        };

        const originRemoveClass = $.fn.removeClass;

        $.fn.removeClass = function removeClass(...args) {
            const [name] = args;
            const context = originRemoveClass.apply(this, args);
            let duration =
                $(this).css('-webkit-transition-duration') ||
                $(this).css('transition-duration');

            if (!_.isEmpty(duration) && parseFloat(duration) > 0) {
                duration = parseFloat(duration);

                animationStart(duration, name);
            }

            return context;
        };

        const _originalSetTimeout = window.setTimeout;

        window.setTimeout = function setTimeout(callback, timeoutInMillis) {
            const maxTimeout = 200;

            let id;

            if (timeoutInMillis < maxTimeout) {
                e2e.timeouts.counter++;

                if (callback) {
                    const originalCallback = callback;

                    // eslint-disable-next-line no-param-reassign
                    callback = function() {
                        // eslint-disable-next-line no-invalid-this
                        originalCallback.apply(this);

                        if (e2e.timeouts.counter > 0) {
                            e2e.timeouts.counter--;
                            delete e2e.timeouts.ids[id];
                        }
                    };
                }
            }

            id = _originalSetTimeout.apply(this, [callback, timeoutInMillis]);

            if (timeoutInMillis < maxTimeout) {
                e2e.timeouts.ids[id] = timeoutInMillis;
            }

            return id;
        };

        const _originalClearTimeout = window.clearTimeout;

        window.clearTimeout = function clearTimeout(...args) {
            const [id] = args;

            if (e2e.timeouts.ids[id] && e2e.timeouts.counter > 0) {
                e2e.timeouts.counter--;
                delete e2e.timeouts.ids[id];
            }

            _originalClearTimeout.apply(this, args);
        };

        // Set field attribute values.
        // Seedbed uses field attributes to create field instances.
        $('input:not([type="hidden"])[name]').each((id, field) => {
            const $field = $(field);

            $field.attr('field-name', $field.attr('name'));
            $field.attr('field-type', $field.attr('type'));
        });
    });
})();
