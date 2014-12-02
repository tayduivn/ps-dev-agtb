({
    extendsFrom: 'EnumField',
    /**
     * @returns {Field} this
     * @override
     * @private
     */
    _render: function() {
        this.items= this.model.get('cas_reassign_user_combo_box');
        this._super('_render');
    },
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
                if (_.isEmpty(this.$(this.fieldTag).data('select2'))) {
                    this.render();
                } else {
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
                    cbObject.user_id=this.model.get(this.name);

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
                    this.$(this.fieldTag).select2('val', this.model.get(this.name));
                }
            }, this);
        }
    }
})
