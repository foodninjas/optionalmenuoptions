@foreach ($menuItem->menu_options->sortBy('priority') as $index => $menuOption)
    <div
        @class([
            'menu-option',
            'd-none' => $menuOption->methodExists('getLinkedMenuItemOptionValueIds') &&
                count($menuOption->getLinkedMenuItemOptionValueIds()) > 0
        ])
        data-linked-option-value-ids="{{
            $menuOption->methodExists('getLinkedMenuItemOptionValueIds') ?
            json_encode($menuOption->getLinkedMenuItemOptionValueIds()) : '[]'
        }}"
        data-control="item-option"
    >
        <div class="option option-{{ $menuOption->display_type }}">
            <div class="option-details">
                <h5 class="mb-0">
                    {{ $menuOption->option_name }}
                    @if ($menuOption->required == 1)
                        <span
                            class="small pull-right text-muted">@lang('igniter.cart::default.text_required')</span>
                    @endif
                </h5>
                @if ($menuOption->min_selected > 0 || $menuOption->max_selected > 0)
                    <p class="mb-0">{!! sprintf(lang('igniter.cart::default.text_option_summary'), $menuOption->min_selected, $menuOption->max_selected) !!}</p>
                @endif
            </div>

            @if (count($optionValues = $menuOption->menu_option_values))
                <input
                    type="hidden"
                    name="menu_options[{{ $index }}][menu_option_id]"
                    value="{{ $menuOption->menu_option_id }}"
                />
                <div class="option-group">
                    @partial('@item_option_'.$menuOption->display_type, [
                    'index' => $index,
                    'cartItem' => $cartItem,
                    'optionValues' => $optionValues->sortBy('priority'),
                    ])
                </div>
            @endif
        </div>
    </div>
@endforeach