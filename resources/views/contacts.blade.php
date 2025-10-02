<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contacts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <livewire:contact-form-modal />
                    </div>
                    <div class="mt-2">
                        <div class="overflow-x-auto">
                            <table id="contactsTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php $j = 1; @endphp
                                    @forelse ($contacts as $contact)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $j++ }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $contact->contact_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $contact->phone_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-2">
                                                    <button 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal{{ $contact->id }}"
                                                        class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                    >
                                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                    </button>
                                                    <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="inline">
                                                        @csrf @method('DELETE')
                                                        <button 
                                                            type="submit" 
                                                            class="inline-flex items-center px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                            onclick="return confirm('Are you sure you want to delete this contact?')"
                                                        >
                                                            <i class="fa-solid fa-trash mr-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                                No contacts found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals for each contact -->
    @foreach ($contacts as $contact)
    <div class="modal fade fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="editModal{{ $contact->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $contact->id }}" aria-hidden="true">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="editModalLabel{{ $contact->id }}">Edit Contact</h3>
                <form action="{{ route('contacts.update', $contact) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label for="contact_name{{ $contact->id }}" class="block text-sm font-medium text-gray-700">Name</label>
                        <input 
                            type="text" 
                            name="contact_name" 
                            id="contact_name{{ $contact->id }}" 
                            value="{{ $contact->contact_name }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        >
                    </div>
                    <div class="mb-4">
                        <label for="phone_number{{ $contact->id }}" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input 
                            type="text" 
                            name="phone_number" 
                            id="phone_number{{ $contact->id }}" 
                            value="{{ $contact->phone_number }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        >
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeModal('editModal{{ $contact->id }}')" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- Include jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#contactsTable').DataTable({
                "pagingType": "numbers",
                "language": {
                    "emptyTable": "No contacts available",
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "responsive": true,
                "autoWidth": false
            });
        });

        // Function to open modal (if using data-bs-toggle)
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        // Function to close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            // Check all modals
            @foreach ($contacts as $contact)
            const modal{{ $contact->id }} = document.getElementById('editModal{{ $contact->id }}');
            if (modal{{ $contact->id }} && event.target === modal{{ $contact->id }}) {
                closeModal('editModal{{ $contact->id }}');
            }
            @endforeach
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                @foreach ($contacts as $contact)
                const modal{{ $contact->id }} = document.getElementById('editModal{{ $contact->id }}');
                if (modal{{ $contact->id }} && !modal{{ $contact->id }}.classList.contains('hidden')) {
                    closeModal('editModal{{ $contact->id }}');
                }
                @endforeach
            }
        });

        // If using Bootstrap JavaScript, you can use this instead
        // But since we're using Tailwind, we'll handle modals manually
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                const targetModal = this.getAttribute('data-bs-target');
                document.querySelector(targetModal).classList.remove('hidden');
            });
        });
    </script>
</x-app-layout>