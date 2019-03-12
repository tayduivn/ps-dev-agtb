(function webpackUniversalModuleDefinition(root, factory) {
    if (typeof exports === 'object' && typeof module === 'object')
        module.exports = factory();
    else if (typeof define === 'function' && define.amd)
        define("singleSpa", [], factory);
    else if (typeof exports === 'object')
        exports["singleSpa"] = factory();
    else
        root["singleSpa"] = factory();
})(window, function() {
    return /******/ (function(modules) { // webpackBootstrap
        /******/ 	// The module cache
        /******/
        var installedModules = {};
        /******/
        /******/ 	// The require function
        /******/
        function __webpack_require__(moduleId) {
            /******/
            /******/ 		// Check if module is in cache
            /******/
            if (installedModules[moduleId]) {
                /******/
                return installedModules[moduleId].exports;
                /******/
            }
            /******/ 		// Create a new module (and put it into the cache)
            /******/
            var module = installedModules[moduleId] = {
                /******/            i: moduleId,
                /******/            l: false,
                /******/            exports: {}
                /******/
            };
            /******/
            /******/ 		// Execute the module function
            /******/
            modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
            /******/
            /******/ 		// Flag the module as loaded
            /******/
            module.l = true;
            /******/
            /******/ 		// Return the exports of the module
            /******/
            return module.exports;
            /******/
        }

        /******/
        /******/
        /******/ 	// expose the modules object (__webpack_modules__)
        /******/
        __webpack_require__.m = modules;
        /******/
        /******/ 	// expose the module cache
        /******/
        __webpack_require__.c = installedModules;
        /******/
        /******/ 	// define getter function for harmony exports
        /******/
        __webpack_require__.d = function(exports, name, getter) {
            /******/
            if (!__webpack_require__.o(exports, name)) {
                /******/
                Object.defineProperty(exports, name, {
                    /******/                configurable: false,
                    /******/                enumerable: true,
                    /******/                get: getter
                    /******/
                });
                /******/
            }
            /******/
        };
        /******/
        /******/ 	// define __esModule on exports
        /******/
        __webpack_require__.r = function(exports) {
            /******/
            Object.defineProperty(exports, '__esModule', {value: true});
            /******/
        };
        /******/
        /******/ 	// getDefaultExport function for compatibility with non-harmony modules
        /******/
        __webpack_require__.n = function(module) {
            /******/
            var getter = module && module.__esModule ?
                /******/            function getDefault() {
                    return module['default'];
                } :
                /******/            function getModuleExports() {
                    return module;
                };
            /******/
            __webpack_require__.d(getter, 'a', getter);
            /******/
            return getter;
            /******/
        };
        /******/
        /******/ 	// Object.prototype.hasOwnProperty.call
        /******/
        __webpack_require__.o = function(object, property) {
            return Object.prototype.hasOwnProperty.call(object, property);
        };
        /******/
        /******/ 	// __webpack_public_path__
        /******/
        __webpack_require__.p = "";
        /******/
        /******/
        /******/ 	// Load entry module and return exports
        /******/
        return __webpack_require__(__webpack_require__.s = 0);
        /******/
    })
    /************************************************************************/
    /******/({

        /***/ "./node_modules/custom-event/index.js":
        /*!********************************************!*\
          !*** ./node_modules/custom-event/index.js ***!
          \********************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            /* WEBPACK VAR INJECTION */
            (function(global) {
                var NativeCustomEvent = global.CustomEvent;

                function useNative() {
                    try {
                        var p = new NativeCustomEvent('cat', {detail: {foo: 'bar'}});
                        return 'cat' === p.type && 'bar' === p.detail.foo;
                    } catch (e) {
                    }
                    return false;
                }

                /**
                 * Cross-browser `CustomEvent` constructor.
                 *
                 * https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent.CustomEvent
                 *
                 * @public
                 */

                module.exports = useNative() ? NativeCustomEvent :

                    // IE >= 9
                    'undefined' !== typeof document && 'function' === typeof document.createEvent ?
                        function CustomEvent(type, params) {
                            var e = document.createEvent('CustomEvent');
                            if (params) {
                                e.initCustomEvent(type, params.bubbles, params.cancelable, params.detail);
                            } else {
                                e.initCustomEvent(type, false, false, void 0);
                            }
                            return e;
                        } :

                        // IE <= 8
                        function CustomEvent(type, params) {
                            var e = document.createEventObject();
                            e.type = type;
                            if (params) {
                                e.bubbles = Boolean(params.bubbles);
                                e.cancelable = Boolean(params.cancelable);
                                e.detail = params.detail;
                            } else {
                                e.bubbles = false;
                                e.cancelable = false;
                                e.detail = void 0;
                            }
                            return e;
                        }

                /* WEBPACK VAR INJECTION */
            }.call(this,
                __webpack_require__(/*! ./../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

            /***/
        }),

        /***/ "./node_modules/webpack/buildin/global.js":
        /*!***********************************!*\
          !*** (webpack)/buildin/global.js ***!
          \***********************************/
        /*! no static exports found */
        /***/ (function(module, exports) {

            var g;

// This works in non-strict mode
            g = (function() {
                return this;
            })();

            try {
                // This works if eval is allowed (see CSP)
                g = g || Function("return this")() || (1, eval)("this");
            } catch (e) {
                // This works if the window reference is available
                if (typeof window === "object") g = window;
            }

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

            module.exports = g;

            /***/
        }),

        /***/ "./src/applications/app-errors.js":
        /*!****************************************!*\
          !*** ./src/applications/app-errors.js ***!
          \****************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.handleAppError = handleAppError;
            exports.addErrorHandler = addErrorHandler;
            exports.removeErrorHandler = removeErrorHandler;
            exports.transformErr = transformErr;

            var _customEvent = _interopRequireDefault(
                __webpack_require__(/*! custom-event */ "./node_modules/custom-event/index.js"));

            function _interopRequireDefault(obj) {
                return obj && obj.__esModule ? obj : {default: obj};
            }

            var errorHandlers = [];

            function handleAppError(err, app) {
                var transformedErr = transformErr(err, app);

                if (errorHandlers.length) {
                    errorHandlers.forEach(function(handler) {
                        return handler(transformedErr);
                    });
                } else {
                    setTimeout(function() {
                        throw transformedErr;
                    });
                }
            }

            function addErrorHandler(handler) {
                if (typeof handler !== 'function') {
                    throw new Error('a single-spa error handler must be a function');
                }

                errorHandlers.push(handler);
            }

            function removeErrorHandler(handler) {
                if (typeof handler !== 'function') {
                    throw new Error('a single-spa error handler must be a function');
                }

                var removedSomething = false;
                errorHandlers = errorHandlers.filter(function(h) {
                    var isHandler = h === handler;
                    removedSomething = removedSomething || isHandler;
                    return !isHandler;
                });
                return removedSomething;
            }

            function transformErr(ogErr, appOrParcel) {
                var objectType = appOrParcel.unmountThisParcel ? 'Parcel' : 'Application';
                var errPrefix = "".concat(objectType, " '")
                    .concat(appOrParcel.name, "' died in status ")
                    .concat(appOrParcel.status, ": ");
                var result;

                if (ogErr instanceof Error) {
                    try {
                        ogErr.message = errPrefix + ogErr.message;
                    } catch (err) {
                        /* Some errors have read-only message properties, in which case there is nothing
                         * that we can do.
                         */
                    }

                    result = ogErr;
                } else {
                    console.warn("While ".concat(appOrParcel.status, ", '")
                        .concat(appOrParcel.name,
                            "' rejected its lifecycle function promise with a non-Error. This will cause stack traces to not be accurate."));

                    try {
                        result = new Error(errPrefix + JSON.stringify(ogErr));
                    } catch (err) {
                        // If it's not an Error and you can't stringify it, then what else can you even do to it?
                        result = ogErr;
                    }
                }

                result.appName = appOrParcel.name;
                result.name = appOrParcel.name;
                return result;
            }

            /***/
        }),

        /***/ "./src/applications/app.helpers.js":
        /*!*****************************************!*\
          !*** ./src/applications/app.helpers.js ***!
          \*****************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.isActive = isActive;
            exports.isntActive = isntActive;
            exports.isLoaded = isLoaded;
            exports.isntLoaded = isntLoaded;
            exports.shouldBeActive = shouldBeActive;
            exports.shouldntBeActive = shouldntBeActive;
            exports.notBootstrapped = notBootstrapped;
            exports.notSkipped = notSkipped;
            exports.toName = toName;
            exports.SKIP_BECAUSE_BROKEN = exports.UNLOADING = exports.UNMOUNTING = exports.UPDATING = exports.MOUNTED = exports.MOUNTING = exports.NOT_MOUNTED = exports.BOOTSTRAPPING = exports.NOT_BOOTSTRAPPED = exports.LOADING_SOURCE_CODE = exports.NOT_LOADED = void 0;

            var _appErrors = __webpack_require__(/*! ./app-errors.js */ "./src/applications/app-errors.js");

            var singleSpa = _interopRequireWildcard(
                __webpack_require__(/*! src/single-spa.js */ "./src/single-spa.js"));

            function _interopRequireWildcard(obj) {
                if (obj && obj.__esModule) {
                    return obj;
                } else {
                    var newObj = {};
                    if (obj != null) {
                        for (var key in obj) {
                            if (Object.prototype.hasOwnProperty.call(obj, key)) {
                                var desc = Object.defineProperty && Object.getOwnPropertyDescriptor ?
                                    Object.getOwnPropertyDescriptor(obj, key) :
                                    {};
                                if (desc.get || desc.set) {
                                    Object.defineProperty(newObj, key, desc);
                                } else {
                                    newObj[key] = obj[key];
                                }
                            }
                        }
                    }
                    newObj.default = obj;
                    return newObj;
                }
            }

