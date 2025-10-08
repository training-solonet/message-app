<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schedules') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <livewire:scheduler-modal />
                    </div>

                    <div class="mt-2 overflow-x-auto">
                        <table id="schedulesTable" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No.</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Scheduler Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Message</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Send At</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php $i = 1; @endphp
                                @forelse ($schedules as $schedule)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $i++ }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $schedule->scheduler_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ \Illuminate\Support\Str::limit($schedule->message, 20) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $schedule->schedule_time }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button 
                                                    type="button"
                                                    onclick="document.getElementById('editModal_{{ $schedule->id }}').classList.remove('hidden')"
                                                    class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                >
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                                </button>

                                                <form action="{{ route('schedules.destroy', $schedule) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button 
                                                        type="submit" 
                                                        class="inline-flex items-center px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                        onclick="return confirm('Are you sure you want to delete this schedule?')"
                                                    >
                                                        <i class="fa-solid fa-trash mr-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal per Schedule -->
                                    <div id="editModal_{{ $schedule->id }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                                        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                                            <div class="mt-3">
                                                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Schedule</h3>

                                                <form id="editForm_{{ $schedule->id }}" method="POST" action="{{ route('schedules.update', $schedule->id) }}">
                                                    @csrf @method('PUT')

                                                    <div class="mt-4">
                                                        <x-label for="edit_scheduler_name_{{ $schedule->id }}" value="{{ __('Scheduler Name') }}" />
                                                        <x-input id="edit_scheduler_name_{{ $schedule->id }}" type="text" class="mt-1 block w-full"
                                                                name="scheduler_name" required
                                                                value="{{ $schedule->scheduler_name }}" />
                                                    </div>

                                                    <div class="mt-4">
                                                        <x-label for="edit_schedule_time_{{ $schedule->id }}" value="{{ __('Time (HH:MM)') }}" />
                                                        <x-input id="edit_schedule_time_{{ $schedule->id }}" type="time" class="mt-1 block w-full"
                                                                name="schedule_time" required
                                                                value="{{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }}" />
                                                    </div>

                                                    <div class="mt-4">
                                                        <x-label for="edit_message_{{ $schedule->id }}" value="{{ __('Message') }}" />
                                                        <textarea id="edit_message_{{ $schedule->id }}" name="message" rows="3"
                                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                                required>{{ $schedule->message }}</textarea>
                                                    </div>

                                                    <!-- Category Dropdown -->
                                                    <div class="mt-4">
                                                        <x-label value="{{ __('Select Category') }}" />
                                                        <select 
                                                            name="selectedCategory" 
                                                            id="edit_category_{{ $schedule->id }}" 
                                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                            required
                                                        >
                                                            <option value="">-- Select a Category --</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" 
                                                                    {{ $schedule->contacts->first()?->category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->category_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="flex justify-end space-x-3 mt-6">
                                                        <x-secondary-button type="button" onclick="document.getElementById('editModal_{{ $schedule->id }}').classList.add('hidden')">
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

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#schedulesTable')) {
                $('#schedulesTable').DataTable().clear().destroy();
            }

            $('#schedulesTable').DataTable({
                "order": [[0, "desc"]],
                "pagingType": "numbers",
                "language": {
                    "emptyTable": "No schedules found.",
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
