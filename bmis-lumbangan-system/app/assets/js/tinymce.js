tinymce.init({
  selector: "#template_editor",
  height: 1000,
  plugins: "lists table code",
  toolbar:
    "undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | table | code",
  setup: function (editor) {
    // --- 1. SHORTCUT TO OPEN MENU ---
    editor.addShortcut("ctrl+alt+p", "Insert Placeholder", function () {
      const container = document.getElementById("placeholderDropdownContainer");
      const rng = editor.selection.getRng();
      let rect;

      // Calculate Position
      if (rng.getClientRects().length > 0) {
        rect = rng.getClientRects()[0];
      } else {
        rect = editor.selection.getNode().getBoundingClientRect();
      }

      const iframe = editor.getContentAreaContainer().querySelector("iframe");
      const iframeRect = iframe.getBoundingClientRect();

      const topPos = iframeRect.top + rect.bottom + window.scrollY;
      const leftPos = iframeRect.left + rect.left + window.scrollX;

      // Apply Styles
      container.style.position = "absolute";
      container.style.left = leftPos + "px";
      container.style.top = topPos + 5 + "px";
      container.style.display = "block";

      // --- CRITICAL FIX ---
      // Do NOT focus the container/select.
      // Force focus BACK to the editor so the cursor keeps blinking.
      editor.focus();
    });

    // --- 2. EDITOR INTERACTION (Hide menu when typing/clicking in editor) ---
    editor.on("keydown mousedown", function (e) {
      const container = document.getElementById("placeholderDropdownContainer");

      // If menu is open...
      if (container.style.display !== "none") {
        // If user presses Ctrl+Alt+P again, don't hide it immediately (let the shortcut handler work)
        if (e.ctrlKey && e.altKey && e.key === "p") return;

        // Otherwise (User types 'abc' or clicks somewhere else in text), hide the menu
        container.style.display = "none";
      }
    });
  },
});
// Listener to Hide Dropdown (Click Outside OR Keyboard Press)
document.addEventListener("mousedown", function (e) {
  const container = document.querySelector("#placeholderDropdownContainer");

  // If the click is NOT inside the dropdown container, hide it.
  if (container.style.display !== "none" && !container.contains(e.target)) {
    container.style.display = "none";
  }
});
