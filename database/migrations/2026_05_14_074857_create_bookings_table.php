<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->string('booking_id')->unique(); // Contoh: BK-001 
        $table->uuid('event_id');               // Sesuaikan dengan UUID dari Service A [cite: 14]
        $table->integer('qty');                 // Maksimal 5 tiket nanti divalidasi di Controller
        $table->decimal('total_price', 12, 2);  // Format uang 
        $table->string('status')->default('PENDING'); // Status awal 
        $table->string('va_number')->nullable();     // Akan diisi oleh Service C [cite: 37]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
