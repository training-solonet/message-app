<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat Histories') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-xl">
                <div class="flex h-[650px]">

                    {{-- Sidebar Contacts --}}
                    <div class="w-1/3 flex flex-col border-r border-gray-200 bg-gray-50">
                        {{-- Header --}}
                        <div class="sticky top-0 z-20 bg-gray-200 p-4 border-b flex items-center justify-between h-[60px]">
                            <h3 class="font-semibold text-lg text-gray-800">Contacts</h3>
                        </div>

                        {{-- Search Bar --}}
                        <div class="sticky top-[60px] z-10 bg-gray-100 p-3 border-b">
                            <div class="relative">
                                <input
                                    type="text"
                                    id="contact-search"
                                    class="w-full pl-10 pr-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none placeholder-gray-400"
                                    placeholder="Search contacts..."
                                    onkeyup="filterContacts()"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                </svg>
                            </div>
                        </div>

                        {{-- Contact List --}}
                        <ul id="contact-list" class="flex-1 overflow-y-auto">
                            @foreach($contacts as $contact)
                                <li class="p-4 hover:bg-indigo-50 transition cursor-pointer border-b duration-150 ease-in-out"
                                    data-name="{{ strtolower($contact->contact_name) }}"
                                    onclick="showChat({{ $contact->id }})">
                                    <div class="font-medium text-gray-800">{{ $contact->contact_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $contact->phone_number }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Chat Area --}}
                    <div class="flex-1 flex flex-col bg-gray-100">
                        <div class="sticky top-0 z-20 bg-gray-200 p-4 border-b flex items-center justify-between h-[60px]">
                            <h3 class="font-semibold text-lg text-gray-800 truncate" id="chat-contact-name">Select a contact</h3>
                            <button 
                                id="contact-info-btn" 
                                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-300 rounded-full transition duration-150 ease-in-out"
                                onclick="toggleContactInfo()"
                                style="display: none;"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>

                        <div id="chat-messages" class="flex-1 p-6 overflow-y-auto space-y-4 bg-gray-50">
                            <p class="text-gray-500 text-center mt-10">Select a contact to view messages</p>
                        </div>
                    </div>

                    {{-- Contact Info Sidebar --}}
                    <div id="contact-info-sidebar" class="hidden w-1/3 lg:w-96 flex flex-col border-l border-gray-200 bg-white transform transition-transform duration-300 ease-in-out overflow-y-auto">
                        <div class="sticky top-0 z-20 bg-gray-200 p-4 border-b flex items-center justify-between h-[60px]">
                            <h3 class="font-semibold text-lg text-gray-800">Contact Info</h3>
                            <button 
                                onclick="toggleContactInfo()"
                                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-300 rounded-full transition duration-150 ease-in-out"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <div class="text-center py-8 border-b border-gray-200">
                                <div class="w-32 h-32 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-4xl font-semibold text-indigo-600" id="contact-avatar"></span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800" id="detail-contact-name"></h2>
                                <p class="text-gray-600 mt-2" id="detail-phone-number"></p>
                                <p class="text-gray-600 mt-2" id="detail-created-at"></p>
                            </div>

                            {{-- Contact Information --}}
                            <div class="p-6 space-y-6">
                                <h3 class="text-lg font-semibold text-gray-800">Contact Information</h3>
                                
                                <form id="contact-edit-form" method="POST" action="{{ route('contacts.update', 'contact') }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                            <input 
                                                type="text" 
                                                name="contact_name" 
                                                id="edit-contact-name"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                                            >
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                            <input 
                                                type="text" 
                                                name="phone_number" 
                                                id="edit-phone-number"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                                            >
                                        </div>

                                        {{-- Category Field --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                            <select 
                                                name="category_id" 
                                                id="edit-category"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                                            >
                                                <option value="">-- Select Category --</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-3 mt-8 pt-6 border-t border-gray-200">
                                        <button 
                                            type="button" 
                                            onclick="toggleContactInfo()"
                                            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out font-medium"
                                        >
                                            Cancel
                                        </button>
                                        <button 
                                            type="submit" 
                                            class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-150 ease-in-out font-medium"
                                        >
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        const contacts = @json($contacts);
        let currentContactId = null;

        function showChat(contactId) {
            currentContactId = contactId;
            const chatBox = document.getElementById('chat-messages');
            const contactName = document.getElementById('chat-contact-name');
            const contactInfoBtn = document.getElementById('contact-info-btn');
            const contactSidebar = document.getElementById('contact-info-sidebar');
            
            contactSidebar.classList.add('hidden');
            contactInfoBtn.style.display = 'block';

            chatBox.innerHTML = '';

            const contact = contacts.find(c => c.id === contactId);
            if (!contact) return;

            contactName.textContent = contact.contact_name;

            if (contact.histories && contact.histories.length > 0) {
                contact.histories.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = `flex ${msg.direction === 'out' ? 'justify-end' : 'justify-start'}`;
                    div.innerHTML = `
                        <div class="max-w-[70%] px-4 py-2 rounded-2xl shadow-sm ${
                            msg.direction === 'out'
                                ? 'bg-indigo-500 text-white'
                                : 'bg-gray-300 text-gray-900'
                        }">${msg.message}</div>`;
                    chatBox.appendChild(div);
                });
            } else {
                chatBox.innerHTML = '<p class="text-gray-500 text-center mt-10">No messages yet</p>';
            }
        }

        function toggleContactInfo() {
            const contactSidebar = document.getElementById('contact-info-sidebar');
            const contactInfoBtn = document.getElementById('contact-info-btn');

            if (contactSidebar.classList.contains('hidden')) {
                contactSidebar.classList.remove('hidden');
                contactInfoBtn.style.display = 'none';
                loadContactDetails();
            } else {
                contactSidebar.classList.add('hidden');
                contactInfoBtn.style.display = 'block';
            }
        }

        function loadContactDetails() {
            if (!currentContactId) return;
            const contact = contacts.find(c => c.id === currentContactId);
            if (!contact) return;

            const form = document.getElementById('contact-edit-form');
            form.action = `/contacts/${currentContactId}`;

            document.getElementById('edit-contact-name').value = contact.contact_name || '';
            document.getElementById('edit-phone-number').value = contact.phone_number || '';
            document.getElementById('edit-category').value = contact.category_id || '';

            document.getElementById('detail-contact-name').textContent = contact.contact_name || '';
            document.getElementById('detail-phone-number').textContent = contact.phone_number || '';
            document.getElementById('detail-created-at').textContent =
                contact.created_at
                    ? `Created at ${new Date(contact.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}.`
                    : 'No data.';

            const avatar = document.getElementById('contact-avatar');
            if (contact.contact_name) avatar.textContent = contact.contact_name.charAt(0).toUpperCase();
        }

        function filterContacts() {
            const search = document.getElementById('contact-search').value.toLowerCase();
            const items = document.querySelectorAll('#contact-list li');
            items.forEach(item => {
                const name = item.getAttribute('data-name');
                item.style.display = name.includes(search) ? '' : 'none';
            });
        }
    </script>
</x-app-layout>
