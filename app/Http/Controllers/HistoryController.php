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
            'direction' => 'required|in:in,out',
            'status' => 'required|in:sent,failed',
            'is_read' => 'required',
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
            'status' => $request->status,
            'is_read' => $request->is_read,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil disimpan',
            'data' => $history,
        ], 201);
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

    public function markAsRead($id)
    {
        \App\Models\History::where('contact_id', $id)
            ->where('direction', 'in')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function toggleNote(History $history)
    {
        $history->update([
            'noted' => !$history->noted
        ]);

        return response()->json([
            'success' => true,
            'noted' => $history->noted
        ]);
    }

}
