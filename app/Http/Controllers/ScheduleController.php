<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index()
    {
        $schedules = Schedule::with('contacts')->get();
        $contacts = Contact::all();
        $categories = Category::all(); // Fetch all categories

        return view('schedules', compact('schedules', 'contacts', 'categories'));
    }

    /**
     * Return schedules as JSON.
     */
    public function showSchedules()
    {
        $schedules = Schedule::with('contacts')->get();
        return response()->json($schedules);
    }

    /**
     * Update the specified schedule.
     */
    public function update(Request $request, string $id)
    {
        $schedule = Schedule::findOrFail($id);

        $validated = $request->validate([
            'scheduler_name' => 'required|string|min:3|max:50',
            'message' => 'required|string|min:1|max:255',
            'schedule_time' => 'required|date_format:H:i',
            'selectedCategory' => 'required|exists:categories,id',
        ]);

        // Update schedule
        $schedule->update([
            'scheduler_name' => $validated['scheduler_name'],
            'message' => $validated['message'],
            'schedule_time' => $validated['schedule_time'],
        ]);

        // Sync contacts by category_id
        $contactIds = Contact::where('category_id', $validated['selectedCategory'])
            ->pluck('id')
            ->toArray();

        $schedule->contacts()->sync($contactIds);

        return redirect()
            ->route('schedules.index')
            ->with('message', 'Schedule updated successfully for category.');
    }

    /**
     * Delete a schedule.
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->contacts()->detach();
        $schedule->delete();

        return redirect()
            ->route('schedules.index')
            ->with('message', 'Schedule deleted successfully.');
    }
}
