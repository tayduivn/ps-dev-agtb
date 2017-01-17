/*jshint globalstrict: true*/
"use strict";

var _ = require('lodash'),
    Cukes = require('@sugarcrm/seedbed'),
    Hashmap = Cukes.HashMap,
    utils = Cukes.Utils,
    BaseLayout = Cukes.BaseLayout;

/**
 * ListView helper to find record on view or layout
 *
 * @class ListHelper
 */
var ListHelper = {

    /**
     * Get view component for given record
     *
     * @param record
     * @param viewOrLayout
     * @returns {SugarCukes.ListItemView}
     */
    getListItem: function(record, viewOrLayout) {

        if (!record) {
            return null;
        }

        viewOrLayout = viewOrLayout && _.isString(viewOrLayout) ?
            seedbed.createComponent(viewOrLayout, { module: record.module }) :
            viewOrLayout;

        //TODO we don't need get child view for dashblelist dashlet view
        var view = viewOrLayout instanceof BaseLayout ? viewOrLayout.$$() : viewOrLayout;

        return view.getListItem({id: record.id}, record);
    },

    /**
     * pass data to be modified for setting on filed
     *
     * @param {object} inputData - pre-modified data
     * @param {object} editView - View or Layout component
     * @returns {object} - modified data that can be set on fields
     */
    prepareInputData: function (inputData, editView) {

        let aggregateField = function aggregateField(data, aggregatedFieldName, fieldsToAggregateRegexp) {
            //create an array of all fieldNames that should be aggregated
            let fieldsToAggregate = [];
            data.iterate((fieldValue, fieldName) => {
                if (fieldsToAggregateRegexp.test(fieldName)) {
                    fieldsToAggregate.unshift(fieldName);
                }
            });

            //save PlaceHolder fieldName and Value
            let aggregatedFieldPlaceHolder = fieldsToAggregate.pop();
            let aggregatedFieldPlaceHolderValue = data.get(aggregatedFieldPlaceHolder);

            //create new hashmap that represents the aggregated field
            let aggregatedField = new Hashmap();
            _.each(fieldsToAggregate, (fieldName) => {
                aggregatedField.unshift(fieldName, data.get(fieldName));
                data.remove(fieldName);
            });
            //add the place holder field to aggregatedFields
            aggregatedField.unshift(aggregatedFieldPlaceHolder, aggregatedFieldPlaceHolderValue);

            //replace placeHolder field with aggregated
            data.replace(aggregatedFieldPlaceHolder, aggregatedFieldName, aggregatedField);
        };

        let aggregateAddressField = function(data){
            let addrRegExp = /^(?=.*_address)((?!email).)*$/;

            //create an array of all used address fields
            let addressFields = [];
            data.iterate((fieldValue, fieldName) => {
                if (addrRegExp.test(fieldName)) {
                    addressFields.push(fieldName.split('_address')[0] + '_address');
                }
            });
            addressFields = _.uniq(addressFields);

            //aggregate each address field and put into input data in proper place
            _.each(addressFields, (fieldName) => {
                let regExp = new RegExp(fieldName + ".*");
                aggregateField(data, fieldName, regExp);
            });

        };

        let aggregateEmailField = function(data) {
            let regExp = /^(email\d*)$/;
            aggregateField(data, 'email', regExp);
        };

        aggregateAddressField(inputData);
        aggregateEmailField(inputData);

        // check whether data contains parent fields. If true then make data.parent_name object with parent_name and parent_type.
        if (inputData.contains('parent_name')) {
            let parentField = new Hashmap();
            parentField.push('parent_name', inputData.get('parent_name'));
            parentField.push('parent_type', inputData.get('parent_type'));
            inputData.update('parent_name', parentField);
            inputData.remove('parent_type');
        }

        //aggregate fieldSet
        _.each(editView.fields, (field, fieldSetName) => {

            if (field.fields) { // this is fieldset
                let aggregatedField = new Hashmap();

                _.each(field.fields, function (f, subFieldName) {
                    if (inputData.contains(subFieldName)) {
                        aggregatedField.push(subFieldName, inputData.get(subFieldName));
                    }
                });

                if (!aggregatedField.isEmpty()) {
                    inputData.replace(aggregatedField.getByIndex(0), fieldSetName, aggregatedField);
                }
            }
        });

        return inputData;
    }
};

module.exports = ListHelper;
