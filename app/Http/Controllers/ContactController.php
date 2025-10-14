<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::with('category')->get();
        $categories = Category::all();

        return view('contacts', compact('contacts', 'categories'));
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
    public function update(Request $request, Contact $contact)
    {
        $request->validate([
            'contact_name' => 'required|string',
            'category_id' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        // Detect category change
        $oldCategory = $contact->category_id;

        $contact->update([
            'contact_name' => $request->contact_name,
            'category_id' => $request->category_id,
            'phone_number' => $request->phone_number,
        ]);

        // If category changed, remove all existing schedule links
        if ($oldCategory != $request->category_id) {
            $contact->schedules()->detach();
        }

        return redirect()->back()->with('message', 'Contact updated and schedules cleared for new category!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->back()->with('message', 'Contact deleted!');
    }
}
