<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Logcat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">

                    <h1 class="mt-2 text-2xl font-medium text-gray-900">
                        <strong>Logs</strong>
                    </h1>
                    <div class="container mt-2">
                        <h2>Monitor your logs here.</h2>

                        <div class="container mt-5">
                            <table id="logsTable" class="table table-bordered table-striped mt-3">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>Message</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 1;
                                    @endphp
                                    @forelse ($logs as $log)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $log->message }}</td>
                                            <td>{{ $log->created_at }}</td>
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
