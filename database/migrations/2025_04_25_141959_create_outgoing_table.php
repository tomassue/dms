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
        Schema::create('outgoing', function (Blueprint $table) {
            $table->id();
            $table->morphs('outgoingable');
            $table->date('date');
            $table->string('details');
            $table->string('destination');
            $table->string('person_responsible');
            $table->foreignId('ref_status_id');
            $table->foreignId('office_id')->comment('a.k.a role_id or Offices');
            $table->foreignId('ref_division_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('outgoing_others', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_type');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_procurements', function (Blueprint $table) {
            $table->id();
            $table->string('pr_no');
            $table->string('po_no');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_ris', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->string('ppmp_code');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outgoing_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing');
        Schema::dropIfExists('outgoing_others');
        Schema::dropIfExists('outgoing_payrolls');
        Schema::dropIfExists('outgoing_procurement');
        Schema::dropIfExists('outgoing_ris');
        Schema::dropIfExists('outgoing_vouchers');
    }
};
