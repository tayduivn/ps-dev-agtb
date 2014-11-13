({
    className: 'emailtemplates',


    loadData: function () {
        //console.log(this);
        this.et_uid = this.options.context.attributes.modelId;
        var self = this;
        App.api.call("read", App.api.buildURL("pmse_Emails_Templates/getFields", null, {id: this.et_uid }), {}, {
            success: function (a) {
                //console.log(a);
                self.et_uid = self.options.context.attributes.modelId;
                self.body = a.body;
                self.bodyHtml = a.body_html;
                self.templateName = a.name;
                self.templateDescription = a.description;
                self.fromName = a.from_name;
                self.fromAddres = a.from_address;
                self.subject = a.subject;

                self.targetFields = a.fields;
                self.relatedModules = a.related_modules;
                self.targetModule = a.base_module;


                self.render();
                $(init(self));
            }
        });


        //
    }

})