+function ($) {
    "use strict"

    if ($.fn === undefined) $.fn = {}

    if ($.fn.optionalMenuOptions === undefined)
        $.fn.optionalMenuOptions = {}

    const OptionalMenuOptions = function (element, options) {
        this.$el = $(element)
        this.options = options
        this.selectedOptionValueIds = []

        this.init()
    };

    OptionalMenuOptions.prototype.constructor = OptionalMenuOptions

    OptionalMenuOptions.prototype.init = function () {
        const self = this;
        // display selected option values
        this.$el.find('.option input[type="radio"]:checked, .option input[type="checkbox"]:checked, .option select').each(function () {
            const $optionInput = $(this),
                optionValue = isNaN($optionInput.val()) ? $optionInput.val() : parseInt($optionInput.val());


            if (optionValue !== 'Select') {
                self.selectedOptionValueIds.push(optionValue);
            }

            const $menuOption = $optionInput.closest('.menu-option.d-none');

            if ($menuOption.length > 0) {
                $menuOption.removeClass('d-none');
            }
        });

        // trigger change event on option input
        this.$el.find('.option input[type="radio"], .option input[type="checkbox"], .option select')
            .on('change', this.onOptionChange.bind(this))
    }

    OptionalMenuOptions.prototype.onOptionChange = function (event) {
        const $menuItemOptions = this.$el.find('[data-control="item-option"]'),
            $optionInput = $(event.target),
            self = this;

        // check if radio input or checkbox input
        if ($optionInput.is('input[type="radio"]') || $optionInput.is('input[type="checkbox"]')) {
            // add checked option value and remove unchecked option value
            const optionValue = isNaN($optionInput.val()) ? $optionInput.val() : parseInt($optionInput.val());
            if ($optionInput.is(':checked')) {
                self.selectedOptionValueIds.push(optionValue);
            } else {
                if (self.selectedOptionValueIds.indexOf(optionValue) >= 0) {
                    self.selectedOptionValueIds.splice(self.selectedOptionValueIds.indexOf(optionValue), 1);
                }
            }

            if ($optionInput.is('input[type="radio"]')) {
                // remove other unchecked option value (specifically for radio input that accepts only one option)
                $optionInput.closest('.form-check').siblings('.form-check').find('input[type="radio"]').each(function () {
                    const $optionInput = $(this),
                        optionValue = isNaN($optionInput.val()) ? $optionInput.val() : parseInt($optionInput.val());

                    if (self.selectedOptionValueIds.indexOf(optionValue) >= 0) {
                        self.selectedOptionValueIds.splice(self.selectedOptionValueIds.indexOf(optionValue), 1);
                    }
                });
            }
        }

        if ($optionInput.is('select')) {
            // add selected option value and remove unselected option value
            const selectedOptionValue = isNaN($optionInput.val()) ? $optionInput.val() : parseInt($optionInput.val());
            if(selectedOptionValue !== 'Select') {
                self.selectedOptionValueIds.push(selectedOptionValue);
            }

            // remove unselected option value
            $optionInput.find('option').each(function () {
                const optionValue = isNaN($(this).val()) ? $(this).val() : parseInt($(this).val());

                if ((optionValue !== selectedOptionValue) && (self.selectedOptionValueIds.indexOf(optionValue) >= 0)) {
                    self.selectedOptionValueIds.splice(self.selectedOptionValueIds.indexOf(optionValue), 1);
                }
            });
        }

        $menuItemOptions.each(function () {
            const $menuItemOption = $(this),
                linkedOptionValueIds = $menuItemOption.data('linkedOptionValueIds');

            if (linkedOptionValueIds.length > 0) {
                if (linkedOptionValueIds.some(r => self.selectedOptionValueIds.indexOf(r) >= 0)) {
                    $menuItemOption.removeClass('d-none');
                }
                else if(!$menuItemOption.hasClass('d-none')) {
                    $menuItemOption.addClass('d-none');
                    // find checked option input
                    const $optionInput = $menuItemOption.find('.option input[type="radio"]:checked, .option input[type="checkbox"]:checked, .option select');

                    // uncheck any child option that is checked by recursively triggering change event
                    if ($optionInput.is('input[type="radio"]') || $optionInput.is('input[type="checkbox"]')) {
                        $optionInput.prop('checked', false).trigger('change');
                    }

                    if($optionInput.is('select')) {
                        // go to first option
                        $optionInput.prop('selectedIndex', 0).trigger('change');
                    }
                }
            }
        })
    }

    OptionalMenuOptions.DEFAULTS = {};

    var old = $.fn.optionalMenuOptions

    $.fn.optionalMenuOptions = function (options) {
        var args = Array.prototype.slice.call(arguments, 1),
            result = undefined

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.optionalMenuOptions')
            var options = $.extend({}, OptionalMenuOptions.DEFAULTS, $this.data(), typeof options == 'object' && options)
            if (!data) $this.data('ti.optionalMenuOptions', (data = new OptionalMenuOptions(this, options)))
            if (typeof options == 'string') result = data[options].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.optionalMenuOptions.noConflict = function () {
        $.fn.optionalMenuOptions = old
        return this
    }

    $(document).render(function () {
        $('[data-control="item-options"]').optionalMenuOptions()
    })
}(window.jQuery)
