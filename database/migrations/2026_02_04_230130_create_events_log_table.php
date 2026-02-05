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
        Schema::create('events_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type', 50); // POST, PUT, DELETE
            $table->string('resource_type', 100); // Product, Currency, etc.
            $table->unsignedBigInteger('resource_id')->nullable(); // ID del recurso afectado
            $table->string('endpoint', 255); // Ruta completa del endpoint
            $table->string('method', 10); // HTTP method
            $table->json('data')->nullable(); // Datos adicionales (payload, response, etc.)
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('user_id');
            $table->index('event_type');
            $table->index('resource_type');
            $table->index('created_at');
            $table->index(['resource_type', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events_log');
    }
};
