<div>
    <x-button wire:click="$set('showModal', true)">
        Add Schedule
    </x-button>

    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ __('Add Scheduler') }}
        </x-slot>

        <x-slot name="content">
            <!-- Scheduler Name -->
            <div class="mt-2">
                <x-label for="scheduler_name" value="{{ __('Scheduler Name') }}" />
                <x-input id="scheduler_name" type="text" class="mt-1 block w-full"
                         wire:model.defer="scheduler_name" />
                <x-input-error for="scheduler_name" class="mt-2" />
            </div>

            <!-- Time -->
            <div class="mt-2">
                <x-label for="schedule_time" value="{{ __('Time (HH:MM)') }}" />
                <x-input id="schedule_time" type="time" class="mt-1 block w-full"
                         wire:model.defer="schedule_time" />
                <x-input-error for="schedule_time" class="mt-2" />
            </div>

            <!-- Message -->
            <div class="mt-2">
                <x-label for="message" value="{{ __('Message') }}" />
                <textarea id="message"
                          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                          wire:model.defer="message"></textarea>
                <x-input-error for="message" class="mt-2" />
            </div>

            <!-- Category -->
            <div class="mt-4">
                <x-label value="{{ __('Select Category') }}" />
                <select 
                    wire:model="selectedCategory" 
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                    <option value="">-- Select a Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
                <x-input-error for="selectedCategory" class="mt-2" />
            </div>

            <!-- File Upload -->
            <div class="mt-4">
                <x-label for="file" value="{{ __('Attach File (optional)') }}" />
                <input id="file" type="file"
                       wire:model="file"
                       class="block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100" />
                <x-input-error for="file" class="mt-2" />

                @if ($file)
                    <p class="text-sm text-gray-600 mt-2">
                        Selected file: <strong>{{ $file->getClientOriginalName() }}</strong>
                    </p>
                @endif
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
