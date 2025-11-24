<?php
/**
 * AI Chatbot Component
 * Reusable AI chatbot widget that can be included on any page
 * 
 * Usage: 
 * Include this file where you want the chatbot to appear
 * Make sure to include the CSS and JS files as well
 */
?>

<!-- AI Chatbot UI (Gemini) -->
<style>
/* AI Chatbot Styles */
.ai-chat-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1e3a5f, #3f3b3bff);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 25px rgba(30, 58, 95, 0.4);
  z-index: 9999;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: aiChatPulse 2s infinite;
}

.ai-chat-toggle:hover {
  transform: scale(1.1);
  box-shadow: 0 12px 35px rgba(30, 58, 95, 0.6);
}

.ai-chat-toggle i {
  font-size: 24px;
  animation: aiChatBounce 1s ease-in-out infinite alternate;
}

@keyframes aiChatPulse {
  0%, 100% { box-shadow: 0 8px 25px rgba(30, 58, 95, 0.4); }
  50% { box-shadow: 0 8px 25px rgba(30, 58, 95, 0.6), 0 0 0 10px rgba(30, 58, 95, 0.1); }
}

@keyframes aiChatBounce {
  0% { transform: translateY(0px); }
  100% { transform: translateY(-3px); }
}

/* Proactive Chat Bubbles */
.ai-proactive-bubble {
  position: fixed;
  bottom: 90px;
  right: 20px;
  background: #fff;
  border-radius: 20px 20px 5px 20px;
  padding: 12px 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(30, 58, 95, 0.1);
  max-width: 250px;
  z-index: 9998;
  opacity: 0;
  transform: translateY(20px) scale(0.8);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 14px;
  color: #333;
  cursor: pointer;
  animation: aiProactivePulse 3s ease-in-out infinite;
}

.ai-proactive-bubble.show {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.ai-proactive-bubble:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.ai-proactive-bubble::before {
  content: '';
  position: absolute;
  bottom: -8px;
  right: 20px;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-top: 8px solid #fff;
}

@keyframes aiProactivePulse {
  0%, 100% { box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); }
  50% { box-shadow: 0 8px 25px rgba(30, 58, 95, 0.3); }
}

.ai-chat-window {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 350px;
  max-height: 500px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  display: none;
  flex-direction: column;
  overflow: hidden;
  z-index: 9999;
  transform: scale(0.8) translateY(20px);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(30, 58, 95, 0.1);
}

.ai-chat-window.show {
  transform: scale(1) translateY(0);
  opacity: 1;
}

.ai-chat-header {
  background: linear-gradient(135deg, #1e3a5f, #c53030);
  color: #fff;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}

.ai-chat-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  animation: aiChatShimmer 3s infinite;
}

@keyframes aiChatShimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

.ai-chat-title {
  font-weight: 700;
  font-size: 16px;
  margin-bottom: 2px;
}

.ai-chat-subtitle {
  font-size: 12px;
  opacity: 0.9;
}

.ai-chat-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: #fff;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 18px;
}

.ai-chat-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.ai-chat-messages {
  padding: 20px;
  height: 320px;
  overflow-y: auto;
  font-size: 14px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  position: relative;
}

.ai-chat-messages::-webkit-scrollbar {
  width: 6px;
}

.ai-chat-messages::-webkit-scrollbar-track {
  background: transparent;
}

.ai-chat-messages::-webkit-scrollbar-thumb {
  background: rgba(30, 58, 95, 0.3);
  border-radius: 3px;
}

.ai-message {
  margin-bottom: 12px;
  animation: aiMessageSlideIn 0.4s ease-out;
}

@keyframes aiMessageSlideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.ai-message-bubble {
  padding: 12px 16px;
  border-radius: 18px;
  max-width: 85%;
  font-size: 13px;
  line-height: 1.4;
  position: relative;
}

.ai-message.user {
  text-align: right;
}

