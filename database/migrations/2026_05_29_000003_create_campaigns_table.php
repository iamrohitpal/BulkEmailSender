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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smtp_setting_id')->nullable()->constrained('smtp_settings')->onDelete('set null');
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->string('status')->default('draft'); // draft, processing, completed, failed
            $table->integer('total_emails')->default(0);
            $table->integer('sent_emails')->default(0);
            $table->integer('failed_emails')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
