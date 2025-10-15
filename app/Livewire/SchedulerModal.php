<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Schedule;
use Livewire\Component;

class SchedulerModal extends Component
{
    public $showModal = false;

    public $scheduler_name;
    public $message;
    public $schedule_time;
    public $selectedCategory = ''; 
    public $categories = [];

    protected $rules = [
        'scheduler_name' => 'required|string|max:13',
        'message' => 'required|string|max:255',
        'schedule_time' => 'required|date_format:H:i',
        'selectedCategory' => 'required|exists:categories,id',
    ];

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function save()
    {
        $this->validate();

        // Buat schedule baru
        $schedule = Schedule::create([
            'scheduler_name' => $this->scheduler_name,
            'message' => $this->message,
            'schedule_time' => $this->schedule_time,
        ]);

        // Hubungkan dengan category_id melalui pivot contact_schedules
        $schedule->categories()->attach($this->selectedCategory);

        // Reset form
        $this->reset(['scheduler_name', 'message', 'schedule_time', 'selectedCategory', 'showModal']);

        // Notify parent
        $this->dispatch('schedulerAdded');

        return redirect()->route('schedules.index');
    }

    public function render()
    {
        return view('livewire.scheduler-modal');
    }
}
