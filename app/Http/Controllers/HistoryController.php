<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\History;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'contact_number' => 'required|string',
            'message' => 'required|string',
            'direction' => 'required|string',
        ]);

        $contact = Contact::where('phone_number', $request->contact_number)->first();

        if(!$contact){
            return response()->json([
                'success' => false,
                'message' => 'Nomor tidak terdaftar di contacts, pesan tidak disimpan',
            ], 400);
        }

        $history = History::create([
            'contact_id' => $contact->id,
            'message' => $request->message,
            'direction' => $request->direction,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil disimpan',
            'data' => $history,
        ]);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function historiesByContact($contactNumber)
    {
        $contact = Contact::where('phone_number', $contactNumber)->firstOrFail();

        return response()->json([
            'success' => true,
            'contact' => $contact,
            'histories' => $contact->histories()->orderBy('created_at', 'asc')->get(),
        ]);
    }
}
