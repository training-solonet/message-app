<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Contact;

class ContactFormModal extends Component
{
    public $showModal = false;
    public $contact_name;
    public $phone_number;

    protected $rules = [
        'contact_name' => 'required|string|min:3',
        'phone_number' => 'required|string|min:8',
    ];

    public function render()
    {
        return view('livewire.contact-form-modal');
    }

    public function save()
    {
        $this->validate();

        Contact::create([
            'contact_name' => $this->contact_name,
            'phone_number' => $this->phone_number,
        ]);

        $this->reset(['contact_name', 'phone_number', 'showModal']);

        $this->dispatch('contactAdded');

        return redirect()->route('manage.index');
    }
}
