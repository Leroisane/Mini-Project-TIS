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
        Schema::create('concert_tickets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('artist_name');
            $table->string('concert_name');
            $table->timestamp('event_date');
            $table->string('ticket_category');
            $table->decimal('price', 12, 2);
            $table->integer('quota');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concert_tickets');
    }
};
