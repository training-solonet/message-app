<x-app-layout>
    <div class="h-screen flex flex-col overflow-hidden">
        <!-- Header aplikasi (jika ada) -->
        
        <div class="flex-1 flex min-h-0">
            {{-- Sidebar Contacts --}}
            <div class="w-1/5 flex flex-col border-r border-gray-200 bg-gray-50 min-w-64 min-h-0">
                {{-- Header --}}
                <div class="sticky top-0 z-20 bg-gray-200 p-4 border-b flex items-center justify-between h-[60px] shrink-0">
                    <h3 class="font-semibold text-lg text-gray-800">Contacts</h3>
                </div>

                {{-- Search Bar --}}
                <div class="sticky top-[60px] z-10 bg-gray-100 p-3 border-b shrink-0">
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
                
                {{-- Tabs Filter --}}
                <div class="sticky top-[108px] z-10 bg-gray-100 border-b flex justify-around text-sm font-medium shrink-0">
                    <button id="tab-all" onclick="setActiveTab('all')" class="w-1/3 py-2 border-b-2 border-indigo-600 text-indigo-600 focus:outline-none transition">All</button>
                    <button id="tab-unread" onclick="setActiveTab('unread')" class="w-1/3 py-2 border-b-2 text-gray-600 hover:text-indigo-600 focus:outline-none transition">Unread</button>
                    <button id="tab-noted" onclick="setActiveTab('noted')" class="w-1/3 py-2 border-b-2 text-gray-600 hover:text-indigo-600 focus:outline-none transition">Noted</button>
                </div>

                {{-- Contact List --}}
                <div class="flex-1 overflow-y-auto min-h-0">
                    <ul id="contact-list" class="h-full">
                        @foreach($contacts as $contact)
                            @php
                                $latest = $contact->histories->last();
                                $unreadCount = $contact->histories->where('direction', 'in')->where('is_read', false)->count();
                                $prefix = $latest ? ($latest->direction === 'out' ? 'You: ' : $contact->contact_name . ': ') : '';
                                $msg = $latest ? $prefix . (strlen($latest->message) > 20 ? substr($latest->message, 0, 20) . '...' : $latest->message) : 'No messages yet';
                            @endphp

                            <li class="relative p-4 hover:bg-indigo-50 transition cursor-pointer border-b duration-150 ease-in-out"
                                data-name="{{ strtolower($contact->contact_name) }}"
                                onclick="showChat({{ $contact->id }})">

                                {{-- Nama Kontak --}}
                                <div class="font-medium text-gray-800 flex justify-between items-center">
                                    <span>{{ $contact->contact_name }}</span>

                                    {{-- 🔹 Badge jumlah pesan belum dibaca --}}
                                    @if($unreadCount > 0)
                                        <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Pesan terakhir --}}
                                <div class="text-sm truncate {{ $unreadCount > 0 ? 'font-semibold text-gray-900' : 'text-gray-500' }}">
                                    {{ $msg }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Chat Area --}}
            <div class="flex-1 flex flex-col bg-gray-100 min-h-0">
                <div class="sticky top-0 z-20 bg-gray-200 p-4 border-b flex items-center justify-between h-[60px] shrink-0">
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

                <div id="chat-messages" class="flex-1 p-6 overflow-y-auto space-y-4 bg-gray-50 min-h-0">
                    <p class="text-gray-500 text-center mt-10">Select a contact to view messages</p>
                </div>
            </div>

            {{-- Contact Info Sidebar --}}
            <div id="contact-info-sidebar" class="hidden w-1/3 lg:w-96 flex flex-col border-l border-gray-200 bg-white min-h-0">
                <div class="sticky top-0 z-20 bg-gray-200 p-4 border-b flex items-center justify-between h-[60px] shrink-0">
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

                <div class="flex-1 overflow-y-auto min-h-0">
                    <div class="text-center py-8 border-b border-gray-200">
                        <div class="w-32 h-32 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-4xl font-semibold text-indigo-600" id="contact-avatar"></span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800" id="detail-contact-name"></h2>
                        <p class="text-gray-600 mt-2" id="detail-phone-number"></p>
                        <p class="text-gray-600 mt-2" id="detail-created-at"></p>
                    </div>

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

    <script>
        const contacts = @json($contacts);
        let currentContactId = null;
        let activeTab = 'all';

        function formatMessage(message) {
            if (!message) return '';
            const words = message.split(' ');
            let currentLine = '';
            let formattedMessage = '';
            const maxChars = 30;
            words.forEach(word => {
                if ((currentLine + word).length > maxChars) {
                    if (currentLine) formattedMessage += currentLine.trim() + '\n';
                    currentLine = word + ' ';
                } else {
                    currentLine += word + ' ';
                }
            });
            if (currentLine) formattedMessage += currentLine.trim();
            return formattedMessage;
        }

        function formatDateDisplay(dateString) {
            const date = new Date(dateString);
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            if (date.toDateString() === today.toDateString()) return 'Today';
            else if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
            else return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function getDateString(dateString) {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        function getMessageStatus(status, direction) {
            switch(status) {
                case 'sent': return { text: 'Sent', color: direction === 'in' ? 'text-green-600' : 'text-green-400' };
                case 'failed': return { text: 'Failed', color: direction === 'in' ? 'text-red-600' : 'text-red-400' };
                default: return { text: 'Sent', color: direction === 'in' ? 'text-green-600' : 'text-green-400' };
            }
        }

        function getFileTypeIcon(filePath) {
            const extension = filePath.split('.').pop().toLowerCase();
            const iconClasses = "w-4 h-4 mr-1";
            
            if (['pdf'].includes(extension)) {
                return `<svg class="${iconClasses} text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V7.414A2 2 0 0016.414 6L14 3.586A2 2 0 0012.586 3H9z"/></svg>`;
            } else if (['doc', 'docx'].includes(extension)) {
                return `<svg class="${iconClasses} text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V7.414A2 2 0 0016.414 6L14 3.586A2 2 0 0012.586 3H9z"/></svg>`;
            } else if (['xls', 'xlsx'].includes(extension)) {
                return `<svg class="${iconClasses} text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V7.414A2 2 0 0016.414 6L14 3.586A2 2 0 0012.586 3H9z"/></svg>`;
            } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(extension)) {
                return `<svg class="${iconClasses} text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>`;
            } else {
                return `<svg class="${iconClasses} text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>`;
            }
        }

        function getFileName(filePath) {
            return filePath.split('/').pop();
        }

        // 🚀 Tambahan fungsi untuk mark as read
        function markMessagesAsRead(contactId) {
            fetch(`/chats/${contactId}/read`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                console.log("Marked as read:", data);
                // 🔥 Segera perbarui tampilan kontak di sidebar
                refreshContactUI(contactId);
            })
            .catch(err => console.error("Error marking as read:", err));
        }

        function refreshContactUI(contactId) {
            const contactItem = document.querySelector(`#contact-list li[onclick="showChat(${contactId})"]`);
            if (!contactItem) return;

            // 🔹 Hapus badge jumlah pesan belum dibaca
            const badge = contactItem.querySelector("span.bg-indigo-600");
            if (badge) badge.remove();

            // 🔹 Hapus gaya bold dari pesan terakhir
            const lastMessage = contactItem.querySelector(".text-sm");
            if (lastMessage) {
                lastMessage.classList.remove("font-semibold", "text-gray-900");
                lastMessage.classList.add("text-gray-500");
            }
        }

        function showChat(contactId) {
            currentContactId = contactId;
            const chatBox = document.getElementById('chat-messages');
            const contactName = document.getElementById('chat-contact-name');
            const contactInfoBtn = document.getElementById('contact-info-btn');
            const contactSidebar = document.getElementById('contact-info-sidebar');
            
            contactSidebar.classList.add('hidden');
            contactInfoBtn.style.display = 'block';

            document.querySelectorAll('#contact-list li').forEach(li => li.classList.remove('active-contact'));
            const activeItem = document.querySelector(`#contact-list li[onclick="showChat(${contactId})"]`);
            if (activeItem) activeItem.classList.add('active-contact');

            chatBox.innerHTML = '';

            const contact = contacts.find(c => c.id === contactId);
            if (!contact) return;

            contactName.textContent = contact.contact_name;

            // 🔔 Panggil fungsi mark as read setiap kali user buka chat
            markMessagesAsRead(contactId);

            if (contact.histories && contact.histories.length > 0) {
                let chatDate = null;
                let hasMessages = false;
                
                contact.histories.forEach(msg => {
                    // Filter pesan berdasarkan tab aktif
                    if (activeTab === 'noted' && msg.noted != 1) {
                        return; // Skip pesan yang tidak di-noted di tab noted
                    }
                    
                    if (activeTab === 'unread') {
                        if (msg.direction === 'in' && msg.is_read) {
                            return; // Skip pesan yang sudah dibaca di tab unread
                        }
                    }

                    hasMessages = true;
                    const messageDate = getDateString(msg.created_at);
                    if (chatDate !== messageDate) {
                        chatDate = messageDate;
                        const dateDiv = document.createElement('div');
                        dateDiv.className = 'flex justify-center my-6';
                        dateDiv.innerHTML = `<div class="bg-gray-200 px-4 py-2 rounded-full text-sm text-gray-600 font-medium">${formatDateDisplay(msg.created_at)}</div>`;
                        chatBox.appendChild(dateDiv);
                    }

                    const div = document.createElement('div');
                    div.className = `flex ${msg.direction === 'out' ? 'justify-end' : 'justify-start'} mb-4`;
                    const sentTime = msg.created_at ? new Date(msg.created_at).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : '';
                    const formattedMessage = formatMessage(msg.message);
                    const status = getMessageStatus(msg.status, msg.direction);
                    
                    // File section jika file_path ada
                    let fileSection = '';
                    if (msg.file_path) {
                        fileSection = `
                            <div class="mb-2">
                                <a href="/storage/${msg.file_path}" target="_blank" class="inline-flex items-center px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition duration-150 ease-in-out">
                                    ${getFileTypeIcon(msg.file_path)}
                                    <span class="text-sm text-blue-700 font-medium">${getFileName(msg.file_path)}</span>
                                </a>
                            </div>
                        `;
                    }
                    
                    div.innerHTML = `
                        <div data-id="${msg.id}" class="flex ${msg.direction === 'out' ? 'justify-end' : 'justify-start'} items-start group mb-4 max-w-[70%]">
                            
                            ${msg.direction === 'out' ? `
                            <!-- Tombol Note di kiri untuk pesan keluar -->
                            <button 
                                class="mr-2 w-8 h-8 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-100 transition duration-150 ease-in-out ${
                                    msg.noted ? 'bg-indigo-700 text-white' : 'bg-indigo-400 text-white'
                                }"
                                onclick="toggleNote(${msg.id}, this)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 2a2 2 0 00-2 2v12l4-4h10a2 2 0 002-2V4a2 2 0 00-2-2H5z"/>
                                </svg>
                            </button>` : ''}

                            <!-- Bubble -->
                            <div class="relative px-4 py-2 rounded-2xl shadow-sm break-words ${
                                msg.direction === 'out' ? 'bg-indigo-500 text-white' : 'bg-gray-300 text-gray-900'
                            }">
                                ${fileSection}
                                <div class="message-content whitespace-pre-wrap">${formattedMessage}</div>

                                <!-- Timestamp + Status -->
                                <div class="flex justify-end items-center mt-1 space-x-1 text-xs ${
                                    msg.direction === 'out' ? 'text-indigo-100' : 'text-gray-600'
                                }">
                                    <span>${sentTime}</span>
                                    <span>|</span>
                                    <span class="${status.color}">${status.text}</span>
                                </div>
                            </div>

                            ${msg.direction === 'in' ? `
                            <!-- Tombol Note di kanan untuk pesan masuk -->
                            <button 
                                class="ml-2 w-8 h-8 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-100 transition duration-150 ease-in-out ${
                                    msg.noted ? 'bg-gray-400 text-white' : 'bg-gray-300 text-gray-900'
                                }"
                                onclick="toggleNote(${msg.id}, this)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 2a2 2 0 00-2 2v12l4-4h10a2 2 0 002-2V4a2 2 0 00-2-2H5z"/>
                                </svg>
                            </button>` : ''}
                        </div>
                        `;
                    chatBox.appendChild(div);
                });
                
                if (!hasMessages) {
                    if (activeTab === 'noted') {
                        chatBox.innerHTML = '<p class="text-gray-500 text-center mt-10">No noted messages for this contact</p>';
                    } else if (activeTab === 'unread') {
                        chatBox.innerHTML = '<p class="text-gray-500 text-center mt-10">No unread messages for this contact</p>';
                    } else {
                        chatBox.innerHTML = '<p class="text-gray-500 text-center mt-10">No messages yet</p>';
                    }
                } else {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
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
                const contactId = parseInt(item.getAttribute('onclick').match(/\d+/)[0]);
                const contact = contacts.find(c => c.id === contactId);
                
                let visible = true;

                // Filter berdasarkan tab aktif
                if (activeTab === 'unread') {
                    const unreadCount = contact.histories.filter(h => h.direction === 'in' && !h.is_read).length;
                    visible = unreadCount > 0;
                } else if (activeTab === 'noted') {
                    const hasNoted = contact.histories.some(h => h.noted === true);
                    visible = hasNoted;
                }

                // Filter berdasarkan teks pencarian
                if (!name.includes(search)) {
                    visible = false;
                }

                item.style.display = visible ? '' : 'none';
            });
        }

        function setActiveTab(tab) {
            activeTab = tab;

            // 🔹 Ubah tampilan tab aktif
            document.querySelectorAll('[id^="tab-"]').forEach(btn => {
                btn.classList.remove('border-indigo-600', 'text-indigo-600');
                btn.classList.add('text-gray-600');
            });

            const activeBtn = document.getElementById(`tab-${tab}`);
            activeBtn.classList.add('border-indigo-600', 'text-indigo-600');
            activeBtn.classList.remove('text-gray-600');

            const contactList = document.getElementById('contact-list');
            contactList.innerHTML = ''; // kosongkan list

            // 🔹 MODE: NOTED
            if (tab === 'noted') {
                let notedMessages = [];

                contacts.forEach(contact => {
                    contact.histories.forEach(msg => {
                        if (msg.noted === 1) {
                            notedMessages.push({
                                contact_name: contact.contact_name,
                                message: msg.message,
                                created_at: msg.created_at,
                                contact_id: contact.id,
                                message_id: msg.id,
                                file_path: msg.file_path // tambahkan file_path
                            });
                        }
                    });
                });

                if (notedMessages.length === 0) {
                    contactList.innerHTML = `<p class="text-gray-500 text-center mt-6">No noted messages</p>`;
                    return;
                }

                notedMessages.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                notedMessages.forEach(msg => {
                    const li = document.createElement('li');
                    li.className = 'relative p-4 hover:bg-indigo-50 transition cursor-pointer border-b duration-150 ease-in-out';
                    li.onclick = () => {
                        showChat(msg.contact_id); // buka chat dulu

                        // ⏳ beri sedikit delay agar DOM pesan sudah muncul
                        setTimeout(() => {
                            const chatBox = document.getElementById('chat-messages');
                            const allBubbles = chatBox.querySelectorAll('.message-content');
                            for (const bubble of allBubbles) {
                                if (bubble.textContent.trim().includes(msg.message.trim().substring(0, 10))) {
                                    bubble.parentElement.classList.add('ring-2', 'ring-indigo-400');
                                    bubble.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    // hilangkan highlight setelah 1 detik
                                    setTimeout(() => bubble.parentElement.classList.remove('ring-2', 'ring-indigo-400'), 1000);
                                    break;
                                }
                            }
                        }, 300);
                    };

                    // Tambahkan indikator file jika ada
                    const fileIndicator = msg.file_path ? 
                        `<div class="inline-flex items-center text-xs text-blue-600 mt-1">
                            ${getFileTypeIcon(msg.file_path)}
                            <span>File attached</span>
                        </div>` : '';

                    li.innerHTML = `
                        <div class="font-medium text-gray-800 flex justify-between items-center">
                            <span>${msg.contact_name}</span>
                            <span class="text-xs text-gray-500">${new Date(msg.created_at).toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'})}</span>
                        </div>
                        <div class="text-sm text-gray-700 truncate">${msg.message}</div>
                        ${fileIndicator}
                    `;
                    contactList.appendChild(li);
                });

                return;
            }


            // 🔹 MODE: ALL & UNREAD (list kontak)
            contacts.forEach(contact => {
                const latest = contact.histories.length ? contact.histories[contact.histories.length - 1] : null;
                const unreadCount = contact.histories.filter(h => h.direction === 'in' && !h.is_read).length;
                const prefix = latest ? (latest.direction === 'out' ? 'You: ' : contact.contact_name + ': ') : '';
                const msg = latest ? prefix + (latest.message.length > 20 ? latest.message.substring(0, 20) + '...' : latest.message) : 'No messages yet';

                if (tab === 'unread' && unreadCount === 0) return;

                const li = document.createElement('li');
                li.className = 'relative p-4 hover:bg-indigo-50 transition cursor-pointer border-b duration-150 ease-in-out';
                li.setAttribute('data-name', contact.contact_name.toLowerCase());
                li.onclick = () => {
                    // 🔸 Buka chat
                    showChat(contact.id);

                    // 🔸 Tandai semua pesan masuk sebagai sudah dibaca
                    contact.histories.forEach(h => {
                        if (h.direction === 'in') h.is_read = true;
                    });

                    // 🔸 Hapus tampilan bold & badge
                    const messageEl = li.querySelector('.text-sm');
                    if (messageEl) messageEl.classList.remove('font-semibold', 'text-gray-900');
                    const badgeEl = li.querySelector('.bg-indigo-600');
                    if (badgeEl) badgeEl.remove();

                    // 🔸 Jika tab "Unread" aktif, hapus dari daftar
                    if (activeTab === 'unread') {
                        li.remove();
                        if (contactList.children.length === 0) {
                            contactList.innerHTML = `<p class="text-gray-500 text-center mt-6">No unread contacts</p>`;
                        }
                    }
                };

                li.innerHTML = `
                    <div class="font-medium text-gray-800 flex justify-between items-center">
                        <span>${contact.contact_name}</span>
                        ${unreadCount > 0 ? `<span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full">${unreadCount}</span>` : ''}
                    </div>
                    <div class="text-sm ${unreadCount > 0 ? 'font-semibold text-gray-900' : 'text-gray-500'}">${msg}</div>
                `;
                contactList.appendChild(li);
            });

            if (tab === 'unread' && contactList.children.length === 0) {
                contactList.innerHTML = `<p class="text-gray-500 text-center mt-6">No unread contacts</p>`;
            }
        }


        function toggleNote(messageId, btn) {
            fetch(`/histories/${messageId}/toggle-note`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const svgIcon = btn.querySelector('svg');

                    if(data.noted) {
                        // Kalau true, ikon putih
                        svgIcon.setAttribute('fill', 'white');
                    } else {
                        // Kalau false, ikon hitam
                        svgIcon.setAttribute('fill', 'black');
                    }
                    
                    // Refresh tampilan jika sedang di tab noted
                    if (activeTab === 'noted' && currentContactId) {
                        showChat(currentContactId);
                    }
                    
                    // Refresh filter kontak
                    filterContacts();
                }
            })
            .catch(err => console.error('Error toggling note:', err));
        }
    </script>
    
    <style>
        .message-content {
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
            line-height: 1.4;
        }

        #contact-list li {
            transition: background-color 0.2s ease-in-out;
        }

        #contact-list li:hover {
            background-color: #eef2ff;
        }

        .active-contact {
            background-color: #e0e7ff;
            border-left: 4px solid #4f46e5;
        }
        
        /* Full screen adjustments */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        /* Memastikan kontainer utama mengambil seluruh tinggi layar */
        .h-screen {
            height: 100vh;
        }
    </style>
</x-app-layout>