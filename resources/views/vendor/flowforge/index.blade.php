@php use Filament\Support\Facades\FilamentAsset; @endphp
@props(["columns", "config"])
<div
    class="w-full h-full flex flex-col relative"
    x-load
    x-load-src="{{ FilamentAsset::getAlpineComponentSrc("flowforge", package: "relaticle/flowforge") }}"
    x-data="flowforge({
        state: {
            columns: @js($columns),
            titleField: "{{ $config["recordTitleAttribute"] }}",
            columnField: "{{ $config["columnIdentifierAttribute"] }}",
            cardLabel: "{{ $config["cardLabel"] }}",
            pluralCardLabel: "{{ $config["pluralCardLabel"] }}",
        }
    })"
>
    @include("flowforge::components.filters")
    <div class="flex-1 overflow-hidden">
        <div class="flex flex-row gap-4 h-full overflow-x-auto overflow-y-hidden pb-4 items-start pt-1">
            @foreach($columns as $columnId => $column)
                <x-flowforge::column
                    :columnId="$columnId"
                    :column="$column"
                    :config="$config"
                    wire:key="column-{{ $columnId }}"
                />
            @endforeach
        </div>
    </div>
    <x-filament-actions::modals/>
</div>