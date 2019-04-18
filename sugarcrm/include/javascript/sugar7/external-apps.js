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
(function(app) {
    var addCompToLayout = function(metadata, targetModule, targetLayout, def) {
        var modMeta = metadata.modules[targetModule];
        if (modMeta) {
            modMeta.layouts = modMeta.layouts || {};
            if (!modMeta.layouts[targetLayout]) {
                // Merge with the global metadata before modifying if the module
                // didn't originally specify a config for this layout.
                modMeta.layouts[targetLayout] = {
                    meta: app.metadata.getLayout('', targetLayout) || {components: []}
                };
            }
            if (!modMeta.layouts[targetLayout].meta) {
                modMeta.layouts[targetLayout].meta = {};
            }
            if (!modMeta.layouts[targetLayout].meta.components) {
                modMeta.layouts[targetLayout].meta.components = [];
            }

            modMeta.layouts[targetLayout].meta.components.push(def);
        }
    };

    app.metadata.addSyncTask(function(metadata, options) {
        if (options.getPublic) {
            console.log('skipping external app sync for public metadata');
            return Promise.resolve();
        }

        if (app.config.externalManifestUrl && app.config.externalManifestUrl !== '') {
            var iframeOrigin = app.config.externalManifestUrl.match(/^.+\:\/\/[^\/]+/)[0];
            var getManifest = function(onSuccess, onError, onLogin) {
                fetch(app.config.externalManifestUrl, {
                    headers: {'Accept': 'application/json'},
                    credentials: 'include'
                }).then(
                    function(response) {
                        response.json().then(function(manifest) {
                            if (manifest.loginRedirect && onLogin) {
                                onLogin(manifest.loginRedirect);
                            } else {
                                onSuccess(manifest);
                            }
                        }).catch(function(error) {
                            onError(error);
                        });
                    }
                ).catch(function(error) {
                    onError(error);
                });
            };

            return new Promise(function(res, error) {
                var handleManifest = function(manifest) {
                    _.each(manifest.layouts, function(def) {
                        if (def.module && def.layout) {
                            addCompToLayout(metadata, def.module, def.layout, {
                                view: {
                                    type: 'external-app',
                                    name: manifest.name,
                                    src: manifest.src
                                }
                            });
                        }
                    });
                    res();
                };

                var onError = function(err) {
                    app.logger.error(err.message);
                    res();
                };

                getManifest(handleManifest, onError, function(loginUrl) {
                    var iframe = document.createElement('iframe');

                    var cleanup = function() {
                        iframe.parentElement.removeChild(iframe);
                        window.removeEventListener('message', eventCallback);
                    };

                    var eventCallback = function(event) {
                        // TODO: Verify the manifest service origin instead of assuming any
                        // origin besides the one we are on is correct
                        if (event.origin === iframeOrigin) {
                            console.log('Event origin was ' + event.origin);
                            console.log(`iframeOrigin was ${iframeOrigin}`);
                            cleanup();
                            //After the iframe event callback, we need to load the manifest again but this time expect to get data.
                            getManifest(handleManifest, onError, function(url) {
                                error('Unable to authenticate with manifest service: Second Login URL:' + url);
                            });
                        }
                    };

                    iframe.onload = function() {
                        console.log('loaded before we got the event');
                        cleanup();
                        res();
                    };
                    iframe.src = loginUrl;
                    iframe.style = 'display:none;\n' +
                        'position: absolute;\n' +
                        'width: 500px;\n' +
                        'height: 500px;\n' +
                        'top: calc(50% - 250px);\n' +
                        'left: calc(50% - 250px);';

                    window.addEventListener('message', eventCallback);
                    document.body.appendChild(iframe);
                });
            });
        } else {
            return Promise.resolve();
        }
    });
})(SUGAR.App);
