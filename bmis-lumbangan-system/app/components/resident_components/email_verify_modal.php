<!-- Email Verification Modal (Matches Forget Password Modal Design) -->
<div id="emailVerifyModal" class="login-modal-overlay" style="display: none;">
  <div style="position: relative; animation: modalSlideIn 0.5s ease;">
    
    <!-- Close Button -->
    <button onclick="closeEmailVerifyModal()" style="
      position: absolute;
      top: -50px;
      right: 0;
      background: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 20px;
      color: #667eea;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      z-index: 10001;
    " onmouseover="this.style.background='#d32f2f'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='#667eea';">×</button>

    <!-- Logo -->
    <img src="https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png" alt="batangas-logo" style="
      position: absolute;
      top: -80px;
      left: 50%;
      transform: translateX(-50%);
      width: 200px;
      filter: drop-shadow(0 0 20px rgba(0,0,0,0.3));
      z-index: 10000;
    ">

    <!-- Main Container -->
    <div style="
      background-color: #fff;
      border-radius: 100px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
      max-width: 500px;
      width: 100%;
      padding: 60px 40px 40px;
      overflow-y: auto;
      max-height: 85vh;
    ">

      <!-- Step 1: Confirmation to send code -->
      <div id="emailVerifyStep1" style="display: block;">
        <h1 style="color: #333; text-align: center; margin-bottom: 10px; font-size: 24px;">Verify Your Email</h1>
        <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 20px;">
          <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png" alt="Batangas" style="width: 40px;">
          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png" style="width: 39px;">
          <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png" style="width: 41px;">
        </div>
        <p style="color: #666; text-align: center; margin-bottom: 30px; font-size: 0.95rem;">We'll send a verification code to complete your registration</p>
        
        <div id="emailVerifyStep1Error" style="display: none; color: #d32f2f; margin-bottom: 15px; font-size: 13px; font-weight: 500;">
          <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
          <span></span>
        </div>

        <button type="button" onclick="sendVerificationCode()" style="
          padding: 10px 45px;
          background-color: #2600ff;
          color: white;
          border: 1px solid transparent;
          border-radius: 8px;
          font-weight: 600;
          cursor: pointer;
          font-size: 12px;
          letter-spacing: 0.5px;
          text-transform: uppercase;
          transition: all 0.3s ease-in-out;
          width: 100%;
        " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 5px 15px rgba(0, 0, 0, 0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">Send Verification Code</button>

        <button type="button" onclick="closeEmailVerifyModal()" style="
          background: transparent;
          color: #667eea;
          margin-top: 15px;
          font-weight: 500;
          border: none;
          cursor: pointer;
          text-decoration: underline;
          width: 100%;
        ">Cancel</button>
      </div>

      <!-- Step 2: Code Verification -->
      <div id="emailVerifyStep2" style="display: none;">
        <h1 style="color: #333; text-align: center; margin-bottom: 20px; font-size: 24px;">Verify Code</h1>
        <p style="color: #666; text-align: center; margin-bottom: 10px; font-size: 0.95rem;">Enter the 6-digit code sent to<br><strong id="emailVerifyEmailDisplay" style="color: #333;"></strong></p>
        
        <div id="emailVerifyCountdown" style="
          background: #f0f4ff;
          padding: 12px;
          border-radius: 8px;
          margin-bottom: 20px;
          text-align: center;
          border: 1px solid #e0e7ff;
        ">
          <i class="fas fa-clock" style="color: #667eea; margin-right: 8px;"></i>
          <span style="color: #4a5568; font-weight: 600; font-size: 0.9rem;">Code expires in: <span id="emailVerifyTimer" style="color: #667eea;">60:00</span></span>
        </div>

        <form onsubmit="verifyEmailCode(event)" style="display: flex; flex-direction: column;">
          <input type="text" placeholder="000000" maxlength="6" id="emailVerifyCode" required style="
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-weight: 600;
            width: 100%;
          ">
          
          <div id="emailVerifyStep2Error" style="display: none; color: #d32f2f; margin-bottom: 15px; font-size: 13px; font-weight: 500;">
            <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
            <span></span>
          </div>

          <button type="submit" style="
            padding: 10px 45px;
            background-color: #2600ff;
            color: white;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease-in-out;
            width: 100%;
          " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 5px 15px rgba(0, 0, 0, 0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">Verify Code</button>
        </form>

        <p style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
          Didn't receive the code? 
          <a href="#" onclick="resendVerificationCode(); return false;" style="color: #667eea; font-weight: 600; text-decoration: none;">Resend</a>
        </p>

        <button type="button" onclick="backToRegistration()" style="
          background: transparent;
          color: #667eea;
          margin-top: 15px;
          font-weight: 500;
          border: none;
          cursor: pointer;
          text-decoration: underline;
          width: 100%;
        ">Back to Registration</button>
      </div>

      <!-- Step 3: Success -->
      <div id="emailVerifyStep3" style="display: none; text-align: center;">
        <div style="
          width: 100px;
          height: 100px;
          background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 20px auto;
          animation: scaleIn 0.5s ease-out;
        ">
          <i class="fas fa-check" style="font-size: 50px; color: white;"></i>
        </div>
        <h1 style="color: #333; margin-bottom: 10px; font-size: 24px;">Email Verified!</h1>
        <p style="color: #666; margin-bottom: 30px; font-size: 0.95rem;">Your account has been created successfully.<br>Redirecting to dashboard...</p>
        
        <div style="width: 60px; height: 60px; border: 4px solid #e2e8f0; border-top-color: #667eea; border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
      </div>

    </div>
  </div>
