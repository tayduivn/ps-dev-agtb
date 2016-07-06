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
describe('Data.Base.NotificationCenter', function() {
    var app, model, module = 'NotificationCenter', sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.declareData('base', 'NotificationCenter', true, false);
        model = app.data.createBean(module, {module_name: module});
        model.set('configMode', 'user');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        model = null;
        sandbox.restore();
    });

    using('methods list',
        ['_copyFiltersFromDefault', '_copyCarriersStatusFromDefault', 'replaceDefaultToActualValues',
            'setSelectedAddresses', 'updateCarriersAddresses'],
        function(method) {
            it('should stop method execution if personal settings are not found', function() {
                model.set('configMode', 'default');
                model.set('personal', undefined);
                expect(model[method]()).toBeUndefined();
            });
        }
    );

    describe('_copyFiltersFromDefault', function() {
        var global, personal;

        beforeEach(function() {
            global = {
                emitter1: {
                    event1: {
                        filter1: [Math.round(Math.random() * 1000), Math.round(Math.random() * 1000)]
                    },
                    event2: {
                        filterA: [Math.round(Math.random() * 1000), Math.round(Math.random() * 1000)],
                        filterB: [Math.round(Math.random() * 1000)]
                    }
                },
                emitter2: {
                    event1: {
                        filter1: [Math.round(Math.random() * 1000)],
                        filter2: []
                    }
                }
            };
            personal = {
                emitter1: {
                    event1: {
                        filter1: [Math.round(Math.random() * 1000)]
                    },
                    event2: {
                        filterA: [Math.round(Math.random() * 1000)],
                        filterB: []
                    }
                },
                emitter2: {
                    event1: {
                        filter1: [],
                        filter2: [Math.round(Math.random() * 1000)]
                    }
                }
            };

            model.set('global', {config: global});
            model.set('personal', {config: personal});
        });

        it('should copy filters from global emitter and event', function() {
            model._copyFiltersFromDefault('emitter1', 'event1');
            var result = model.get('personal')['config'];

            expect(result.emitter1.event1).toEqual(global.emitter1.event1);
            expect(result.emitter1.event2).toEqual(personal.emitter1.event2);
            expect(result.emitter2).toEqual(personal.emitter2);
        });

        it('should copy all events from global emitter', function() {
            model._copyFiltersFromDefault('emitter1');
            var result = model.get('personal')['config'];

            expect(result.emitter1).toEqual(global.emitter1);
            expect(result.emitter2).toEqual(personal.emitter2);
        });

        it('should copy all emitter from global', function() {
            model._copyFiltersFromDefault();
            var result = model.get('personal')['config'];

            expect(result.emitter1).toEqual(global.emitter1);
            expect(result.emitter2).toEqual(global.emitter2);
        });
    });

    describe('_copyCarriersStatusFromDefault', function() {
        it('should replace statuses of personal carriers with statuses of global ones', function() {
            var global = {
                foo: {status: false},
                bar: {status: false}
            };
            var personal = {
                foo: {status: true},
                bar: {status: true}
            };

            model.set('global', {carriers: global});
            model.set('personal', {carriers: personal});

            model._copyCarriersStatusFromDefault();

            expect(model.get('personal')['carriers']['foo']['status']).toBeFalsy();
            expect(model.get('personal')['carriers']['bar']['status']).toBeFalsy();
        });
    });

    describe('replaceDefaultToActualValues', function() {
        var mock;

        beforeEach(function() {
            mock = sandbox.mock(model);
        });

        using('emitter data',
            [
                {
                    'case': 'Empty emitter',
                    config: {emitter1: {}},
                    calledTimes: 0
                },
                {
                    'case': 'Empty event',
                    config: {emitter1: {event1: {}}},
                    calledTimes: 0
                },
                {
                    'case': 'Filter with "default" string as data',
                    config: {emitter1: {event1: {filter1: 'default'}}},
                    calledTimes: 1
                },
                {
                    'case': 'Filter with "default" string as data',
                    config: {emitter1: {event1: {filter1: [['foo', '']]}}},
                    calledTimes: 0
                },
                {
                    'case': 'Filter with no values',
                    config: {emitter1: {event1: {filter1: []}}},
                    calledTimes: 0
                },
                {
                    'case': '2 filters with "default" string as data',
                    config: {emitter1: {event1: {filter1: 'default'}, event2: {filter1: 'default'}}},
                    calledTimes: 2
                },
                {
                    'case': 'Filter with "default" string as data & filter with data',
                    config: {emitter1: {event1: {filter1: 'default'}, event2: {filter1: [['foo', '']]}}},
                    calledTimes: 1
                },
                {
                    'case': '2 Emitters with 2 events that have 3 filters with "default" string as data',
                    config: {
                        emitter1: {event1: {filter1: 'default'}, event2: {filter1: 'default'}},
                        emitter2: {event1: {filter1: [['foo', 0]]}, event2: {filter1: 'default'}}
                    },
                    calledTimes: 3
                }
            ],
            function(data) {
                it('should call _copyFiltersFromDefault() ' + data.calledTimes + ' times in case of ' + data.case, function() {
                    mock.expects('_copyFiltersFromDefault').exactly(data.calledTimes);
                    model.set('personal', {config: data.config});
                    model.replaceDefaultToActualValues();
                    mock.verify();
                });
            }
        );

        it('should call _copyFiltersFromDefault() with emitter and event names', function() {
            mock.expects('_copyFiltersFromDefault').once().withArgs('emitter1', 'event1');
            model.set('personal', {config: {emitter1: {event1: {filter1: 'default'}}}});
            model.replaceDefaultToActualValues();
            mock.verify();
        });
    });

    describe('isEmitterDefaultConfigured', function() {
        using('emitters under comparison data',
            [
                {
                    'case': 'Empty emitters',
                    global: {},
                    personal: {},
                    result: true
                },
                {
                    'case': 'Emitters with empty marker',
                    global: {A: {event1: {filter1: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', '']]}}},
                    result: true
                },
                {
                    'case': 'Emitters with empty marker, and address marker',
                    global: {A: {event1: {filter1: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', 1]]}}},
                    result: true
                },
                {
                    'case': 'Emitters with 2 filters that have only empty markers',
                    global: {A: {event1: {filter1: [['foo', '']], filter2: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', '']], filter2: [['foo', '']]}}},
                    result: true
                },
                {
                    'case': 'Emitters with 2 filters that have different carriers',
                    global: {A: {event1: {filter1: [['foo', '']], filter2: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', '']], filter2: [['bar', '']]}}},
                    result: false
                },
                {
                    'case': 'Emitters 1 filter that have 2 carriers',
                    global: {A: {event1: {filter1: [['foo', ''], ['bar', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', ''], ['bar', '']]}}},
                    result: true
                },
                {
                    'case': 'Emitters with different carriers',
                    global: {A: {event1: {filter1: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['bar', '']]}}},
                    result: false
                },
                {
                    'case': 'Emitters with different number of carriers',
                    global: {A: {event1: {filter1: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', ''], ['bar', '']]}}},
                    result: false
                },
                {
                    'case': 'Emitters with 2 events that have filter with the same carriers',
                    global: {A: {event1: {filter1: [['foo', '']]}, event2: {filter1: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', '']]}, event2: {filter1: [['foo', '']]}}},
                    result: true
                },
                {
                    'case': 'Emitters with 2 events that have filter with the different carriers',
                    global: {A: {event1: {filter1: [['foo', '']]}, event2: {filter1: [['foo', '']]}}},
                    personal: {A: {event1: {filter1: [['foo', '']]}, event2: {filter1: [['bar', '']]}}},
                    result: false
                }
            ],
            function(data) {
                it('should detect if personal emitter has all settings by default or not comparing ' + data.case, function() {
                    model.set('global', {config: data.global});
                    model.set('personal', {config: data.personal});
                    expect(model.isEmitterDefaultConfigured('A')).toBe(data.result);
                });
            }
        );
    });

    describe('resetToDefault', function() {
        var mock;

        beforeEach(function() {
            model.set('personal', {carriers: {foo: {}}, config: {bar: {}}});
            model.set('personal', {config: {emitter1:{}}});
            model.set('global', {config: {emitter1:{}}});
            mock = sandbox.mock(model);
        });

        it('should reset statuses of all carriers in case of reset-all', function() {
            mock.expects('_copyFiltersFromDefault').once().withExactArgs();
            model.resetToDefault('all');
            mock.verify();
        });

        it('should reset selected addresses in case of reset-all', function() {
            mock.expects('setSelectedAddresses').once();
            model.resetToDefault('all');
            mock.verify();
        });

        it('should only copy data only of a given emitter in case of reset-emitter', function() {
            mock.expects('_copyFiltersFromDefault').once().withExactArgs('emitter1');
            mock.expects('_copyCarriersStatusFromDefault').never();
            mock.expects('setSelectedAddresses').never();
            model.resetToDefault('emitter1');
            mock.verify();
        });

        it('should trigger reset event', function() {
            mock.expects('trigger').once().withArgs('reset:foo');
            model.resetToDefault('foo');
            mock.verify();
        });
    });

    describe('setSelectedAddresses', function() {
        using('emitter data & selected addresses',
            [
                {
                    'case': 'Empty emitter & and selectable carrier',
                    emitters: {emitter1: {}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        }
                    },
                    result: {foo: ['0']}
                },
                {
                    'case': 'Empty emitter & and not selectable carrier',
                    emitters: {emitter1: {}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'none'}
                        }
                    },
                    result: {}
                },
                {
                    'case': 'Emitter with empty event',
                    emitters: {emitter1: {event1: {}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        }
                    },
                    result: {foo: ['0']}
                },
                {
                    'case': 'Emitter with "default" string filter',
                    emitters: {emitter1: {event1: {filter1: 'default'}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        }
                    },
                    result: {foo: ['0']}
                },
                {
                    'case': 'Emitter with filter that has empty marker',
                    emitters: {emitter1: {event1: {filter1: [['foo', '']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        }
                    },
                    result: {foo: ['0']}
                },
                {
                    'case': 'Emitter with filter that has empty and address marker',
                    emitters: {emitter1: {event1: {filter1: [['foo', ''], ['foo', '0']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        }
                    },
                    result: {foo: ['0']}
                },
                {
                    'case': 'Emitter with filter that has empty and 2 address markers',
                    emitters: {emitter1: {event1: {filter1: [['foo', ''], ['foo', '0'], ['foo', '1']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0, 1: 1}
                        }
                    },
                    result: {foo: ['0', '1']}
                },
                {
                    'case': 'Emitter with filter that has only address marker',
                    emitters:  {emitter1: {event1: {filter1: [['foo', '1']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0, 1: 1}
                        }
                    },
                    result: {foo: ['1']}
                },
                {
                    'case': 'Emitter with filter that has only 2 address markers',
                    emitters: {emitter1: {event1: {filter1: [['foo', '1']], filter2: [['foo', '1']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0, 1: 1}
                        }
                    },
                    result: {foo: ['1']}
                },
                {
                    'case': 'Emitter with filter that has 2 empty markers',
                    emitters: {emitter1: {event1: {filter1: [['foo', ''], ['bar', '']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        },
                        bar: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        }
                    },
                    result: {foo: ['0'], bar: ['0']}
                },
                {
                    'case': 'Emitter with 2 events with filters that have only empty markers',
                    emitters: {emitter1: {event1: {filter1: [['foo', '']]}, event2: {filter1: [['foo', '']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0}
                        }
                    },
                    result: {foo: ['0']}
                },
                {
                    'case': 'Emitter with 2 events with filters that have only address markers',
                    emitters: {emitter1: {event1: {filter1: [['foo', '0']]}, event2: {filter1: [['foo', '1']]}}},
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0, 1: 1}
                        }
                    },
                    result: {foo: ['0', '1']}
                },
                {
                    'case': 'Two emitters with 2 events with filters that have same address markers and empty ones',
                    emitters: {
                        emitter1: {event1: {filter1: []}, event2: {filter1: [['foo', '1']]}},
                        emitter2: {event1: {filter1: [['bar', '0']]}, event2: {filter1: [['foo', '1']]}}
                    },
                    carriers: {
                        foo: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0, 1: 1}
                        },
                        bar: {
                            options: {deliveryDisplayStyle: 'select'},
                            addressTypeOptions: {0: 0, 1: 1}
                        }
                    },
                    result: {foo: ['1'], bar: ['0']}
                }
            ],
            function(data) {
                it('should set proper selectedAddresses attribute in model in case of ' + data.case, function() {
                    model.set('personal', {carriers: data.carriers, config: data.emitters});
                    model.setSelectedAddresses();
                    expect(model.get('selectedAddresses')).toEqual(data.result);
                });
            }
        );
    });

    describe('updateCarriersAddresses', function() {
        using('selected addresses, configuration',
            [
                {
                    'case': 'Emitter with one carrier with empty marker',
                    addresses: {foo: ['0']},
                    config: {emitter1: {event1: {filter1: [['foo', '']]}}},
                    result: {emitter1: {event1: {filter1: [['foo', '0']]}}}
                },
                {
                    'case': 'Emitter with one carrier with empty and address markers',
                    addresses: {foo: ['0', '1']},
                    config: {emitter1: {event1: {filter1: [['foo', '']]}}},
                    result: {emitter1: {event1: {filter1: [['foo', '0'], ['foo', '1']]}}}
                },
                {
                    'case': 'Emitter with unknown carrier with empty marker',
                    addresses: {foo: ['0']},
                    config: {emitter1: {event1: {filter1: [['bar', '']]}}},
                    result: {emitter1: {event1: {filter1: [['bar', '']]}}}
                },
                {
                    'case': 'Emitter with with one address marker that repeats',
                    addresses: {foo: ['0']},
                    config: {emitter1: {event1: {filter1: [['foo', '0']]}}},
                    result: {emitter1: {event1: {filter1: [['foo', '0']]}}}
                },
                {
                    'case': 'Emitter with 2 address markers that do repeat',
                    addresses: {foo: ['0', '1']},
                    config: {emitter1: {event1: {filter1: [['foo', '1']]}}},
                    result: {emitter1: {event1: {filter1: [['foo', '0'], ['foo', '1']]}}}
                },
                {
                    'case': 'Emitter with 2 filters with an empty & address markers that do repeat',
                    addresses: {foo: ['0']},
                    config: {emitter1: {event1: {filter1: [['foo', '']], filter2: [['foo', '']]}}},
                    result: {emitter1: {event1: {filter1: [['foo', '0']], filter2: [['foo', '0']]}}}
                },
                {
                    'case': 'Emitter with 2 events with one empty and one address marker that repeat',
                    addresses: {foo: ['0']},
                    config: {emitter1: {event1: {filter1: [['foo', '']]}, event2: {filter1: [['foo', '']]}}},
                    result: {emitter1: {event1: {filter1: [['foo', '0']]}, event2: {filter1: [['foo', '0']]}}}
                }
            ],
            function(data) {
                it('should put carrier-array into filters for every selected address in case of ' + data.case, function() {
                    model.set('selectedAddresses', data.addresses);
                    model.set('personal', {config: data.config});
                    model.updateCarriersAddresses();
                    expect(model.get('personal')['config']).toEqual(data.result);
                });
            }
        );
    });
});
