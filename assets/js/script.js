document.addEventListener('DOMContentLoaded', () => {
    // --- CUTE AUDIO FX (Web Audio API) ---
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function playCuteSound(type = 'click') {
        if (!audioCtx) return;
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        const osc = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        osc.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        const now = audioCtx.currentTime;

        if (type === 'click') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(450, now);
            osc.frequency.exponentialRampToValueAtTime(200, now + 0.06);
            gainNode.gain.setValueAtTime(0.12, now);
            gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.06);
            osc.start(now);
            osc.stop(now + 0.06);
        } else if (type === 'send') {
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(587.33, now);
            osc.frequency.setValueAtTime(880, now + 0.08);
            gainNode.gain.setValueAtTime(0.15, now);
            gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.22);
            osc.start(now);
            osc.stop(now + 0.22);
        } else if (type === 'delete') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(250, now);
            osc.frequency.exponentialRampToValueAtTime(100, now + 0.08);
            gainNode.gain.setValueAtTime(0.1, now);
            gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
            osc.start(now);
            osc.stop(now + 0.08);
        }
    }

    document.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            if (!e.target.classList.contains('send-btn')) {
                playCuteSound('click');
            }
        }
    });

    // --- TAB SWITCHER LOGIC ---
    const navTabBtns = document.querySelectorAll('.nav-tab-btn');
    const glassCards = document.querySelectorAll('.glass-card');
    const chatMessages = document.getElementById('chatMessages');

    navTabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');

            navTabBtns.forEach(b => b.classList.remove('active'));
            glassCards.forEach(card => card.classList.remove('active-view'));

            btn.classList.add('active');
            document.getElementById(targetId).classList.add('active-view');

            if (targetId === 'chatSection') {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    });

    // Chat & Instructions Elements
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const senderSelect = document.getElementById('senderSelect');

    const instructionsList = document.getElementById('instructionsList');
    const instructionCounter = document.getElementById('instructionCounter');
    const tabCounter = document.getElementById('tabCounter');
    const searchInstruction = document.getElementById('searchInstruction');
    const statusFilter = document.getElementById('statusFilter');

    const instructionModal = document.getElementById('instructionModal');
    const saveInstructionForm = document.getElementById('saveInstructionForm');
    const modalHeaderTitle = document.getElementById('modalHeaderTitle');
    const modalInstructionId = document.getElementById('modalInstructionId');
    const modalMessageId = document.getElementById('modalMessageId');
    const modalTitle = document.getElementById('modalTitle');
    const modalDetails = document.getElementById('modalDetails');
    const saveModalSubmitBtn = document.getElementById('saveModalSubmitBtn');
    const manualFromGroup = document.getElementById('manualFromGroup');
    const openAddInstructionModalBtn = document.getElementById('openAddInstructionModalBtn');

    let isEditingMessage = false;
    let editingMessageId = null;

    function fetchMessages(scrollToBottom = false) {
        fetch('chat_action.php?action=fetch')
            .then(res => res.json())
            .then(messages => renderMessages(messages, scrollToBottom))
            .catch(err => console.error('Error fetching messages:', err));
    }

    function renderMessages(messages, shouldScroll) {
        if (messages.length === 0) {
            chatMessages.innerHTML = `<div class="empty-state">No messages yet. Send a message to start!</div>`;
            return;
        }

        let html = '';
        messages.forEach(msg => {
            const saveBtnHtml = msg.is_saved 
                ? `<span style="color:#34c759; font-weight:700;"><i class="fa-solid fa-check"></i> Saved</span>`
                : `<button type="button" class="chat-action-btn" onclick="openSaveModal(${msg.id}, \`${escapeHtml(msg.message)}\`)"><i class="fa-solid fa-bookmark"></i> Save</button>`;

            html += `
                <div class="message-bubble ${msg.sender}">
                    <div class="message-header-info">${msg.sender}</div>
                    <div class="message-body-card">
                        <div class="message-text">${escapeHtml(msg.message)}</div>
                        <div class="message-footer-info">
                            <span>${formatTime(msg.created_at)}</span>
                            <div class="action-btn-group">
                                ${saveBtnHtml}
                                <button type="button" class="chat-action-btn" onclick="editChat(${msg.id}, \`${escapeHtml(msg.message)}\`)"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" class="chat-action-btn" onclick="deleteChat(${msg.id})"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        chatMessages.innerHTML = html;
        if (shouldScroll) chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const sender = senderSelect.value;
        const message = messageInput.value.trim();

        if (!sender && !isEditingMessage) {
            alert('Please select who is sending the message.');
            return;
        }
        if (!message) return;

        playCuteSound('send');

        const formData = new FormData();
        if (isEditingMessage) {
            formData.append('action', 'update');
            formData.append('id', editingMessageId);
            formData.append('message', message);
        } else {
            formData.append('action', 'send');
            formData.append('sender', sender);
            formData.append('message', message);
        }

        fetch('chat_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    isEditingMessage = false;
                    editingMessageId = null;
                    document.querySelector('.send-btn').innerHTML = `<i class="fa-solid fa-paper-plane"></i>`;
                    fetchMessages(true);
                    fetchInstructions();
                }
            });
    });

    window.editChat = function(id, text) {
        isEditingMessage = true;
        editingMessageId = id;
        messageInput.value = text;
        messageInput.focus();
        document.querySelector('.send-btn').innerHTML = `<i class="fa-solid fa-check"></i>`;
    };

    window.deleteChat = function(id) {
        if (!confirm('Are you sure you want to delete this message?')) return;
        playCuteSound('delete');
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('chat_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    fetchMessages();
                    fetchInstructions();
                }
            });
    };

    window.fetchInstructions = function() {
        const search = searchInstruction.value;
        const status = statusFilter.value;
        
        fetch(`instructions_action.php?action=fetch&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`)
            .then(res => res.json())
            .then(instructions => renderInstructions(instructions));
    };

    function renderInstructions(instructions) {
        instructionCounter.textContent = `${instructions.length} saved`;
        tabCounter.textContent = instructions.length;

        if (instructions.length === 0) {
            instructionsList.innerHTML = `<div class="empty-state">No instructions saved yet.</div>`;
            return;
        }

        let html = '';
        instructions.forEach(inst => {
            const statusClass = inst.status.replace(' ', '');
            html += `
                <div class="instruction-card">
                    <div class="instruction-top">
                        <div class="instruction-title">${escapeHtml(inst.title)}</div>
                        <span class="status-badge ${statusClass}">${inst.status}</span>
                    </div>
                    <div class="instruction-details">${escapeHtml(inst.details)}</div>
                    <div class="instruction-meta">
                        <span>From: <strong>${escapeHtml(inst.from_name)}</strong></span>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <select onchange="updateInstructionStatus(${inst.id}, this.value)" style="border-radius:6px; padding:2px 4px; font-size:0.75rem;">
                                <option value="Pending" ${inst.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                <option value="In Progress" ${inst.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                                <option value="Done" ${inst.status === 'Done' ? 'selected' : ''}>Done</option>
                            </select>
                            <button type="button" class="inst-control-btn edit" onclick="openEditInstructionModal(${inst.id}, \`${escapeHtml(inst.title)}\`, \`${escapeHtml(inst.details)}\`)"><i class="fa-solid fa-pen"></i></button>
                            <button type="button" class="inst-control-btn delete" onclick="deleteInstruction(${inst.id})"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
        });
        instructionsList.innerHTML = html;
    }

    window.updateInstructionStatus = function(id, status) {
        playCuteSound('click');
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', id);
        formData.append('status', status);

        fetch('instructions_action.php', { method: 'POST', body: formData })
            .then(() => fetchInstructions());
    };

    window.deleteInstruction = function(id) {
        if (!confirm('Are you sure you want to delete this instruction?')) return;
        playCuteSound('delete');
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('instructions_action.php', { method: 'POST', body: formData })
            .then(() => {
                fetchMessages();
                fetchInstructions();
            });
    };

    // --- MANUAL ADD INSTRUCTION MODAL ---
    if (openAddInstructionModalBtn) {
        openAddInstructionModalBtn.addEventListener('click', () => {
            modalInstructionId.value = '';
            modalMessageId.value = '';
            modalHeaderTitle.innerHTML = `<i class="fa-solid fa-plus"></i> Add New Instruction`;
            manualFromGroup.style.display = 'block';
            modalTitle.value = '';
            modalDetails.value = '';
            saveModalSubmitBtn.textContent = 'Add Instruction';
            instructionModal.classList.add('active');
        });
    }

    window.openSaveModal = function(msgId, text) {
        modalInstructionId.value = '';
        modalMessageId.value = msgId;
        modalHeaderTitle.innerHTML = `<i class="fa-solid fa-bookmark"></i> Save Instruction`;
        manualFromGroup.style.display = 'none';
        modalTitle.value = text.length > 40 ? text.substring(0, 37) + '...' : text;
        modalDetails.value = text;
        saveModalSubmitBtn.textContent = 'Save';
        instructionModal.classList.add('active');
    };

    window.openEditInstructionModal = function(instId, title, details) {
        modalInstructionId.value = instId;
        modalMessageId.value = '';
        modalHeaderTitle.innerHTML = `<i class="fa-solid fa-pen"></i> Edit Instruction`;
        manualFromGroup.style.display = 'none';
        modalTitle.value = title;
        modalDetails.value = details;
        saveModalSubmitBtn.textContent = 'Update';
        instructionModal.classList.add('active');
    };

    saveInstructionForm.addEventListener('submit', (e) => {
        e.preventDefault();
        playCuteSound('send');
        const formData = new FormData(saveInstructionForm);
        const instId = modalInstructionId.value;
        const msgId = modalMessageId.value;

        if (instId) {
            formData.append('action', 'update_instruction');
            formData.append('id', instId);
        } else if (msgId) {
            formData.append('action', 'save_instruction');
        } else {
            formData.append('action', 'manual_add_instruction');
        }

        fetch('instructions_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    instructionModal.classList.remove('active');
                    saveInstructionForm.reset();
                    fetchMessages();
                    fetchInstructions();
                } else {
                    alert(data.error || 'Failed to save.');
                }
            });
    });

    document.getElementById('closeModalBtn').onclick = () => instructionModal.classList.remove('active');
    document.getElementById('cancelModalBtn').onclick = () => instructionModal.classList.remove('active');

    searchInstruction.oninput = fetchInstructions;
    statusFilter.onchange = fetchInstructions;

    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    function formatTime(ts) {
        return new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    fetchMessages(true);
    fetchInstructions();
    setInterval(() => {
        fetchMessages(false);
        fetchInstructions();
    }, 3000);
});