describe('Underscore Mixins', function() {

    describe('_moveItem', function() {
        var order = [];

        beforeEach(function() {
            order = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        });

        it('should move F before A', function() {
            expect(_.moveIndex(order, 5, 0)).toEqual(['F', 'A', 'B', 'C', 'D', 'E', 'G', 'H']);
        });

        it('should move F before B', function() {
            expect(_.moveIndex(order, 5, 1)).toEqual(['A', 'F', 'B', 'C', 'D', 'E', 'G', 'H']);
        });

        it('should move F before D', function() {
            expect(_.moveIndex(order, 5, 3)).toEqual(['A', 'B', 'C', 'F', 'D', 'E', 'G', 'H']);
        });

        it('should move F before E', function() {
            expect(_.moveIndex(order, 5, 4)).toEqual(['A', 'B', 'C', 'D', 'F', 'E', 'G', 'H']);
        });

        it('should move F before F (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 5, 5)).toEqual(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        });

        it('should move F after F (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 5, 6)).toEqual(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        });

        it('should move F before H', function() {
            expect(_.moveIndex(order, 5, 7)).toEqual(['A', 'B', 'C', 'D', 'E', 'G', 'F', 'H']);
        });

        it('should move A before A (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 0, 0)).toEqual(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        });

        it('should move H before H (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 7, 7)).toEqual(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        });

        it('should move H after H (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 7, 8)).toEqual(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        });

        it('should move F after H', function() {
            expect(_.moveIndex(order, 5, 8)).toEqual(['A', 'B', 'C', 'D', 'E', 'G', 'H', 'F']);
        });
    });
});
