const API_URL = 'http://localhost/sites/github/flashcards/api'; // Adjust if needed

const state = {
    user: JSON.parse(localStorage.getItem('user')) || null,
    decks: [],
    currentDeck: null,
    currentCardIndex: 0,
    isFlipped: false
};

// DOM Elements
const authSection = document.getElementById('authSection');
const appSection = document.getElementById('appSection');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const deckListArea = document.getElementById('deckListArea');
const appContent = document.getElementById('appContent');
const deckList = document.getElementById('deckList');

// Auth Functions
async function login(username, password) {
    try {
        const response = await fetch(`${API_URL}/auth.php`, {
            method: 'POST',
            body: JSON.stringify({ action: 'login', username, password })
        });
        const data = await response.json();
        if (response.ok) {
            state.user = { id: data.user_id, username: data.username };
            localStorage.setItem('user', JSON.stringify(state.user));
            showApp();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('Erro ao conectar com o servidor.');
    }
}

async function register(username, password) {
    try {
        const response = await fetch(`${API_URL}/auth.php`, {
            method: 'POST',
            body: JSON.stringify({ action: 'register', username, password })
        });
        const data = await response.json();
        if (response.ok) {
            alert('Registro realizado com sucesso! Faça login.');
            toggleAuthMode();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Register error:', error);
        alert('Erro ao conectar com o servidor.');
    }
}

function logout() {
    state.user = null;
    localStorage.removeItem('user');
    showAuth();
}

// Deck Functions
async function fetchDecks() {
    if (!state.user) return;
    try {
        const response = await fetch(`${API_URL}/decks.php?user_id=${state.user.id}`);
        const data = await response.json();
        if (response.ok) {
            state.decks = data;
            renderDeckList();
        }
    } catch (error) {
        console.error('Fetch decks error:', error);
    }
}

async function createDeck(title) {
    if (!state.user) return;
    try {
        const response = await fetch(`${API_URL}/decks.php`, {
            method: 'POST',
            body: JSON.stringify({ title, user_id: state.user.id })
        });
        if (response.ok) {
            fetchDecks();
        }
    } catch (error) {
        console.error('Create deck error:', error);
    }
}

async function deleteDeck(id) {
    if (!confirm('Tem certeza que deseja excluir este deck?')) return;
    try {
        const response = await fetch(`${API_URL}/decks.php`, {
            method: 'DELETE',
            body: JSON.stringify({ id, user_id: state.user.id })
        });
        if (response.ok) {
            fetchDecks();
        }
    } catch (error) {
        console.error('Delete deck error:', error);
    }
}

async function openDeck(id) {
    try {
        const response = await fetch(`${API_URL}/decks.php?id=${id}&user_id=${state.user.id}`);
        const data = await response.json();
        if (response.ok) {
            state.currentDeck = data;
            state.currentCardIndex = 0;
            state.isFlipped = false;
            showFlashcards();
        }
    } catch (error) {
        console.error('Open deck error:', error);
    }
}

// UI Functions
function showAuth() {
    authSection.classList.remove('hidden');
    appSection.classList.add('hidden');
    deckListArea.classList.add('hidden');
    appContent.classList.remove('active');
    if (editorSection) editorSection.classList.remove('active');
}

function showApp() {
    authSection.classList.add('hidden');
    appSection.classList.remove('hidden');
    deckListArea.classList.remove('hidden');
    appContent.classList.remove('active');
    if (editorSection) editorSection.classList.remove('active');
    fetchDecks();
    document.getElementById('welcomeUser').textContent = `Olá, ${state.user.username}`;
}

function showFlashcards() {
    deckListArea.classList.add('hidden');
    appContent.classList.add('active');
    renderCard();
    updateProgress();
}

function renderDeckList() {
    deckList.innerHTML = '';
    state.decks.forEach(deck => {
        const div = document.createElement('div');
        div.className = 'deck-item';
        div.innerHTML = `
            <div class="deck-item-title">${deck.title}</div>
            <div class="deck-item-actions">
                <button class="deck-item-btn" onclick="openDeck(${deck.id})">Abrir</button>
                <button class="deck-item-btn" onclick="deleteDeck(${deck.id})" style="color:red; border-color:red;">Excluir</button>
            </div>
        `;
        deckList.appendChild(div);
    });
}

function renderCard() {
    if (!state.currentDeck || !state.currentDeck.cards || state.currentDeck.cards.length === 0) {
        document.getElementById('cardText').textContent = "Deck vazio";
        return;
    }
    const card = state.currentDeck.cards[state.currentCardIndex];
    // Default to side 1
    document.getElementById('cardText').textContent = card.side1;
    document.getElementById('cardLabel').textContent = "Side 1";

    // Reset active side button
    document.querySelectorAll('.side-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector('.side-btn[data-side="1"]').classList.add('active');
}

function updateProgress() {
    if (!state.currentDeck || !state.currentDeck.cards) return;
    const total = state.currentDeck.cards.length;
    const current = state.currentCardIndex + 1;
    const percent = (current / total) * 100;

    document.getElementById('progressText').textContent = `Card ${current} de ${total}`;
    document.getElementById('progressPercent').textContent = `${Math.round(percent)}%`;
    document.getElementById('progressFill').style.width = `${percent}%`;
}

// Event Listeners
document.getElementById('loginBtn').addEventListener('click', (e) => {
    e.preventDefault();
    const user = document.getElementById('loginUser').value;
    const pass = document.getElementById('loginPass').value;
    login(user, pass);
});

document.getElementById('registerBtn').addEventListener('click', (e) => {
    e.preventDefault();
    const user = document.getElementById('regUser').value;
    const pass = document.getElementById('regPass').value;
    register(user, pass);
});

document.getElementById('logoutBtn').addEventListener('click', logout);

document.getElementById('createNewDeckBtn').addEventListener('click', () => {
    const title = prompt("Nome do novo deck:");
    if (title) createDeck(title);
});

document.getElementById('toggleAuth').addEventListener('click', toggleAuthMode);

function toggleAuthMode() {
    loginForm.classList.toggle('hidden');
    registerForm.classList.toggle('hidden');
}

// Navigation
document.getElementById('nextBtn').addEventListener('click', () => {
    if (state.currentDeck && state.currentCardIndex < state.currentDeck.cards.length - 1) {
        state.currentCardIndex++;
        renderCard();
        updateProgress();
    }
});

document.getElementById('prevBtn').addEventListener('click', () => {
    if (state.currentDeck && state.currentCardIndex > 0) {
        state.currentCardIndex--;
        renderCard();
        updateProgress();
    }
});

document.getElementById('newDeckBtn').addEventListener('click', () => {
    appContent.classList.remove('active');
    deckListArea.classList.remove('hidden');
});

// Side buttons
document.querySelectorAll('.side-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const side = e.target.dataset.side;
        const card = state.currentDeck.cards[state.currentCardIndex];
        document.getElementById('cardText').textContent = card[`side${side}`];

        document.querySelectorAll('.side-btn').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
    });
});

