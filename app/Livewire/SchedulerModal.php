<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Schedule;
use Livewire\Component;
use Livewire\WithFileUploads;

class SchedulerModal extends Component
{
    use WithFileUploads;

    public $showModal = false;

    public $scheduler_name;
    public $message;
    public $schedule_time;
    public $selectedCategory = ''; 
    public $categories = [];
    public $file;

    protected $rules = [
        'scheduler_name' => 'required|string|max:13',
        'message' => 'required|string|max:255',
        'schedule_time' => 'required|date_format:H:i',
        'selectedCategory' => 'required|exists:categories,id',
        'file' => 'nullable|file|max:5120',
    ];

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function save()
    {
        $this->validate();

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('uploads/schedules', 'public');
        }

        // Buat schedule baru
        $schedule = Schedule::create([
            'scheduler_name' => $this->scheduler_name,
            'message' => $this->message,
            'schedule_time' => $this->schedule_time,
            'file_path' => $filePath, // simpan path-nya
        ]);

        // Hubungkan dengan category_id
        $schedule->categories()->attach($this->selectedCategory);

        // Reset form
        $this->reset(['scheduler_name', 'message', 'schedule_time', 'selectedCategory', 'file', 'showModal']);

        // Notify parent
        $this->dispatch('schedulerAdded');

        // return redirect()->route('schedules.index');
    }

    public function render()
    {
        return view('livewire.scheduler-modal');
    }
}
