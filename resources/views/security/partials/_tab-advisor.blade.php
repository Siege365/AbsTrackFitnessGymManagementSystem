<!-- Sub-Module 4: AI Security Advisor (Interactive Executive Copilot) -->
<div class="row">
    <!-- Left Column: Interactive Advisor Console -->
    <div class="col-12 col-lg-8 grid-margin stretch-card">
        <div class="card clean-security-card w-100">
            <div class="card-header">
                <h4>
                    <i class="mdi mdi-robot-outline text-info mr-1"></i> CyberShield AI Security Advisor
                </h4>
                <span class="badge badge-clean-info">
                    <i class="mdi mdi-sparkles"></i> AI Copilot Ready
                </span>
            </div>
            <div class="card-body">
                <!-- Quick Prompt Chips -->
                <div class="advisor-prompt-bar">
                    <button type="button" class="advisor-prompt-btn advisor-prompt-chip" data-prompt="How secure are our cashier refund and financial permissions?">
                        <i class="mdi mdi-cash-refund"></i> Audit cashier refund security
                    </button>
                    <button type="button" class="advisor-prompt-btn advisor-prompt-chip" data-prompt="Generate Content Security Policy (CSP) headers for our web server.">
                        <i class="mdi mdi-code-tags"></i> Generate CSP defensive headers
                    </button>
                    <button type="button" class="advisor-prompt-btn advisor-prompt-chip" data-prompt="What brute-force safeguards are currently active on the login endpoint?">
                        <i class="mdi mdi-shield-lock"></i> Check brute-force safeguards
                    </button>
                    <button type="button" class="advisor-prompt-btn advisor-prompt-chip" data-prompt="What should our checklist be before deploying this gym platform to public hosting?">
                        <i class="mdi mdi-rocket-launch"></i> Production deployment checklist
                    </button>
                </div>

                <!-- Chat History Console Stream -->
                <div class="advisor-chat-stream" id="advisorChatHistory">
                    <!-- Default Welcome AI Message -->
                    <div class="msg-ai">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-clean-info mr-2"><i class="mdi mdi-robot"></i> CyberShield AI</span>
                            <small class="text-muted">Security Advisor</small>
                        </div>
                        <div>
                            Hello <strong>{{ Auth::user()->name }}</strong>. I am your <strong>CyberShield AI Security Advisor</strong>. I continuously analyze the AbsTrack application structure, environment integrity, activity logs, and RBAC policies.<br><br>
                            Click any quick topic above or type a question below to analyze vulnerabilities, evaluate threat logs, or generate defensive hardening configurations.
                        </div>
                    </div>
                </div>

                <!-- Input Form -->
                <form id="advisorForm">
                    <div class="advisor-input-box">
                        <input type="text" 
                               id="advisorQueryInput" 
                               class="form-control" 
                               placeholder="Ask the AI Security Advisor anything about vulnerabilities, RBAC, or defensive hardening..." 
                               required 
                               autocomplete="off">
                        <button type="submit">
                            <i class="mdi mdi-send"></i> Consult AI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Security Profile & Executive Directives -->
    <div class="col-12 col-lg-4 grid-margin stretch-card">
        <div class="card clean-security-card w-100">
            <div class="card-header">
                <h4>
                    <i class="mdi mdi-shield-account-outline text-warning mr-1"></i> Security Profile
                </h4>
            </div>
            <div class="card-body">
                <div class="info-row-item">
                    <span class="info-row-label">RBAC Clearance</span>
                    <span class="info-row-val text-success">Owner, Manager, Admin</span>
                </div>
                <div class="info-row-item">
                    <span class="info-row-label">Role Isolation</span>
                    <span class="info-row-val">Cashier & Staff Blocked</span>
                </div>
                <div class="info-row-item">
                    <span class="info-row-label">Data Destruction Protection</span>
                    <span class="info-row-val text-info">SoftDeletes Active</span>
                </div>
                <div class="info-row-item">
                    <span class="info-row-label">Password Hashing Factor</span>
                    <span class="info-row-val">Bcrypt (Cost 12)</span>
                </div>
                <div class="info-row-item">
                    <span class="info-row-label">Brute-Force Rate Limiter</span>
                    <span class="info-row-val">throttle:5,1 Active</span>
                </div>
                <div class="info-row-item">
                    <span class="info-row-label">Turnstile Anti-Bot</span>
                    <span class="info-row-val text-muted">Dev Bypass (Toggleable)</span>
                </div>
                <div class="info-row-item">
                    <span class="info-row-label">Session Driver</span>
                    <span class="info-row-val">Database (120m Timeout)</span>
                </div>

                <div class="mt-4 pt-3 border-top border-dark">
                    <a href="{{ route('security.export-report') }}" target="_blank" class="btn btn-outline-warning btn-block">
                        <i class="mdi mdi-file-pdf-box mr-1"></i> Export Full Audit PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
