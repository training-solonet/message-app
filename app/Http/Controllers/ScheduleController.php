<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = Schedule::all();
        $contacts = Contact::all();

        return view('schedules', compact('schedules', 'contacts'));
    }

    public function showSchedules()
    {
        $schedules = Schedule::with('contacts')->get();
        return response()->json($schedules);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $schedule = Schedule::findOrFail($id);

        $request->validate([
            'scheduler_name' => 'required|string|min:3',
            'message' => 'required|string|min:1',
            'schedule_time' => 'required|date_format:H:i',
            'selectedContacts' => 'required|array',
            'selectedContacts.*' => 'exists:contacts,id',
        ]);

        // Update schedule record
        $schedule->update([
            'scheduler_name' => $request->scheduler_name,
            'message' => $request->message,
            'schedule_time' => $request->schedule_time,
        ]);

        // Update kontak terkait di pivot table
        $schedule->contacts()->sync($request->selectedContacts);

        return redirect()->route('schedules.index')
                        ->with('message', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
