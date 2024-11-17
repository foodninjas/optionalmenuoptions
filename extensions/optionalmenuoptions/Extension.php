<?php

namespace FoodNinjas\OptionalMenuOptions;

use Admin\Models\Menu_item_options_model;
use Admin\Models\Menus_model;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;

class Extension extends BaseExtension
{
    public function boot()
    {
        Event::listen('admin.form.extendFieldsBefore', function($form) {
            if ($form->model instanceof Menu_item_options_model) {
                $menuItemOptionValues = $form->model->menu->menu_options
                    ->where('menu_option_id', '<>', $form->model->menu_option_id)
                    ->map(function($menuOption) {
                        return $menuOption->menu_option_values->map(function($menuItemOptionValue) use ($menuOption) {
                            return [
                                'value' => $menuItemOptionValue->menu_option_value_id,
                                'text' => $menuOption->option_name.' -> '.$menuItemOptionValue->option_value->value,
                            ];
                        });
                    })->flatten(1)->pluck('text', 'value')->toArray();

                $fields = [
                    'label' => 'lang:foodninjas.optionalmenuoptions::default.label_linked_menu_option_values',
                    'type' => 'selectlist',
                    'span' => 'left',
                    'options' => $menuItemOptionValues,
                    'default' => $form->model->getLinkedMenuItemOptionValueIds(),
                ];

                $form->fields['_linked_menu_option_value_ids'] = $fields;
            }
        });

        Menu_item_options_model::extend(function(Menu_item_options_model $model) {
            $model->relation['belongsToMany']['menu_item_option_values'] = [
                'Admin\Models\Menu_item_option_values_model',
                'table' => 'foodninjas_optional_menu_options'
            ];

            $model->bindEvent('model.afterSave', function() use ($model) {
                if (!request()->has('Menu.connectorData')) {
                    return;
                }

                $menuItemOptionValuesIds = post('Menu.connectorData._linked_menu_option_value_ids');
                $model->menu_item_option_values()->sync($menuItemOptionValuesIds);
            });

            $model->addDynamicMethod('getLinkedMenuItemOptionValueIds', function() use ($model) {
                return $model->menu_item_option_values->pluck('menu_option_value_id')->toArray();
            });
        });

        Menus_model::extend(function (Menus_model $model) {
            //  update the menu_price_from to be the minimum price of the parent menu options
            $model->bindEvent('model.afterFetch', function() use ($model) {
                $menuPriceFrom = $model->menu_price;

                if($menuPriceFrom <= 0) {
                    $filteredMenuOptions = $model
                        ->menu_options()
                        ->with('menu_item_option_values', 'menu_option_values')
                        ->get()
                        ->filter(fn($menuOption) => !count($menuOption->menu_item_option_values));

                    $menuPriceFrom = $filteredMenuOptions
                        ->mapWithKeys(
                            fn($menuOption) =>
                                $menuOption->menu_option_values->keyBy('menu_option_value_id'))
                        ->min('price');

                    $model->addDynamicProperty('menu_price_from', $menuPriceFrom ?? 0);
                }

            });
        });

        \Igniter\Cart\Components\CartBox::extend(function() {
            Event::listen('main.page.beforeRenderPage', function($controller) {
                $controller->addJs('$/foodninjas/optionalmenuoptions/assets/js/optional-menu-options.js', 'optional-menu-options-js');
            });
        });
    }
}