// App statuses
            var NOT_LOADED = 'NOT_LOADED';
            exports.NOT_LOADED = NOT_LOADED;
            var LOADING_SOURCE_CODE = 'LOADING_SOURCE_CODE';
            exports.LOADING_SOURCE_CODE = LOADING_SOURCE_CODE;
            var NOT_BOOTSTRAPPED = 'NOT_BOOTSTRAPPED';
            exports.NOT_BOOTSTRAPPED = NOT_BOOTSTRAPPED;
            var BOOTSTRAPPING = 'BOOTSTRAPPING';
            exports.BOOTSTRAPPING = BOOTSTRAPPING;
            var NOT_MOUNTED = 'NOT_MOUNTED';
            exports.NOT_MOUNTED = NOT_MOUNTED;
            var MOUNTING = 'MOUNTING';
            exports.MOUNTING = MOUNTING;
            var MOUNTED = 'MOUNTED';
            exports.MOUNTED = MOUNTED;
            var UPDATING = 'UPDATING';
            exports.UPDATING = UPDATING;
            var UNMOUNTING = 'UNMOUNTING';
            exports.UNMOUNTING = UNMOUNTING;
            var UNLOADING = 'UNLOADING';
            exports.UNLOADING = UNLOADING;
            var SKIP_BECAUSE_BROKEN = 'SKIP_BECAUSE_BROKEN';
            exports.SKIP_BECAUSE_BROKEN = SKIP_BECAUSE_BROKEN;

            function isActive(app) {
                return app.status === MOUNTED;
            }

            function isntActive(app) {
                return !isActive(app);
            }

            function isLoaded(app) {
                return app.status !== NOT_LOADED && app.status !== LOADING_SOURCE_CODE;
            }

            function isntLoaded(app) {
                return !isLoaded(app);
            }

            function shouldBeActive(app) {
                try {
                    return app.activeWhen(window.location);
                } catch (err) {
                    (0, _appErrors.handleAppError)(err, app);
                    app.status = SKIP_BECAUSE_BROKEN;
                }
            }

            function shouldntBeActive(app) {
                try {
                    return !app.activeWhen(window.location);
                } catch (err) {
                    (0, _appErrors.handleAppError)(err, app);
                    app.status = SKIP_BECAUSE_BROKEN;
                }
            }

            function notBootstrapped(app) {
                return app.status !== NOT_BOOTSTRAPPED;
            }

            function notSkipped(item) {
                return item !== SKIP_BECAUSE_BROKEN && (!item || item.status !== SKIP_BECAUSE_BROKEN);
            }

            function toName(app) {
                return app.name;
            }

            /***/
        }),

        /***/ "./src/applications/apps.js":
        /*!**********************************!*\
          !*** ./src/applications/apps.js ***!
          \**********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.getMountedApps = getMountedApps;
            exports.getAppNames = getAppNames;
            exports.getAppStatus = getAppStatus;
            exports.declareChildApplication = declareChildApplication;
            exports.registerApplication = registerApplication;
            exports.checkActivityFunctions = checkActivityFunctions;
            exports.getAppsToLoad = getAppsToLoad;
            exports.getAppsToUnmount = getAppsToUnmount;
            exports.getAppsToMount = getAppsToMount;
            exports.unloadChildApplication = unloadChildApplication;
            exports.unloadApplication = unloadApplication;

            var _jquerySupport = __webpack_require__(/*! ../jquery-support.js */ "./src/jquery-support.js");

            var _appHelpers = __webpack_require__(/*! ./app.helpers.js */ "./src/applications/app.helpers.js");

            var _reroute = __webpack_require__(/*! src/navigation/reroute.js */ "./src/navigation/reroute.js");

            var _find = __webpack_require__(/*! src/utils/find.js */ "./src/utils/find.js");

            var _unmount = __webpack_require__(/*! src/lifecycles/unmount.js */ "./src/lifecycles/unmount.js");

            var _unload = __webpack_require__(/*! src/lifecycles/unload.js */ "./src/lifecycles/unload.js");

            function _typeof(obj) {
                if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
                    _typeof = function _typeof(obj) {
                        return typeof obj;
                    };
                } else {
                    _typeof = function _typeof(obj) {
                        return obj && typeof Symbol === "function" && obj.constructor === Symbol &&
                        obj !== Symbol.prototype ? "symbol" : typeof obj;
                    };
                }
                return _typeof(obj);
            }

            var apps = [];

            function getMountedApps() {
                return apps.filter(_appHelpers.isActive).map(_appHelpers.toName);
            }

            function getAppNames() {
                return apps.map(_appHelpers.toName);
            }

            function getAppStatus(appName) {
                var app = (0, _find.find)(apps, function(app) {
                    return app.name === appName;
                });
                return app ? app.status : null;
            }

            function declareChildApplication(appName, arg1, arg2) {
                console.warn(
                    'declareChildApplication is deprecated and will be removed in the next major version, use "registerApplication" instead');
                return registerApplication(appName, arg1, arg2);
            }

            function registerApplication(appName, applicationOrLoadingFn, activityFn) {
                var customProps = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
                if (typeof appName !== 'string' || appName.length === 0) throw new Error(
                    "The first argument must be a non-empty string 'appName'");
                if (getAppNames().indexOf(appName) !== -1) throw new Error(
                    "There is already an app declared with name ".concat(appName));
                if (_typeof(customProps) !== 'object' || Array.isArray(customProps)) throw new Error(
                    'customProps must be an object');
                if (!applicationOrLoadingFn) throw new Error("The application or loading function is required");
                var loadImpl;

                if (typeof applicationOrLoadingFn !== 'function') {
                    // applicationOrLoadingFn is an application
                    loadImpl = function loadImpl() {
                        return Promise.resolve(applicationOrLoadingFn);
                    };
                } else {
                    // applicationOrLoadingFn is a loadingFn
                    loadImpl = applicationOrLoadingFn;
                }

                if (typeof activityFn !== 'function') throw new Error("The activeWhen argument must be a function");
                apps.push({
                    name: appName,
                    loadImpl: loadImpl,
                    activeWhen: activityFn,
                    status: _appHelpers.NOT_LOADED,
                    parcels: {},
                    customProps: customProps
                });
                (0, _jquerySupport.ensureJQuerySupport)();
                (0, _reroute.reroute)();
            }

            function checkActivityFunctions(location) {
                var activeApps = [];

                for (var i = 0; i < apps.length; i++) {
                    if (apps[i].activeWhen(location)) {
                        activeApps.push(apps[i].name);
                    }
                }

                return activeApps;
            }

            function getAppsToLoad() {
                return apps.filter(_appHelpers.notSkipped)
                    .filter(_appHelpers.isntLoaded)
                    .filter(_appHelpers.shouldBeActive);
            }

            function getAppsToUnmount() {
                return apps.filter(_appHelpers.notSkipped)
                    .filter(_appHelpers.isActive)
                    .filter(_appHelpers.shouldntBeActive);
            }

            function getAppsToMount() {
                return apps.filter(_appHelpers.notSkipped)
                    .filter(_appHelpers.isntActive)
                    .filter(_appHelpers.isLoaded)
                    .filter(_appHelpers.shouldBeActive);
            }

            function unloadChildApplication(appName, opts) {
                console.warn(
                    'unloadChildApplication is deprecated and will be removed in the next major version, use "unloadApplication" instead');
                return unloadApplication(appName, opts);
            }

            function unloadApplication(appName) {
                var opts = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
                    waitForUnmount: false
                };

                if (typeof appName !== 'string') {
                    throw new Error("unloadApplication requires a string 'appName'");
                }

                var app = (0, _find.find)(apps, function(App) {
                    return App.name === appName;
                });

                if (!app) {
                    throw new Error("Could not unload application '".concat(appName,
                        "' because no such application has been declared"));
                }

                var appUnloadInfo = (0, _unload.getAppUnloadInfo)(app.name);

                if (opts && opts.waitForUnmount) {
                    // We need to wait for unmount before unloading the app
                    if (appUnloadInfo) {
                        // Someone else is already waiting for this, too
                        return appUnloadInfo.promise;
                    } else {
                        // We're the first ones wanting the app to be resolved.
                        var promise = new Promise(function(resolve, reject) {
                            (0, _unload.addAppToUnload)(app, function() {
                                return promise;
                            }, resolve, reject);
                        });
                        return promise;
                    }
                } else {
                    /* We should unmount the app, unload it, and remount it immediately.
                     */
                    var resultPromise;

                    if (appUnloadInfo) {
                        // Someone else is already waiting for this app to unload
                        resultPromise = appUnloadInfo.promise;
                        immediatelyUnloadApp(app, appUnloadInfo.resolve, appUnloadInfo.reject);
                    } else {
                        // We're the first ones wanting the app to be resolved.
                        resultPromise = new Promise(function(resolve, reject) {
                            (0, _unload.addAppToUnload)(app, function() {
                                return resultPromise;
                            }, resolve, reject);
                            immediatelyUnloadApp(app, resolve, reject);
                        });
                    }

                    return resultPromise;
                }
            }

            function immediatelyUnloadApp(app, resolve, reject) {
                (0, _unmount.toUnmountPromise)(app).then(_unload.toUnloadPromise).then(function() {
                    resolve();
                    setTimeout(function() {
                        // reroute, but the unload promise is done
                        (0, _reroute.reroute)();
                    });
                }).catch(reject);
            }

            /***/
        }),

        /***/ "./src/applications/timeouts.js":
        /*!**************************************!*\
          !*** ./src/applications/timeouts.js ***!
          \**************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.setBootstrapMaxTime = setBootstrapMaxTime;
            exports.setMountMaxTime = setMountMaxTime;
            exports.setUnmountMaxTime = setUnmountMaxTime;
            exports.setUnloadMaxTime = setUnloadMaxTime;
            exports.reasonableTime = reasonableTime;
            exports.ensureValidAppTimeouts = ensureValidAppTimeouts;

            function _objectSpread(target) {
                for (var i = 1; i < arguments.length; i++) {
                    var source = arguments[i] != null ? arguments[i] : {};
                    var ownKeys = Object.keys(source);
                    if (typeof Object.getOwnPropertySymbols === 'function') {
                        ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function(sym) {
                            return Object.getOwnPropertyDescriptor(source, sym).enumerable;
                        }));
                    }
                    ownKeys.forEach(function(key) {
                        _defineProperty(target, key, source[key]);
                    });
                }
                return target;
            }

            function _defineProperty(obj, key, value) {
                if (key in obj) {
                    Object.defineProperty(obj, key,
                        {value: value, enumerable: true, configurable: true, writable: true});
                } else {
                    obj[key] = value;
                }
                return obj;
            }

            var globalTimeoutConfig = {
                bootstrap: {
                    millis: 4000,
                    dieOnTimeout: false
                },
                mount: {
                    millis: 3000,
                    dieOnTimeout: false
                },
                unmount: {
                    millis: 3000,
                    dieOnTimeout: false
                },
                unload: {
                    millis: 3000,
                    dieOnTimeout: false
                }
            };

            function setBootstrapMaxTime(time) {
                var dieOnTimeout = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

                if (typeof time !== 'number' || time <= 0) {
                    throw new Error("bootstrap max time must be a positive integer number of milliseconds");
                }

                globalTimeoutConfig.bootstrap = {
                    millis: time,
                    dieOnTimeout: dieOnTimeout
                };
            }

            function setMountMaxTime(time) {
                var dieOnTimeout = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

                if (typeof time !== 'number' || time <= 0) {
                    throw new Error("mount max time must be a positive integer number of milliseconds");
                }

                globalTimeoutConfig.mount = {
                    millis: time,
                    dieOnTimeout: dieOnTimeout
                };
            }

            function setUnmountMaxTime(time) {
                var dieOnTimeout = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

                if (typeof time !== 'number' || time <= 0) {
                    throw new Error("unmount max time must be a positive integer number of milliseconds");
                }

                globalTimeoutConfig.unmount = {
                    millis: time,
                    dieOnTimeout: dieOnTimeout
                };
            }

            function setUnloadMaxTime(time) {
                var dieOnTimeout = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

                if (typeof time !== 'number' || time <= 0) {
                    throw new Error("unload max time must be a positive integer number of milliseconds");
                }

                globalTimeoutConfig.unload = {
                    millis: time,
                    dieOnTimeout: dieOnTimeout
                };
            }

            function reasonableTime(promise, description, timeoutConfig) {
                var warningPeriod = 1000;
                return new Promise(function(resolve, reject) {
                    var finished = false;
                    var errored = false;
                    promise.then(function(val) {
                        finished = true;
                        resolve(val);
                    }).catch(function(val) {
                        finished = true;
                        reject(val);
                    });
                    setTimeout(function() {
                        return maybeTimingOut(1);
                    }, warningPeriod);
                    setTimeout(function() {
                        return maybeTimingOut(true);
                    }, timeoutConfig.millis);

                    function maybeTimingOut(shouldError) {
                        if (!finished) {
                            if (shouldError === true) {
                                errored = true;

                                if (timeoutConfig.dieOnTimeout) {
                                    reject("".concat(description, " did not resolve or reject for ")
                                        .concat(timeoutConfig.millis, " milliseconds"));
                                } else {
                                    console.error("".concat(description, " did not resolve or reject for ")
                                        .concat(timeoutConfig.millis,
                                            " milliseconds -- we're no longer going to warn you about it.")); //don't resolve or reject, we're waiting this one out
                                }
                            } else if (!errored) {
                                var numWarnings = shouldError;
                                var numMillis = numWarnings * warningPeriod;
                                console.warn("".concat(description, " did not resolve or reject within ")
                                    .concat(numMillis, " milliseconds"));

                                if (numMillis + warningPeriod < timeoutConfig.millis) {
                                    setTimeout(function() {
                                        return maybeTimingOut(numWarnings + 1);
                                    }, warningPeriod);
                                }
                            }
                        }
                    }
                });
            }

            function ensureValidAppTimeouts() {
                var timeouts = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
                return _objectSpread({}, globalTimeoutConfig, timeouts);
            }

            /***/
        }),

        /***/ "./src/jquery-support.js":
        /*!*******************************!*\
          !*** ./src/jquery-support.js ***!
          \*******************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.ensureJQuerySupport = ensureJQuerySupport;

            var _navigationEvents = __webpack_require__(/*! ./navigation/navigation-events.js */
                "./src/navigation/navigation-events.js");

            var hasInitialized = false;

            function ensureJQuerySupport() {
                var jQuery = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : window.jQuery;

                if (!jQuery) {
                    if (window.$ && window.$.fn && window.$.fn.jquery) {
                        jQuery = window.$;
                    }
                }

                if (jQuery && !hasInitialized) {
                    var originalJQueryOn = jQuery.fn.on;
                    var originalJQueryOff = jQuery.fn.off;

                    jQuery.fn.on = function(eventString, fn) {
                        return captureRoutingEvents.call(this, originalJQueryOn, window.addEventListener, eventString,
                            fn, arguments);
                    };

                    jQuery.fn.off = function(eventString, fn) {
                        return captureRoutingEvents.call(this, originalJQueryOff, window.removeEventListener,
                            eventString, fn, arguments);
                    };

                    hasInitialized = true;
                }
            }

            function captureRoutingEvents(originalJQueryFunction, nativeFunctionToCall, eventString, fn, originalArgs) {
                if (typeof eventString !== 'string') {
                    return originalJQueryFunction.apply(this, originalArgs);
                }

                var eventNames = eventString.split(/\s+/);
                eventNames.forEach(function(eventName) {
                    if (_navigationEvents.routingEventsListeningTo.indexOf(eventName) >= 0) {
                        nativeFunctionToCall(eventName, fn);
                        eventString = eventString.replace(eventName, '');
                    }
                });

                if (eventString.trim() === '') {
                    return this;
                } else {
                    return originalJQueryFunction.apply(this, originalArgs);
                }
            }

            /***/
        }),

        /***/ "./src/lifecycles/bootstrap.js":
        /*!*************************************!*\
          !*** ./src/lifecycles/bootstrap.js ***!
          \*************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.toBootstrapPromise = toBootstrapPromise;

            var _appHelpers = __webpack_require__(/*! ../applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _timeouts = __webpack_require__(/*! ../applications/timeouts.js */ "./src/applications/timeouts.js");

            var _appErrors = __webpack_require__(/*! ../applications/app-errors.js */
                "./src/applications/app-errors.js");

            var _propHelpers = __webpack_require__(/*! ./prop.helpers.js */ "./src/lifecycles/prop.helpers.js");

            function toBootstrapPromise(appOrParcel) {
                var hardFail = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
                return Promise.resolve().then(function() {
                    if (appOrParcel.status !== _appHelpers.NOT_BOOTSTRAPPED) {
                        return appOrParcel;
                    }

                    appOrParcel.status = _appHelpers.BOOTSTRAPPING;
                    return (0, _timeouts.reasonableTime)(appOrParcel.bootstrap((0, _propHelpers.getProps)(appOrParcel)),
                        "Bootstrapping appOrParcel '".concat(appOrParcel.name, "'"), appOrParcel.timeouts.bootstrap)
                        .then(function() {
                            appOrParcel.status = _appHelpers.NOT_MOUNTED;
                            return appOrParcel;
                        })
                        .catch(function(err) {
                            appOrParcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;

                            if (hardFail) {
                                var transformedErr = (0, _appErrors.transformErr)(err, appOrParcel);
                                throw transformedErr;
                            } else {
                                (0, _appErrors.handleAppError)(err, appOrParcel);
                                return appOrParcel;
                            }
                        });
                });
            }

            /***/
        }),

        /***/ "./src/lifecycles/lifecycle.helpers.js":
        /*!*********************************************!*\
          !*** ./src/lifecycles/lifecycle.helpers.js ***!
          \*********************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.validLifecycleFn = validLifecycleFn;
            exports.flattenFnArray = flattenFnArray;
            exports.smellsLikeAPromise = smellsLikeAPromise;

            var _find = __webpack_require__(/*! src/utils/find.js */ "./src/utils/find.js");

            function validLifecycleFn(fn) {
                return fn && (typeof fn === 'function' || isArrayOfFns(fn));

                function isArrayOfFns(arr) {
                    return Array.isArray(arr) && !(0, _find.find)(arr, function(item) {
                        return typeof item !== 'function';
                    });
                }
            }

            function flattenFnArray(fns, description) {
                fns = Array.isArray(fns) ? fns : [fns];

                if (fns.length === 0) {
                    fns = [
                        function() {
                            return Promise.resolve();
                        }];
                }

                return function(props) {
                    return new Promise(function(resolve, reject) {
                        waitForPromises(0);

                        function waitForPromises(index) {
                            var promise = fns[index](props);

                            if (!smellsLikeAPromise(promise)) {
                                reject("".concat(description, " at index ").concat(index, " did not return a promise"));
                            } else {
                                promise.then(function() {
                                    if (index === fns.length - 1) {
                                        resolve();
                                    } else {
                                        waitForPromises(index + 1);
                                    }
                                }).catch(reject);
                            }
                        }
                    });
                };
            }

            function smellsLikeAPromise(promise) {
                return promise && typeof promise.then === 'function' && typeof promise.catch === 'function';
            }

            /***/
        }),

        /***/ "./src/lifecycles/load.js":
        /*!********************************!*\
          !*** ./src/lifecycles/load.js ***!
          \********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.toLoadPromise = toLoadPromise;

            var _appHelpers = __webpack_require__(/*! ../applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _timeouts = __webpack_require__(/*! ../applications/timeouts.js */ "./src/applications/timeouts.js");

            var _appErrors = __webpack_require__(/*! ../applications/app-errors.js */
                "./src/applications/app-errors.js");

            var _find = __webpack_require__(/*! src/utils/find.js */ "./src/utils/find.js");

            var _lifecycleHelpers = __webpack_require__(/*! ./lifecycle.helpers.js */
                "./src/lifecycles/lifecycle.helpers.js");

            var _propHelpers = __webpack_require__(/*! ./prop.helpers.js */ "./src/lifecycles/prop.helpers.js");

            function _typeof(obj) {
                if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
                    _typeof = function _typeof(obj) {
                        return typeof obj;
                    };
                } else {
                    _typeof = function _typeof(obj) {
                        return obj && typeof Symbol === "function" && obj.constructor === Symbol &&
                        obj !== Symbol.prototype ? "symbol" : typeof obj;
                    };
                }
                return _typeof(obj);
            }

            function toLoadPromise(app) {
                return Promise.resolve().then(function() {
                    if (app.status !== _appHelpers.NOT_LOADED) {
                        return app;
                    }

                    app.status = _appHelpers.LOADING_SOURCE_CODE;
                    var appOpts;
                    return Promise.resolve().then(function() {
                        var loadPromise = app.loadImpl((0, _propHelpers.getProps)(app));

                        if (!(0, _lifecycleHelpers.smellsLikeAPromise)(loadPromise)) {
                            // The name of the app will be prepended to this error message inside of the handleAppError function
                            throw new Error(
                                "single-spa loading function did not return a promise. Check the second argument to registerApplication('".concat(
                                    app.name, "', loadingFunction, activityFunction)"));
                        }

                        return loadPromise.then(function(val) {
                            appOpts = val;
                            var validationErrMessage;

                            if (_typeof(appOpts) !== 'object') {
                                validationErrMessage = "does not export anything";
                            }

                            if (!(0, _lifecycleHelpers.validLifecycleFn)(appOpts.bootstrap)) {
                                validationErrMessage = "does not export a bootstrap function or array of functions";
                            }

                            if (!(0, _lifecycleHelpers.validLifecycleFn)(appOpts.mount)) {
                                validationErrMessage = "does not export a mount function or array of functions";
                            }

                            if (!(0, _lifecycleHelpers.validLifecycleFn)(appOpts.unmount)) {
                                validationErrMessage = "does not export an unmount function or array of functions";
                            }

                            if (validationErrMessage) {
                                (0, _appErrors.handleAppError)(validationErrMessage, app);
                                app.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                                return app;
                            }

                            app.status = _appHelpers.NOT_BOOTSTRAPPED;
                            app.bootstrap = (0, _lifecycleHelpers.flattenFnArray)(appOpts.bootstrap,
                                "App '".concat(app.name, "' bootstrap function"));
                            app.mount = (0, _lifecycleHelpers.flattenFnArray)(appOpts.mount,
                                "App '".concat(app.name, "' mount function"));
                            app.unmount = (0, _lifecycleHelpers.flattenFnArray)(appOpts.unmount,
                                "App '".concat(app.name, "' unmount function"));
                            app.unload = (0, _lifecycleHelpers.flattenFnArray)(appOpts.unload || [],
                                "App '".concat(app.name, "' unload function"));
                            app.timeouts = (0, _timeouts.ensureValidAppTimeouts)(appOpts.timeouts);
                            return app;
                        });
                    }).catch(function(err) {
                        (0, _appErrors.handleAppError)(err, app);
                        app.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                        return app;
                    });
                });
            }

            /***/
        }),

        /***/ "./src/lifecycles/mount.js":
        /*!*********************************!*\
          !*** ./src/lifecycles/mount.js ***!
          \*********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.toMountPromise = toMountPromise;

            var _appHelpers = __webpack_require__(/*! ../applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _appErrors = __webpack_require__(/*! ../applications/app-errors.js */
                "./src/applications/app-errors.js");

            var _timeouts = __webpack_require__(/*! ../applications/timeouts.js */ "./src/applications/timeouts.js");

            var _customEvent = _interopRequireDefault(
                __webpack_require__(/*! custom-event */ "./node_modules/custom-event/index.js"));

            var _propHelpers = __webpack_require__(/*! ./prop.helpers.js */ "./src/lifecycles/prop.helpers.js");

            function _interopRequireDefault(obj) {
                return obj && obj.__esModule ? obj : {default: obj};
            }

            var beforeFirstMountFired = false;
            var firstMountFired = false;

            function toMountPromise(appOrParcel) {
                var hardFail = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
                return Promise.resolve().then(function() {
                    if (appOrParcel.status !== _appHelpers.NOT_MOUNTED) {
                        return appOrParcel;
                    }

                    if (!beforeFirstMountFired) {
                        window.dispatchEvent(new _customEvent.default('single-spa:before-first-mount'));
                        beforeFirstMountFired = true;
                    }

                    return (0, _timeouts.reasonableTime)(appOrParcel.mount((0, _propHelpers.getProps)(appOrParcel)),
                        "Mounting application '".concat(appOrParcel.name, "'"), appOrParcel.timeouts.mount)
                        .then(function() {
                            appOrParcel.status = _appHelpers.MOUNTED;

                            if (!firstMountFired) {
                                window.dispatchEvent(new _customEvent.default('single-spa:first-mount'));
                                firstMountFired = true;
                            }

                            return appOrParcel;
                        })
                        .catch(function(err) {
                            if (!hardFail) {
                                (0, _appErrors.handleAppError)(err, appOrParcel);
                                appOrParcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                                return appOrParcel;
                            } else {
                                var transformedErr = (0, _appErrors.transformErr)(err, appOrParcel);
                                appOrParcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                                throw transformedErr;
                            }
                        });
                });
            }

            /***/
        }),

        /***/ "./src/lifecycles/prop.helpers.js":
        /*!****************************************!*\
          !*** ./src/lifecycles/prop.helpers.js ***!
          \****************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.getProps = getProps;

            var singleSpa = _interopRequireWildcard(
                __webpack_require__(/*! src/single-spa.js */ "./src/single-spa.js"));

            var _mountParcel = __webpack_require__(/*! src/parcels/mount-parcel.js */ "./src/parcels/mount-parcel.js");

            function _interopRequireWildcard(obj) {
                if (obj && obj.__esModule) {
                    return obj;
                } else {
                    var newObj = {};
                    if (obj != null) {
                        for (var key in obj) {
                            if (Object.prototype.hasOwnProperty.call(obj, key)) {
                                var desc = Object.defineProperty && Object.getOwnPropertyDescriptor ?
                                    Object.getOwnPropertyDescriptor(obj, key) :
                                    {};
                                if (desc.get || desc.set) {
                                    Object.defineProperty(newObj, key, desc);
                                } else {
                                    newObj[key] = obj[key];
                                }
                            }
                        }
                    }
                    newObj.default = obj;
                    return newObj;
                }
            }

            function _objectSpread(target) {
                for (var i = 1; i < arguments.length; i++) {
                    var source = arguments[i] != null ? arguments[i] : {};
                    var ownKeys = Object.keys(source);
                    if (typeof Object.getOwnPropertySymbols === 'function') {
                        ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function(sym) {
                            return Object.getOwnPropertyDescriptor(source, sym).enumerable;
                        }));
                    }
                    ownKeys.forEach(function(key) {
                        _defineProperty(target, key, source[key]);
                    });
                }
                return target;
            }

            function _defineProperty(obj, key, value) {
                if (key in obj) {
                    Object.defineProperty(obj, key,
                        {value: value, enumerable: true, configurable: true, writable: true});
                } else {
                    obj[key] = value;
                }
                return obj;
            }

            function getProps(appOrParcel) {
                var result = _objectSpread({}, appOrParcel.customProps, {
                    name: appOrParcel.name,
                    mountParcel: _mountParcel.mountParcel.bind(appOrParcel),
                    singleSpa: singleSpa
                });

                if (appOrParcel.unmountThisParcel) {
                    result.unmountSelf = appOrParcel.unmountThisParcel;
                }

                return result;
            }

            /***/
        }),

        /***/ "./src/lifecycles/unload.js":
        /*!**********************************!*\
          !*** ./src/lifecycles/unload.js ***!
          \**********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.toUnloadPromise = toUnloadPromise;
            exports.addAppToUnload = addAppToUnload;
            exports.getAppUnloadInfo = getAppUnloadInfo;
            exports.getAppsToUnload = getAppsToUnload;

            var _appHelpers = __webpack_require__(/*! ../applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _appErrors = __webpack_require__(/*! ../applications/app-errors.js */
                "./src/applications/app-errors.js");

            var _timeouts = __webpack_require__(/*! ../applications/timeouts.js */ "./src/applications/timeouts.js");

            var _propHelpers = __webpack_require__(/*! ./prop.helpers.js */ "./src/lifecycles/prop.helpers.js");

            var appsToUnload = {};

            function toUnloadPromise(app) {
                return Promise.resolve().then(function() {
                    var unloadInfo = appsToUnload[app.name];

                    if (!unloadInfo) {
                        /* No one has called unloadApplication for this app,
                        */
                        return app;
                    }

                    if (app.status === _appHelpers.NOT_LOADED) {
                        /* This app is already unloaded. We just need to clean up
                         * anything that still thinks we need to unload the app.
                         */
                        finishUnloadingApp(app, unloadInfo);
                        return app;
                    }

                    if (app.status === _appHelpers.UNLOADING) {
                        /* Both unloadApplication and reroute want to unload this app.
                         * It only needs to be done once, though.
                         */
                        return unloadInfo.promise.then(function() {
                            return app;
                        });
                    }

                    if (app.status !== _appHelpers.NOT_MOUNTED) {
                        /* The app cannot be unloaded until it is unmounted.
                        */
                        return app;
                    }

                    app.status = _appHelpers.UNLOADING;
                    return (0, _timeouts.reasonableTime)(app.unload((0, _propHelpers.getProps)(app)),
                        "Unloading application '".concat(app.name, "'"), app.timeouts.unload).then(function() {
                        finishUnloadingApp(app, unloadInfo);
                        return app;
                    }).catch(function(err) {
                        errorUnloadingApp(app, unloadInfo, err);
                        return app;
                    });
                });
            }

            function finishUnloadingApp(app, unloadInfo) {
                delete appsToUnload[app.name]; // Unloaded apps don't have lifecycles

                delete app.bootstrap;
                delete app.mount;
                delete app.unmount;
                delete app.unload;
                app.status = _appHelpers.NOT_LOADED;
                /* resolve the promise of whoever called unloadApplication.
                 * This should be done after all other cleanup/bookkeeping
                 */

                unloadInfo.resolve();
            }

            function errorUnloadingApp(app, unloadInfo, err) {
                delete appsToUnload[app.name]; // Unloaded apps don't have lifecycles

                delete app.bootstrap;
                delete app.mount;
                delete app.unmount;
                delete app.unload;
                (0, _appErrors.handleAppError)(err, app);
                app.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                unloadInfo.reject(err);
            }

            function addAppToUnload(app, promiseGetter, resolve, reject) {
                appsToUnload[app.name] = {
                    app: app,
                    resolve: resolve,
                    reject: reject
                };
                Object.defineProperty(appsToUnload[app.name], 'promise', {
                    get: promiseGetter
                });
            }

            function getAppUnloadInfo(appName) {
                return appsToUnload[appName];
            }

            function getAppsToUnload() {
                return Object.keys(appsToUnload).map(function(appName) {
                    return appsToUnload[appName].app;
                }).filter(_appHelpers.isntActive);
            }

            /***/
        }),

        /***/ "./src/lifecycles/unmount.js":
        /*!***********************************!*\
          !*** ./src/lifecycles/unmount.js ***!
          \***********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.toUnmountPromise = toUnmountPromise;

            var _appHelpers = __webpack_require__(/*! ../applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _appErrors = __webpack_require__(/*! ../applications/app-errors.js */
                "./src/applications/app-errors.js");

            var _timeouts = __webpack_require__(/*! ../applications/timeouts.js */ "./src/applications/timeouts.js");

            var _propHelpers = __webpack_require__(/*! ./prop.helpers.js */ "./src/lifecycles/prop.helpers.js");

            function toUnmountPromise(appOrParcel) {
                var hardFail = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
                return Promise.resolve().then(function() {
                    if (appOrParcel.status !== _appHelpers.MOUNTED) {
                        return appOrParcel;
                    }

                    appOrParcel.status = _appHelpers.UNMOUNTING;
                    var unmountChildrenParcels = Object.keys(appOrParcel.parcels).map(function(parcelId) {
                        return appOrParcel.parcels[parcelId].unmountThisParcel();
                    });
                    var parcelError;
                    return Promise.all(unmountChildrenParcels).then(unmountAppOrParcel, function(parcelError) {
                        // There is a parcel unmount error
                        return unmountAppOrParcel().then(function() {
                            // Unmounting the app/parcel succeeded, but unmounting its children parcels did not
                            var parentError = new Error(parcelError.message);

                            if (hardFail) {
                                var transformedErr = (0, _appErrors.transformErr)(parentError, appOrParcel);
                                appOrParcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                                throw transformedErr;
                            } else {
                                (0, _appErrors.handleAppError)(parentError, appOrParcel);
                                appOrParcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                            }
                        });
                    }).then(function() {
                        return appOrParcel;
                    });

                    function unmountAppOrParcel() {
                        // We always try to unmount the appOrParcel, even if the children parcels failed to unmount.
                        return (0, _timeouts.reasonableTime)(
                            appOrParcel.unmount((0, _propHelpers.getProps)(appOrParcel)),
                            "Unmounting application ".concat(appOrParcel.name, "'"), appOrParcel.timeouts.unmount)
                            .then(function() {
                                // The appOrParcel needs to stay in a broken status if its children parcels fail to unmount
                                if (!parcelError) {
                                    appOrParcel.status = _appHelpers.NOT_MOUNTED;
                                }
                            })
                            .catch(function(err) {
                                if (hardFail) {
                                    var transformedErr = (0, _appErrors.transformErr)(err, appOrParcel);
                                    appOrParcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                                    throw transformedErr;
                                } else {
                                    (0, _appErrors.handleAppError)(err, appOrParcel);
                                    appOrParcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                                }
                            });
                    }
                });
            }

            /***/
        }),

        /***/ "./src/lifecycles/update.js":
        /*!**********************************!*\
          !*** ./src/lifecycles/update.js ***!
          \**********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.toUpdatePromise = toUpdatePromise;

            var _appHelpers = __webpack_require__(/*! ../applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _appErrors = __webpack_require__(/*! ../applications/app-errors.js */
                "./src/applications/app-errors.js");

            var _timeouts = __webpack_require__(/*! ../applications/timeouts.js */ "./src/applications/timeouts.js");

            var _propHelpers = __webpack_require__(/*! ./prop.helpers.js */ "./src/lifecycles/prop.helpers.js");

            function toUpdatePromise(parcel) {
                return Promise.resolve().then(function() {
                    if (parcel.status !== _appHelpers.MOUNTED) {
                        throw new Error("Cannot update parcel '".concat(parcel.name, "' because it is not mounted"));
                    }

                    parcel.status = _appHelpers.UPDATING;
                    return (0, _timeouts.reasonableTime)(parcel.update((0, _propHelpers.getProps)(parcel)),
                        "Updating parcel '".concat(parcel.name, "'"), parcel.timeouts.mount).then(function() {
                        parcel.status = _appHelpers.MOUNTED;
                        return parcel;
                    }).catch(function(err) {
                        var transformedErr = (0, _appErrors.transformErr)(err, parcel);
                        parcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                        throw transformedErr;
                    });
                });
            }

            /***/
        }),

        /***/ "./src/navigation/navigation-events.js":
        /*!*********************************************!*\
          !*** ./src/navigation/navigation-events.js ***!
          \*********************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.navigateToUrl = navigateToUrl;
            exports.callCapturedEventListeners = callCapturedEventListeners;
            exports.routingEventsListeningTo = void 0;

            var _reroute = __webpack_require__(/*! ./reroute.js */ "./src/navigation/reroute.js");

            var _find = __webpack_require__(/*! src/utils/find.js */ "./src/utils/find.js");

            /* We capture navigation event listeners so that we can make sure
             * that application navigation listeners are not called until
             * single-spa has ensured that the correct applications are
             * unmounted and mounted.
             */
            var capturedEventListeners = {
                hashchange: [],
                popstate: []
            };
            var routingEventsListeningTo = ['hashchange', 'popstate'];
            exports.routingEventsListeningTo = routingEventsListeningTo;

            function navigateToUrl(obj) {
                var opts = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
                var url;

                if (typeof obj === 'string') {
                    url = obj;
                } else if (this && this.href) {
                    url = this.href;
                } else if (obj && obj.currentTarget && obj.currentTarget.href && obj.preventDefault) {
                    url = obj.currentTarget.href;
                    obj.preventDefault();
                } else {
                    throw new Error(
                        "singleSpaNavigate must be either called with a string url, with an <a> tag as its context, or with an event whose currentTarget is an <a> tag");
                }

                var current = parseUri(window.location.href);
                var destination = parseUri(url);

                if (url.indexOf('#') === 0) {
                    window.location.hash = '#' + destination.anchor;
                } else if (current.host !== destination.host && destination.host) {
                    if (opts.isTestingEnv) {
                        return {
                            wouldHaveReloadedThePage: true
                        };
                    } else {
                        window.location.href = url;
                    }
                } else if (!isSamePath(destination.path, current.path)) {
                    // different path or a different host
                    window.history.pushState(null, null, url);
                } else {
                    window.location.hash = '#' + destination.anchor;
                }

                function isSamePath(destination, current) {
                    // if the destination has a path but no domain, it doesn't include the root '/'
                    return current === destination || current === '/' + destination;
                }
            }

            function callCapturedEventListeners(eventArguments) {
                var _this = this;

                if (eventArguments) {
                    var eventType = eventArguments[0].type;

                    if (routingEventsListeningTo.indexOf(eventType) >= 0) {
                        capturedEventListeners[eventType].forEach(function(listener) {
                            listener.apply(_this, eventArguments);
                        });
                    }
                }
            }

            function urlReroute() {
                (0, _reroute.reroute)([], arguments);
            } // We will trigger an app change for any routing events.

            window.addEventListener('hashchange', urlReroute);
            window.addEventListener('popstate', urlReroute); // Monkeypatch addEventListener so that we can ensure correct timing

            var originalAddEventListener = window.addEventListener;
            var originalRemoveEventListener = window.removeEventListener;

            window.addEventListener = function(eventName, fn) {
                if (typeof fn === 'function') {
                    if (routingEventsListeningTo.indexOf(eventName) >= 0 &&
                        !(0, _find.find)(capturedEventListeners[eventName], function(listener) {
                            return listener === fn;
                        })) {
                        capturedEventListeners[eventName].push(fn);
                        return;
                    }
                }

                return originalAddEventListener.apply(this, arguments);
            };

            window.removeEventListener = function(eventName, listenerFn) {
                if (typeof listenerFn === 'function') {
                    if (routingEventsListeningTo.indexOf(eventName) >= 0) {
                        capturedEventListeners[eventName] = capturedEventListeners[eventName].filter(function(fn) {
                            return fn !== listenerFn;
                        });
                        return;
                    }
                }

                return originalRemoveEventListener.apply(this, arguments);
            };

            var originalPushState = window.history.pushState;

            window.history.pushState = function(state) {
                var result = originalPushState.apply(this, arguments);
                (0, _reroute.reroute)();
                return result;
            };

            var originalReplaceState = window.history.replaceState;

            window.history.replaceState = function() {
                var result = originalReplaceState.apply(this, arguments);
                (0, _reroute.reroute)();
                return result;
            };
            /* For convenience in `onclick` attributes, we expose a global function for navigating to
             * whatever an <a> tag's href is.
             */

            window.singleSpaNavigate = navigateToUrl;

            function parseUri(str) {
                // parseUri 1.2.2
                // (c) Steven Levithan <stevenlevithan.com>
                // MIT License
                // http://blog.stevenlevithan.com/archives/parseuri
                var parseOptions = {
                    strictMode: true,
                    key: [
                        "source",
                        "protocol",
                        "authority",
                        "userInfo",
                        "user",
                        "password",
                        "host",
                        "port",
                        "relative",
                        "path",
                        "directory",
                        "file",
                        "query",
                        "anchor"],
                    q: {
                        name: "queryKey",
                        parser: /(?:^|&)([^&=]*)=?([^&]*)/g
                    },
                    parser: {
                        strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                        loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
                    }
                };
                var o = parseOptions;
                var m = o.parser[o.strictMode ? "strict" : "loose"].exec(str);
                var uri = {};
                var i = 14;

                while (i--) {
                    uri[o.key[i]] = m[i] || "";
                }

                uri[o.q.name] = {};
                uri[o.key[12]].replace(o.q.parser, function($0, $1, $2) {
                    if ($1) uri[o.q.name][$1] = $2;
                });
                return uri;
            }

            /***/
        }),

        /***/ "./src/navigation/reroute.js":
        /*!***********************************!*\
          !*** ./src/navigation/reroute.js ***!
          \***********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.reroute = reroute;

            var _customEvent = _interopRequireDefault(
                __webpack_require__(/*! custom-event */ "./node_modules/custom-event/index.js"));

            var _start = __webpack_require__(/*! src/start.js */ "./src/start.js");

            var _load = __webpack_require__(/*! src/lifecycles/load.js */ "./src/lifecycles/load.js");

            var _bootstrap = __webpack_require__(/*! src/lifecycles/bootstrap.js */ "./src/lifecycles/bootstrap.js");

            var _mount = __webpack_require__(/*! src/lifecycles/mount.js */ "./src/lifecycles/mount.js");

            var _unmount = __webpack_require__(/*! src/lifecycles/unmount.js */ "./src/lifecycles/unmount.js");

            var _apps = __webpack_require__(/*! src/applications/apps.js */ "./src/applications/apps.js");

            var _appHelpers = __webpack_require__(/*! src/applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _navigationEvents = __webpack_require__(/*! ./navigation-events.js */
                "./src/navigation/navigation-events.js");

            var _unload = __webpack_require__(/*! src/lifecycles/unload.js */ "./src/lifecycles/unload.js");

            function _interopRequireDefault(obj) {
                return obj && obj.__esModule ? obj : {default: obj};
            }

            var appChangeUnderway = false,
                peopleWaitingOnAppChange = [];

            function reroute() {
                var pendingPromises = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
                var eventArguments = arguments.length > 1 ? arguments[1] : undefined;

                if (appChangeUnderway) {
                    return new Promise(function(resolve, reject) {
                        peopleWaitingOnAppChange.push({
                            resolve: resolve,
                            reject: reject,
                            eventArguments: eventArguments
                        });
                    });
                }

                appChangeUnderway = true;
                var wasNoOp = true;

                if ((0, _start.isStarted)()) {
                    return performAppChanges();
                } else {
                    return loadApps();
                }

                function loadApps() {
                    return Promise.resolve().then(function() {
                        var loadPromises = (0, _apps.getAppsToLoad)().map(_load.toLoadPromise);

                        if (loadPromises.length > 0) {
                            wasNoOp = false;
                        }

                        return Promise.all(loadPromises).then(finishUpAndReturn).catch(function(err) {
                            callAllEventListeners();
                            throw err;
                        });
                    });
                }

                function performAppChanges() {
                    return Promise.resolve().then(function() {
                        var myCE;

                        if (eventArguments && eventArguments[0]) {
                            myCE = {
                                detail: eventArguments[0]
                            };
                        }

                        window.dispatchEvent(new _customEvent.default("single-spa:before-routing-event", myCE));
                        var unloadPromises = (0, _unload.getAppsToUnload)().map(_unload.toUnloadPromise);
                        var unmountUnloadPromises = (0, _apps.getAppsToUnmount)()
                            .map(_unmount.toUnmountPromise)
                            .map(function(unmountPromise) {
                                return unmountPromise.then(_unload.toUnloadPromise);
                            });
                        var allUnmountPromises = unmountUnloadPromises.concat(unloadPromises);

                        if (allUnmountPromises.length > 0) {
                            wasNoOp = false;
                        }

                        var unmountAllPromise = Promise.all(allUnmountPromises);
                        var appsToLoad = (0, _apps.getAppsToLoad)();
                        /* We load and bootstrap apps while other apps are unmounting, but we
                         * wait to mount the app until all apps are finishing unmounting
                         */

                        var loadThenMountPromises = appsToLoad.map(function(app) {
                            return (0, _load.toLoadPromise)(app)
                                .then(_bootstrap.toBootstrapPromise)
                                .then(function(app) {
                                    return unmountAllPromise.then(function() {
                                        return (0, _mount.toMountPromise)(app);
                                    });
                                });
                        });

                        if (loadThenMountPromises.length > 0) {
                            wasNoOp = false;
                        }
                        /* These are the apps that are already bootstrapped and just need
                         * to be mounted. They each wait for all unmounting apps to finish up
                         * before they mount.
                         */

                        var mountPromises = (0, _apps.getAppsToMount)().filter(function(appToMount) {
                            return appsToLoad.indexOf(appToMount) < 0;
                        }).map(function(appToMount) {
                            return (0, _bootstrap.toBootstrapPromise)(appToMount).then(function() {
                                return unmountAllPromise;
                            }).then(function() {
                                return (0, _mount.toMountPromise)(appToMount);
                            });
                        });

                        if (mountPromises.length > 0) {
                            wasNoOp = false;
                        }

                        return unmountAllPromise.catch(function(err) {
                            callAllEventListeners();
                            throw err;
                        }).then(function() {
                            /* Now that the apps that needed to be unmounted are unmounted, their DOM navigation
                             * events (like hashchange or popstate) should have been cleaned up. So it's safe
                             * to let the remaining captured event listeners to handle about the DOM event.
                             */
                            callAllEventListeners();
                            return Promise.all(loadThenMountPromises.concat(mountPromises)).catch(function(err) {
                                pendingPromises.forEach(function(promise) {
                                    return promise.reject(err);
                                });
                                throw err;
                            }).then(function() {
                                return finishUpAndReturn(false);
                            });
                        });
                    });
                }

                function finishUpAndReturn() {
                    var callEventListeners = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
                    var returnValue = (0, _apps.getMountedApps)();

                    if (callEventListeners) {
                        callAllEventListeners();
                    }

                    pendingPromises.forEach(function(promise) {
                        return promise.resolve(returnValue);
                    });

                    try {
                        var appChangeEventName = wasNoOp ? "single-spa:no-app-change" : "single-spa:app-change";
                        window.dispatchEvent(new _customEvent.default(appChangeEventName));
                        window.dispatchEvent(new _customEvent.default("single-spa:routing-event"));
                    } catch (err) {
                        /* We use a setTimeout because if someone else's event handler throws an error, single-spa
                         * needs to carry on. If a listener to the event throws an error, it's their own fault, not
                         * single-spa's.
                         */
                        setTimeout(function() {
                            throw err;
                        });
                    }
                    /* Setting this allows for subsequent calls to reroute() to actually perform
                     * a reroute instead of just getting queued behind the current reroute call.
                     * We want to do this after the mounting/unmounting is done but before we
                     * resolve the promise for the `reroute` function.
                     */

                    appChangeUnderway = false;

                    if (peopleWaitingOnAppChange.length > 0) {
                        /* While we were rerouting, someone else triggered another reroute that got queued.
                         * So we need reroute again.
                         */
                        var nextPendingPromises = peopleWaitingOnAppChange;
                        peopleWaitingOnAppChange = [];
                        reroute(nextPendingPromises);
                    }

                    return returnValue;
                }

                /* We need to call all event listeners that have been delayed because they were
                 * waiting on single-spa. This includes haschange and popstate events for both
                 * the current run of performAppChanges(), but also all of the queued event listeners.
                 * We want to call the listeners in the same order as if they had not been delayed by
                 * single-spa, which means queued ones first and then the most recent one.
                 */

                function callAllEventListeners() {
                    pendingPromises.forEach(function(pendingPromise) {
                        (0, _navigationEvents.callCapturedEventListeners)(pendingPromise.eventArguments);
                    });
                    (0, _navigationEvents.callCapturedEventListeners)(eventArguments);
                }
            }

            /***/
        }),

        /***/ "./src/parcels/mount-parcel.js":
        /*!*************************************!*\
          !*** ./src/parcels/mount-parcel.js ***!
          \*************************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.mountRootParcel = mountRootParcel;
            exports.mountParcel = mountParcel;

            var _lifecycleHelpers = __webpack_require__(/*! src/lifecycles/lifecycle.helpers.js */
                "./src/lifecycles/lifecycle.helpers.js");

            var _appHelpers = __webpack_require__(/*! src/applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            var _bootstrap = __webpack_require__(/*! src/lifecycles/bootstrap.js */ "./src/lifecycles/bootstrap.js");

            var _mount = __webpack_require__(/*! src/lifecycles/mount.js */ "./src/lifecycles/mount.js");

            var _update = __webpack_require__(/*! src/lifecycles/update.js */ "./src/lifecycles/update.js");

            var _unmount = __webpack_require__(/*! src/lifecycles/unmount.js */ "./src/lifecycles/unmount.js");

            var _timeouts = __webpack_require__(/*! src/applications/timeouts.js */ "./src/applications/timeouts.js");

            var _appErrors = __webpack_require__(/*! ../applications/app-errors.js */
                "./src/applications/app-errors.js");

            function _typeof(obj) {
                if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
                    _typeof = function _typeof(obj) {
                        return typeof obj;
                    };
                } else {
                    _typeof = function _typeof(obj) {
                        return obj && typeof Symbol === "function" && obj.constructor === Symbol &&
                        obj !== Symbol.prototype ? "symbol" : typeof obj;
                    };
                }
                return _typeof(obj);
            }

            var parcelCount = 0;
            var rootParcels = {
                parcels: {}
            }; // This is a public api, exported to users of single-spa

            function mountRootParcel() {
                return mountParcel.apply(rootParcels, arguments);
            }

            function mountParcel(config, customProps) {
                var owningAppOrParcel = this; // Validate inputs

                if (!config || _typeof(config) !== 'object' && typeof config !== 'function') {
                    throw new Error('Cannot mount parcel without a config object or config loading function');
                }

                if (config.name && typeof config.name !== 'string') {
                    throw new Error('Parcel name must be a string, if provided');
                }

                if (_typeof(customProps) !== 'object') {
                    throw new Error("Parcel ".concat(name, " has invalid customProps -- must be an object"));
                }

                if (!customProps.domElement) {
                    throw new Error(
                        "Parcel ".concat(name, " cannot be mounted without a domElement provided as a prop"));
                }

                var id = parcelCount++;
                var passedConfigLoadingFunction = typeof config === 'function';
                var configLoadingFunction = passedConfigLoadingFunction ? config : function() {
                    return Promise.resolve(config);
                }; // Internal representation

                var parcel = {
                    id: id,
                    parcels: {},
                    status: passedConfigLoadingFunction ?
                        _appHelpers.LOADING_SOURCE_CODE :
                        _appHelpers.NOT_BOOTSTRAPPED,
                    customProps: customProps,
                    owningAppOrParcel: owningAppOrParcel,
                    unmountThisParcel: function unmountThisParcel() {
                        if (parcel.status !== _appHelpers.MOUNTED) {
                            throw new Error("Cannot unmount parcel '".concat(name, "' -- it is in a ")
                                .concat(parcel.status, " status"));
                        }

                        return (0, _unmount.toUnmountPromise)(parcel, true).then(function(value) {
                            if (parcel.owningAppOrParcel) {
                                delete parcel.owningAppOrParcel.parcels[parcel.id];
                            }

                            return value;
                        }).then(function(value) {
                            resolveUnmount(value);
                            return value;
                        }).catch(function(err) {
                            parcel.status = _appHelpers.SKIP_BECAUSE_BROKEN;
                            rejectUnmount(err);
                            throw err;
                        });
                    }
                }; // We return an external representation

                var externalRepresentation; // Add to owning app or parcel

                owningAppOrParcel.parcels[id] = parcel;
                var loadPromise = configLoadingFunction();

                if (!loadPromise || typeof loadPromise.then !== 'function') {
                    throw new Error(
                        "When mounting a parcel, the config loading function must return a promise that resolves with the parcel config");
                }

                loadPromise = loadPromise.then(function(config) {
                    if (!config) {
                        throw new Error(
                            "When mounting a parcel, the config loading function returned a promise that did not resolve with a parcel config");
                    }

                    var name = config.name || "parcel-".concat(id);

                    if (!(0, _lifecycleHelpers.validLifecycleFn)(config.bootstrap)) {
                        throw new Error("Parcel ".concat(name, " must have a valid bootstrap function"));
                    }

                    if (!(0, _lifecycleHelpers.validLifecycleFn)(config.mount)) {
                        throw new Error("Parcel ".concat(name, " must have a valid mount function"));
                    }

                    if (!(0, _lifecycleHelpers.validLifecycleFn)(config.unmount)) {
                        throw new Error("Parcel ".concat(name, " must have a valid unmount function"));
                    }

                    if (config.update && !(0, _lifecycleHelpers.validLifecycleFn)(config.update)) {
                        throw new Error("Parcel ".concat(name, " provided an invalid update function"));
                    }

                    var bootstrap = (0, _lifecycleHelpers.flattenFnArray)(config.bootstrap);
                    var mount = (0, _lifecycleHelpers.flattenFnArray)(config.mount);
                    var unmount = (0, _lifecycleHelpers.flattenFnArray)(config.unmount);
                    parcel.status = _appHelpers.NOT_BOOTSTRAPPED;
                    parcel.name = name;
                    parcel.bootstrap = bootstrap;
                    parcel.mount = mount;
                    parcel.unmount = unmount;
                    parcel.timeouts = (0, _timeouts.ensureValidAppTimeouts)(parcel);

                    if (config.update) {
                        parcel.update = (0, _lifecycleHelpers.flattenFnArray)(config.update);

                        externalRepresentation.update = function(customProps) {
                            parcel.customProps = customProps;
                            return promiseWithoutReturnValue((0, _update.toUpdatePromise)(parcel));
                        };
                    }
                }); // Start bootstrapping and mounting
                // The .then() causes the work to be put on the event loop instead of happening immediately

                var bootstrapPromise = loadPromise.then(function() {
                    return (0, _bootstrap.toBootstrapPromise)(parcel, true);
                });
                var mountPromise = bootstrapPromise.then(function() {
                    return (0, _mount.toMountPromise)(parcel, true);
                });
                var resolveUnmount, rejectUnmount;
                var unmountPromise = new Promise(function(resolve, reject) {
                    resolveUnmount = resolve;
                    rejectUnmount = reject;
                });
                externalRepresentation = {
                    mount: function mount() {
                        return promiseWithoutReturnValue(Promise.resolve().then(function() {
                            if (parcel.status !== _appHelpers.NOT_MOUNTED) {
                                throw new Error("Cannot mount parcel '".concat(name, "' -- it is in a ")
                                    .concat(parcel.status, " status"));
                            } // Add to owning app or parcel

                            owningAppOrParcel.parcels[id] = parcel;
                            return (0, _mount.toMountPromise)(parcel);
                        }));
                    },
                    unmount: function unmount() {
                        return promiseWithoutReturnValue(parcel.unmountThisParcel());
                    },
                    getStatus: function getStatus() {
                        return parcel.status;
                    },
                    loadPromise: promiseWithoutReturnValue(loadPromise),
                    bootstrapPromise: promiseWithoutReturnValue(bootstrapPromise),
                    mountPromise: promiseWithoutReturnValue(mountPromise),
                    unmountPromise: promiseWithoutReturnValue(unmountPromise)
                };
                return externalRepresentation;
            }

            function promiseWithoutReturnValue(promise) {
                return promise.then(function() {
                    return null;
                });
            }

            /***/
        }),

        /***/ "./src/single-spa.js":
        /*!***************************!*\
          !*** ./src/single-spa.js ***!
          \***************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            Object.defineProperty(exports, "start", {
                enumerable: true,
                get: function get() {
                    return _start.start;
                }
            });
            Object.defineProperty(exports, "ensureJQuerySupport", {
                enumerable: true,
                get: function get() {
                    return _jquerySupport.ensureJQuerySupport;
                }
            });
            Object.defineProperty(exports, "setBootstrapMaxTime", {
                enumerable: true,
                get: function get() {
                    return _timeouts.setBootstrapMaxTime;
                }
            });
            Object.defineProperty(exports, "setMountMaxTime", {
                enumerable: true,
                get: function get() {
                    return _timeouts.setMountMaxTime;
                }
            });
            Object.defineProperty(exports, "setUnmountMaxTime", {
                enumerable: true,
                get: function get() {
                    return _timeouts.setUnmountMaxTime;
                }
            });
            Object.defineProperty(exports, "setUnloadMaxTime", {
                enumerable: true,
                get: function get() {
                    return _timeouts.setUnloadMaxTime;
                }
            });
            Object.defineProperty(exports, "registerApplication", {
                enumerable: true,
                get: function get() {
                    return _apps.registerApplication;
                }
            });
            Object.defineProperty(exports, "getMountedApps", {
                enumerable: true,
                get: function get() {
                    return _apps.getMountedApps;
                }
            });
            Object.defineProperty(exports, "getAppStatus", {
                enumerable: true,
                get: function get() {
                    return _apps.getAppStatus;
                }
            });
            Object.defineProperty(exports, "unloadApplication", {
                enumerable: true,
                get: function get() {
                    return _apps.unloadApplication;
                }
            });
            Object.defineProperty(exports, "checkActivityFunctions", {
                enumerable: true,
                get: function get() {
                    return _apps.checkActivityFunctions;
                }
            });
            Object.defineProperty(exports, "getAppNames", {
                enumerable: true,
                get: function get() {
                    return _apps.getAppNames;
                }
            });
            Object.defineProperty(exports, "declareChildApplication", {
                enumerable: true,
                get: function get() {
                    return _apps.declareChildApplication;
                }
            });
            Object.defineProperty(exports, "unloadChildApplication", {
                enumerable: true,
                get: function get() {
                    return _apps.unloadChildApplication;
                }
            });
            Object.defineProperty(exports, "navigateToUrl", {
                enumerable: true,
                get: function get() {
                    return _navigationEvents.navigateToUrl;
                }
            });
            Object.defineProperty(exports, "triggerAppChange", {
                enumerable: true,
                get: function get() {
                    return _reroute.reroute;
                }
            });
            Object.defineProperty(exports, "addErrorHandler", {
                enumerable: true,
                get: function get() {
                    return _appErrors.addErrorHandler;
                }
            });
            Object.defineProperty(exports, "removeErrorHandler", {
                enumerable: true,
                get: function get() {
                    return _appErrors.removeErrorHandler;
                }
            });
            Object.defineProperty(exports, "mountRootParcel", {
                enumerable: true,
                get: function get() {
                    return _mountParcel.mountRootParcel;
                }
            });
            Object.defineProperty(exports, "NOT_LOADED", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.NOT_LOADED;
                }
            });
            Object.defineProperty(exports, "LOADING_SOURCE_CODE", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.LOADING_SOURCE_CODE;
                }
            });
            Object.defineProperty(exports, "NOT_BOOTSTRAPPED", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.NOT_BOOTSTRAPPED;
                }
            });
            Object.defineProperty(exports, "BOOTSTRAPPING", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.BOOTSTRAPPING;
                }
            });
            Object.defineProperty(exports, "NOT_MOUNTED", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.NOT_MOUNTED;
                }
            });
            Object.defineProperty(exports, "MOUNTING", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.MOUNTING;
                }
            });
            Object.defineProperty(exports, "UPDATING", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.UPDATING;
                }
            });
            Object.defineProperty(exports, "MOUNTED", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.MOUNTED;
                }
            });
            Object.defineProperty(exports, "UNMOUNTING", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.UNMOUNTING;
                }
            });
            Object.defineProperty(exports, "SKIP_BECAUSE_BROKEN", {
                enumerable: true,
                get: function get() {
                    return _appHelpers.SKIP_BECAUSE_BROKEN;
                }
            });

            var _start = __webpack_require__(/*! ./start.js */ "./src/start.js");

            var _jquerySupport = __webpack_require__(/*! ./jquery-support.js */ "./src/jquery-support.js");

            var _timeouts = __webpack_require__(/*! ./applications/timeouts.js */ "./src/applications/timeouts.js");

            var _apps = __webpack_require__(/*! ./applications/apps.js */ "./src/applications/apps.js");

            var _navigationEvents = __webpack_require__(/*! ./navigation/navigation-events.js */
                "./src/navigation/navigation-events.js");

            var _reroute = __webpack_require__(/*! ./navigation/reroute.js */ "./src/navigation/reroute.js");

            var _appErrors = __webpack_require__(/*! ./applications/app-errors.js */"./src/applications/app-errors.js");

            var _mountParcel = __webpack_require__(/*! src/parcels/mount-parcel.js */ "./src/parcels/mount-parcel.js");

            var _appHelpers = __webpack_require__(/*! ./applications/app.helpers.js */
                "./src/applications/app.helpers.js");

            /***/
        }),

        /***/ "./src/start.js":
        /*!**********************!*\
          !*** ./src/start.js ***!
          \**********************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.start = start;
            exports.isStarted = isStarted;
            exports.started = void 0;

            var _reroute = __webpack_require__(/*! ./navigation/reroute.js */ "./src/navigation/reroute.js");

            var started = false;
            exports.started = started;

            function start() {
                exports.started = started = true;
                (0, _reroute.reroute)();
            }

            function isStarted() {
                return started;
            }

            var startWarningDelay = 5000;
            setTimeout(function() {
                if (!started) {
                    console.warn("singleSpa.start() has not been called, ".concat(startWarningDelay,
                        "ms after single-spa was loaded. Before start() is called, apps can be declared and loaded, but not bootstrapped or mounted. See https://github.com/CanopyTax/single-spa/blob/master/docs/single-spa-api.md#start"));
                }
            }, startWarningDelay);

            /***/
        }),

        /***/ "./src/utils/find.js":
        /*!***************************!*\
          !*** ./src/utils/find.js ***!
          \***************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            "use strict";

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.find = find;

            /* the array.prototype.find polyfill on npmjs.com is ~20kb (not worth it)
             * and lodash is ~200kb (not worth it)
             */
            function find(arr, func) {
                for (var i = 0; i < arr.length; i++) {
                    if (func(arr[i])) {
                        return arr[i];
                    }
                }

                return null;
            }

            /***/
        }),

        /***/ 0:
        /*!*********************************!*\
          !*** multi ./src/single-spa.js ***!
          \*********************************/
        /*! no static exports found */
        /***/ (function(module, exports, __webpack_require__) {

            module.exports = __webpack_require__(/*! /Users/dwheeler/Downloads/single-spa-4.0.1/src/single-spa.js */
                "./src/single-spa.js");

            /***/
        })

        /******/
    });
});
//# sourceMappingURL=single-spa.js.map