// --- Missing Logic Appended ---

// Deck Actions
const shuffleBtn = document.getElementById('shuffleBtn');
const restartBtn = document.getElementById('restartBtn');
const ttsBtn = document.getElementById('ttsBtn');
const editDeckBtn = document.getElementById('editDeckBtn');

if (shuffleBtn) {
    shuffleBtn.addEventListener('click', () => {
        if (!state.currentDeck || !state.currentDeck.cards) return;
        for (let i = state.currentDeck.cards.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [state.currentDeck.cards[i], state.currentDeck.cards[j]] = [state.currentDeck.cards[j], state.currentDeck.cards[i]];
        }
        state.currentCardIndex = 0;
        renderCard();
        updateProgress();
    });
}

if (restartBtn) {
    restartBtn.addEventListener('click', () => {
        state.currentCardIndex = 0;
        renderCard();
        updateProgress();
    });
}

// TTS
let voices = [];
const voiceSelect = document.getElementById('voiceSelect');
const rateInput = document.getElementById('rate');
const pitchInput = document.getElementById('pitch');
const rateValue = document.getElementById('rateValue');
const pitchValue = document.getElementById('pitchValue');

function loadVoices() {
    voices = speechSynthesis.getVoices();
    if (!voiceSelect) return;
    voiceSelect.innerHTML = '';
    voices.forEach((voice, index) => {
        if (voice.lang === 'ja-JP') {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `${voice.name} (${voice.lang})`;
            option.selected = true;
            voiceSelect.appendChild(option);
        }
    });
}

speechSynthesis.onvoiceschanged = loadVoices;
loadVoices();

