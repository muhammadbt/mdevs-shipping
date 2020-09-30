define(["jquery"], function($) {
    "use strict";
    return function() {
        let deliveryOption = '.delivery-options-container .delivery-option',
            deliveryPoints = '.delivery-options-container .delivery-points',
            deliveryForm = $('#get-zip-points');
        $(document).on('change', deliveryOption, function() {
            $(deliveryPoints).slideUp('fast');
            let currentElement = $(this),
                id = currentElement.attr('id');

            if (id=='ap') {
                currentElement.closest('.do-radio').find('.delivery-points').slideDown('fast');
                storeValues (currentElement.closest('.do-radio').find('.delivery-point:checked'));
                toggleCheckOutButtons();
                return;
            } else {
                enableCheckoutButtons();
            }
            clearValue();
        });
        $(document).on('change', deliveryPoints+' .delivery-point', function() {
            storeValues ($(this));
        });
        deliveryForm.submit(function(e) {
            e.preventDefault();
            var currentInstance = $(this);
            if (currentInstance.find('input[name="zipcode"]').val().length<4) {
                alert('Enter a valid zipcode or must be greater than 3 numbers.'); return false;
            }
            currentInstance.addClass('active');
            $.ajax({
                url: currentInstance.attr('action'),
                type: "POST",
                dataType: 'json',
                data: {
                    zip: currentInstance.find('input[name="zipcode"]').val(),
                    formKey: currentInstance.find('input[name="form_key"]').val()
                },
                success: function(res) {
                    currentInstance.removeClass('active');
                    var html = "<ul>";
                    if (res.length>0) {
                        $.each(res, function(i, v) {
                            html += '<li>';
                            html += '<label for="'+i+'">';
                            html += '<input type="radio" ';
                            html += (i===0) ? " checked " : "";
                            html += 'id="'+i+'" class="delivery-point" name="d-points"';
                            html += 'value="'+$.trim(v.pointName)+'" data-address="'+$.trim(v.pointName)+'"';
                            html += 'data-city="'+$.trim(v.pointCity)+'" data-zip="'+$.trim(v.pointZip)+'"';
                            html += 'data-loc="'+v.latitude+'-'+v.longitude+'" />';
                            html += v.pointName;
                            html += '</label>';
                            html += '</li>';
                        });
                    } else {
                        html += '<li class="notfound">Unable to find the delivery points for zipcode ('+currentInstance.find('input[name="zipcode"]').val()+')</li>';
                    }
                    html += '</ul>';

                    if ($(deliveryPoints).find('ul').length>0) {
                        $(deliveryPoints).find('ul').remove();
                        $(deliveryPoints).append(html);

                        storeValues ($('.delivery-points input.delivery-point:checked'));
                        toggleCheckOutButtons();

                    } else {
                        $(deliveryPoints).append(html);

                        if (res.length>0) {
                            storeValues ($('.delivery-points input.delivery-point:checked'));
                            toggleCheckOutButtons();
                        } else {
                            disableCheckoutButtons();
                        }
                    }
                }
            });
        });
        function storeValues (e)
        {
            let cEle = e,
                deliveryOptions = {
                    address: cEle.data('address'),
                    city:    cEle.data('city'),
                    zip:     cEle.data('zip'),
                    loc:     cEle.data('loc')
                };
            localStorage.setItem('deliveryOptions', JSON.stringify(deliveryOptions));
        }
        function toggleCheckOutButtons ()
        {
            if (localStorage.getItem('deliveryOptions').length<4) {
                disableCheckoutButtons();
            } else {
                enableCheckoutButtons();
            }
        }
        function enableCheckoutButtons ()
        {
            $('[data-role="proceed-to-checkout"], #top-cart-btn-checkout').removeAttr('disabled');
        }
        function disableCheckoutButtons ()
        {
            $('[data-role="proceed-to-checkout"], #top-cart-btn-checkout').attr('disabled', 'disabled');
        }
        function clearValue()
        {
            localStorage.setItem('deliveryOptions', "{}");
        }
    }
});
