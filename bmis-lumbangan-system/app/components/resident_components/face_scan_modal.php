<!-- Face Scan Modal for Signup Liveness Verification -->
<div id="faceScanModal" class="login-modal-overlay" style="display: none; z-index: 10005;">
  <div style="position: relative; animation: modalSlideIn 0.5s ease;">

    <!-- Close Button -->
    <button onclick="closeFaceScanModal()" style="
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
      z-index: 10010;
    " onmouseover="this.style.background='#d32f2f'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='#667eea';">×</button>

    <!-- Logo -->
    <img src="https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png" alt="batangas-logo" style="
      position: absolute;
      top: -80px;
      left: 50%;
      transform: translateX(-50%);
      width: 200px;
      filter: drop-shadow(0 0 20px rgba(0,0,0,0.3));
      z-index: 10009;
    ">

    <!-- Main Container -->
    <div style="
      background-color: #fff;
      border-radius: 30px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
      max-width: 540px;
      width: 100%;
      padding: 60px 40px 40px;
      overflow-y: auto;
      max-height: 90vh;
    ">

      <!-- Step Indicators -->
      <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 24px;" id="faceScanStepIndicators">
        <div class="face-step-dot face-step-dot-active" id="faceDot1" title="Position Face"></div>
        <div class="face-step-dot" id="faceDot2" title="Blink Twice"></div>
        <div class="face-step-dot" id="faceDot3" title="Turn Head Left"></div>
        <div class="face-step-dot" id="faceDot4" title="Turn Head Right"></div>
        <div class="face-step-dot" id="faceDot5" title="Complete"></div>
      </div>

      <!-- ======================== -->
      <!-- STEP 0: Loading Models   -->
      <!-- ======================== -->
      <div id="faceScanStep0" style="text-align: center; display: block;">
        <div style="width: 80px; height: 80px; margin: 20px auto; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
          <i class="fas fa-brain" style="font-size: 36px; color: white;"></i>
        </div>
        <h2 style="color: #333; margin-bottom: 10px; font-size: 22px;">Face Verification</h2>
        <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">Loading AI models, please wait...</p>
        <div id="faceModelLoadProgress" style="background: #e2e8f0; border-radius: 10px; height: 8px; overflow: hidden; margin: 0 auto 15px; max-width: 300px;">
          <div id="faceModelLoadBar" style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: 0%; transition: width 0.5s ease; border-radius: 10px;"></div>
        </div>
        <p id="faceModelLoadStatus" style="color: #888; font-size: 0.8rem;">Initializing...</p>
        <div id="faceModelLoadError" style="display: none; color: #d32f2f; margin-top: 15px; font-size: 13px; font-weight: 500; background: #fff5f5; padding: 12px; border-radius: 8px; border: 1px solid #fed7d7;">
          <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
          <span></span>
        </div>
        <button onclick="closeFaceScanModal()" style="
          background: transparent; color: #667eea; margin-top: 20px;
          font-weight: 500; border: none; cursor: pointer; text-decoration: underline; width: 100%;
        ">Cancel</button>
      </div>

      <!-- ============================== -->
      <!-- STEP 1: Position Face          -->
      <!-- ============================== -->
      <div id="faceScanStep1" style="display: none; text-align: center;">
        <h2 style="color: #333; margin-bottom: 6px; font-size: 22px;"><i class="fas fa-camera" style="color: #667eea; margin-right: 8px;"></i>Position Your Face</h2>
        <p style="color: #666; font-size: 0.88rem; margin-bottom: 16px;">Center your face in the oval. Make sure you're in a well-lit area.</p>

        <!-- Webcam Container -->
        <div id="faceScanVideoContainer" style="position: relative; width: 280px; height: 280px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; box-shadow: 0 0 0 4px #667eea, 0 8px 30px rgba(102,126,234,0.3);">
          <video id="faceScanVideo" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
          <canvas id="faceScanOverlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: scaleX(-1);"></canvas>
          <!-- Oval guide overlay -->
          <div style="position: absolute; inset: 0; pointer-events: none;">
            <svg width="100%" height="100%" viewBox="0 0 280 280" style="position: absolute; inset: 0;">
              <ellipse cx="140" cy="140" rx="100" ry="120" fill="none" stroke="rgba(102,126,234,0.6)" stroke-width="2" stroke-dasharray="6,4"/>
            </svg>
          </div>
        </div>

        <!-- Face Detection Indicator -->
        <div id="faceDetectionIndicator" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: 20px; background: #f8f9ff; border: 1px solid #e0e7ff; margin-bottom: 16px; font-size: 0.88rem; color: #555;">
          <div id="faceDetectDot" style="width: 10px; height: 10px; border-radius: 50%; background: #ccc; transition: background 0.3s;"></div>
          <span id="faceDetectLabel">Searching for face...</span>
        </div>

        <div id="faceScanStep1Error" style="display: none; color: #d32f2f; margin-bottom: 12px; font-size: 13px; font-weight: 500;">
          <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
          <span></span>
        </div>

        <button id="faceScanStartBtn" onclick="startLivenessCheck()" disabled style="
          padding: 12px 45px; background-color: #667eea; color: white; border: none;
          border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px;
          letter-spacing: 0.5px; text-transform: uppercase; transition: all 0.3s;
          width: 100%; opacity: 0.5;
        " onmouseover="if(!this.disabled){this.style.transform='scale(1.03)';}" onmouseout="this.style.transform='scale(1)';">
          Start Verification
        </button>
        <button onclick="closeFaceScanModal()" style="
          background: transparent; color: #667eea; margin-top: 12px;
          font-weight: 500; border: none; cursor: pointer; text-decoration: underline; width: 100%;
        ">Cancel</button>
      </div>

      <!-- ============================== -->
      <!-- STEP 2: Blink Detection        -->
      <!-- ============================== -->
      <div id="faceScanStep2" style="display: none; text-align: center;">
        <h2 style="color: #333; margin-bottom: 6px; font-size: 22px;"><i class="fas fa-eye" style="color: #667eea; margin-right: 8px;"></i>Blink Twice</h2>
        <p style="color: #666; font-size: 0.88rem; margin-bottom: 16px;">Slowly blink both eyes <strong>twice</strong> to prove you're live.</p>

        <div id="faceScanVideoContainer2" style="position: relative; width: 280px; height: 280px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden; box-shadow: 0 0 0 4px #667eea, 0 8px 30px rgba(102,126,234,0.3);">
          <!-- same video element, re-parented via JS -->
        </div>

        <!-- Blink Progress -->
        <div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 16px;">
          <div id="blinkCircle1" style="width: 48px; height: 48px; border-radius: 50%; border: 3px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: all 0.3s;">👁</div>
          <div id="blinkCircle2" style="width: 48px; height: 48px; border-radius: 50%; border: 3px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: all 0.3s;">👁</div>
        </div>

        <div id="blinkStatus" style="padding: 10px 20px; border-radius: 20px; background: #f8f9ff; border: 1px solid #e0e7ff; margin-bottom: 16px; font-size: 0.88rem; color: #555;">
          Waiting for blink...
        </div>

        <!-- Timeout bar -->
        <div style="background: #e2e8f0; border-radius: 10px; height: 6px; overflow: hidden; margin-bottom: 16px;">
          <div id="livenessTimeoutBar" style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: 100%; transition: width 1s linear;"></div>
        </div>

        <div id="faceScanStep2Error" style="display: none; color: #d32f2f; margin-bottom: 12px; font-size: 13px;">
          <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i><span></span>
        </div>
        <button onclick="retryLiveness()" style="
          background: transparent; color: #667eea; margin-top: 8px;
          font-weight: 500; border: none; cursor: pointer; text-decoration: underline; width: 100%;
        ">Retry</button>
      </div>

      <!-- ============================== -->
      <!-- STEP 3: Head Turn Left         -->
      <!-- ============================== -->
      <div id="faceScanStep3" style="display: none; text-align: center;">
        <h2 style="color: #333; margin-bottom: 6px; font-size: 22px;"><i class="fas fa-arrow-left" style="color: #667eea; margin-right: 8px;"></i>Turn Head Left</h2>
        <p style="color: #666; font-size: 0.88rem; margin-bottom: 16px;">Slowly turn your head to the <strong>left</strong>.</p>

        <div id="faceScanVideoContainer3" style="position: relative; width: 280px; height: 280px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden; box-shadow: 0 0 0 4px #667eea, 0 8px 30px rgba(102,126,234,0.3);"></div>

        <div id="headTurnLeftStatus" style="padding: 10px 20px; border-radius: 20px; background: #f8f9ff; border: 1px solid #e0e7ff; margin-bottom: 16px; font-size: 0.88rem; color: #555;">
          Waiting for head turn...
        </div>

        <div style="background: #e2e8f0; border-radius: 10px; height: 6px; overflow: hidden; margin-bottom: 16px;">
          <div id="livenessTimeoutBar3" style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: 100%; transition: width 1s linear;"></div>
        </div>
        <div id="faceScanStep3Error" style="display: none; color: #d32f2f; margin-bottom: 12px; font-size: 13px;">
          <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i><span></span>
        </div>
        <button onclick="retryLiveness()" style="
          background: transparent; color: #667eea; margin-top: 8px;
          font-weight: 500; border: none; cursor: pointer; text-decoration: underline; width: 100%;
        ">Retry</button>
      </div>

      <!-- ============================== -->
      <!-- STEP 4: Head Turn Right        -->
      <!-- ============================== -->
      <div id="faceScanStep4" style="display: none; text-align: center;">
        <h2 style="color: #333; margin-bottom: 6px; font-size: 22px;"><i class="fas fa-arrow-right" style="color: #667eea; margin-right: 8px;"></i>Turn Head Right</h2>
        <p style="color: #666; font-size: 0.88rem; margin-bottom: 16px;">Now slowly turn your head to the <strong>right</strong>.</p>

        <div id="faceScanVideoContainer4" style="position: relative; width: 280px; height: 280px; margin: 0 auto 16px; border-radius: 50%; overflow: hidden; box-shadow: 0 0 0 4px #667eea, 0 8px 30px rgba(102,126,234,0.3);"></div>

        <div id="headTurnRightStatus" style="padding: 10px 20px; border-radius: 20px; background: #f8f9ff; border: 1px solid #e0e7ff; margin-bottom: 16px; font-size: 0.88rem; color: #555;">
          Waiting for head turn...
        </div>

        <div style="background: #e2e8f0; border-radius: 10px; height: 6px; overflow: hidden; margin-bottom: 16px;">
          <div id="livenessTimeoutBar4" style="background: linear-gradient(90deg, #667eea, #764ba2); height: 100%; width: 100%; transition: width 1s linear;"></div>
        </div>
        <div id="faceScanStep4Error" style="display: none; color: #d32f2f; margin-bottom: 12px; font-size: 13px;">
          <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i><span></span>
        </div>
        <button onclick="retryLiveness()" style="
          background: transparent; color: #667eea; margin-top: 8px;
          font-weight: 500; border: none; cursor: pointer; text-decoration: underline; width: 100%;
        ">Retry</button>
      </div>

      <!-- ============================== -->
      <!-- STEP 5: Processing / Success   -->
      <!-- ============================== -->
      <div id="faceScanStep5" style="display: none; text-align: center;">
        <div id="faceScanProcessing">
          <div style="width: 80px; height: 80px; border: 4px solid #e2e8f0; border-top-color: #667eea; border-radius: 50%; margin: 20px auto; animation: spin 1s linear infinite;"></div>
          <h2 style="color: #333; margin-bottom: 10px;">Processing Face...</h2>
          <p style="color: #666; font-size: 0.9rem;">Checking for duplicate accounts, please wait.</p>
        </div>
        <div id="faceScanSuccess" style="display: none;">
          <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #48bb78, #38a169); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 20px auto; animation: scaleIn 0.5s ease-out;">
            <i class="fas fa-check" style="font-size: 50px; color: white;"></i>
          </div>
          <h2 style="color: #333; margin-bottom: 10px;">Face Verified!</h2>
          <p style="color: #666; font-size: 0.9rem;">Proceeding to email verification...</p>
        </div>
        <div id="faceScanDuplicate" style="display: none;">
          <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #fc8181, #e53e3e); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 20px auto;">
            <i class="fas fa-user-times" style="font-size: 46px; color: white;"></i>
          </div>
          <h2 style="color: #c53030; margin-bottom: 10px;">Duplicate Account Detected</h2>
          <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">A matching face was found. Only one account per person is allowed.</p>
          <button onclick="closeFaceScanModal()" style="
            padding: 10px 45px; background-color: #e53e3e; color: white; border: none;
            border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 12px;
            letter-spacing: 0.5px; text-transform: uppercase; width: 100%;
          ">Close</button>
        </div>
        <div id="faceScanError" style="display: none;">
          <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fc8181, #e53e3e); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 20px auto;">
            <i class="fas fa-exclamation-triangle" style="font-size: 36px; color: white;"></i>
          </div>
          <h2 style="color: #c53030; margin-bottom: 10px;">Verification Failed</h2>
          <p id="faceScanErrorMsg" style="color: #666; font-size: 0.9rem; margin-bottom: 20px;"></p>
          <button onclick="retryLiveness()" style="
            padding: 10px 45px; background-color: #667eea; color: white; border: none;
            border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 12px;
            letter-spacing: 0.5px; text-transform: uppercase; width: 100%;
          ">Try Again</button>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
.face-step-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #e2e8f0;
  transition: all 0.3s ease;
}
.face-step-dot-active {
  background: #667eea;
  transform: scale(1.3);
}
.face-step-dot-done {
  background: #48bb78;
}

@media (max-width: 768px) {
  #faceScanModal > div > div {
    padding: 50px 20px 30px !important;
    border-radius: 20px !important;
    max-height: 95vh !important;
    max-width: 100% !important;
  }
  #faceScanVideoContainer,
  #faceScanVideoContainer2,
  #faceScanVideoContainer3,
  #faceScanVideoContainer4 {
    width: 220px !important;
    height: 220px !important;
  }
  #faceScanModal img[alt="batangas-logo"] {
    width: 140px !important;
    top: -60px !important;
  }
}

@media (max-width: 480px) {
  #faceScanVideoContainer,
  #faceScanVideoContainer2,
  #faceScanVideoContainer3,
  #faceScanVideoContainer4 {
    width: 180px !important;
    height: 180px !important;
  }
}
</style>
