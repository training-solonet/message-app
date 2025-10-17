<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <h1 class="mt-2 text-2xl font-medium text-gray-900">
                        <strong>Logs</strong>
                    </h1>
                    <p class="text-gray-600 mt-1">Monitor your logs here.</p>

                    <div class="mt-6 overflow-x-auto">
                        <table id="logsTable" class="min-w-full border border-gray-200 rounded-lg text-sm text-gray-800">
                            <thead class="bg-gray-800 text-white text-center">
                                <tr>
                                    <th class="py-3 px-4 border-b border-gray-300 w-16">No.</th>
                                    <th class="py-3 px-4 border-b border-gray-300 w-2/3">Message</th>
                                    <th class="py-3 px-4 border-b border-gray-300 w-1/3">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @forelse ($logs as $log)
                                    <tr class="hover:bg-gray-100 transition">
                                        <td class="py-2 px-4 border-b border-gray-200 text-center">{{ $i++ }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200 truncate max-w-xs">{{ $log->message }}</td>
                                        <td class="py-2 px-4 border-b border-gray-200 text-center whitespace-nowrap">{{ $log->created_at }}</td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $('#logsTable').DataTable({
        "order": [[2, "desc"]],
        "pagingType": "numbers",
        "language": {
            "emptyTable": "No data available"
        }
    });
    </script>
</x-app-layout>
