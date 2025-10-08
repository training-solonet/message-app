<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;

class CategoryModal extends Component
{
    public $showModal = false;
    public $category;

    protected $rules = [
        'category' => 'required|string|min:3|unique:categories,category_name',
    ];

    public function openModal()
    {
        $this->resetValidation();
        $this->reset('category');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        Category::create([
            'category_name' => $this->category,
        ]);

        $this->reset('category');
        $this->showModal = false;

        session()->flash('success', 'Category added successfully!');
        $this->dispatch('category-added'); // trigger event if needed

        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.category-modal');
    }
}
