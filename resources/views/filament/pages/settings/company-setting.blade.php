{{-- resources/views/filament/pages/settings/company-setting.blade.php --}}
<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit="save">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit">
                    Guardar cambios
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
