
({
    initialize: function(options) {
        console.log('init');
        app.view.View.prototype.initialize.call(this, options);
    },


    injectLinkedin: function () {
        console.log('renderHTML');

        // Module is Contacts
        if (this.module == "Contacts") {
            //var linkedin_account = this.model.get('linkedin_account'); // TO BE ADDED
            var linkedin_account = "reidhoffman"; // mock data
            var script = "<script src='http://localhost:8888/Mango/ent/sugarcrm/clients/summer/views/linkedin/in.js' type='text/javascript'></script>";
            script += "<script type='IN/MemberProfile' data-id='http://www.linkedin.com/in/" + linkedin_account + "' data-format='inline' data-related='false'></script>";
            this.$(".linkedinmemberprofile").append(script);
        }

        // Module is Accounts
        else if (this.module == "Accounts") {
            // var linkedin_company_id = this.model.get('linkedin_company_id'); // TO BE ADDED
            var linkedin_company_id = "17345"; // mock data
            var script = "<script src='http://localhost:8888/Mango/ent/sugarcrm/clients/summer/views/linkedin/in.js' type='text/javascript'></script>";
            script += "<script type='IN/CompanyProfile' data-id='" + linkedin_company_id + "' data-format='inline'></script>";
            this.$(".linkedinmemberprofile").append(script);
        }

        // Module is Unknown
        else {
            this.$(".linkedinmemberprofile").html("No LinkedIn profile found");
        }
    },


    bindDataChange: function() {
        var self = this;
        this.model.on('change', function () { self.injectLinkedin(); }, this);
    }


})
