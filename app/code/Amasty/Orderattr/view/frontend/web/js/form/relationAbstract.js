define([
    'ko',
    'underscore',
    'uiRegistry',
    'Amasty_Orderattr/js/form/relationRegistry'
], function (ko, _, registry, relationRegistry) {
    'use strict';

    /**
     * @abstract
     */
    return {
        hidedByRate: false,
        hidedByDepend: false,

        /**
         * @param {Object[]} relations
         * @param {string} relations[].attribute_name - element name of parent attribute
         * @param {string} relations[].dependent_name - element name of depend attribute
         * @param {string} relations[].option_value   - value which Parent should have to show Depend
         */
        relations: {},

        isRelationsInit: false,

        /**
         * check attribute dependencies on value change
         */
        onUpdate: function () {
            this._super();
            if (this.isRelationsInit) {
                this.checkDependencies();
            }
        },

        /**
         * run check dependency and clear relations
         */
        initCheck: function () {
            if (this.relations && this.relations.length) {
                this.isRelationsInit = true;
                this.checkDependencies();
            }
        },

        checkDependencies: function () {
            if (this.relations && this.relations.length) {
                var listDisplayedUID = [];
                registry.async(this.parentName)(function (fieldset) {
                    this.relations.map(function (relation) {
                        registry.async(fieldset.name + '.' + relation.dependent_name)(function (dependElement) {
                            if (this.isCanShow(relation)) {
                                listDisplayedUID.push(this.uid);
                                this.showDepend(dependElement);
                            } else {
                                /** hide element only if no relation rules to show. On one check */
                                if (listDisplayedUID.indexOf(this.uid) === -1) {
                                    this.hideDepend(dependElement);
                                }
                            }
                        }.bind(this));
                    }.bind(this));
                }.bind(this));
            }
        },

        /**
         * Is element value eq relation value
         *
         * @param relation
         * @returns {boolean}
         */
        isCanShow: function (relation) {
            if (_.isArray(this.value())) {
                return _.contains(this.value(), relation.option_value) && this.visible();
            } else {
                return (this.value() === relation.option_value && this.visible());
            }
        },

        showDepend: function (dependElement) {
            relationRegistry.add(dependElement.index, this.index);
            dependElement.hidedByDepend = false;

            if (dependElement.hidedByRate) {
                return false;
            }

            dependElement.show();
            if (_.isFunction(dependElement.checkDependencies) && dependElement.isRelationsInit) {
                dependElement.checkDependencies();
            }
        },

        hideDepend: function (dependElement) {
            relationRegistry.remove(dependElement.index, this.index);
            if (!relationRegistry.isExist(dependElement.index)) {
                dependElement.hide();
                dependElement.hidedByDepend = true;
                if (_.isFunction(dependElement.checkDependencies) && dependElement.isRelationsInit) {
                    dependElement.checkDependencies();
                }
            }
        }
    };
});
