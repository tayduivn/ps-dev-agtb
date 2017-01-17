var _ = require('lodash'),
    path = require('path'),
    child_process = require('child_process'),
    Cukes = require('@sugarcrm/seedbed'),
    async = require('async'),
    utils = Cukes.Utils;

//runs as soon as log in page is loaded and metadata that is available at that moment saved
seedbed.addAsyncHandler(seedbed.events.BEFORE_INIT, () => {

    _.each(seedbed.metaBootstrap.modules, (module, moduleName) => {
        if (_.includes(['Login'], moduleName)) {
            seedbed.meta.modules[moduleName] = module;
            seedbed.defineComponent(moduleName, moduleName + 'Layout', {module: moduleName});
        }
    });
});

// is called after cukes init, one time
seedbed.addAsyncHandler(seedbed.events.AFTER_INIT, () => {

    //create default layouts using synced meta.
    //cache layouts that doesn't have modules

    //cache the default users as records
    _.each(_.keys(seedbed.users), (user) => {
        if (!seedbed.cachedRecords.contains(user)) {
            seedbed.cachedRecords.push(utils.capitalize(user) + "User",  {id: seedbed.users[user], module: "Users"});
        }
    });

    //cache drawers for modules
    _.each(seedbed.meta.modules, (module, moduleName) => {

        seedbed.defineComponent(moduleName + "List", 'ListLayout', { module : moduleName });

        // If module supports "RecordLayout" let's pre-create it
        if (module.views && module.views['record']) {
            seedbed.defineComponent(moduleName + "Record", 'RecordLayout', { module : moduleName});
        }
    });

});

// is called before tests run
seedbed.addAsyncHandler(seedbed.events.BEFORE_RUN, (config) => {

    _.extend(seedbed, {
        users: require(path.resolve(__dirname, './support/default-users')),
        classes: require(path.resolve(__dirname, './support/class-loader'))
    });

});

seedbed.addAsyncHandler(seedbed.events.AFTER_SCENARIO, () => {

    //clean cachedRecords and cachedViews
    var recordsToRemove = [];
    seedbed.cachedRecords.iterate(function(record, alias) {
        if (!(record.module && _.includes(['Users', 'Teams'], record.module))) {
            recordsToRemove.push(alias);
        }
    }, this);

    recordsToRemove.forEach(function(alias) {
        seedbed.cachedRecords.remove(alias);
        delete seedbed.components[alias + 'Record'];
    });

});

/**
 * After login we need to define layouts
 * based on cached records test created
 */
seedbed.addAsyncHandler(seedbed.events.LOGIN, () => {
    seedbed.cachedRecords.iterate(function (record, recordAlias) {

        if (record.module) {

            // Define Detail Layout for cached record
            seedbed.defineComponent(recordAlias + 'Record', 'RecordLayout', {
                module: record.module,
                id: record.id,
                record : record
            });

            seedbed.$$(record.module + 'List').$$('ListView').createListItem(record);
        }

    }, this);

});

// is called after waitForApp, each time
seedbed.addAsyncHandler(seedbed.events.SYNC, (clientInfo) => {

    //we need this logic only for offline mode
    var item = _.last(clientInfo.create);

    if (item) {
        //find record info for created record
        var recordInfo = _.find(seedbed.scenario.recordsInfo, function (recordInfo) {
            /*
             We need to make sure we find correct record to be updated
             Why need this fix: Sugar do POST requests on Dashboards to create them, if not available (for new installs)
             Those POST requests are pushed to clientInfo.create and assigned to wrong seedbed.scenario.recordsInfo[] elements
             */
            return !recordInfo.recordId && item._module && item._module == recordInfo.module;
        });

        if (recordInfo && !seedbed.cachedRecords.contains(recordInfo.uid)) {

            seedbed.cachedRecords.push(
                recordInfo.uid,
                {
                    input: recordInfo.input,
                    id: item.id,
                    module: recordInfo.module
                }
            );

            seedbed.defineComponent(recordInfo.uid + 'Record', 'RecordLayout', {
                module: recordInfo.module,
                id: item.id,
                record: item
            });

        }
    }
});

seedbed.addAsyncHandler(seedbed.events.RESPONSE, (data, req, res) => {

    var url = req.url,
        responseData;

    //Cache Activities records when Activities stream is loaded
    if ((parseInt(res.statusCode) === 200) &&
        _.includes(['POST', 'PUT'], req.method) &&
        !/(oauth2|bulk|filter)/.test(url)) {

        responseData = JSON.parse(data.buffer.toString());

        var responseRecord = responseData.related_record || responseData;

        if (_.includes(['POST'], req.method) && url.indexOf('/file/filename') === -1) {

            //find record info for created record
            var recordInfo = _.find(seedbed.scenario.recordsInfo, function (recordInfo) {
                return responseRecord && responseRecord.id && responseRecord.id === recordInfo.recordId;
            });

            //save record in cachedRecords by uid
            if (recordInfo && recordInfo.uid) {

                var record = seedbed.cachedRecords.push(recordInfo.uid, {
                    input: recordInfo.input,
                    id: responseRecord.id,
                    module: recordInfo.module
                });

                seedbed.defineComponent(recordInfo.uid + 'Record', 'RecordLayout', {
                    module: record.module,
                    id: record.id,
                    record: record
                });

            }
        }
    }
});