if (ttsBtn) {
    ttsBtn.addEventListener('click', () => {
        const text = document.getElementById('cardText').textContent;
        const utterance = new SpeechSynthesisUtterance(text);
        const selectedVoice = voices[voiceSelect.value];
        if (selectedVoice) {
            utterance.voice = selectedVoice;
        }
        utterance.rate = parseFloat(rateInput.value);
        utterance.pitch = parseFloat(pitchInput.value);
        speechSynthesis.speak(utterance);
    });
}

if (rateInput) rateInput.addEventListener('input', () => rateValue.textContent = rateInput.value + 'x');
if (pitchInput) pitchInput.addEventListener('input', () => pitchValue.textContent = pitchInput.value);

// --- Editor Logic ---
const editorSection = document.getElementById('editorSection');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const saveDeckBtn = document.getElementById('saveDeckBtn');
const addCardBtn = document.getElementById('addCardBtn');
const editorCardList = document.getElementById('editorCardList');
const editDeckTitle = document.getElementById('editDeckTitle');

if (editDeckBtn) {
    editDeckBtn.addEventListener('click', showEditor);
}

function showEditor() {
    appContent.classList.remove('active');
    editorSection.classList.add('active');

    editDeckTitle.value = state.currentDeck.title;
    renderEditorCards();
}

if (cancelEditBtn) {
    cancelEditBtn.addEventListener('click', () => {
        editorSection.classList.remove('active');
        appContent.classList.add('active');
    });
}

function renderEditorCards() {
    editorCardList.innerHTML = '';
    state.currentDeck.cards.forEach((card, index) => {
        const cardItem = document.createElement('div');
        cardItem.className = 'editor-card-item';
        cardItem.innerHTML = `
            <div class="editor-card-header">
                <span class="editor-card-title">Card ${index + 1}</span>
                <button class="delete-btn" onclick="deleteCard(${index})">🗑️</button>
            </div>
            <div class="input-group">
                <label>Lado 1 (Kanji/Principal)</label>
                <input type="text" value="${card.side1 || ''}" onchange="updateCardData(${index}, 'side1', this.value)">
            </div>
            <div class="input-group">
                <label>Lado 2 (Hiragana/Leitura)</label>
                <input type="text" value="${card.side2 || ''}" onchange="updateCardData(${index}, 'side2', this.value)">
            </div>
            <div class="input-group">
                <label>Lado 3 (Romaji)</label>
                <input type="text" value="${card.side3 || ''}" onchange="updateCardData(${index}, 'side3', this.value)">
            </div>
            <div class="input-group">
                <label>Lado 4 (Tradução)</label>
                <input type="text" value="${card.side4 || ''}" onchange="updateCardData(${index}, 'side4', this.value)">
            </div>
        `;
        editorCardList.appendChild(cardItem);
    });
}

// Global functions for inline onclicks
window.deleteCard = (index) => {
    if (confirm('Excluir este card?')) {
        state.currentDeck.cards.splice(index, 1);
        renderEditorCards();
    }
};

window.updateCardData = (index, field, value) => {
    state.currentDeck.cards[index][field] = value;
};

if (addCardBtn) {
    addCardBtn.addEventListener('click', () => {
        state.currentDeck.cards.push({
            side1: '',
            side2: '',
            side3: '',
            side4: ''
        });
        renderEditorCards();
        setTimeout(() => {
            editorCardList.scrollTop = editorCardList.scrollHeight;
        }, 100);
    });
}

if (saveDeckBtn) {
    saveDeckBtn.addEventListener('click', async () => {
        const newTitle = editDeckTitle.value;
        const cards = state.currentDeck.cards;

        try {
            const response = await fetch(`${API_URL}/decks.php`, {
                method: 'PUT',
                body: JSON.stringify({
                    id: state.currentDeck.id,
                    title: newTitle,
                    user_id: state.user.id,
                    cards: cards
                })
            });

            if (response.ok) {
                alert('Deck salvo com sucesso!');
                state.currentDeck.title = newTitle;

                editorSection.classList.remove('active');
                appContent.classList.add('active');

                document.getElementById('deckTitle').textContent = newTitle;
                renderCard();
                fetchDecks(); // Refresh list
            } else {
                alert('Erro ao salvar deck.');
            }
        } catch (error) {
            console.error('Save deck error:', error);
            alert('Erro ao conectar com o servidor.');
        }
    });
}
