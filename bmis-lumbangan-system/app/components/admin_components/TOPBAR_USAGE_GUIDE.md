# 📘 **Topbar Component Usage Guide**

## 🎯 **Quick Start**

The Admin Topbar is a **reusable header component** that includes notifications and messages modals.

---

## 📋 **Component Overview**

### **What's Included:**
- ✅ Government seals (Bagong Pilipinas, Nasugbu, Batangas)
- ✅ Page title & subtitle
- ✅ Notification bell icon with badge
- ✅ Messages/Inbox icon with badge
- ✅ Admin profile dropdown (with name, role, settings, logout)
- ✅ Dark mode ready
- ✅ Responsive design (mobile-friendly)

### **Auto-Loaded Assets:**
- `topbar-admin.css` - Styling
- `topbar-admin.js` - Functionality (dropdown, modal triggers)

---

## 🚀 **How to Use in Your Pages**

### **Step 1: Load Required Data & Components**

```php
<?php
// At the top of your PHP file
$adminName = 'Admin Secretary';      // Your admin name
$adminRole = 'Barangay Administrator'; // Your role

// Load centralized data
$dashboardData = require_once dirname(__DIR__) . '/app/config/dashboard_data.php';

// Load component functions for modals
require_once dirname(__DIR__) . '/app/components/admin_components/modal-items.php';
?>
```

### **Step 2: Include Topbar in Your HTML**

```php
<?php 
    $pageTitle = 'Your Page Title Here';
    $pageSubtitle = 'Your page description';
    // $adminName and $adminRole are already set
    
    include '../../components/admin_components/topbar-admin.php'; 
?>
```

### **Step 3: Include Modals Before `</body>`**

```php
    <!-- Your page content here -->
    
    <!-- At the very end, before </body> -->
    <?php include '../../components/admin_components/topbar-modals.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 💡 **Tips & Features**

### **Tip 1: Customizing Page Title**
```php
// Simple title
$pageTitle = 'Document Management';

// With description
$pageSubtitle = 'View and manage all document requests';

// With emoji for visual appeal
$pageTitle = '📄 Document Management';
$pageSubtitle = 'Approvals and processing status';
```

### **Tip 2: Notification & Message Badges**
- **Notification Bell**: Shows count of unread notifications
- **Message Envelope**: Shows count of unread messages
- **Auto-updates** from `$dashboardData['notifications']` and `$dashboardData['messages']`

**Click the icons** → **Modals pop up** with full list

### **Tip 3: Admin Profile Dropdown**
**Click on the avatar icon** → Opens dropdown menu with:
- Admin name & role
- "My Profile" → Opens profile modal
- "Settings" → Settings page
- "Logout" → Signs out

### **Tip 4: Dark Mode Support**
The topbar automatically adapts to dark mode! Toggle with the sidebar dark mode button:
```javascript
// Automatically handled by sidebar-admin.js
localStorage.setItem('darkMode', 'enabled');
```

---

## 📱 **Responsive Behavior**

### **Desktop (> 991px)**
- Full topbar visible
- Government seals + title fully displayed
- Action buttons and profile visible

### **Tablet (768px - 991px)**
- Topbar compresses
- Smaller seals
- Title may hide when scrolled

### **Mobile (< 576px)**
- Minimal topbar
- Icons only (seals shrink)
- Hamburger menu in sidebar
- Touch-friendly buttons (44px minimum)

---

## 🔗 **Component Files Reference**

| File | Purpose | Lines |
|------|---------|-------|
| `topbar-admin.php` | Main topbar HTML | 134 |
| `topbar-admin.js` | Dropdown & modal triggers | 67 |
| `topbar-admin.css` | Styling | ~500 |
| `topbar-modals.php` | Notification & message modals | 105 |
| `modal-items.php` | Individual item renderers | 99 |
| `dashboard_data.php` | Centralized data | 242 |

---

## ⚡ **JavaScript Functions**

### **Opening Notifications Modal**
```javascript
// Automatically called when bell icon is clicked
toggleNotifications();
```

### **Opening Messages Modal**
```javascript
// Automatically called when envelope icon is clicked
toggleMessages();
```

### **Custom Implementation**
```html
<button onclick="toggleNotifications()">
    <i class="fas fa-bell"></i> View Notifications
