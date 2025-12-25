document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const envelopeSection = document.getElementById('envelope-section');
    const pagesSection = document.getElementById('pages-section');
    const openBtn = document.getElementById('open-btn');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const yesBtn = document.getElementById('yes-btn');
    const noBtn = document.getElementById('no-btn');
    const backToProposalBtn = document.getElementById('back-to-proposal');
    const closeSiteBtn = document.getElementById('close-site');
    const musicToggle = document.getElementById('music-toggle');
    const bgMusic = document.getElementById('bg-music');
    const envelope = document.querySelector('.envelope');
    const letter = document.querySelector('.letter');
    const currentPageEl = document.getElementById('current-page');
    const totalPagesEl = document.getElementById('total-pages');
    const pages = document.querySelectorAll('.page:not(#page-yes):not(#page-no)');
    const pageYes = document.getElementById('page-yes');
    const pageNo = document.getElementById('page-no');
    const heartsContainer = document.querySelector('.hearts-container');
    const loveTapEffect = document.getElementById('love-tap-effect');
    const responseForm = document.getElementById('response-form');
    const responseInput = document.getElementById('response-input');
    const userAgentInput = document.getElementById('user-agent');
    const timestampInput = document.getElementById('timestamp');
    
    // State variables
    let currentPage = 1;
    const totalPages = pages.length;
    let musicPlaying = false;
    let touchStartX = 0;
    let touchEndX = 0;
    let proposalMade = false;
    
    // Initialize - START MUSIC AUTOMATICALLY WHEN WEBSITE LOADS
    function init() {
        totalPagesEl.textContent = totalPages;
        updatePageControls();
        createFloatingHearts();
        setupTouchGestures();
        setFormData();
        
        // Start music automatically when website loads
        setTimeout(() => {
            startBackgroundMusic();
        }, 500); // Small delay to ensure everything loads
        
        // Add tap effect listener
        document.addEventListener('touchstart', createLoveTapEffect, { passive: true });
        
        // Add vibration on button tap (if supported)
        if ('vibrate' in navigator) {
            const buttons = document.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.addEventListener('touchstart', () => {
                    navigator.vibrate(10);
                });
            });
        }
    }
    
    // Function to start background music
    function startBackgroundMusic() {
        if (musicPlaying) return;
        
        bgMusic.volume = 0.4;
        const playPromise = bgMusic.play();
        
        if (playPromise !== undefined) {
            playPromise.then(() => {
                musicPlaying = true;
                musicToggle.innerHTML = '<i class="fas fa-music"></i><span class="music-text">Music</span>';
                console.log("Music started automatically");
            }).catch(error => {
                console.log("Autoplay prevented. User interaction required.");
                musicToggle.innerHTML = '<i class="fas fa-volume-mute"></i><span class="music-text">Play</span>';
                musicPlaying = false;
                
                // Add click event to start music on user interaction
                document.body.addEventListener('click', startMusicOnInteraction, { once: true });
            });
        }
    }
    
    // Start music on user interaction if autoplay was blocked
    function startMusicOnInteraction() {
        bgMusic.volume = 0.4;
        bgMusic.play().then(() => {
            musicPlaying = true;
            musicToggle.innerHTML = '<i class="fas fa-music"></i><span class="music-text">Music</span>';
        }).catch(err => {
            console.log("Still can't play music:", err);
        });
    }
    
    // Set form data for database
    function setFormData() {
        userAgentInput.value = navigator.userAgent;
        timestampInput.value = new Date().toISOString();
    }
    
    // Create floating hearts
    function createFloatingHearts() {
        const heartCount = 15;
        
        for (let i = 0; i < heartCount; i++) {
            const heart = document.createElement('div');
            heart.innerHTML = '<i class="fas fa-heart"></i>';
            heart.style.position = 'fixed';
            heart.style.fontSize = `${16 + Math.random() * 20}px`;
            heart.style.left = `${Math.random() * 100}%`;
            heart.style.top = `${Math.random() * 120}%`;
            heart.style.opacity = `${0.2 + Math.random() * 0.3}`;
            heart.style.zIndex = '1';
            heart.style.pointerEvents = 'none';
            heart.style.userSelect = 'none';
            heart.style.color = `rgba(${Math.floor(184 + Math.random() * 40)}, ${Math.floor(50 + Math.random() * 40)}, ${Math.floor(128 + Math.random() * 40)}, ${0.3 + Math.random() * 0.3})`;
            heart.style.animation = `float ${6 + Math.random() * 10}s infinite ease-in-out`;
            heart.style.animationDelay = `${Math.random() * 4}s`;
            
            heartsContainer.appendChild(heart);
        }
    }
    
    // Setup touch gestures for swiping
    function setupTouchGestures() {
        const pagesWrapper = document.querySelector('.pages-wrapper');
        
        pagesWrapper.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        pagesWrapper.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
    }
    
    function handleSwipe() {
        const swipeThreshold = 40;
        const diff = touchStartX - touchEndX;
        
        // Don't allow swiping on response pages
        if (pageYes.classList.contains('active') || pageNo.classList.contains('active')) {
            return;
        }
        
        // Left swipe - next page
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0 && currentPage < totalPages) {
                goToNextPage();
            } 
            // Right swipe - previous page
            else if (diff < 0 && currentPage > 1) {
                goToPreviousPage();
            }
        }
    }
    
    // Create love tap effect
    function createLoveTapEffect(e) {
        if (e.target.closest('button')) return;
        
        const tapX = e.touches ? e.touches[0].clientX : e.clientX;
        const tapY = e.touches ? e.touches[0].clientY : e.clientY;
        
        const heart = document.createElement('div');
        heart.innerHTML = '<i class="fas fa-heart"></i>';
        heart.style.position = 'fixed';
        heart.style.left = `${tapX - 15}px`;
        heart.style.top = `${tapY - 15}px`;
        heart.style.fontSize = '30px';
        heart.style.color = 'var(--light-pink)';
        heart.style.zIndex = '1000';
        heart.style.pointerEvents = 'none';
        heart.style.animation = 'loveTap 0.8s forwards';
        
        loveTapEffect.appendChild(heart);
        
        // Remove after animation
        setTimeout(() => {
            heart.remove();
        }, 800);
    }
    
    // Open envelope button event
    openBtn.addEventListener('click', openEnvelope);
    openBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        openEnvelope();
    }, { passive: false });
    
    function openEnvelope() {
        // Add vibration
        if ('vibrate' in navigator) navigator.vibrate([30, 20, 30]);
        
        // Add open class to envelope for animation
        envelope.classList.add('open');
        
        // Show letter after a delay
        setTimeout(() => {
            letter.classList.add('show');
        }, 300);
        
        // Transition to pages section after a longer delay
        setTimeout(() => {
            envelopeSection.classList.remove('active');
            envelopeSection.classList.add('hidden');
            pagesSection.classList.remove('hidden');
            pagesSection.classList.add('active');
            
            // Start with first page
            showPage(currentPage);
            
            // Ensure music is playing (in case it wasn't started automatically)
            if (!musicPlaying) {
                startBackgroundMusic();
            }
        }, 1800);
    }
    
    function showPage(pageNumber) {
        // Hide all pages including response pages
        document.querySelectorAll('.page').forEach(page => {
            page.classList.remove('active');
        });
        
        // Show the requested page
        const pageToShow = document.getElementById(`page-${pageNumber}`);
        if (pageToShow) {
            pageToShow.classList.add('active');
            currentPageEl.textContent = pageNumber;
            updatePageControls();
            
            // Add special animations based on page
            addPageAnimations(pageNumber);
            
            // Play special music on proposal page
            if (pageNumber === 6 && !proposalMade) {
                playProposalMusic();
            }
        }
    }
    
    function showResponsePage(responseType) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(page => {
            page.classList.remove('active');
        });
        
        // Show response page
        const responsePage = document.getElementById(`page-${responseType}`);
        if (responsePage) {
            responsePage.classList.add('active');
            
            // Hide page controls
            document.querySelector('.page-controls').style.display = 'none';
            
            // Submit to database
            submitResponse(responseType);
            
            // Play celebration or sad music
            if (responseType === 'yes') {
                playCelebration();
                if ('vibrate' in navigator) navigator.vibrate([80, 40, 80, 40, 80]);
            } else {
                if ('vibrate' in navigator) navigator.vibrate([150, 80, 150]);
            }
        }
    }
    
    function submitResponse(response) {
        responseInput.value = response;
        
        // In a real implementation, you would submit to a PHP file
        // For now, we'll log it
        console.log('Response submitted:', response);
        console.log('User Agent:', userAgentInput.value);
        console.log('Timestamp:', timestampInput.value);
        
        // Simulate form submission
        fetch('save_response.php', {
            method: 'POST',
            body: new FormData(responseForm)
        }).catch(err => {
            console.log('Could not submit to server:', err);
        });
    }
    
    function goToPreviousPage() {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
            playSound('page');
            if ('vibrate' in navigator) navigator.vibrate(10);
        }
    }
    
    function goToNextPage() {
        if (currentPage < totalPages) {
            currentPage++;
            showPage(currentPage);
            playSound('page');
            if ('vibrate' in navigator) navigator.vibrate(10);
        }
    }
    
    prevBtn.addEventListener('click', goToPreviousPage);
    nextBtn.addEventListener('click', goToNextPage);
    
    // Touch events for navigation buttons
    prevBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        goToPreviousPage();
    }, { passive: false });
    
    nextBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        goToNextPage();
    }, { passive: false });
    
    // Proposal buttons
    yesBtn.addEventListener('click', () => handleProposalResponse('yes'));
    yesBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        handleProposalResponse('yes');
    }, { passive: false });
    
    noBtn.addEventListener('click', () => handleProposalResponse('no'));
    noBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        handleProposalResponse('no');
    }, { passive: false });
    
    backToProposalBtn.addEventListener('click', () => {
        showPage(6);
        document.querySelector('.page-controls').style.display = 'flex';
        if ('vibrate' in navigator) navigator.vibrate(10);
    });
    
    backToProposalBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        showPage(6);
        document.querySelector('.page-controls').style.display = 'flex';
        if ('vibrate' in navigator) navigator.vibrate(10);
    }, { passive: false });
    
    closeSiteBtn.addEventListener('click', closeWebsite);
    closeSiteBtn.addEventListener('touchstart', (e) => {
        e.preventDefault();
        closeWebsite();
    }, { passive: false });
    
    function handleProposalResponse(response) {
        proposalMade = true;
        showResponsePage(response);
    }
    
    function closeWebsite() {
        // Create a beautiful closing animation
        const container = document.querySelector('.container');
        container.style.animation = 'fadeOut 1s forwards';
        
        // Stop music
        bgMusic.pause();
        
        // Show final message
        setTimeout(() => {
            document.body.innerHTML = `
                <div class="closing-screen">
                    <div class="final-message">
                        <h1>Thank You Manvi! ❤️</h1>
                        <p>I love you more than anything in this world</p>
                        <p class="sign-off">- Hitesh</p>
                    </div>
                </div>
            `;
            
            // Add closing screen styles
            const style = document.createElement('style');
            style.textContent = `
                .closing-screen {
                    background: linear-gradient(135deg, var(--dark-bg) 0%, #1a0b23 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    animation: fadeIn 1s;
                }
                .final-message {
                    text-align: center;
                    color: var(--soft-pink);
                }
                .final-message h1 {
                    font-family: 'Dancing Script', cursive;
                    font-size: 2.5rem;
                    margin-bottom: 15px;
                }
                .final-message p {
                    font-size: 1.3rem;
                    margin-bottom: 10px;
                }
                .sign-off {
                    font-family: 'Dancing Script', cursive;
                    font-size: 1.8rem;
                    color: var(--light-pink);
                    margin-top: 20px;
                }
                @keyframes fadeOut {
                    to { opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }, 1000);
    }
    
    function updatePageControls() {
        // Update button states
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        
        // Visual feedback for disabled buttons
        if (prevBtn.disabled) {
            prevBtn.style.opacity = '0.4';
            prevBtn.style.cursor = 'not-allowed';
        } else {
            prevBtn.style.opacity = '1';
            prevBtn.style.cursor = 'pointer';
        }
        
        if (nextBtn.disabled) {
            nextBtn.style.opacity = '0.4';
            nextBtn.style.cursor = 'not-allowed';
        } else {
            nextBtn.style.opacity = '1';
            nextBtn.style.cursor = 'pointer';
        }
    }
    
    function addPageAnimations(pageNumber) {
        // Remove any existing animations
        const animatedElements = document.querySelectorAll('.animate-on-view');
        animatedElements.forEach(el => {
            el.classList.remove('animate-on-view');
        });
        
        // Add animations based on page
        switch(pageNumber) {
            case 1:
                // Animate hearts in page 1
                const hearts = document.querySelectorAll('.heart-rain i');
                hearts.forEach((heart, index) => {
                    setTimeout(() => {
                        heart.classList.add('animate-on-view');
                    }, index * 200);
                });
                break;
                
            case 4:
                // Animate candle flame
                const flame = document.querySelector('.flame');
                if (flame) {
                    setTimeout(() => {
                        flame.classList.add('animate-on-view');
                    }, 300);
                }
                
                // Animate wish items
                const wishItems = document.querySelectorAll('.wish-item');
                wishItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('animate-on-view');
                    }, index * 200);
                });
                break;
                
            case 6:
                // Animate ring
                const ring = document.querySelector('.ring');
                if (ring) {
                    setTimeout(() => {
                        ring.classList.add('animate-on-view');
                    }, 300);
                }
                break;
        }
    }
    
    // Music control
    musicToggle.addEventListener('click', toggleMusic);
    musicToggle.addEventListener('touchstart', (e) => {
        e.preventDefault();
        toggleMusic();
    }, { passive: false });
    
    function playProposalMusic() {
        bgMusic.currentTime = 0;
        bgMusic.play().catch(e => console.log("Could not play music"));
    }
    
    function playCelebration() {
        playSound('celebration');
        
        // Ensure background music is playing
        if (bgMusic.paused) {
            bgMusic.play();
        }
    }
    
    function toggleMusic() {
        if ('vibrate' in navigator) navigator.vibrate(10);
        
        if (bgMusic.paused) {
            bgMusic.play();
            musicToggle.innerHTML = '<i class="fas fa-music"></i><span class="music-text">Music</span>';
            musicPlaying = true;
        } else {
            bgMusic.pause();
            musicToggle.innerHTML = '<i class="fas fa-volume-mute"></i><span class="music-text">Play</span>';
            musicPlaying = false;
        }
    }
    
    function playSound(type) {
        // Simple sound effect using Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            // Different sounds for different events
            switch(type) {
                case 'page':
                    oscillator.frequency.value = 659.25;
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.15, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.2);
                    break;
                    
                case 'celebration':
                    // Celebration fanfare
                    const frequencies = [523.25, 659.25, 783.99];
                    let currentTime = audioContext.currentTime;
                    
                    frequencies.forEach((freq, index) => {
                        const osc = audioContext.createOscillator();
                        const gain = audioContext.createGain();
                        
                        osc.connect(gain);
                        gain.connect(audioContext.destination);
                        
                        osc.frequency.value = freq;
                        osc.type = 'sine';
                        
                        gain.gain.setValueAtTime(0, currentTime);
                        gain.gain.linearRampToValueAtTime(0.2, currentTime + 0.1);
                        gain.gain.exponentialRampToValueAtTime(0.01, currentTime + 0.3);
                        
                        osc.start(currentTime);
                        osc.stop(currentTime + 0.3);
                        
                        currentTime += 0.1;
                    });
                    break;
            }
        } catch (e) {
            console.log("Audio context not supported");
        }
    }
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes loveTap {
            0% {
                transform: scale(0.5) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: scale(1.5) rotate(15deg);
                opacity: 0;
            }
        }
        
        .animate-on-view {
            animation: popIn 0.5s ease-out forwards;
        }
        
        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.5);
            }
            70% {
                transform: scale(1.05);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .wish-item.animate-on-view {
            animation: slideInRight 0.4s ease-out forwards;
            opacity: 0;
        }
        
        @keyframes slideInRight {
            0% {
                opacity: 0;
                transform: translateX(20px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Prevent text selection */
        .no-select {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Fix for image loading */
        .girlfriend-photo {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Ensure pages don't overflow */
        .page-body {
            overflow-y: auto;
            max-height: calc(60vh - 80px);
        }
        
        /* Scrollbar styling */
        .page-body::-webkit-scrollbar {
            width: 4px;
        }
        
        .page-body::-webkit-scrollbar-track {
            background: rgba(184, 50, 128, 0.05);
            border-radius: 2px;
        }
        
        .page-body::-webkit-scrollbar-thumb {
            background: var(--medium-pink);
            border-radius: 2px;
        }
    `;
    document.head.appendChild(style);
    
    // Add no-select class to interactive elements
    document.querySelectorAll('button, .page-content, .memory-card, .quality-item, .wish-item').forEach(el => {
        el.classList.add('no-select');
    });
    
    // Fix for image loading error
    const girlfriendPhoto = document.querySelector('.girlfriend-photo');
    if (girlfriendPhoto) {
        girlfriendPhoto.onerror = function() {
            this.onerror = null;
            this.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="%231a0b23"/><text x="50" y="55" text-anchor="middle" font-family="Dancing Script" font-size="16" fill="%23f687b3">My Love</text></svg>';
        };
    }
    
    // Initialize the app - MUSIC WILL START AUTOMATICALLY
    init();
});