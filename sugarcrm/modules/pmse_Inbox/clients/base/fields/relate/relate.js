({
    extendsFrom: 'RelateField',
    /**
     * @returns {Field} this
     * @override
     * @private
     */
    getSizeOBJ: function(obj){
                var size = 0, key;
                for (key in obj) {
                        if (obj.hasOwnProperty(key)) size++;
                }
                return size;
    },
    /**
     * {@inheritDoc}
     * Avoid rendering process on select2 change in order to keep focus.
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:' + this.name, function() {
                if (!_.isEmpty(this.$(this.fieldTag).data('select2'))) {
                    // Just setting the value on select2 doesn't cause the label to show up
                    // so we need to render the field next after setting this value
                    this.$(this.fieldTag).select2('val', this.model.get(this.name));
                }
                // double-check field isn't disposed before trying to render
                if (!this.disposed) {
//                    this.render();
                    if(this.model.get(this.name).length>0){
                        var cbObject = new Object();
                    var cbData,count= 0;
                    if(this.getSizeOBJ(window.globalObjectUser)>0){
                        cbData=window.globalObjectUser;
                    }
                    else
                    {
                        cbData = {};
                    }
                        cbObject.cas_id=this.model.get('cas_id');
                        cbObject.cas_index=this.model.get('cas_index');
                        //cbObject.user_id=this.model.get(this.name);
                        cbObject.user_id=this.model.changed['id'];

                    for (var cnKey in cbData) {
                        var newData = cbData[cnKey];
                        for(var aux in newData){
                            if(newData.cas_id===cbObject.cas_id && newData.cas_index===cbObject.cas_index){
                                newData.user_id=cbObject.user_id;
                                count++;
                            }
                        }
                    }
                    if(count===0){
                        cbData[this.getSizeOBJ(cbData)]=cbObject;
                    }
                    window.globalObjectUser=cbData;
//                    console.log('DATA->',cbData);
                    }
                    this.render();
                }
            }, this);
        }
    }
})
