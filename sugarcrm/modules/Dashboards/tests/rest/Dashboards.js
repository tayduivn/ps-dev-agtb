const expect = require('chakram').expect;
const {Agent, Fixtures} = require('@sugarcrm/thorn');

describe('Dashboards', function() {
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
        this.john = Agent.as('John');
        this.jane = Agent.as('Jane');
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    describe('Accessing one\'s own dashboard', function() {
        it('should allow user to manage his own dashboard', function*() {
            let testDashboard = {
                definition: {
                    name: 'TestDashboard'
                },
                record: null
            };

            // create test
            let response = yield this.john.post('Dashboards', testDashboard.definition);
            expect(response).to.have.status(200);
            testDashboard.record = response.response.body;

            // read test
            response = yield this.john.get('Dashboards/' + testDashboard.record.id);
            expect(response).to.have.status(200);

            // edit test
            response = yield this.john.put('Dashboards/' + testDashboard.record.id, {name: 'UpdatedTestDashboard'});
            expect(response).to.have.status(200);
            expect(response.response.body.name).to.equal('UpdatedTestDashboard');

            // delete test
            response = yield this.john.delete('Dashboards/' + testDashboard.record.id);
            expect(response).to.have.status(200);

            // delete test verification
            try {
                yield this.john.get('Dashboards/' + testDashboard.record.id);
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });
    });

    describe('Accessing someone else\'s dashboard', function() {
        before(function*() {
            let response = yield this.john.post('Dashboards', {name: 'JohnsDashboard'});
            this.johnsDashboard = response.response.body;
            this.johnsDashboardEndpoint = 'Dashboards/' + this.johnsDashboard.id;
        });

        after(function*() {
            yield this.john.delete(this.johnsDashboardEndpoint);
        });

        it('should not let a user view another user\'s dashboard', function*() {
            try {
                yield this.jane.get(this.johnsDashboardEndpoint);
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });

        it('should not let a user edit another user\'s dashboard', function*() {
            try {
                yield this.jane.put(this.johnsDashboardEndpoint, {name: 'UpdateName'});
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });

        it('should not let a user delete another user\'s dashboard', function*() {
            try {
                yield this.jane.delete(this.johnsDashboardEndpoint);
            } catch (response) {
                expect(response).to.have.status(404);
            }
        });
    });
});
