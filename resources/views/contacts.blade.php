<x-app-layout>
    <style>
        nav a {
            text-decoration: none !important;
        }
        nav a:hover {
            text-decoration: none !important;
        }
    </style>

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
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Category</th>
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
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $contact->category->category_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-2">
                                                    <button 
                                                        class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $contact->id }}">
                                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                    </button>

                                                    <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="inline">
                                                        @csrf @method('DELETE')
                                                        <button 
                                                            type="submit" 
                                                            class="inline-flex items-center px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                            onclick="return confirm('Are you sure you want to delete this contact?')">
                                                            <i class="fa-solid fa-trash mr-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Handled by DataTables --}}
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Edit Modals -->
    @foreach ($contacts as $contact)
    <div class="modal fade" id="editModal{{ $contact->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $contact->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('contacts.update', $contact) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $contact->id }}">Edit Contact</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input 
                                type="text" name="contact_name" value="{{ $contact->contact_name }}" 
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input 
                                type="text" name="phone_number" value="{{ $contact->phone_number }}" 
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="" disabled>Select a category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" 
                                        {{ $cat->id == $contact->category_id ? 'selected' : '' }}>
                                        {{ $cat->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Bootstrap & DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#contactsTable').DataTable({
                "order": [[0, "asc"]],
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
    </script>
</x-app-layout>
