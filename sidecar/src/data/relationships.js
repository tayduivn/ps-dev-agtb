/*
 * Manages relationships between beans.
 * Variable naming convention:
 * "relationship" - relationship metadata object
 * "relation(s)" - instance of Relation class or RelationCollection class
 *
 */
(function(app) {

    app.augment("Relationships",  {

        /*
         * Relation factory method.
         * @param link
         * @param bean1
         * @param beanOrId2
         * @param data Custom data fields
         */
        buildRelation: function(link, bean1, beanOrId2, data) {
            var name = bean1.fields[link]["relationship"];
            var relationship = bean1.relationships[name];
            var ids, beans, id2;

            if (beanOrId2 instanceof app.Bean) {
                id2 = beanOrId2.id;
            }
            else {
                id2 = beanOrId2;
                beanOrId2 = null;
            }

            ids = [bean1.id, id2];
            beans = [bean1, beanOrId2];

            if (relationship["rhs_module"] == bean1.module) {
               ids.reverse();
               beans.reverse();
            }

            var relation = new app.Relationships.Relation({
                name:         name,
                relationship: relationship,
                id1:          ids[0],
                id2:          ids[1],
                bean1:        beans[0],
                bean2:        beans[1],
                data:         data
            });

            relation.id = name + "-" + ids[0] + "-" + ids[1];
            return relation;
        },

        /*
         * Relation collection factory method.
         * @param link
         * @param bean Owner
         */
        buildCollection: function(link, bean) {
            var name = bean.fields[link]["relationship"];
            var relationship = bean.relationships[name];
            return new app.Relationships.RelationCollection(undefined, {
                name:         name,
                relationship: relationship,
                bean:         bean
            });

        },

        /*
         * Represents instance of a relationship between two beans.
         */
        Relation: Backbone.Model.extend({

            toString: function() {
                return this.id;
            }

        }),

        /*
         *
         */
        RelationCollection: Backbone.Collection.extend({
            model: undefined,

            constructor: function(models, options) {
                this.name = options.name;
                this.relationship = options.relationship;
                this.bean = options.bean;
            },

            parse: function(resp, xhr) {
                // TODO: We need to override parse method to properly build instances of Relation class
                // The shape of the response depends where it comes from: offline storage or REST API
                return resp;
            },

            toString: function() {
                return "rel-coll";
            }

        })

    }, false);

    app.Relationships.RelationCollection.prototype.model = app.Relationships.Relation;


})(SUGAR.App);

