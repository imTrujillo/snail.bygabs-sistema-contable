<x-filament-panels::page>
    <form wire:submit="select">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Ingresar al período
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
