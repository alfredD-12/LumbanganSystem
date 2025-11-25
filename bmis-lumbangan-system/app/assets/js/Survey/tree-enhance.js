// Enhance tree modal: inject svg defs and ensure controls overlay is outside svg
(function(){
  function ensureDefs(svg) {
    if (!svg) return;
    if (svg.querySelector('#treeDefs')) return;

    var ns = 'http://www.w3.org/2000/svg';
    var defs = document.createElementNS(ns,'defs');
    defs.setAttribute('id','treeDefs');

    var grad = document.createElementNS(ns,'linearGradient');
    grad.setAttribute('id','nodeGradient');
    grad.setAttribute('x1','0%'); grad.setAttribute('y1','0%');
    grad.setAttribute('x2','0%'); grad.setAttribute('y2','100%');
    var stop1 = document.createElementNS(ns,'stop'); stop1.setAttribute('offset','0%'); stop1.setAttribute('stop-color','#ffffff'); stop1.setAttribute('stop-opacity','1');
    var stop2 = document.createElementNS(ns,'stop'); stop2.setAttribute('offset','100%'); stop2.setAttribute('stop-color','#f8fafc'); stop2.setAttribute('stop-opacity','1');
    grad.appendChild(stop1); grad.appendChild(stop2);

    var edgeG = document.createElementNS(ns,'linearGradient');
    edgeG.setAttribute('id','edgeGradient');
    edgeG.setAttribute('x1','0%'); edgeG.setAttribute('y1','0%');
    edgeG.setAttribute('x2','100%'); edgeG.setAttribute('y2','0%');
    var e1 = document.createElementNS(ns,'stop'); e1.setAttribute('offset','0%'); e1.setAttribute('stop-color','#94a3b8');
    var e2 = document.createElementNS(ns,'stop'); e2.setAttribute('offset','100%'); e2.setAttribute('stop-color','#cbd5e1');
    edgeG.appendChild(e1); edgeG.appendChild(e2);

    defs.appendChild(grad);
    defs.appendChild(edgeG);
    svg.insertBefore(defs, svg.firstChild);
  }

  function centerPlaceholder(svg) {
    if (!svg) return;
    var placeholder = svg.querySelector('text.placeholder') || svg.querySelector('text');
    if (!placeholder) return;
    placeholder.setAttribute('x','50%');
    placeholder.setAttribute('y','50%');
    placeholder.setAttribute('text-anchor','middle');
    placeholder.classList.add('placeholder');
  }

  function ensureControlsOverlay() {
    var body = document.querySelector('.tree-modal-body');
    var controls = body ? body.querySelector('.tree-controls') : null;
    if (!controls || !body) return;
    // ensure controls are direct child of body and absolutely positioned (overlay)
    if (controls.parentElement !== body) {
      controls.remove();
      controls.style.position = 'absolute';
      controls.style.top = '14px';
      controls.style.right = '14px';
      controls.style.zIndex = '40';
      body.appendChild(controls);
    } else {
      // ensure correct style if already in place
      controls.style.position = 'absolute';
      controls.style.top = '14px';
      controls.style.right = '14px';
      controls.style.zIndex = '40';
    }
  }

  var treeModal = document.getElementById('family-tree-modal');
  var svg = document.getElementById('tree-svg');

  if (!treeModal || !svg) return;

  var mo = new MutationObserver(function(){
    ensureDefs(svg);
    centerPlaceholder(svg);
    ensureControlsOverlay();
  });
  mo.observe(svg, { childList:true, subtree:true });

  // initial
  ensureDefs(svg);
  centerPlaceholder(svg);
  ensureControlsOverlay();

  var btn = document.getElementById('btn-view-tree');
  if (btn) {
    btn.addEventListener('click', function(){
      setTimeout(function(){
        ensureDefs(svg);
        centerPlaceholder(svg);
        ensureControlsOverlay();
      }, 80);
    });
  }

  // also run when modal opens via attribute changes (accessibility)
  var obs = new MutationObserver(function(mutations){
    mutations.forEach(function(m){
      if (m.type === 'attributes' && m.attributeName === 'aria-hidden') {
        if (treeModal.getAttribute('aria-hidden') === 'false') {
          setTimeout(function(){ ensureDefs(svg); ensureControlsOverlay(); }, 40);
        }
      }
    });
  });
  obs.observe(treeModal, { attributes: true });

  // small accessibility
  var btnClose = document.getElementById('btn-close-tree');
  if (btnClose) btnClose.setAttribute('aria-label','Close family tree');

})();