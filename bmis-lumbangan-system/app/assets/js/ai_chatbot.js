/**
 * AI Chatbot JavaScript
 * Handles all chatbot functionality
 * 
 * Configuration:
 * - Automatically detects the correct API path based on current page location
 * - Works from any directory depth
 */

(function() {
  // Auto-detect API path based on current location
  function getApiPath() {
    const currentPath = window.location.pathname;
    
    // If we're in the bmis-lumbangan-system directory structure
    if (currentPath.includes('/bmis-lumbangan-system/')) {
      return '/Lumbangan_BMIS/bmis-lumbangan-system/api/ai_chatbot_gemini.php';
    }
    
    // Fallback - try to construct relative path
    const depth = (currentPath.match(/\//g) || []).length - 1;
    if (depth <= 2) {
      return './api/ai_chatbot_gemini.php';
    } else if (depth === 3) {
      return '../api/ai_chatbot_gemini.php';
    } else {
      return '../../api/ai_chatbot_gemini.php';
    }
  }

  const toggle = document.getElementById('aiChatToggle');
  const windowEl = document.getElementById('aiChatWindow');
  const closeBtn = document.getElementById('aiChatClose');
  const messagesEl = document.getElementById('aiChatMessages');
  const inputEl = document.getElementById('aiChatInput');
  const sendBtn = document.getElementById('aiChatSend');

  // Only initialize if elements exist
  if (!toggle || !windowEl || !closeBtn || !messagesEl || !inputEl || !sendBtn) {
    console.warn('AI Chatbot: Required elements not found');
    return;
  }

  function appendMessage(text, sender) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `ai-message ${sender}`;
    
    const bubble = document.createElement('div');
    bubble.className = 'ai-message-bubble';
    bubble.textContent = text;
    
    messageDiv.appendChild(bubble);
    messagesEl.appendChild(messageDiv);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  let isSending = false;

  // Detect current page context
  function getCurrentPageContext() {
    const currentPath = window.location.pathname;
    const currentUrl = window.location.href;
    const searchParams = new URLSearchParams(window.location.search);
    const pageParam = searchParams.get('page');
    
    let pageContext = {
      page: 'unknown',
      pageName: 'Unknown Page',
      context: 'general'
    };
    
    // Check URL parameter first (for index.php routing)
    if (pageParam) {
      if (pageParam.includes('dashboard') || pageParam === 'dashboard_resident') {
        pageContext = {
          page: 'dashboard',
          pageName: 'Resident Dashboard',
          context: 'User is in their personal dashboard where they can view their document requests, profile, and account status'
        };
      } else if (pageParam.includes('document')) {
        pageContext = {
          page: 'document_request',
          pageName: 'Document Request Page',
          context: 'User is on the document request page where they can request barangay certificates and clearances'
        };
      } else if (pageParam.includes('announcement')) {
        pageContext = {
          page: 'announcement',
          pageName: 'Announcements Page',
          context: 'User is viewing barangay announcements and news'
        };
      } else if (pageParam.includes('survey')) {
        pageContext = {
          page: 'survey',
          pageName: 'Health Survey Page',
          context: 'User is taking health surveys or wizards for medical assessments'
        };
      }
    }
    // Check path if no param match (for direct file access)
    else if (currentPath.includes('/landing/') || currentPath === '/' || currentPath.includes('index.php')) {
      pageContext = {
        page: 'landing',
        pageName: 'Landing Page',
        context: 'User is browsing the main landing page with barangay information and services overview'
      };
    } else if (currentPath.includes('/Dashboard/') || currentPath.includes('/dashboard')) {
      pageContext = {
        page: 'dashboard',
        pageName: 'Resident Dashboard',
        context: 'User is in their personal dashboard where they can view their document requests, profile, and account status'
      };
    } else if (currentPath.includes('/document_request')) {
      pageContext = {
        page: 'document_request',
        pageName: 'Document Request Page',
        context: 'User is on the document request page where they can request barangay certificates and clearances'
      };
    } else if (currentPath.includes('/announcement')) {
      pageContext = {
        page: 'announcement',
        pageName: 'Announcements Page',
        context: 'User is viewing barangay announcements and news'
      };
    } else if (currentPath.includes('/gallery')) {
      pageContext = {
        page: 'gallery',
        pageName: 'Gallery Page',
        context: 'User is viewing the barangay photo gallery and events'
      };
    } else if (currentPath.includes('/admin') || currentPath.includes('/SecDash')) {
      pageContext = {
        page: 'admin',
        pageName: 'Admin Dashboard',
        context: 'User is in the admin area managing barangay system operations'
      };
    } else if (currentPath.includes('/Survey/')) {
      pageContext = {
        page: 'survey',
        pageName: 'Health Survey Page',
        context: 'User is taking health surveys or wizards for medical assessments'
      };
    }
    
    return pageContext;
  }

  async function sendMessage() {
    const text = inputEl.value.trim();
    if (!text || isSending) return;

    appendMessage(text, 'user');
    inputEl.value = '';
    isSending = true;

    const typingId = 'ai-typing';
    const typingDiv = document.createElement('div');
    typingDiv.id = typingId;
    typingDiv.className = 'ai-message bot';
    
    const typingBubble = document.createElement('div');
    typingBubble.className = 'ai-typing-indicator';
    typingBubble.innerHTML = `
      <div class="ai-typing-dots">
        <div class="ai-typing-dot"></div>
        <div class="ai-typing-dot"></div>
        <div class="ai-typing-dot"></div>
      </div>
    `;
    
    typingDiv.appendChild(typingBubble);
    messagesEl.appendChild(typingDiv);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    try {
      const apiUrl = getApiPath();
      console.log('AI Chatbot: Sending request to:', apiUrl);
      
      // Get current page context
      const pageContext = getCurrentPageContext();
      
      const res = await fetch(apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ 
          message: text,
          pageContext: pageContext
        })
      });

      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }

      const data = await res.json();
      document.getElementById(typingId)?.remove();

      if (data.reply) {
        appendMessage(data.reply, 'bot');
      } else if (data.error) {
        console.error('AI Chatbot API Error:', data.error);
        if (data.error.includes('API key')) {
          appendMessage('Pasensya na, may problema sa API key. Pakicheck ang configuration.', 'bot');
        } else {
          appendMessage('Error: ' + data.error, 'bot');
        }
      } else {
        appendMessage('Pasensya na, nagka-error sa server. Subukan ulit mamaya.', 'bot');
      }
    } catch (err) {
      console.error('AI Chatbot Fetch error:', err);
      document.getElementById(typingId)?.remove();
      appendMessage('Pasensya na, hindi ako makakonek sa server ngayon.', 'bot');
    } finally {
      isSending = false;
    }
  }

  // Event listeners with cool animations
  toggle.addEventListener('click', () => {
    if (windowEl.style.display === 'flex') {
      // Close animation
      windowEl.classList.remove('show');
      setTimeout(() => {
        windowEl.style.display = 'none';
      }, 300);
    } else {
      // Open animation
      windowEl.style.display = 'flex';
      setTimeout(() => {
        windowEl.classList.add('show');
      }, 10);
    }
  });

  closeBtn.addEventListener('click', () => {
    windowEl.classList.remove('show');
    setTimeout(() => {
      windowEl.style.display = 'none';
    }, 300);
  });

  sendBtn.addEventListener('click', sendMessage);

  inputEl.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      sendMessage();
    }
  });

  // Add some extra cool effects
  inputEl.addEventListener('focus', () => {
    inputEl.parentElement.style.transform = 'scale(1.02)';
  });

  inputEl.addEventListener('blur', () => {
    inputEl.parentElement.style.transform = 'scale(1)';
  });

  // Proactive Chat Bubbles with Page Context
  function getProactiveMessages() {
    const pageContext = getCurrentPageContext();
    
    const generalMessages = [
      "Hi kamusta! 👋",
      "Kumusta ka? May problema ba? 😊",
      "Hello! Need help with barangay services?",
      "Kailangan mo ba ng tulong? I'm here!"
    ];
    
    const pageSpecificMessages = {
      landing: [
        "Welcome sa Barangay Lumbangan! Need info?",
        "Hi! Exploring our barangay services?",
        "Kamusta! May tanong about our barangay?",
        "Need help navigating our services?"
      ],
      dashboard: [
        "Hi! How's your dashboard experience?",
        "Need help with your account or requests?",
        "Kamusta! Check mo ba ang status ng documents?",
        "Any issues with your profile settings?"
      ],
      document_request: [
        "Need help requesting documents?",
        "Anong certificate ang kailangan mo?",
        "I can guide you through the process!",
        "Questions about document requirements?"
      ],
      announcement: [
        "Reading the latest barangay news?",
        "Any questions about announcements?",
        "Need clarification on recent updates?",
        "Kamusta! Ano ang latest news?"
      ],
      gallery: [
        "Enjoying our barangay photos?",
        "Questions about past events?",
        "Want to know about upcoming activities?",
        "Nice photos, right? Any questions?"
      ],
      admin: [
        "Hi admin! Need system assistance?",
        "Any admin tasks I can help with?",
        "Questions about system management?",
        "How's the admin work going?"
      ],
      survey: [
        "Need help with the health survey?",
        "Questions about the medical forms?",
        "I can guide you through the survey!",
        "Any clarification needed sa survey?"
      ]
    };
    
    const contextMessages = pageSpecificMessages[pageContext.page] || [];
    return [...generalMessages, ...contextMessages];
  }

  let currentBubble = null;
  let bubbleTimeout = null;
  let hasUserInteracted = false;

  function createProactiveBubble(message) {
    // Remove existing bubble
    if (currentBubble) {
      currentBubble.remove();
    }

    currentBubble = document.createElement('div');
    currentBubble.className = 'ai-proactive-bubble';
    currentBubble.textContent = message;
    
    // Click to open chat
    currentBubble.addEventListener('click', () => {
      hasUserInteracted = true;
      openChatWithMessage(message);
      hideProactiveBubble();
    });

    document.body.appendChild(currentBubble);
    
    // Show with animation
    setTimeout(() => {
      currentBubble.classList.add('show');
    }, 100);

    // Auto hide after 4 seconds (shorter for more frequent messages)
    bubbleTimeout = setTimeout(() => {
      hideProactiveBubble();
    }, 4000);
  }

  function hideProactiveBubble() {
    if (currentBubble) {
      currentBubble.classList.remove('show');
      setTimeout(() => {
        if (currentBubble) {
          currentBubble.remove();
          currentBubble = null;
        }
      }, 400);
    }
    if (bubbleTimeout) {
      clearTimeout(bubbleTimeout);
      bubbleTimeout = null;
    }
  }

  function openChatWithMessage(message) {
    // Open chat window
    windowEl.style.display = 'flex';
    setTimeout(() => {
      windowEl.classList.add('show');
    }, 10);

    // Add the proactive message as first bot message
    setTimeout(() => {
      appendMessage(message, 'bot');
    }, 500);
  }

  function startProactiveBubbles() {
    if (hasUserInteracted) return;

    // First bubble after 2 seconds
    setTimeout(() => {
      if (!hasUserInteracted && windowEl.style.display !== 'flex') {
        const proactiveMessages = getProactiveMessages();
        const randomMessage = proactiveMessages[Math.floor(Math.random() * proactiveMessages.length)];
        createProactiveBubble(randomMessage);
      }
    }, 2000);

    // Show bubbles periodically (every 5 seconds)
    setInterval(() => {
      if (!hasUserInteracted && windowEl.style.display !== 'flex' && !currentBubble) {
        const proactiveMessages = getProactiveMessages();
        const randomMessage = proactiveMessages[Math.floor(Math.random() * proactiveMessages.length)];
        createProactiveBubble(randomMessage);
      }
    }, 5000);
  }

  // Track user interaction
  toggle.addEventListener('click', () => {
    hasUserInteracted = true;
    hideProactiveBubble();
  });

  inputEl.addEventListener('focus', () => {
    hasUserInteracted = true;
    hideProactiveBubble();
  });

  // Start proactive bubbles
  startProactiveBubbles();

  console.log('AI Chatbot: Initialized successfully with proactive bubbles');
})();