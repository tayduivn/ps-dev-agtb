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
const chakram = require('chakram');
const expect = chakram.expect;
const {Agent, Fixtures} = require('@sugarcrm/thorn');

describe('Discovery', function() {
    before(function*() {
        this.url = `${process.env.THORN_SERVER_URL}/rest/v11`;
    });

    it('should get discovery info', function*() {
        let response = yield chakram.get(`${this.url}/discovery`);

        expect(response).to.have.status(200);
        expect(response).to.have.json('idmMode', false);
    });
});
