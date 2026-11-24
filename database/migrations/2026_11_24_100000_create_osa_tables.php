<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('osa_admins', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('osa_executives', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('position');
            $t->unsignedSmallInteger('year_completed')->nullable();
            $t->foreignId('house_id')->nullable()->constrained()->nullOnDelete();
            $t->text('bio')->nullable();
            $t->string('chapter')->nullable()->comment('e.g. Global, Accra, UK');
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('osa_events', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('venue')->nullable();
            $t->text('description')->nullable();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at')->nullable();
            $t->string('token', 40)->unique();
            $t->boolean('registration_open')->default(true);
            $t->timestamps();
        });

        Schema::create('osa_attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('osa_event_id')->constrained('osa_events')->cascadeOnDelete();
            $t->string('name');
            $t->unsignedSmallInteger('year_completed');
            $t->foreignId('house_id')->nullable()->constrained()->nullOnDelete();
            $t->string('email');
            $t->string('phone', 20);
            $t->string('sms_status')->default('pending');
            $t->string('sms_note')->nullable();
            $t->string('email_status')->default('pending');
            $t->string('email_note')->nullable();
            $t->string('source', 12)->default('qr');
            $t->timestamp('checked_in_at')->useCurrent();
            $t->timestamps();

            // One check-in per phone number per meeting.
            $t->unique(['osa_event_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osa_attendances');
        Schema::dropIfExists('osa_events');
        Schema::dropIfExists('osa_executives');
        Schema::dropIfExists('osa_admins');
    }
};
