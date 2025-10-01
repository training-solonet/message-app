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

                    {{-- <h1 class="mt-2 text-2xl font-medium text-gray-900">
                        <strong>Manage your daily messages</strong>
                    </h1>
                    @if(session('message'))
                        <div class="p-2 bg-yellow-100 text-yellow-800 rounded mb-3">
                            {{ session('message') }}
                        </div>
                    @endif
                    <div class="container mb-5">
                        <h1>Manage the messages you desired to send at any time here.</h1>
                    </div> --}}
                    <div class="container d-flex align-items-center justify-content-between">
                        <livewire:scheduler-modal />
                    </div>
                    <div class="container mt-2">
                        <div>
                            <table id="schedulesTable" class="table table-bordered table-striped w-full">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>Scheduler Name</th>
                                        <th>Message</th>
                                        <th>Send At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @forelse ($schedules as $schedule)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $schedule->scheduler_name }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($schedule->message, 20) }}</td>
                                            <td>{{ $schedule->schedule_time }}</td>
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
    $('#schedulesTable').DataTable({
        "pagingType": "numbers",
        "language": {
            "emptyTable": "No data available"
        }
    });
    </script>
</x-app-layout>
