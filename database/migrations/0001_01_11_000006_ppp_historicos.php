<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppp_historicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppp_id');
            $table->unsignedBigInteger('status_anterior')->nullable();
            $table->unsignedBigInteger('status_atual');
            $table->text('justificativa')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('ppp_id')->references('id')->on('pca_ppps')->onDelete('cascade');
            $table->foreign('status_anterior')->references('id')->on('ppp_statuses');
            $table->foreign('status_atual')->references('id')->on('ppp_statuses');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppp_historicos');
    }
};