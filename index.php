<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MKSB-IT WIZARD - Shared Workspace</title>
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Glowing Background Blobs -->
    <div class="app-background">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="workspace-container">
        <!-- iOS Header & Navigation Switcher -->
        <header class="app-header">
            <div class="header-top">
                <div class="logo-area">
                    <h1>MKSB-IT WIZARD ✨</h1>
                    <p class="tagline">our little workspace ♡</p>
                </div>
                <div class="members-badge">
                    <span class="member-dot ifwat"></span> Ifwat
                    <span class="member-dot amira"></span> Amira
                    <span class="member-dot umairah"></span> Umairah
                </div>
            </div>

            <!-- Navigation Tabs -->
            <nav class="nav-tabs">
                <button class="nav-tab-btn active" data-target="chatSection">
                    <i class="fa-solid fa-comments"></i> Group Chat
                </button>
                <button class="nav-tab-btn" data-target="instructionsSection">
                    <i class="fa-solid fa-clipboard-list"></i> My Instructions
                    <span class="tab-badge" id="tabCounter">0</span>
                </button>
            </nav>
        </header>

        <!-- Main Workspace Views -->
        <main class="workspace-views">
            
            <!-- VIEW A: GROUP CHAT -->
            <section class="glass-card active-view" id="chatSection">
                <div class="card-header">
                    <h2><i class="fa-solid fa-messages"></i> Team Group Chat</h2>
                    <span class="online-indicator"><span class="pulse-dot"></span> 3 active</span>
                </div>

                <div class="chat-messages-container" id="chatMessages">
                    <div class="loading-state">Loading messages...</div>
                </div>

                <form id="chatForm" class="chat-input-form">
                    <div class="input-row">
                        <select id="senderSelect" name="sender" required>
                            <option value="" disabled selected>Who is sending?</option>
                            <option value="Ifwat">Ifwat</option>
                            <option value="Amira">Amira</option>
                            <option value="Umairah">Umairah</option>
                        </select>
                    </div>
                    <div class="message-row">
                        <textarea id="messageInput" name="message" placeholder="Type a message... (Shift + Enter for new line)" rows="1" required></textarea>
                        <button type="submit" class="send-btn" title="Send">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </section>

            <!-- VIEW B: MY INSTRUCTIONS -->
            <section class="glass-card" id="instructionsSection">
                <div class="card-header">
                    <h2><i class="fa-solid fa-list-check"></i> Saved Instructions</h2>
                    <div class="header-actions">
                        <button type="button" class="btn-primary" id="openAddInstructionModalBtn" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 10px;">
                            <i class="fa-solid fa-plus"></i> Add Instruction
                        </button>
                        <span class="instruction-counter" id="instructionCounter">0 saved</span>
                    </div>
                </div>

                <div class="instructions-toolbar">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInstruction" placeholder="Search instructions...">
                    </div>
                    <div class="filter-box">
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                </div>

                <div class="instructions-list-container" id="instructionsList">
                    <div class="loading-state">Loading instructions...</div>
                </div>
            </section>

        </main>
    </div>

    <!-- MODAL -->
    <div class="modal-overlay" id="instructionModal">
        <div class="glass-modal">
            <div class="modal-header">
                <h3 id="modalHeaderTitle"><i class="fa-solid fa-bookmark"></i> Save Instruction</h3>
                <button type="button" class="close-modal-btn" id="closeModalBtn">&times;</button>
            </div>
            <form id="saveInstructionForm">
                <input type="hidden" id="modalMessageId" name="message_id">
                <input type="hidden" id="modalInstructionId" name="instruction_id">
                
                <div class="form-group" id="manualFromGroup" style="display: none;">
                    <label for="modalFromName">From (Supervisor / Intern)</label>
                    <select id="modalFromName" name="from_name">
                        <option value="Ifwat">Ifwat</option>
                        <option value="Amira">Amira</option>
                        <option value="Umairah">Umairah</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="modalTitle">Title</label>
                    <input type="text" id="modalTitle" name="title" required>
                </div>

                <div class="form-group">
                    <label for="modalDetails">Details</label>
                    <textarea id="modalDetails" name="details" rows="4" required></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancelModalBtn">Cancel</button>
                    <button type="submit" class="btn-primary" id="saveModalSubmitBtn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>