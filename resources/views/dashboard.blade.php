<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat Histories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex h-[600px]">

                    {{-- Sidebar Contacts --}}
                    <div class="w-1/3 border-r border-gray-200 overflow-y-auto">
                        <div class="p-4 border-b bg-gray-300">
                            <h3 class="font-bold text-lg">Contacts</h3>
                        </div>
                        <ul>
                            @foreach($contacts as $contact)
                                <li class="p-4 hover:bg-gray-200 cursor-pointer"
                                    onclick="showChat({{ $contact->id }})">
                                    <div class="font-semibold">{{ $contact->contact_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $contact->phone_number }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Chat Area --}}
                    <div class="flex-1 flex flex-col">
                        <div class="p-4 border-b bg-gray-300">
                            <h3 class="font-bold text-lg" id="chat-contact-name">Select a contact</h3>
                        </div>

                        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto space-y-2 bg-gray-50">
                            {{-- Pesan akan dimunculkan sesuai kontak --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        const contacts = @json($contacts);

        function showChat(contactId) {
            const chatBox = document.getElementById('chat-messages');
            const contactName = document.getElementById('chat-contact-name');
            chatBox.innerHTML = '';

            const contact = contacts.find(c => c.id === contactId);
            if (!contact) return;

            contactName.textContent = contact.contact_name;

            if (contact.histories && contact.histories.length > 0) {
                contact.histories.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = msg.direction === 'out' ? 'text-right' : 'text-left';
                    div.innerHTML = `<span class="inline-block px-3 py-2 rounded-lg ${msg.direction === 'out'
                        ? 'bg-indigo-500 text-white'
                        : 'bg-gray-300 text-black'}">${msg.message}</span>`;
                    chatBox.appendChild(div);
                });
            } else {
                chatBox.innerHTML = '<p class="text-gray-500">No messages yet</p>';
            }
        }
    </script>
</x-app-layout>