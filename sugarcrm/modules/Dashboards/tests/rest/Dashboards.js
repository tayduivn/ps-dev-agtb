let chakram = require('chakram');
let expect = chakram.expect;
let thorn = require('@sugarcrm/thorn');
let Fixtures = thorn.Fixtures;
let Agent = thorn.Agent;

describe('Dashboards', () => {
    let jane;
    let john;

    before(function*() {
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

        yield Fixtures.create(users, {module: 'Users'});
        john = Agent.as('John');
        jane = Agent.as('Jane');
    });

    after(() => {
        return Fixtures.cleanup();
    });

    describe('Accessing one\'s own dashboard', () => {
        it('should allow user to manage his own dashboard', function*() {
            let testDashboard = {
                definition: {
                    name: 'TestDashboard'
                },
                record: null
            };

            // create test
            let response = yield john.post('Dashboards', testDashboard.definition);
            expect(response).to.have.status(200);
            testDashboard.record = response.response.body;

            // read test
            response = yield john.get('Dashboards/' + testDashboard.record.id);
            expect(response).to.have.status(200);

            // edit test
            response = yield john.put('Dashboards/' + testDashboard.record.id, {name: 'UpdatedTestDashboard'});
            expect(response).to.have.status(200);
            expect(response.response.body.name).to.equal('UpdatedTestDashboard');

            // delete test
            response = yield john.delete('Dashboards/' + testDashboard.record.id);
            expect(response).to.have.status(200);

            // delete test verification
            response = yield john.get('Dashboards/' + testDashboard.record.id);
            expect(response).to.have.status(404);
        });
    });

    describe('Accessing someone else\'s dashboard', () => {
        let johnsDashboard;
        let johnsDashboardEndpoint;

        before(function*() {
            let response = yield john.post('Dashboards', {name: 'JohnsDashboard'});
            johnsDashboard = response.response.body;
            johnsDashboardEndpoint = 'Dashboards/' + johnsDashboard.id;
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
