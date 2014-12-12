//***********************other class******************************************************//
describe("PMUI.util.ArrayList", function () {
    var al, testItem1, testItem2, testItem3, testItem4, testItem5,
        Item = function (id) {
            this.id = id;
        };

    beforeEach(function () {
        al = new jCore.ArrayList();
        testItem1 = new Item(123);
        testItem2 = new Item(336);
        testItem3 = new Item(0);
        testItem4 = new Item(77);
        testItem5 = new Item(108);
    });
    describe('method "asArray"', function () {
        it("should return the ArrayList", function () {
        var notArray = {};
        //testing _.isArray function
        expect(Object.prototype.toString.call(notArray) === '[object Array]').toBeFalsy();
        expect(Object.prototype.toString.call(al.asArray()) === '[object Array]').toBeTruthy();
        });
        it("should accept create a new object with constructor", function () {
        expect(typeof al).toEqual('object');
        expect(al.id).toBeDefined();
    });
    });

    describe('method "insert"', function () {
        it("should insert any item to the ArrayList at the end",
        function () {
            var textArray1 = [testItem1],
                textArray2 = [testItem1, testItem2],
                textArray3 = [testItem1, testItem2, testItem5];

            expect(al.insert(testItem1).asArray()).toEqual(textArray1);
            expect(al.insert(testItem2).asArray()).toEqual(textArray2);
            expect(al.insert(testItem5).asArray()).toEqual(textArray3);
        });
    });

    describe('method "get"', function () {
        it("should retrieve the item in the index position",
        function () {
            al.insert(testItem1);
            al.insert(testItem2);
            al.insert(testItem4);
            al.insert(testItem3);
            expect(al.get(0)).toEqual(testItem1);
            expect(al.get(1)).toEqual(testItem2);
            expect(al.get(2)).toEqual(testItem4);
            expect(al.get(3)).toEqual(testItem3);
        });
    });

    describe('method "indexOf"', function () {
        it("should return the first the index of an item found",
        function () {
            al.insert(testItem1);
            al.insert(testItem2);
            al.insert(testItem4);
            al.insert(testItem3);
            al.insert(testItem2);
            al.insert(testItem5);
            expect(al.indexOf(testItem1)).toEqual(0);
            expect(al.indexOf(testItem2)).toEqual(1);
            expect(al.indexOf(testItem3)).toEqual(3);
            expect(al.indexOf(testItem4)).toEqual(2);
            expect(al.indexOf(testItem5)).toEqual(5);
        }
    );
    });

    describe('method "revome"', function () {
        it("should remove the first item in the ArrayList or " +
        "return false if the item is not found",
        function () {
            al.insert(testItem1);
            al.insert(testItem2);
            al.insert(testItem4);
            al.insert(testItem2);
            al.insert(testItem5);

            expect(al.remove(testItem2)).toBeTruthy();
            expect(al.asArray()).toEqual([testItem1, testItem4, testItem2,
                testItem5]);
            expect(al.remove(testItem2)).toBeTruthy();
            expect(al.asArray()).toEqual([testItem1, testItem4, testItem5]);
            expect(al.remove(testItem1)).toBeTruthy();
            expect(al.asArray()).toEqual([testItem4, testItem5]);
            expect(al.remove(testItem3)).toBeFalsy();
        }
    );
    });

    describe('method "getSize"', function () {
        it("should return the size of the ArrayList", function () {
        var i = Math.floor((Math.random() * 100) + 1),
            j;
        expect(al.getSize()).toEqual(0);
        al.insert(testItem3);
        expect(al.getSize()).toEqual(1);
        al.remove(testItem3);
        expect(al.getSize()).toEqual(0);
        for (j = 0; j < i; j += 1) {
            al.insert(testItem3);
        }
        expect(al.getSize()).toEqual(i);
    });
    });

    describe('method "isEmpty"', function () {
        it("should return true if the ArrayList is empty or " +
        "false if not", function () {
        expect(al.isEmpty()).toBeTruthy();
        al.insert(testItem3);
        expect(al.isEmpty()).not.toBeTruthy();
        al.remove(testItem3);
        expect(al.isEmpty()).toBeTruthy();
    });
    });

    describe('method "contains"', function () {
        it("should return true if contain an item or false " +
        "if not", function () {
        al.insert(testItem1);
        al.insert(testItem2);
        al.insert(testItem4);
        expect(al.contains(testItem2)).toBeTruthy();
        expect(al.contains(testItem3)).toBeFalsy();
    });
    });

    describe('method "find"', function () {
        it("should returns the first item found with the attribute " +
        "and value entered", function () {
        Item.prototype.myParam = '';
        Item.prototype.len = '45';
        testItem1.myParam = 'a';
        testItem2.myParam = 32;
        testItem5.len = '34';
        testItem4.myParam = true;
        al.insert(testItem1);
        al.insert(testItem2);
        al.insert(testItem4);
        al.insert(testItem5);
        expect(al.find("myParam", "a")).toEqual(testItem1);
        expect(al.find("myParam", 32)).toEqual(testItem2);
        expect(al.find("myParam", true)).toEqual(testItem4);
        expect(al.find("len", '34')).toEqual(testItem5);
        expect(al.find("myVar", 54)).toBeUndefined();
        expect(al.find("myParam", "b")).toBeUndefined();

    });
    });

    describe('method "sort"', function () {
        it("should order the ArrayList", function () {
        var aTest1 = [testItem3, testItem4, testItem1, testItem5, testItem2],
            aTest2 = [testItem3, testItem4, testItem5, testItem1, testItem2];
        al.insert(testItem1);   
        al.insert(testItem2);
        al.insert(testItem3);
        al.insert(testItem4);
        al.insert(testItem5);
        al.sort();
        expect(al.asArray()).toEqual(aTest2);

    });
    });

    describe('method "getFirst"', function () {
        it("should return the first element or undefined if " +
        "array is empty", function () {
        expect(al.getFirst()).toBeUndefined();
        al.insert(testItem1);
        al.insert(testItem3);
        expect(al.getFirst()).toEqual(testItem1);
    });
    });

    describe('method "getLast"', function () {
        it("should return the last element or undefined id the " +
        "array is empty", function () {
        expect(al.getLast()).toBeUndefined();
        al.insert(testItem1);
        al.insert(testItem3);
        expect(al.getLast()).toEqual(testItem3);
    });
    });

    describe('method "popLast"', function () {
        it("should return the last element and remove it from the " +
        "ArrayList", function () {
        var testArray = [testItem1, testItem2, testItem3];
        al.insert(testItem1);
        al.insert(testItem2);
        al.insert(testItem3);
        al.insert(testItem4);
        expect(al.popLast()).toEqual(testItem4);
        expect(al.asArray()).toEqual(testArray);
    });
    });

    describe('method "getDimensionLimit"', function () {
        it("should return an array with the min and " +
        "max coords between all the elements", function () {
        Item.prototype.x = 0;
        Item.prototype.y = 0;
        Item.prototype.height = 10;
        Item.prototype.width = 10;
        testItem1.x = 10;
        testItem1.y = 15;
        testItem2.x = 20;
        testItem2.y = 45;
        testItem3.x = 60;
        testItem3.y = 90;
        testItem4.x = 120;
        testItem4.y = 5;
        testItem5.x = 52;
        testItem5.y = 7;
        al.insert(testItem1);
        al.insert(testItem2);
        al.insert(testItem3);
        al.insert(testItem4);
        al.insert(testItem5);
        expect(al.getDimensionLimit()).toEqual([5, 130, 100, 10]);
    });
    });

    describe('method "clear"', function () {
        it("should empty the array list", function () {
        al.insert(testItem1);
        al.insert(testItem2);
        al.insert(testItem3);
        al.insert(testItem4);
        al.clear();
        expect(al.asArray()).toEqual([]);
        expect(al.getSize()).toEqual(0);
    });
    });

});
