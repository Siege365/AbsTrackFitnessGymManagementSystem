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
        // 1. Security Scans Table
        if (!Schema::hasTable('security_scans')) {
            Schema::create('security_scans', function (Blueprint $table) {
                $table->id();
                $table->string('scan_type')->default('full'); // full, config, logs, owasp
                $table->string('status')->default('completed'); // completed, running, failed
                $table->unsignedTinyInteger('health_score')->default(100);
                $table->unsignedInteger('total_checks')->default(0);
                $table->unsignedInteger('passed_checks')->default(0);
                $table->unsignedInteger('warning_checks')->default(0);
                $table->unsignedInteger('critical_checks')->default(0);
                $table->json('results')->nullable();
                $table->string('scanned_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Security Threat Alerts Table
        if (!Schema::hasTable('security_threat_alerts')) {
            Schema::create('security_threat_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('threat_type'); // brute_force, privilege_escalation, financial_anomaly, off_hours, misconfiguration
                $table->enum('severity', ['info', 'low', 'medium', 'high', 'critical'])->default('low');
                $table->string('title');
                $table->text('description');
                $table->string('source_ip')->nullable();
                $table->string('affected_user')->nullable();
                $table->json('metadata')->nullable();
                $table->enum('status', ['active', 'investigating', 'resolved', 'dismissed'])->default('active');
                $table->text('resolution_notes')->nullable();
                $table->string('resolved_by')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('threat_type');
                $table->index('severity');
                $table->index('status');
                $table->index('created_at');
            });
        }

        // 3. AI Security Consultation / Advisor Logs Table
        if (!Schema::hasTable('security_ai_consultations')) {
            Schema::create('security_ai_consultations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('user_name')->nullable();
                $table->string('topic')->default('general');
                $table->text('query');
                $table->longText('ai_response');
                $table->json('suggested_actions')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_ai_consultations');
        Schema::dropIfExists('security_threat_alerts');
        Schema::dropIfExists('security_scans');
    }
};

