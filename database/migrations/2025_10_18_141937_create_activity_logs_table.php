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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // GET, POST, PUT, DELETE, etc.
            $table->string('route_name')->nullable(); // nombre de la ruta
            $table->string('url', 500); // URL completa
            $table->string('method'); // HTTP method
            $table->string('ip_address', 45); // IPv6 compatible - client IP
            $table->string('real_ip', 45)->nullable(); // Real public IP from external services
            $table->text('user_agent')->nullable();
            $table->json('ip_info')->nullable(); // Geo location and ISP info from IP services
            $table->json('request_data')->nullable(); // datos de la petición (sin passwords)
            $table->json('response_data')->nullable(); // datos de la respuesta
            $table->integer('status_code')->nullable(); // código de respuesta HTTP
            $table->integer('execution_time')->nullable(); // tiempo de ejecución en ms
            $table->string('controller')->nullable(); // controlador que maneja la petición
            $table->string('controller_action')->nullable(); // método del controlador
            $table->text('description')->nullable(); // descripción legible de la acción
            $table->json('metadata')->nullable(); // metadatos adicionales
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['route_name', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['real_ip', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
