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
