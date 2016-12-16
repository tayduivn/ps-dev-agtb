let chakram = require('chakram');
let expect = chakram.expect;
let thorn = require('@sugarcrm/thorn');
let Fixtures = thorn.Fixtures;
let Agent = thorn.Agent;

describe('Dashboards', () => {
    let jane;
    let john;

    before(() => {
        let users = [
            {
                attributes: {
                    user_name: 'John'
                }
            },
            {
                attributes: {
                    user_name: 'Jane'
                }
            }
        ];

        return Fixtures.create(users, {module: 'Users'})
            .then(() => {
                john = Agent.as('John');
                jane = Agent.as('Jane');
            });
    });

    after(() => {
        return Fixtures.cleanup();
    });

    describe('Accessing one\'s own dashboard', () => {
        it('should allow user to manage his own dashboard', () => {
            let testDashboard = {
                definition: {
                    name: 'TestDashboard'
                },
                record: null
            };

            return john.post('Dashboards', testDashboard.definition)
                .then((response) => {
                    // create test
                    expect(response).to.have.status(200);
                    testDashboard.record = response.response.body;

                    return john.get('Dashboards/' + testDashboard.record.id);
                })
                .then((response) => {
                    // read test
                    expect(response).to.have.status(200);

                    return john.put('Dashboards/' + testDashboard.record.id, {name: 'UpdatedTestDashboard'});
                })
                .then((response) => {
                    // edit test
                    expect(response).to.have.status(200);
                    expect(response.response.body.name).to.equal('UpdatedTestDashboard');

                    return john.delete('Dashboards/' + testDashboard.record.id);
                })
                .then((response) => {
                    // delete test
                    expect(response).to.have.status(200);

                    return john.get('Dashboards/' + testDashboard.record.id);
                })
                .then((response) => {
                    // delete test verification
                    expect(response).to.have.status(404);
                });
        });
    });

    describe('Accessing someone else\'s dashboard', () => {
        let johnsDashboard;
        let johnsDashboardEndpoint;

        before(() => {
            return john.post('Dashboards', {name: 'JohnsDashboard'})
                .then((response) => {
                    johnsDashboard = response.response.body;
                    johnsDashboardEndpoint = 'Dashboards/' + johnsDashboard.id;
                });
        });

        after(() => {
            return john.delete(johnsDashboardEndpoint);
        });

        it('should not let a user view another user\'s dashboard', () => {
            return expect(jane.get(johnsDashboardEndpoint)).to.have.status(404);
        });

        it('should not let a user edit another user\'s dashboard', () => {
            return expect(jane.put(johnsDashboardEndpoint, {name: 'UpdateName'})).to.have.status(404);
        });

        it('should not let a user delete another user\'s dashboard', () => {
            return expect(jane.delete(johnsDashboardEndpoint)).to.have.status(404);
        });
    });
});
