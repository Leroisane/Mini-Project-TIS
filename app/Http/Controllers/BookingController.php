<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input (Aturan Maksimal 5 Tiket)
        $request->validate([
            'event_id' => 'required|uuid',
            'qty' => 'required|integer|min:1|max:5',
        ], [
            'qty.max' => 'Waduh, maksimal pembelian cuma boleh 5 tiket ya!',
            'qty.min' => 'Minimal beli 1 tiket dong.'
        ]);

        // Konfigurasi URL Service (Bisa dipindah ke .env nanti)
        $urlServiceA = "http://127.0.0.1:8000/api/events";
        $urlServiceC = "http://127.0.0.1:8002/api/payments";

        // 2. CEK STOK & HARGA ke Service A
        try {
            // Kita lakukan pemanggilan sekali saja dan simpan di $responseEvent
            $responseEvent = Http::timeout(5)->get($urlServiceA . "/" . $request->event_id);
            
            if ($responseEvent->failed()) {
                return response()->json(['message' => 'Event tidak ditemukan di katalog!'], 404);
            }

            $eventData = $responseEvent->json();
            $stokTersedia = $eventData['quota'] ?? 0; 
            $hargaSatuan = $eventData['price'] ?? 0; 

            if ($stokTersedia < $request->qty) {
                return response()->json(['message' => 'Maaf, stok tiket sisa ' . $stokTersedia], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal terhubung ke Layanan Katalog (Service A).'], 503);
        }

        // 3. HITUNG TOTAL & GENERATE ID
        $totalPrice = $hargaSatuan * $request->qty;
        $bookingId = 'BK-' . strtoupper(Str::random(6));

        // 4. SIMPAN KE DATABASE (Supabase) dengan status PENDING
        $booking = Booking::create([
            'booking_id'  => $bookingId,
            'event_id'    => $request->event_id,
            'qty'         => $request->qty,
            'total_price' => $totalPrice,
            'status'      => 'PENDING',
        ]);

        // 5. MINTA VIRTUAL ACCOUNT ke Service C
        try {
            $responsePayment = Http::timeout(5)->post($urlServiceC, [
                'booking_id'   => $bookingId,
                'total_amount' => $totalPrice
            ]);

            if ($responsePayment->successful()) {
                $vaNumber = $responsePayment->json('va_number');
                $booking->update(['va_number' => $vaNumber]);
            }
        } catch (\Exception $e) {
            // Kita tidak return error di sini agar booking tetap tersimpan, 
            // tapi kita beri info di response nanti bahwa VA gagal dimuat.
            $vaError = "Gagal mendapatkan nomor VA dari Service C.";
        }

        // 6. POTONG STOK ke Service A
        try {
            $responseReduce = Http::timeout(5)->patch($urlServiceA . "/" . $request->event_id . "/reduce-quota", [
                'qty' => $request->qty
            ]);
            $reduceStatus = $responseReduce->successful() ? 'Success' : 'Failed';
        } catch (\Exception $e) {
            $reduceStatus = 'Error connecting to Service A for stock update';
        }

        // 7. KEMBALIKAN RESPON KE USER
        return response()->json([
            'status'  => 'success',
            'message' => 'Booking berhasil dibuat!',
            'data'    => $booking->fresh(), // Ambil data terbaru setelah update VA
            'payment_info' => $vaError ?? 'Silakan lakukan pembayaran ke nomor VA yang tertera.',
            'stock_update' => $reduceStatus
        ], 201);
    }

    /**
     * Webhook/Callback dari Service C
     */
    public function handleCallback(Request $request)
    {
        // Validasi input dari webhook
        $request->validate([
            'booking_id' => 'required',
            'status' => 'required'
        ]);

        $booking = Booking::where('booking_id', $request->booking_id)->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->update([
            'status' => strtoupper($request->status) // Pastikan uppercase (PAID)
        ]);

        return response()->json([
            'message' => 'Callback processed successfully',
            'new_status' => $booking->status
        ]);
    }
}