.ai-message.user .ai-message-bubble {
  background: linear-gradient(135deg, #1e3a5f, #c53030);
  color: #fff;
  display: inline-block;
  box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
}

.ai-message.bot .ai-message-bubble {
  background: #fff;
  color: #2d3748;
  display: inline-block;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(30, 58, 95, 0.1);
}

.ai-typing-indicator {
  display: inline-block;
  background: #fff;
  padding: 12px 16px;
  border-radius: 18px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.ai-typing-dots {
  display: inline-flex;
  gap: 4px;
}

.ai-typing-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #1e3a5f;
  animation: aiTypingBounce 1.4s infinite ease-in-out;
}

.ai-typing-dot:nth-child(1) { animation-delay: -0.32s; }
.ai-typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes aiTypingBounce {
  0%, 80%, 100% {
    transform: scale(0);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

.ai-chat-input-area {
  padding: 16px 20px;
  background: #fff;
  border-top: 1px solid rgba(30, 58, 95, 0.1);
  display: flex;
  gap: 12px;
  align-items: center;
}

.ai-chat-input {
  flex: 1;
  border: 2px solid #e2e8f0;
  border-radius: 25px;
  padding: 12px 16px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
  background: #f7fafc;
}

.ai-chat-input:focus {
  border-color: #1e3a5f;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

.ai-chat-send {
  background: linear-gradient(135deg, #1e3a5f, #c53030);
  color: #fff;
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 16px;
}

.ai-chat-send:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(30, 58, 95, 0.4);
}

.ai-chat-send:active {
  transform: scale(0.95);
}

/* Mobile responsiveness */
@media (max-width: 480px) {
  .ai-chat-window {
    width: calc(100vw - 30px);
    right: 15px;
    left: 15px;
    bottom: 85px;
    max-height: 70vh;
  }
  
  .ai-chat-toggle {
    bottom: 15px;
    right: 15px;
    width: 50px;
    height: 50px;
    z-index: 99999 !important;
  }
  
  .ai-chat-toggle i {
    font-size: 20px;
  }
  
  .ai-proactive-bubble {
    bottom: 75px;
    right: 15px;
    max-width: calc(100vw - 90px);
    font-size: 13px;
    padding: 10px 14px;
  }
  
  .ai-chat-messages {
    height: 250px;
    padding: 15px;
  }
  
  .ai-chat-header {
    padding: 12px 15px;
  }
  
  .ai-chat-title {
    font-size: 14px;
  }
  
  .ai-chat-subtitle {
    font-size: 11px;
  }
  
  .ai-chat-input-area {
    padding: 12px 15px;
  }
  
  .ai-chat-input {
    font-size: 13px;
    padding: 10px 14px;
  }
  
  .ai-chat-send {
    width: 40px;
    height: 40px;
    font-size: 14px;
  }
}

@media (max-width: 768px) and (min-width: 481px) {
  .ai-chat-window {
    width: calc(100vw - 50px);
    right: 25px;
    left: 25px;
  }
}
</style>

<div id="aiChatToggle" class="ai-chat-toggle">
  <i class="fas fa-robot"></i>
</div>

<div id="aiChatWindow" class="ai-chat-window">
  <div class="ai-chat-header">
    <div>
      <div class="ai-chat-title">🤖 Lumbangan AI Assistant</div>
      <div class="ai-chat-subtitle">Ask about barangay services</div>
    </div>
    <button id="aiChatClose" class="ai-chat-close">
      <i class="fas fa-times"></i>
    </button>
  </div>
  
  <div id="aiChatMessages" class="ai-chat-messages">
    <div class="ai-message bot">
      <div class="ai-message-bubble">
        👋 Hi! Ako ang AI Assistant ng Barangay Lumbangan. Ano ang maitutulong ko sa iyo?
      </div>
    </div>
  </div>
  
  <div class="ai-chat-input-area">
    <input id="aiChatInput" type="text" class="ai-chat-input" placeholder="Type your question...">
    <button id="aiChatSend" class="ai-chat-send">
      <i class="fas fa-paper-plane"></i>
    </button>
  </div>
</div>