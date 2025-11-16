/* ============================================
   ADMIN HEADER - Combined Topbar & Sidebar JS
   Handles dropdowns, modals, sidebar toggle, dark mode
   ============================================ */

// Initialize all components
document.addEventListener("DOMContentLoaded", function () {
  // Sidebar Toggle Setup
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector(".sidebar-toggle");
  const sidebarMenu = document.querySelectorAll(".sidebar-menu a");
  const body = document.body;

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function (e) {
      // Toggle collapsed state on sidebar
      sidebar.classList.toggle("collapsed");

      // Sync body class for padding adjustment
      body.classList.toggle(
        "sidebar-collapsed",
        sidebar.classList.contains("collapsed")
      );

      // Create ripple effect
      const ripple = document.createElement("span");
      ripple.style.position = "absolute";
      ripple.style.width = "100px";
      ripple.style.height = "100px";
      ripple.style.borderRadius = "50%";
      ripple.style.background = "rgba(255, 255, 255, 0.5)";
      ripple.style.transform = "scale(0)";
      ripple.style.animation = "ripple-effect 0.6s ease-out";
      ripple.style.pointerEvents = "none";
      ripple.style.left = "50%";
      ripple.style.top = "50%";
      ripple.style.marginLeft = "-50px";
      ripple.style.marginTop = "-50px";

      this.style.position = "absolute";
      this.appendChild(ripple);

      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  }

  // Add tooltips for menu items when collapsed
  if (sidebarMenu) {
    sidebarMenu.forEach((item) => {
      const text = item.querySelector("span");
      if (text) {
        item.setAttribute("data-tooltip", text.textContent.trim());
      }
    });
  }

  // Mobile Menu Toggle
  const mobileTrigger = document.querySelector(".mobile-menu-toggle");

  if (mobileTrigger) {
    mobileTrigger.addEventListener("click", function () {
      sidebar.classList.toggle("show");
    });
  }

  // Close sidebar when clicking menu item on mobile
  const menuItems = document.querySelectorAll(".sidebar-menu a");
  menuItems.forEach((item) => {
    item.addEventListener("click", function () {
      if (window.innerWidth <= 991) {
        sidebar.classList.remove("show");
      }
    });
  });

  // Dark Mode Setup
  const themeToggle = document.querySelector(".theme-toggle");
  const icon = themeToggle?.querySelector("i");
  const text = themeToggle?.querySelector("span");

  // Check for saved theme preference or default to 'light' mode
  const currentTheme = localStorage.getItem("theme") || "light";

  // Apply the saved theme on page load
  if (currentTheme === "dark") {
    body.classList.add("dark-mode");
    if (icon) icon.classList.replace("fa-moon", "fa-sun");
    if (text) text.textContent = "Light Mode";
  }

  // Toggle theme on button click
  if (themeToggle) {
    themeToggle.addEventListener("click", function (e) {
      e.preventDefault();

      // Toggle dark mode class
      body.classList.toggle("dark-mode");

      // Update icon and text
      const isDark = body.classList.contains("dark-mode");

      if (isDark) {
        if (icon) icon.classList.replace("fa-moon", "fa-sun");
        if (text) text.textContent = "Light Mode";
        localStorage.setItem("theme", "dark");
      } else {
        if (icon) icon.classList.replace("fa-sun", "fa-moon");
        if (text) text.textContent = "Dark Mode";
        localStorage.setItem("theme", "light");
      }

      // Add ripple effect
      const ripple = document.createElement("span");
      ripple.style.position = "absolute";
      ripple.style.width = "80px";
      ripple.style.height = "80px";
      ripple.style.borderRadius = "50%";
      ripple.style.background = isDark
        ? "rgba(59, 130, 246, 0.3)"
        : "rgba(30, 58, 95, 0.3)";
      ripple.style.transform = "scale(0)";
      ripple.style.animation = "ripple-effect 0.6s ease-out";
      ripple.style.pointerEvents = "none";
      ripple.style.left = "50%";
      ripple.style.top = "50%";
      ripple.style.marginLeft = "-40px";
      ripple.style.marginTop = "-40px";

      this.style.position = "relative";
      this.style.overflow = "hidden";
      this.appendChild(ripple);

      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  }
});

// Window Resize Handler - Fix Layout Switching
let resizeTimer;
window.addEventListener("resize", function () {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(function () {
    const sidebar = document.querySelector(".sidebar");
    const body = document.body;
    const isMobile = window.innerWidth <= 991;

    if (sidebar) {
      // On desktop view
      if (!isMobile) {
        // Remove mobile class if it exists
        sidebar.classList.remove("show");

        // Restore based on saved collapsed state
        const wasCollapsed =
          localStorage.getItem("sidebarCollapsed") === "true";
        sidebar.classList.toggle("collapsed", wasCollapsed);
        body.classList.toggle("sidebar-collapsed", wasCollapsed);

        // Force reflow if not collapsed
        if (!wasCollapsed) {
          sidebar.style.width = "var(--sidebar-width)";
        }
      }
      // On mobile view
      else {
        // Always ensure full width on mobile (never collapsed)
        sidebar.classList.remove("collapsed");
        sidebar.classList.remove("show"); // Start hidden
        body.classList.remove("sidebar-collapsed");
      }
    }
  }, 250);
});

// Save sidebar collapsed state and initial hide
window.addEventListener("load", function () {
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector(".sidebar-toggle");
  const body = document.body;

  if (sidebar && toggleBtn) {
    // Set default to collapsed (hidden) if no saved state
    const savedState = localStorage.getItem("sidebarCollapsed") ?? "true";

    // Apply on desktop only
    if (window.innerWidth > 991) {
      if (savedState === "true") {
        sidebar.classList.add("collapsed");
        body.classList.add("sidebar-collapsed");
      }
    }

    // Save state when toggling
    toggleBtn.addEventListener("click", function () {
      const isCollapsed = sidebar.classList.contains("collapsed");
      localStorage.setItem("sidebarCollapsed", isCollapsed);
    });
  }
});

// Notification toggle function - More robust
function toggleNotifications() {
  const modalElement = document.getElementById("notificationsModal");
  if (!modalElement) {
    console.error("Notifications modal not found");
    return;
  }

  try {
    const notificationsModal = new bootstrap.Modal(modalElement);
    notificationsModal.show();
  } catch (e) {
    console.error("Error opening notifications modal:", e);
  }
}

// Messages toggle function - More robust
function toggleMessages() {
  const modalElement = document.getElementById("messagesModal");
  if (!modalElement) {
    console.error("Messages modal not found");
    return;
  }

  try {
    const messagesModal = new bootstrap.Modal(modalElement);
    messagesModal.show();
  } catch (e) {
    console.error("Error opening messages modal:", e);
  }
}

// Export functions for external use if needed
window.toggleNotifications = toggleNotifications;
window.toggleMessages = toggleMessages;

// Ripple effect animation
const style = document.createElement("style");
style.textContent = `
    @keyframes ripple-effect {
        0% {
            transform: scale(0);
            opacity: 1;
        }
        100% {
            transform: scale(2.5);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
