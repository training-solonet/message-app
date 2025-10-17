<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules.
     */
    public function index()
    {
        $schedules = Schedule::with('categories')->get();
        $contacts = Contact::all();
        $categories = Category::all();

        return view('schedules', compact('schedules', 'contacts', 'categories'));
    }

    /**
     * Return schedules as JSON.
     */
    public function showSchedules()
    {
        $schedules = Schedule::with('categories')
                            ->where('status', 'active')
                            ->get();
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
            'file' => 'nullable|file|max:5120', // 5MB max
        ]);

        // Handle file upload (jika ada)
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($schedule->file_path && Storage::disk('public')->exists($schedule->file_path)) {
                Storage::disk('public')->delete($schedule->file_path);
            }

            // Simpan file baru
            $filePath = $request->file('file')->store('uploads/schedules', 'public');
            $schedule->file_path = $filePath;
        }

        // Update field lain
        $schedule->scheduler_name = $validated['scheduler_name'];
        $schedule->message = $validated['message'];
        $schedule->schedule_time = $validated['schedule_time'];
        $schedule->save();

        // Update kategori relasi pivot
        $schedule->categories()->sync([$validated['selectedCategory']]);

        return redirect()
            ->route('schedules.index')
            ->with('message', 'Schedule updated successfully.');
    }

    /**
     * Delete a schedule.
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::findOrFail($id);

        // Hapus file jika ada
        if ($schedule->file_path && Storage::disk('public')->exists($schedule->file_path)) {
            Storage::disk('public')->delete($schedule->file_path);
        }

        // Hapus relasi kategori
        $schedule->categories()->detach();

        // Hapus data schedule
        $schedule->delete();

        return redirect()
            ->route('schedules.index')
            ->with('message', 'Schedule deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->status = $schedule->status === 'active' ? 'inactive' : 'active';
        $schedule->save();

        return response()->json([
            'success' => true,
            'new_status' => $schedule->status,
        ]);
    }
}
