<div>
    <x-button wire:click="$set('showModal', true)">
        + Add Schedule
    </x-button>

    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ __('Add Scheduler') }}
        </x-slot>

        <x-slot name="content">
            <div class="mt-2">
                <x-label for="scheduler_name" value="{{ __('Scheduler Name') }}" />
                <x-input id="scheduler_name" type="text" class="mt-1 block w-full"
                         wire:model.defer="scheduler_name" />
                <x-input-error for="scheduler_name" class="mt-2" />
            </div>

            <div class="mt-2">
                <x-label for="schedule_time" value="{{ __('Time (HH:MM)') }}" />
                <x-input id="schedule_time" type="time" class="mt-1 block w-full"
                         wire:model.defer="schedule_time" />
                <x-input-error for="time" class="mt-2" />
            </div>

            <div class="mt-2">
                <x-label for="message" value="{{ __('Message') }}" />
                <textarea id="message" class="mt-1 block w-full"
                          wire:model.defer="message"></textarea>
                <x-input-error for="message" class="mt-2" />
            </div>

            <div class="mt-2">
                <label>Select Contacts</label>
                @foreach($contacts as $contact)
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            wire:model="selectedContacts" 
                            value="{{ $contact->id }}" 
                            class="form-check-input"
                            id="contact-{{ $contact->id }}">
                        <label for="contact-{{ $contact->id }}" class="form-check-label">
                            {{ $contact->contact_name }} ({{ $contact->phone_number }})
                        </label>
                    </div>
                @endforeach
                @error('selectedContacts') <span class="text-danger">{{ $message }}</span>@enderror
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
