            <!-- Navy Hero Section -->
            <div class="case-hero">
                <!-- Top row: case info + buttons -->
                <div class="hero-top">
                    <div class="hero-left">
                        <div class="hero-id-row">
                            <span class="hero-case-num" x-text="caseData.case_number"></span>
                            <span class="hero-badge"
                                x-text="getStatusLabel(caseData.status)"></span>
                        </div>
                        <div class="hero-client" x-text="caseData.client_name"></div>
                    </div>
                    <div class="hero-actions">
                        <button @click="showEditModal = true" class="hero-btn hero-btn-ghost">Edit</button>
                        <button x-show="caseData && FORWARD_TRANSITIONS[caseData.status] && FORWARD_TRANSITIONS[caseData.status].length > 0"
                            @click="openMoveForwardModal()"
                            class="hero-btn hero-btn-gold">
                            Move to <span x-text="getStatusLabel(FORWARD_TRANSITIONS[caseData.status][0])"></span> &rarr;
                        </button>
                        <button x-show="caseData && BACKWARD_TRANSITIONS[caseData.status] && BACKWARD_TRANSITIONS[caseData.status].length > 0"
                            @click="openSendBackModal()"
                            class="hero-btn hero-btn-gold-outline">Send Back</button>
                    </div>
                </div>

                <!-- Stage Pipeline -->
                <div class="pipeline" :data-count="workflowSteps.length">
                    <template x-for="(step, idx) in workflowSteps" :key="step.key">
                        <div class="stage" :class="{
                            'stage-done': getStepState(step.key) === 'completed',
                            'stage-cur': getStepState(step.key) === 'active',
                            'stage-first': idx === 0,
                            'stage-last': idx === workflowSteps.length - 1,
                        }">
                            <div class="stage-circle">
                                <template x-if="getStepState(step.key) === 'completed'">
                                    <span>&#10003;</span>
                                </template>
                                <template x-if="getStepState(step.key) !== 'completed'">
                                    <span x-text="idx + 1"></span>
                                </template>
                            </div>
                            <div class="stage-label" x-text="step.label"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Client info cards (light bg) -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6" style="margin-top: 16px;">
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Date of Birth</p>
                    <p class="text-sm font-medium" x-text="formatDate(caseData.client_dob) || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Date of Injury</p>
                    <p class="text-sm font-medium" x-text="formatDate(caseData.doi) || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Attorney</p>
                    <p class="text-sm font-medium" x-text="caseData.attorney_name || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Assigned To</p>
                    <p class="text-sm font-medium" x-text="caseData.assigned_name || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">INI Completed</p>
                    <p class="text-sm font-medium" x-text="caseData.ini_completed ? 'Yes' : 'No'"></p>
                </div>
            </div>

            <style>
                /* ── Hero Section ── */
                .case-hero {
                    background: #FFFFFF;
                    margin: -24px -24px 0;
                    padding: 24px 28px 0;
                    border-bottom: 3px solid var(--gold, #C9A84C);
                }
                .hero-top {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    margin-bottom: 24px;
                }
                .hero-left {
                    display: flex;
                    flex-direction: column;
                    gap: 5px;
                }
                .hero-id-row {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                .hero-case-num {
                    font-size: 28px;
                    font-weight: 600;
                    color: #1a2535;
                    font-family: 'IBM Plex Mono', 'Libre Franklin', monospace;
                    letter-spacing: -0.02em;
                    line-height: 1;
                }
                .hero-badge {
                    font-size: 10px;
                    font-weight: 700;
                    padding: 3px 10px;
                    border-radius: 3px;
                    letter-spacing: 0.07em;
                    text-transform: uppercase;
                    background: rgba(201,168,76,0.15);
                    color: #B8973F;
                    border: 1px solid rgba(201,168,76,0.35);
                }
                .hero-client {
                    font-size: 14px;
                    color: #8a8a82;
                }
                .hero-actions {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .hero-btn {
                    padding: 7px 16px;
                    border-radius: 5px;
                    font-size: 12px;
                    font-weight: 600;
                    cursor: pointer;
                    font-family: inherit;
                    transition: all 0.12s;
                    letter-spacing: 0.02em;
                }
                .hero-btn-ghost {
                    background: #fff;
                    border: 1px solid #d0cdc6;
                    color: #6b6b63;
                }
                .hero-btn-ghost:hover {
                    border-color: #b0ada6;
                    color: #1a2535;
                }
                .hero-btn-gold {
                    background: var(--gold, #C9A84C);
                    border: 1px solid var(--gold, #C9A84C);
                    color: #fff;
                }
                .hero-btn-gold:hover {
                    background: var(--gold-hover, #B8973F);
                    border-color: var(--gold-hover, #B8973F);
                }
                .hero-btn-gold-outline {
                    background: #fff;
                    border: 1px solid #d0cdc6;
                    color: #6b6b63;
                }
                .hero-btn-gold-outline:hover {
                    border-color: #b0ada6;
                    color: #1a2535;
                }

                /* ── Stage Pipeline ── */
                .pipeline {
                    display: flex;
                    gap: 0;
                    margin: 0 -28px;
                    border-top: 1px solid #e8e4dc;
                }
                .stage {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 10px 6px 0;
                    position: relative;
                    border-bottom: 3px solid transparent;
                    transition: all 0.15s;
                }
                .stage:hover {
                    background: rgba(201,168,76,0.03);
                }
                .stage-done {
                    border-bottom-color: rgba(201,168,76,0.3);
                }
                .stage-cur {
                    border-bottom-color: var(--gold, #C9A84C);
                }

                /* Connector lines */
                .stage::before {
                    content: '';
                    position: absolute;
                    top: 23px;
                    left: 0;
                    right: 50%;
                    height: 1px;
                    background: #e8e4dc;
                }
                .stage::after {
                    content: '';
                    position: absolute;
                    top: 23px;
                    left: 50%;
                    right: 0;
                    height: 1px;
                    background: #e8e4dc;
                }
                .stage-first::before { display: none; }
                .stage-last::after { display: none; }
                .stage-done::before,
                .stage-done::after {
                    background: rgba(201,168,76,0.35);
                }
                .stage-cur::before {
                    background: rgba(201,168,76,0.35);
                }

                .stage-circle {
                    width: 28px;
                    height: 28px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    font-weight: 700;
                    flex-shrink: 0;
                    z-index: 1;
                    position: relative;
                    margin-bottom: 6px;
                    transition: all 0.15s;
                    /* Default (future/pending) */
                    background: #f0eee8;
                    color: #bbb;
                    border: 1.5px solid #e0ddd6;
                }
                .stage-done .stage-circle {
                    background: rgba(201,168,76,0.15);
                    color: #B8973F;
                    border-color: rgba(201,168,76,0.35);
                }
                .stage-cur .stage-circle {
                    background: var(--gold, #C9A84C);
                    color: #fff;
                    border-color: var(--gold, #C9A84C);
                }
                .stage-label {
                    font-size: 9px;
                    font-weight: 700;
                    letter-spacing: 0.1em;
                    text-transform: uppercase;
                    text-align: center;
                    padding-bottom: 10px;
                    /* Default (future/pending) */
                    color: #bbb;
                }
                .stage-done .stage-label {
                    color: #B8973F;
                }
                .stage-cur .stage-label {
                    color: var(--gold, #C9A84C);
                }
            </style>
