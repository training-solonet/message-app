<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Contact;
use App\Models\Category;
use Illuminate\Cache\RedisTagSet;

class ContactFormModal extends Component
{
    public $showModal = false;
    public $contact_name;
    public $selectedCategory; // this will now be the category ID
    public $phone_number;

    public $categories = [];

    protected function rules()
    {
        return [
            'contact_name' => 'required|string|min:3',
            'phone_number' => 'required|string|min:8',
            'selectedCategory' => 'required', // validate ID exists
        ];
    }

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function save()
    {
        $this->validate();
        // Create new contact with category_id
        Contact::create([
            'contact_name' => $this->contact_name,
            'category_id' => $this->selectedCategory,
            'phone_number' => $this->phone_number,
        ]);

        // Refresh category list if needed
        $this->mount();

        // Reset fields and close modal
        $this->reset(['contact_name', 'selectedCategory', 'phone_number', 'showModal']);

        // Dispatch Livewire event
        $this->dispatch('contactAdded');

        session()->flash('message', 'Contact added successfully.');

        return redirect()->route('contacts.index');
    }

    public function render()
    {
        return view('livewire.contact-form-modal');
    }
}
