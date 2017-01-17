'use strict';

const _ = require('lodash');
const path = require('path');
const ciConfig = require('./ci-config.js');
const Cukes = require('@sugarcrm/seedbed');
const ciUtils = Cukes.CIUtils;
const utils = Cukes.Utils;
const chalk = utils.chalk;
const { Generator, Promise } = Cukes.PromiseGenerator;
const fs = Promise.promisifyAll(require('fs-extra'));

let zipResults = () => {
    return ciUtils.zipCiResults(
        {
            testsOutputFolders: ciConfig.resultsConfig,
            resultsFolder: ciConfig.resultsFolder
        })
        .then(() => {
            console.log(`zip artifacts: ${chalk.green('success')}`);
        });
};

module.exports = {
    run() {
        //create results folder
        fs.emptyDirSync(ciConfig.resultsFolder);

        //start CI procedure
        Generator.run(function*() {
            try {
                try {

                    yield Cukes.CI.ci(ciConfig);

                } finally {

                    yield zipResults();
                    yield fs.removeAsync(path.resolve(ciConfig.resultsFolder));
                }
            } catch (error) {
                console.log(`${error.logs || error.stack || ''}`);
                process.exit(1);
            }
        });

    }
};

//handle errors
process.on('unhandledRejection', (error, p) => {
    console.log(`Unhandled Rejection at: Promise ${p}
${error.stack || error}`);
    zipResults()
        .then(() => {
            process.exit(1);
        });
});
