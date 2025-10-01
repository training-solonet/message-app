<?php

namespace App\Livewire;

use App\Models\Contact;
use Livewire\Component;
use App\Models\Schedule; // model scheduler kamu

class SchedulerModal extends Component
{
    public $showModal = false;

    public $scheduler_name;
    public $message;
    public $schedule_time;
    public $selectedContacts = [];

    protected $rules = [
        'scheduler_name' => 'required|string|max:13',
        'message' => 'required|string|max:255',
        'schedule_time' => 'required|date_format:H:i',
        'selectedContacts' => 'required|array|min:1'
    ];

    public function save()
    {
        $this->validate();

        $schedule = Schedule::create([
            'scheduler_name' => $this->scheduler_name,
            'message' => $this->message,
            'schedule_time' => $this->schedule_time,
        ]);

        $schedule->contacts()->attach($this->selectedContacts);

        $this->reset(['scheduler_name', 'message', 'schedule_time', 'selectedContacts', 'showModal']);

        $this->dispatch('schedulerAdded'); // buat refresh tabel di ManageController

        return redirect()->route('manage.index');
    }

    public function render()
    {
        return view('livewire.scheduler-modal', [
            'contacts' => Contact::all(),
        ]);
    }
}
