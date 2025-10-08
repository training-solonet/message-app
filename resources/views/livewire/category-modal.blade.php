<div>
    <!-- Add Button -->
    <x-button wire:click="$set('showModal', true)">
        + Add Category
    </x-button>

    <!-- Modal -->
    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ __('Add Category') }}
        </x-slot>

        <x-slot name="content">
            <!-- Category Name -->
            <div class="mt-2">
                <x-label for="category" value="{{ __('Category Name') }}" />
                <x-input id="category" type="text" class="mt-1 block w-full"
                        wire:model.defer="category" />
                <x-input-error for="category" class="mt-2" />
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

    <!-- Success Message -->
    @if (session()->has('success'))
        <div class="mt-3 text-green-600 font-semibold">
            {{ session('success') }}
        </div>
    @endif
</div>
