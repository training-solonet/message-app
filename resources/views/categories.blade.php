<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contact Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <livewire:category-modal />
                    </div>
                    <div class="mt-2">
                        <div class="overflow-x-auto">
                            <table id="categoriesTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Contact Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php $i = 1; @endphp
                                    @forelse ($categories as $category)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $i++ }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $category->category_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-2">
                                                    <button 
                                                        type="button"
                                                        onclick="document.getElementById('editModal_{{ $category->id }}').classList.remove('hidden')"
                                                        class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                    >
                                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                    </button>

                                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline">
                                                        @csrf @method('DELETE')
                                                        <button 
                                                            type="submit" 
                                                            class="inline-flex items-center px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                            onclick="return confirm('Are you sure you want to delete this category?')"
                                                        >
                                                            <i class="fa-solid fa-trash mr-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal per Category -->
                                        <div id="editModal_{{ $category->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                                            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                                                <div class="mt-3">
                                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Category</h3>

                                                    <form id="editForm_{{ $category->id }}" method="POST" action="{{ route('categories.update', $category->id) }}">
                                                        @csrf @method('PUT')

                                                        <div class="mt-4">
                                                            <x-label for="edit_category_name_{{ $category->id }}" value="{{ __('Category Name') }}" />
                                                            <x-input id="edit_category_name_{{ $category->id }}" type="text" class="mt-1 block w-full"
                                                                    name="category_name" required
                                                                    value="{{ $category->category_name }}" />
                                                        </div>
                                                        <div class="flex justify-end space-x-3 mt-6">
                                                            <x-secondary-button type="button" onclick="document.getElementById('editModal_{{ $category->id }}').classList.add('hidden')">
                                                                {{ __('Cancel') }}
                                                            </x-secondary-button>

                                                            <x-button type="submit">
                                                                {{ __('Update') }}
                                                            </x-button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>


                                    @empty
                                        
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">

    <!-- Include jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Destroy existing instance if reloaded
            if ($.fn.DataTable.isDataTable('#categoriesTable')) {
                $('#categoriesTable').DataTable().clear().destroy();
            }

            // Initialize DataTable once
            $('#categoriesTable').DataTable({
                "order": [[0, "asc"]],
                "pagingType": "numbers",
                "language": {
                    "emptyTable": "No category found.",
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