</div>

<style>
@keyframes scaleIn {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes modalSlideIn {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Mobile responsive styles */
@media (max-width: 768px) {
  #emailVerifyModal {
    align-items: center !important;
    justify-content: center !important;
  }
  
  #emailVerifyModal > div {
    margin: 0 15px;
  }
  
  #emailVerifyModal img[alt="batangas-logo"] {
    width: 120px !important;
    top: -60px !important;
  }
  
  #emailVerifyModal > div > div {
    max-width: 100% !important;
    padding: 50px 25px 30px !important;
    border-radius: 40px !important;
    max-height: 90vh !important;
  }
  
  #emailVerifyModal h1 {
    font-size: 20px !important;
  }
  
  #emailVerifyModal p {
    font-size: 0.85rem !important;
  }
  
  #emailVerifyModal > div > button {
    width: 35px !important;
    height: 35px !important;
    top: -40px !important;
    font-size: 18px !important;
  }
  
  #emailVerifyModal .login-modal-overlay {
    padding: 10px !important;
  }
  
  #emailVerifyStep1 img,
  #emailVerifyStep2 img {
    width: 32px !important;
  }
  
  #emailVerifyCode {
    font-size: 20px !important;
    letter-spacing: 6px !important;
    padding: 10px 12px !important;
  }
}

@media (max-width: 480px) {
  #emailVerifyModal img[alt="batangas-logo"] {
    width: 100px !important;
    top: -50px !important;
  }
  
  #emailVerifyModal > div > div {
    padding: 40px 20px 25px !important;
    border-radius: 30px !important;
  }
  
  #emailVerifyModal h1 {
    font-size: 18px !important;
    margin-bottom: 8px !important;
  }
  
  #emailVerifyModal p {
    font-size: 0.8rem !important;
  }
  
  #emailVerifyStep1 img,
  #emailVerifyStep2 img {
    width: 28px !important;
    gap: 10px !important;
  }
  
  #emailVerifyModal button[type="submit"],
  #emailVerifyModal button[onclick*="send"] {
    padding: 10px 30px !important;
    font-size: 11px !important;
  }
  
  #emailVerifyCode {
    font-size: 18px !important;
    letter-spacing: 4px !important;
  }
  
  #emailVerifyStep3 > div {
    width: 80px !important;
    height: 80px !important;
  }
  
  #emailVerifyStep3 > div i {
    font-size: 40px !important;
  }
}
</style>
