/**
 * CyberShield AI Security Assessment Subsystem JavaScript
 * AbsTrack Fitness Gym Management System
 */

document.addEventListener('DOMContentLoaded', function () {
    initTabNavigation();
    initScanButton();
    initAdvisorConsole();
    initAlertActions();
    initThreatAnalysis();
});

/**
 * 1. Sub-Module Tab Switching Navigation
 */
function initTabNavigation() {
    const tabButtons = document.querySelectorAll('.security-tab-btn');
    const tabPanes = document.querySelectorAll('.security-tab-pane');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');

            tabButtons.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            const targetPane = document.getElementById(target);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });
}

/**
 * 2. On-Demand Hardening & Configuration Scan Execution
 */
function initScanButton() {
    const scanBtn = document.getElementById('btnRunSystemScan');
    if (!scanBtn) return;

    scanBtn.addEventListener('click', function () {
        const originalHtml = scanBtn.innerHTML;
        scanBtn.disabled = true;
        scanBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> Running Security Audit...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/security/scan/run', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            scanBtn.disabled = false;
            scanBtn.innerHTML = originalHtml;

            if (data.success && data.scan) {
                if (window.ToastUtils && typeof ToastUtils.showSuccess === 'function') {
                    ToastUtils.showSuccess('Security scan completed. Health Score: ' + data.scan.health_score + '%');
                } else {
                    alert('Security scan completed. Health Score: ' + data.scan.health_score + '%');
                }

                // Update score in UI
                const scoreDisplay = document.getElementById('kpiHealthScore');
                if (scoreDisplay) {
                    scoreDisplay.innerText = data.scan.health_score + '%';
                }

                // Reload tab content seamlessly after short pause
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            }
        })
        .catch(err => {
            console.error('Scan error:', err);
            scanBtn.disabled = false;
            scanBtn.innerHTML = originalHtml;
            if (window.ToastUtils && typeof ToastUtils.showError === 'function') {
                ToastUtils.showError('Unable to complete security scan.');
            }
        });
    });
}

/**
 * 3. AI Security Advisor Console & Interactive Chat
 */
function initAdvisorConsole() {
    const advisorForm = document.getElementById('advisorForm');
    const advisorInput = document.getElementById('advisorQueryInput');
    const chatContainer = document.getElementById('advisorChatHistory');
    const promptChips = document.querySelectorAll('.advisor-prompt-chip');

    // Quick prompt chip autofill
    promptChips.forEach(chip => {
        chip.addEventListener('click', function () {
            const promptText = this.getAttribute('data-prompt');
            if (advisorInput) {
                advisorInput.value = promptText;
                advisorInput.focus();
            }
        });
    });

    if (!advisorForm || !advisorInput || !chatContainer) return;

    advisorForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const query = advisorInput.value.trim();
        if (!query) return;

        // Render user message bubble
        appendChatBubble('user', query);
        advisorInput.value = '';

        // Render typing indicator
        const typingId = 'typingIndicator_' + Date.now();
        appendTypingIndicator(typingId);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/security/advisor/query', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ query: query, topic: 'general' })
        })
        .then(res => res.json())
        .then(data => {
            removeElement(typingId);
            if (data.success && data.consultation) {
                appendChatBubble('ai', data.consultation.ai_response, data.consultation.suggested_actions);
            } else {
                appendChatBubble('ai', 'An error occurred while generating the security analysis.');
            }
        })
        .catch(err => {
            removeElement(typingId);
            appendChatBubble('ai', 'Error connecting to the AI Security Advisor service.');
            console.error(err);
        });
    });

    function appendChatBubble(sender, text, actions = []) {
        const bubble = document.createElement('div');
        bubble.className = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-ai';

        if (sender === 'user') {
            bubble.innerText = text;
        } else {
            // Render basic markdown / formatted response
            let formatted = escapeHtml(text)
                .replace(/\n\n/g, '<br><br>')
                .replace(/### (.*?)(<br>|$)/g, '<h5 class="text-white font-weight-bold mb-2">$1</h5>')
                .replace(/\* (.*?)(<br>|$)/g, '• $1<br>')
                .replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

            let actionsHtml = '';
            if (actions && actions.length > 0) {
                actionsHtml = `
                    <div class="mt-3 pt-2 border-top border-secondary">
                        <small class="text-warning font-weight-bold"><i class="mdi mdi-checkbox-marked-circle-outline"></i> Recommended Next Actions:</small>
                        <ul class="pl-3 mt-1 mb-0" style="font-size: 0.85rem; color: #b0b0b0;">
                            ${actions.map(a => `<li>${escapeHtml(a)}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            bubble.innerHTML = `
                <div class="d-flex align-items-center mb-2">
                    <span class="badge badge-outline-info mr-2"><i class="mdi mdi-robot"></i> CyberShield AI</span>
                    <small class="text-muted">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
                <div>${formatted}</div>
                ${actionsHtml}
            `;
        }

        chatContainer.appendChild(bubble);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function appendTypingIndicator(id) {
        const indicator = document.createElement('div');
        indicator.id = id;
        indicator.className = 'chat-bubble-ai text-muted';
        indicator.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> CyberShield AI is evaluating security posture...';
        chatContainer.appendChild(indicator);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function removeElement(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function escapeHtml(string) {
        return String(string)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
}

/**
 * 4. Alert Actions (Resolve / Dismiss)
 */
function initAlertActions() {
    const resolveButtons = document.querySelectorAll('.btn-resolve-alert');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    resolveButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const alertId = this.getAttribute('data-id');
            const alertCard = document.getElementById('threatCard_' + alertId);

            btn.disabled = true;

            fetch(`/security/alerts/${alertId}/resolve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: 'resolved', resolution_notes: 'Reviewed and dismissed by security manager.' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && alertCard) {
                    alertCard.style.opacity = '0.5';
                    btn.outerHTML = '<span class="badge badge-success"><i class="mdi mdi-check"></i> Resolved</span>';
                    if (window.ToastUtils && typeof ToastUtils.showSuccess === 'function') {
                        ToastUtils.showSuccess('Threat alert marked as resolved.');
                    }
                }
            })
            .catch(err => {
                btn.disabled = false;
                console.error(err);
            });
        });
    });
}

/**
 * 5. AI Anomaly Threat Log Analysis
 */
function initThreatAnalysis() {
    const analyzeBtn = document.getElementById('btnAnalyzeThreats');
    if (!analyzeBtn) return;

    analyzeBtn.addEventListener('click', function () {
        const originalHtml = analyzeBtn.innerHTML;
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> Running AI Analysis...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/security/threats/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = originalHtml;

            if (data.success) {
                if (window.ToastUtils && typeof ToastUtils.showSuccess === 'function') {
                    ToastUtils.showSuccess(data.message || 'AI threat log analysis completed.');
                } else {
                    alert(data.message || 'AI threat log analysis completed.');
                }

                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            }
        })
        .catch(err => {
            console.error('Threat analysis error:', err);
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = originalHtml;
            if (window.ToastUtils && typeof ToastUtils.showError === 'function') {
                ToastUtils.showError('AI Threat Analysis could not be completed.');
            }
        });
    });
}