</button>
```

---

## 🛠️ **Customization Options**

### **Change Government Seals**
Edit `topbar-admin.php` lines 71-82:
```php
<img src="YOUR_LOGO_URL" alt="Logo Name" title="Logo Description">
```

### **Change Badge Colors**
Edit `topbar-admin.css`:
```css
.badge-count {
    background: #ef4444;  /* Change red color */
}
```

### **Change Dropdown Menu Items**
Edit `topbar-admin.php` lines 115-128:
```php
<li>
    <a class="dropdown-item" href="your-custom-link">
        <i class="fas fa-your-icon"></i> Custom Menu Item
    </a>
</li>
```

---

## ✅ **Checklist Before Using**

```
Required Files:
☐ topbar-admin.php
☐ topbar-admin.js
☐ topbar-admin.css
☐ topbar-modals.php
☐ modal-items.php
☐ dashboard_data.php

HTML/CSS Requirements:
☐ Bootstrap 5.3.0 CSS in <head>
☐ Bootstrap 5.3.0 JS before </body>
☐ Poppins font loaded
☐ Font Awesome 6.4.0 loaded

PHP Requirements:
☐ $adminName variable set
☐ $adminRole variable set
☐ $pageTitle variable set
☐ $pageSubtitle variable set
☐ $dashboardData loaded
☐ modal-items.php included

Output Check:
☐ Topbar displays correctly
☐ Notification bell shows
☐ Message envelope shows
☐ Profile dropdown works
☐ Click bell → Modal opens
☐ Click envelope → Modal opens
☐ Dark mode works
☐ Mobile responsive
```

---

## 🐛 **Troubleshooting**

### **Issue: Modals not opening**
**Solution**: Make sure `topbar-modals.php` is included **before `</body>`**
```php
<?php include '../../components/admin_components/topbar-modals.php'; ?>
</body>
```

### **Issue: Modals show blank/no items**
**Solution**: Verify `dashboard_data.php` has notifications & messages:
```php
$dashboardData = require_once 'path/to/dashboard_data.php';
// Check if it has: $dashboardData['notifications'] & $dashboardData['messages']
```

### **Issue: Dropdown not working**
**Solution**: Ensure `topbar-admin.js` is loaded (auto-loaded in component)

### **Issue: Styling looks wrong**
**Solution**: Check CSS file is loaded and no conflicting CSS overrides

### **Issue: Avatar icon shows?**
**Solution**: Ensure Font Awesome 6.4.0 is loaded:
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

---

## 🎨 **Styling Guide**

### **CSS Variables Available**
```css
--primary-blue: #1e3a5f;
--secondary-blue: #2c5282;
--accent-red: #c53030;
--success-green: #22c55e;
--warning-yellow: #ff9800;
--info-blue: #3b82f6;
```

### **Dark Mode Classes**
```css
body.dark-mode { /* Applied automatically */ }
body.dark-mode .top-bar { /* Dark topbar styles */ }
```

---

## 💬 **For Your Group Members**

If they want to use your topbar in their pages:

1. **Copy the 6 component files** to their project
2. **Follow the 3 steps above** (Load data → Include topbar → Include modals)
3. **They get notifications & messages for FREE!** ✨

No additional coding needed!

---

## 📞 **Support**

If issues occur:
- Check browser console for errors
- Verify all files are in correct paths
- Ensure Bootstrap is loaded
- Check if `$dashboardData` is properly populated

---

## 🚀 **Next Steps**

- ✅ Integrate with database for real notifications
- ✅ Add real-time WebSocket updates
- ✅ Create mobile app version
- ✅ Add notification sounds
- ✅ Implement read/unread functionality

---

**Happy coding! 🎉**
