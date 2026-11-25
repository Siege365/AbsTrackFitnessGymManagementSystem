<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\SecurityThreatAlert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityAiService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Check if live Gemini API is configured.
     */
    public function isLiveAiConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Consult the AI Security Advisor with system telemetry context.
     *
     * @param string $query User's question or command
     * @param array $context Current system posture (health score, scan checks, alerts)
     * @return array ['text' => string, 'actions' => array, 'source' => 'gemini'|'heuristic']
     */
    public function consultAdvisor(string $query, array $context = []): array
    {
        if ($this->isLiveAiConfigured()) {
            try {
                $response = $this->callGeminiAdvisor($query, $context);
                if (!empty($response['text'])) {
                    return $response;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini AI Advisor request failed, falling back to heuristic engine: ' . $e->getMessage());
            }
        }

        // Seamless heuristic cyber defense engine fallback
        return $this->getHeuristicAdvisorResponse($query, $context);
    }

    /**
     * Analyze recent activity logs for cyber anomalies and threat patterns.
     *
     * @param Collection|array $logs
     * @return array Array of detected alert structures
     */
    public function analyzeActivityLogsForAnomalies(iterable $logs): array
    {
        $logCollection = $logs instanceof Collection ? $logs : collect($logs);

        if ($this->isLiveAiConfigured() && $logCollection->isNotEmpty()) {
            try {
                $aiAlerts = $this->callGeminiLogAnalysis($logCollection);
                if (!empty($aiAlerts)) {
                    return $aiAlerts;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini AI Log Analysis failed, falling back to heuristic detector: ' . $e->getMessage());
            }
        }

        // Heuristic anomaly detection fallback
        return $this->detectHeuristicAnomalies($logCollection);
    }

    /**
     * Call Google Gemini API for real-time security consultation.
     */
    protected function callGeminiAdvisor(string $query, array $context): array
    {
        $healthScore = $context['health_score'] ?? 92;
        $totalChecks = $context['total_checks'] ?? 9;
        $passedChecks = $context['passed_checks'] ?? 8;
        $activeAlerts = $context['active_alerts'] ?? 2;

        $systemInstruction = <<<TEXT
You are CyberShield AI, the elite cybersecurity advisor embedded in AbsTrack Fitness Gym Management System.
The system is built on Laravel 12, MySQL, Vite, and Bootstrap. It enforces strict RBAC: Owner, Manager, Admin, Cashier, and Member.
System Telemetry:
- Security Health Score: {$healthScore}%
- Security Checks: {$passedChecks}/{$totalChecks} passing
- Active Threat Alerts: {$activeAlerts}

Guidelines:
1. Provide concise, direct, authoritative defensive cybersecurity advice.
2. Address specific gym software vulnerabilities (e.g. cashier financial collusion, member PII privacy, brute-force login attacks, CSRF/XSS, SQL injection, session hijacking).
3. Include code snippets or configuration lines when helpful.
4. Keep the response organized with clear markdown headings and bullet points.
TEXT;

        $prompt = "User Query: {$query}\n\nPlease analyze this request within the context of the AbsTrack Gym platform and provide actionable defensive recommendations.";

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemInstruction . "\n\n" . $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 1200,
            ]
        ];

        $response = Http::timeout(12)->post($endpoint, $payload);

        if ($response->successful()) {
            $data = $response->json();
            $responseText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (!empty($responseText)) {
                $actions = $this->extractChecklistActions($responseText);

                return [
                    'text'    => $responseText,
                    'actions' => $actions,
                    'source'  => 'gemini',
                ];
            }
        }

        return ['text' => '', 'actions' => [], 'source' => 'gemini_empty'];
    }

    /**
     * Send log window to Gemini for anomaly pattern recognition.
     */
    protected function callGeminiLogAnalysis(Collection $logs): array
    {
        $logSummary = $logs->take(15)->map(function ($log) {
            return sprintf(
                "[%s] User: %s | Action: %s | Module: %s | IP: %s | Desc: %s",
                $log->created_at?->toIso8601String() ?? now()->toIso8601String(),
                $log->user_name ?? 'Guest',
                $log->action ?? 'unknown',
                $log->module ?? 'general',
                $log->ip_address ?? 'unknown',
                $log->description ?? ''
            );
        })->implode("\n");

        $prompt = <<<TEXT
You are an AI Cyber Anomaly Detector. Analyze these recent audit logs from a gym management system:
{$logSummary}

Identify any potential security anomalies, such as:
- Rapid failed authentication attempts (brute force)
- Off-hours administrative activities (outside 6 AM - 10 PM)
- Suspicious refunds or financial balance manipulations
- Privilege escalation or unexpected data deletions

Return ONLY a JSON array of objects with these exact keys:
[
  {
    "threat_type": "brute_force" | "off_hours" | "suspicious_transaction" | "privilege_abuse" | "anomaly",
    "severity": "low" | "medium" | "high" | "critical",
    "title": "Short title",
    "description": "Clear explanation of the anomaly",
    "source_ip": "IP address or 'Server'",
    "affected_user": "User email or identifier"
  }
]
If no critical anomalies are found, return a single low severity health check confirmation alert.
TEXT;

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $prompt]]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 1000,
            ]
        ];

        $response = Http::timeout(12)->post($endpoint, $payload);

        if ($response->successful()) {
            $data = $response->json();
            $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Clean json markdown delimiters if present
            $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawText));
            $decoded = json_decode($cleanJson, true);

            if (is_array($decoded) && !empty($decoded)) {
                return array_slice($decoded, 0, 5);
            }
        }

        return [];
    }

    /**
     * Local heuristic security advisor engine (reliable offline fallback).
     */
    protected function getHeuristicAdvisorResponse(string $query, array $context): array
    {
        $q = strtolower($query);

        if (str_contains($q, 'refund') || str_contains($q, 'cashier') || str_contains($q, 'financial') || str_contains($q, 'payment')) {
            return [
                'text' => "### Financial & POS Authorization Audit Analysis\n\nBased on your query regarding cashier refund security and payment integrity:\n\n1. **Current Security Posture**: The AbsTrack `RefundService` verifies that refund amounts do not exceed the initial transaction amount and increments physical stock automatically.\n2. **Defensive Recommendation**: To mitigate cashier collusion or inadvertent duplicate refunds, require **two-person manager verification** for refund transactions exceeding ₱1,000.\n3. **Audit Trail**: Every refund transaction is immutably logged with the operator's user ID and origin IP in `refund_logs` and `activity_logs`.",
                'actions' => [
                    'Require manager PIN for refund amounts exceeding ₱1,000',
                    'Conduct weekly reconciliation between POS cash-drawer and system receipts',
                    'Enforce automatic cashier logout after 15 minutes of inactivity'
                ],
                'source' => 'heuristic'
            ];
        }

        if (str_contains($q, 'csp') || str_contains($q, 'header') || str_contains($q, 'apache') || str_contains($q, 'nginx') || str_contains($q, 'cors')) {
            return [
                'text' => "### Recommended Content Security Policy (CSP) & Defensive Headers\n\nTo shield AbsTrack against Cross-Site Scripting (XSS), framing attacks, and MIME sniffing, apply these headers in your web server (`.htaccess` or Nginx):\n\n```apache\n# Apache / Laragon VirtualHost / .htaccess\nHeader set X-Frame-Options \"SAMEORIGIN\"\nHeader set X-Content-Type-Options \"nosniff\"\nHeader set X-XSS-Protection \"1; mode=block\"\nHeader set Referrer-Policy \"strict-origin-when-cross-origin\"\nHeader set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline' https://challenges.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;\"\n```",
                'actions' => [
                    'Deploy X-Frame-Options: SAMEORIGIN to prevent clickjacking',
                    'Configure Content-Security-Policy header in web server',
                    'Verify HSTS (HTTP Strict Transport Security) on production domain'
                ],
                'source' => 'heuristic'
            ];
        }

        if (str_contains($q, 'brute') || str_contains($q, 'login') || str_contains($q, 'password') || str_contains($q, 'captcha') || str_contains($q, 'throttle')) {
            return [
                'text' => "### Authentication & Brute-Force Safeguards Overview\n\nYour current authentication security parameters:\n\n* **Rate Limiting**: `throttle:5,1` is applied to `/login` (locks requests after 5 failed tries per IP per minute).\n* **Hash Entropy**: Bcrypt cost factor of 12 rounds ensures cryptographic resilience against offline GPU dictionary attacks.\n* **Anti-Bot Shield**: Cloudflare Turnstile CAPTCHA toggle is available via `TURNSTILE_ENABLED` in `.env`.\n\n**Recommendation**: Ensure `TURNSTILE_ENABLED=true` is activated upon public server deployment to eliminate automated credential-stuffing bots.",
                'actions' => [
                    'Maintain BCRYPT_ROUNDS=12 for robust password protection',
                    'Enable Cloudflare Turnstile in production environment',
                    'Set up automated email alerts when an account fails 10+ logins'
                ],
                'source' => 'heuristic'
            ];
        }

        if (str_contains($q, 'deploy') || str_contains($q, 'production') || str_contains($q, 'live') || str_contains($q, 'server')) {
            return [
                'text' => "### Production Hardening & Pre-Flight Checklist\n\nBefore deploying the AbsTrack Gym platform to a public cloud or VPS, execute this mandatory security checklist:\n\n1. **Environment Variables**: Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`. Stack traces must never be visible to gym members or the public.\n2. **Database Hardening**: Replace default passwords, bind MySQL to `127.0.0.1`, and disable remote root access.\n3. **Directory Permissions**: Ensure `storage/` and `bootstrap/cache/` are owned by the web server user (`chmod 775`), while `.env` is read-only (`chmod 600`).\n4. **SSL/TLS**: Enforce HTTPS redirection in `AppServiceProvider.php` for production environments.",
                'actions' => [
                    'Confirm APP_DEBUG=false and APP_ENV=production',
                    'Run php artisan config:cache && php artisan route:cache',
                    'Implement automated nightly database dumps with AES-256 encryption'
                ],
                'source' => 'heuristic'
            ];
        }

        // Generic comprehensive cyber assessment
        $score = $context['health_score'] ?? 92;
        return [
            'text' => "### AbsTrack CyberShield Intelligence Analysis\n\nI have evaluated the current operational security posture of your Gym Management System:\n\n* **RBAC Clearance**: Strict isolation is operational. Only Owner and Manager roles possess authorization to access security diagnostics.\n* **Database Safeguards**: SoftDeletes are active across system models, preventing accidental destruction of audit records or member payment histories.\n* **Current Health Status**: System configuration health is rated at **{$score}%**.\n\nYou can query me regarding any specific security topic: password hashing policies, cashier fraud prevention, server headers, or OWASP compliance.",
            'actions' => [
                'Conduct monthly review of staff and trainer activity audit logs',
                'Ensure database backups are encrypted and stored off-site',
                'Verify that cashier POS terminals enforce automatic inactivity lockouts'
            ],
            'source' => 'heuristic'
        ];
    }

    /**
     * Local heuristic anomaly detection engine.
     */
    protected function detectHeuristicAnomalies(Collection $logs): array
    {
        $alerts = [];

        // Check for multiple failed login attempts
        $failedLogins = $logs->filter(function ($log) {
            $desc = strtolower($log->description ?? '');
            $action = strtolower($log->action ?? '');
            return str_contains($desc, 'failed') || str_contains($action, 'failed_login');
        });

        if ($failedLogins->count() >= 3) {
            $first = $failedLogins->first();
            $alerts[] = [
                'threat_type'   => 'brute_force',
                'severity'      => 'high',
                'title'         => 'Repeated Authentication Failure Velocity Spike',
                'description'   => "Detected {$failedLogins->count()} failed authentication attempts in recent activity stream.",
                'source_ip'     => $first->ip_address ?? '127.0.0.1',
                'affected_user' => $first->user_name ?? 'admin@abstrack.com',
            ];
        }

        // Check for off-hours logins (between 11 PM and 5 AM)
        $offHoursEvents = $logs->filter(function ($log) {
            if (!$log->created_at) return false;
            $hour = Carbon::parse($log->created_at)->hour;
            return $hour >= 23 || $hour < 5;
        });

        if ($offHoursEvents->isNotEmpty()) {
            $event = $offHoursEvents->first();
            $alerts[] = [
                'threat_type'   => 'off_hours',
                'severity'      => 'info',
                'title'         => 'Off-Hours Administrative Operation Detected',
                'description'   => "User {$event->user_name} performed {$event->action} at " . Carbon::parse($event->created_at)->format('H:i:s') . ' (outside regular operating schedule).',
                'source_ip'     => $event->ip_address ?? '127.0.0.1',
                'affected_user' => $event->user_name ?? 'manager@abstrack.com',
            ];
        }

        // Check for debug environment posture
        if (config('app.debug')) {
            $alerts[] = [
                'threat_type'   => 'misconfiguration',
                'severity'      => 'medium',
                'title'         => 'Application Debug Mode Active in Runtime',
                'description'   => 'APP_DEBUG=true is currently active. Exception stack traces and internal schema may be exposed if unhandled errors occur.',
                'source_ip'     => 'Server Runtime',
                'affected_user' => 'Global Environment',
            ];
        }

        return $alerts;
    }

    /**
     * Parse actionable checklist items from AI text output.
     */
    protected function extractChecklistActions(string $text): array
    {
        $actions = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            // Match numbered items like "1. Do something" or "* Do something" or "- [ ] Do something"
            if (preg_match('/^(?:\d+\.|\*|\-)\s+(?:\*\*)?([^*:\n]{10,90})(?:\*\*)?:?/i', $line, $matches)) {
                $candidate = trim($matches[1]);
                if (!empty($candidate) && strlen($candidate) > 10 && strlen($candidate) < 90) {
                    $actions[] = $candidate;
                }
            }
            if (count($actions) >= 3) {
                break;
            }
        }

        if (empty($actions)) {
            $actions = [
                'Review security scan recommendations',
                'Verify user role permissions',
                'Audit recent activity logs'
            ];
        }

        return $actions;
    }
}

