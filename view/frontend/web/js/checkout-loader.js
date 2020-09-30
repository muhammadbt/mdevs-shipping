define([
    'rjsResolver',
    'jquery'
], function (resolver, $) {
    'use strict';

    /**
     * Removes provided loader element from DOM.
     *
     * @param {HTMLElement} $loader - Loader DOM element.
     */
    function hideLoader($loader) {
        let getShippingData = JSON.parse((localStorage.getItem('deliveryOptions')=="" ? "{}" : localStorage.getItem('deliveryOptions'))),
            shippingForm = $("#shipping-new-address-form");

        if (shippingForm.length>0) {
            shippingForm.find("input[name='street[0]']").val(getShippingData.address);
            shippingForm.find("input[name='city']").val(getShippingData.city);
            shippingForm.find("input[name='postcode']").val(getShippingData.zip);
        }
        $loader.parentNode.removeChild($loader);
    }

    /**
     * Initializes assets loading process listener.
     *
     * @param {Object} config - Optional configuration
     * @param {HTMLElement} $loader - Loader DOM element.
     */
    function init(config, $loader) {
        resolver(hideLoader.bind(null, $loader));
    }

    return init;
});
