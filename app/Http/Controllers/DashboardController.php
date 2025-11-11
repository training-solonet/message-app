<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::with(['category','histories' => function($q) {
                $q->orderBy('created_at', 'asc'); // ambil semua pesan urut dari lama ke baru
            }])
            ->orderBy('contact_name', 'asc')
            ->get();
        
        $categories = Category::all();
        $botStatus = DB::table('bot_statuses')->value('status');

        return view('dashboard', compact('contacts','categories','botStatus'));
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
    public function update(Request $request, $id)
    {
        $request->validate([
            'contact_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'category' => 'nullable|string|max:255',
        ]);

        try {
            $contact = Contact::findOrFail($id);

            $contact->update([
                'contact_name' => $request->contact_name,
                'phone_number' => $request->phone_number,
                'category' => $request->category,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully.',
                'contact' => $contact
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating contact: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}