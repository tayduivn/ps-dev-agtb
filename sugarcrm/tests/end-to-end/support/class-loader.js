'use strict';

const _ = require('lodash');
const path = require('path');
const Cukes = require('@sugarcrm/seedbed');
const utils = Cukes.Utils;
const _s = require('underscore.string');

let views = [];
utils.getFilesRecursiveSync(path.resolve(__dirname, '../views'), views);
utils.getFilesRecursiveSync(path.resolve(__dirname, '../layouts'), views);
utils.getFilesRecursiveSync(path.resolve(__dirname, '../components'), views);
utils.getFilesRecursiveSync(path.resolve(__dirname, '../fields'), views);

let classes = {};

_.each(views, (viewPath) => {
    let fileName = _.last(viewPath.split('/'));
    let viewName = _s.classify(fileName.split('.js')[0]);

    if (classes[viewName]) {
        throw new Error('Class name: ' + viewName + ' already defined, please check');
    }

    classes[viewName] = require(path.normalize(viewPath));
});

module.exports = classes;
