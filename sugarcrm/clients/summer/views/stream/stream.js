({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    mockData: [
        {
            name: "Peter",
            verb: "created",
            context: "a new Opportunity"
        },
        {
            name: "Majed",
            verb: "deleted",
            context: "the opportunity 1000 Bump Nuts"
        },
        {
            name: "MJ",
            verb: "edited",
            contexted: "the Account Appo Computers"
        },
        {
            name: "Jee",
            verb: "edited",
            context: "the Contact Steve JOBs"
        }
    ]
})