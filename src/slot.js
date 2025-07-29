// Slot Machine Logic
class SlotMachine {
    constructor() {
        this.symbols = ['🍒', '🍋', '🔔', '💎', '👑', '🍀', '⭐', '💰'];
        this.credits = 1000;
        this.bet = 10;
        this.isSpinning = false;
        
        this.reels = [
            document.getElementById('reel1'),
            document.getElementById('reel2'),
            document.getElementById('reel3')
        ];
        
        this.creditsDisplay = document.getElementById('credits');
        this.betDisplay = document.getElementById('bet');
        this.spinButton = document.getElementById('spinBtn');
        
        this.initializeEventListeners();
    }
    
    initializeEventListeners() {
        this.spinButton.addEventListener('click', () => this.spin());
        
        // Add keyboard support
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && !this.isSpinning) {
                e.preventDefault();
                this.spin();
            }
        });
    }
    
    getRandomSymbol() {
        return this.symbols[Math.floor(Math.random() * this.symbols.length)];
    }
    
    async spin() {
        if (this.isSpinning || this.credits < this.bet) {
            return;
        }
        
        this.isSpinning = true;
        this.credits -= this.bet;
        this.updateDisplay();
        
        // Disable spin button
        this.spinButton.disabled = true;
        this.spinButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>SPINNING...';
        
        // Add spinning animation
        this.reels.forEach(reel => {
            reel.style.animation = 'spin 2s linear infinite';
        });
        
        // Simulate spinning with symbol changes
        const spinDuration = 2000;
        const intervalTime = 100;
        let spinTime = 0;
        
        const spinInterval = setInterval(() => {
            this.reels.forEach(reel => {
                reel.textContent = this.getRandomSymbol();
            });
            
            spinTime += intervalTime;
            
            if (spinTime >= spinDuration) {
                clearInterval(spinInterval);
                this.finishSpin();
            }
        }, intervalTime);
    }
    
    finishSpin() {
        // Stop animations
        this.reels.forEach(reel => {
            reel.style.animation = '';
        });
        
        // Set final symbols
        const finalSymbols = [
            this.getRandomSymbol(),
            this.getRandomSymbol(),
            this.getRandomSymbol()
        ];
        
        this.reels.forEach((reel, index) => {
            reel.textContent = finalSymbols[index];
        });
        
        // Check for wins
        const winAmount = this.checkWin(finalSymbols);
        if (winAmount > 0) {
            this.credits += winAmount;
            this.showWinAnimation(winAmount);
        }
        
        // Re-enable spin button
        this.spinButton.disabled = false;
        this.spinButton.innerHTML = '<i class="fas fa-play mr-2"></i>SPIN';
        this.isSpinning = false;
        
        this.updateDisplay();
        
        // Check if player is out of credits
        if (this.credits < this.bet) {
            this.showGameOver();
        }
    }
    
    checkWin(symbols) {
        // Check for three of a kind
        if (symbols[0] === symbols[1] && symbols[1] === symbols[2]) {
            switch (symbols[0]) {
                case '💎': return this.bet * 100; // Diamond jackpot
                case '👑': return this.bet * 50;  // Crown big win
                case '⭐': return this.bet * 30;  // Star win
                case '💰': return this.bet * 25;  // Money win
                case '🍀': return this.bet * 20;  // Clover win
                case '🔔': return this.bet * 15;  // Bell win
                case '🍋': return this.bet * 10;  // Lemon win
                case '🍒': return this.bet * 5;   // Cherry win
                default: return this.bet * 3;
            }
        }
        
        // Check for two of a kind
        if (symbols[0] === symbols[1] || symbols[1] === symbols[2] || symbols[0] === symbols[2]) {
            return this.bet * 2;
        }
        
        return 0;
    }
    
    showWinAnimation(amount) {
        // Create win notification
        const winNotification = document.createElement('div');
        winNotification.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-yellow-400 text-black text-2xl font-bold px-8 py-4 rounded-xl shadow-2xl z-50 animate-bounce';
        winNotification.innerHTML = `
            <div class="text-center">
                <i class="fas fa-trophy text-3xl mb-2"></i>
                <div>MENANG!</div>
                <div class="text-4xl">+${amount}</div>
            </div>
        `;
        
        document.body.appendChild(winNotification);
        
        // Add confetti effect
        this.createConfetti();
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            winNotification.remove();
        }, 3000);
    }
    
    createConfetti() {
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'][Math.floor(Math.random() * 5)];
            confetti.style.pointerEvents = 'none';
            confetti.style.zIndex = '1000';
            confetti.style.animation = `fall ${Math.random() * 3 + 2}s linear forwards`;
            
            document.body.appendChild(confetti);
            
            setTimeout(() => {
                confetti.remove();
            }, 5000);
        }
    }
    
    showGameOver() {
        const gameOverModal = document.createElement('div');
        gameOverModal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
        gameOverModal.innerHTML = `
            <div class="bg-gradient-to-b from-red-600 to-red-800 p-8 rounded-xl text-center text-white max-w-md">
                <i class="fas fa-exclamation-triangle text-6xl mb-4 text-yellow-400"></i>
                <h3 class="text-3xl font-bold mb-4">Game Over!</h3>
                <p class="mb-6">Kredit Anda habis! Klik tombol di bawah untuk mendapatkan kredit gratis.</p>
                <button onclick="this.parentElement.parentElement.remove(); slotMachine.credits = 1000; slotMachine.updateDisplay();" 
                        class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3 px-6 rounded-lg">
                    <i class="fas fa-gift mr-2"></i>Claim 1000 Credits
                </button>
            </div>
        `;
        
        document.body.appendChild(gameOverModal);
    }
    
    updateDisplay() {
        this.creditsDisplay.textContent = this.credits;
        this.betDisplay.textContent = this.bet;
    }
}

// Add CSS animation for falling confetti
const style = document.createElement('style');
style.textContent = `
    @keyframes fall {
        to {
            transform: translateY(100vh) rotate(360deg);
        }
    }
`;
document.head.appendChild(style);

// Initialize slot machine when page loads
let slotMachine;
document.addEventListener('DOMContentLoaded', () => {
    slotMachine = new SlotMachine();
});

// Add some interactive effects
document.addEventListener('DOMContentLoaded', () => {
    // Add hover effects to game cards
    const gameCards = document.querySelectorAll('.bg-gradient-to-b.from-gray-800');
    gameCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'scale(1.05) rotate(1deg)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'scale(1) rotate(0deg)';
        });
    });
    
    // Add floating animation to features
    const featureCards = document.querySelectorAll('.bg-gradient-to-b.from-purple-800, .bg-gradient-to-b.from-blue-800, .bg-gradient-to-b.from-green-800');
    featureCards.forEach((card, index) => {
        card.style.animation = `float ${3 + index * 0.5}s ease-in-out infinite`;
    });
});

// Add floating animation CSS
const floatStyle = document.createElement('style');
floatStyle.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
`;
document.head.appendChild(floatStyle);