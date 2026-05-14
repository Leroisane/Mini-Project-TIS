<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConcertTicket;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Mengambil semua daftar konser
    public function index() {
        $events = ConcertTicket::all();
        return response()->json([
            'status' => 'success',
            'data' => $events
        ]);
    }

    // Mengambil detail satu tiket berdasarkan ID
    public function show($id) {
        $event = ConcertTicket::find($id);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }
        return response()->json($event);
    }

    public function reduceQuota(Request $request, $id) {
        $event = ConcertTicket::findOrFail($id);
        $event->quota = $event->quota - $request->qty; // Kurangi sesuai jumlah pesanan
        $event->save();

        return response()->json(['message' => 'Kuota berhasil dikurangi!']);
    }
}