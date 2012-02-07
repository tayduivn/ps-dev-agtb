

describe('SUGAR.sidecar.MetadataManager',function(){
    it('exists',function(){
        expect(typeof(SUGAR.sidecar.MetadataManager)).toBe('object');
    });
    it('gets vardefs' ,function(){
        expect(SUGAR.sidecar.MetadataManager.get({
            type: "vardef",
            module: "Contacts"
        })).toBe(fixtures.vardefs.Contact);
    });
    it('gets viewdefs' ,function(){
        expect(SUGAR.sidecar.MetadataManager.get({
            type: "vardef",
            module: "Contacts"
        })).toBe(fixtures.vardefs.Contact);
    });
    it('gets layoutdefs' ,function(){
        expect(SUGAR.sidecar.MetadataManager.get({
            type: "vardef",
            module: "Contacts"
        })).toBe(fixtures.vardefs.Contact);
    });
});