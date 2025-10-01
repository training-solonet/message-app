<div>
    <!-- Tombol buka modal -->
    <x-button wire:click="$set('showModal', true)">
        + Add Contact
    </x-button>

    <!-- Modal -->
    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ __('Add Contact') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-2">
                <x-label for="contact_name" value="{{ __('Contact Name') }}" />
                <x-input id="contact_name" type="text" class="mt-1 block w-full"
                         wire:model.defer="contact_name" />
                <x-input-error for="contact_name" class="mt-2" />
            </div>

            <div class="mt-2">
                <x-label for="phone_number" value="{{ __('Phone Number') }}" />
                <x-input id="phone_number" type="text" class="mt-1 block w-full"
                         wire:model.defer="phone_number" />
                <x-input-error for="phone_number" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-button class="ms-3" wire:click="save" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
