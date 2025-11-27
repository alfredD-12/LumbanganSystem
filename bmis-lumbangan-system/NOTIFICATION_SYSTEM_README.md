# Barangay Lumbangan Notification System

## Overview
A real-time notification system for the Barangay Lumbangan BMIS that automatically notifies users about:
- **New Announcements** - Notify all users when announcements are published
- **Complaints/Incidents** - Notify officials when new complaints are filed or status changes
- **Document Requests** - Notify officials on new requests and residents on status updates

## Features
✅ **Automatic Notifications** - MySQL triggers create notifications automatically  
✅ **Role-Based Filtering** - Different notifications for residents (user) and officials (admin)  
✅ **Real-Time Polling** - Auto-refresh every 30 seconds  
✅ **Unread Badge** - Visual indicator for new notifications  
✅ **Mark as Read** - Individual or bulk mark all as read  
✅ **Session-Based** - Works with existing session authentication  
✅ **No Controller Modifications** - Completely independent system  

## Installation Instructions

### Step 1: Import Database Schema
Run the SQL file to create the notifications table and triggers:

```bash
# Using MySQL command line
mysql -u your_username -p lumbangansystem < notifications.sql

# Or import via phpMyAdmin:
# 1. Open phpMyAdmin
# 2. Select 'lumbangansystem' database
# 3. Click 'Import' tab
# 4. Choose 'notifications.sql' file
# 5. Click 'Go'
```

### Step 2: Verify Installation
Check that everything is properly installed:

```sql
-- Verify notifications table exists
SHOW TABLES LIKE 'notifications';

-- Verify triggers were created
SHOW TRIGGERS WHERE `Trigger` LIKE 'notify_%';

-- You should see 5 triggers:
-- 1. notify_new_announcement
-- 2. notify_new_complaint
-- 3. notify_complaint_update
-- 4. notify_new_document_request
-- 5. notify_document_status
```

### Step 3: Files Already Integrated
The following files have been created and integrated into your headers/footers:

**Database:**
- ✅ `notifications.sql` - Database schema and triggers

**Backend:**
- ✅ `app/api/notifications.php` - REST API endpoint

**Frontend:**
- ✅ `app/assets/js/notifications.js` - Client-side JavaScript
- ✅ `app/assets/css/notifications.css` - Notification styles

**Integration:**
- ✅ Admin header modified (`app/components/admin_components/header-admin.php`)
- ✅ Admin footer modified (`app/components/admin_components/footer-admin.php`)
- ✅ Resident header modified (`app/components/resident_components/header-resident.php`)
- ✅ Resident footer modified (`app/components/resident_components/footer-resident.php`)

## How It Works

### 1. Database Triggers
When data is inserted or updated in these tables, triggers automatically create notifications:

```sql
-- New announcement published
INSERT INTO announcements (...) → Creates notification for 'all' users

-- New complaint filed
INSERT INTO incidents (...) → Creates notification for 'official' users

-- Complaint status changed
UPDATE incidents SET status_id = X → Creates notification for 'official' users

-- New document request
INSERT INTO document_requests (...) → Creates notification for 'official' users

-- Document status changed
UPDATE document_requests SET status = 'Approved' → Creates notifications for both 'user' and 'official'
```

### 2. Notification Flow
```
User Action → Database Trigger → Notification Created → API Fetches → UI Updates
```

### 3. User Types
- **`user`** - Residents (logged in via resident portal)
- **`official`** - Admins and officials (logged in via admin dashboard)
- **`all`** - Both residents and officials receive the notification

## API Endpoints

### Fetch Notifications
```javascript
GET /app/api/notifications.php?action=fetch&limit=20&offset=0&unread_only=true

Response:
{
  "success": true,
  "notifications": [...],
  "unread_count": 5,
  "user_type": "user"
}
```

### Mark as Read
```javascript
POST /app/api/notifications.php
Body: action=mark_read&id=123

Response:
{
  "success": true,
  "message": "Notification marked as read"
}
```

### Mark All as Read
```javascript
POST /app/api/notifications.php
Body: action=mark_all_read

Response:
{
  "success": true,
  "message": "Marked 5 notification(s) as read"
}
```

### Get Unread Count
```javascript
GET /app/api/notifications.php?action=count

Response:
{
  "success": true,
  "unread_count": 3
}
```

## Testing

### Test 1: New Announcement Notification
```sql
-- Create a new published announcement
INSERT INTO announcements (title, message, status, type, author)
VALUES ('Test Announcement', 'This is a test message', 'published', 'general', 'Admin');

-- Check if notification was created
SELECT * FROM notifications WHERE notification_type = 'announcement' ORDER BY created_at DESC LIMIT 1;
```

### Test 2: New Complaint Notification
```sql
-- Create a new complaint
INSERT INTO incidents (incident_title, blotter_type, case_type_id, complainant_name, complainant_type, 
                       complainant_gender, date_of_incident, time_of_incident, location, narrative, status_id)
VALUES ('Test Complaint', 'Complaint', 1, 'Test User', 'Resident', 'male', 
        CURDATE(), '10:00:00', 'Test Location', 'Test narrative', 1);

-- Check if notification was created
SELECT * FROM notifications WHERE notification_type = 'complaint' ORDER BY created_at DESC LIMIT 1;
```

### Test 3: Document Request Notification
```sql
-- Create a new document request (replace user_id and document_type_id with valid values)
INSERT INTO document_requests (user_id, document_type_id, purpose, status)
VALUES (1, 1, 'Test Purpose', 'Pending');

-- Check if notification was created
SELECT * FROM notifications WHERE notification_type = 'document_request' ORDER BY created_at DESC LIMIT 1;
```

### Test 4: Frontend Polling
1. Open browser developer console (F12)
2. Look for console messages: "Notification system initialized"
3. Check Network tab for API calls to `notifications.php` every 30 seconds
4. Create a new announcement and watch the badge update

## Customization

### Change Polling Interval
Edit `app/assets/js/notifications.js`:
```javascript
const NOTIFICATION_CONFIG = {
    pollInterval: 60000, // Change to 60 seconds (60000ms)
    // ... other config
};
```

### Change Maximum Displayed Notifications
```javascript
const NOTIFICATION_CONFIG = {
    maxNotifications: 20, // Show up to 20 notifications
    // ... other config
};
```

### Customize Notification Colors
Edit `app/assets/css/notifications.css`:
```css
.notification-icon.announcement {
    background: linear-gradient(135deg, #your-color-1, #your-color-2);
}
```

## Troubleshooting

### Notifications Not Showing
1. **Check if user is logged in**: Notifications only work for authenticated users
2. **Verify database table**: `SELECT * FROM notifications LIMIT 10;`
3. **Check browser console** for JavaScript errors
4. **Verify session**: Check that `$_SESSION['user_type']` is set to 'user' or 'official'

### Badge Not Updating
1. **Clear browser cache**
2. **Check Network tab** - Ensure API calls are succeeding
3. **Verify API endpoint** - Visit `/app/api/notifications.php?action=count` directly

### Triggers Not Firing
```sql
-- Check if triggers exist
SHOW TRIGGERS FROM lumbangansystem;

-- If missing, re-import notifications.sql
```

### API Returns 401 Unauthorized
- User is not logged in
- Session has expired
- Check `session_helper.php` is properly included

## Database Schema

### Notifications Table
```sql
CREATE TABLE `notifications` (
  `id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT(20) UNSIGNED NULL,           -- NULL = notify all users
  `user_type` ENUM('user','official','all'),     -- Target audience
  `notification_type` VARCHAR(50) NOT NULL,      -- announcement, complaint, document_request
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(500) NULL,                      -- Where to navigate when clicked
  `reference_id` INT(11) NULL,                   -- ID of related record
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_type` (`user_id`, `user_type`),
  INDEX `idx_created` (`created_at` DESC),
  INDEX `idx_is_read` (`is_read`)
);
```

## Security Considerations
✅ SQL injection protected (PDO prepared statements)  
✅ Session-based authentication  
✅ Role-based access control  
✅ XSS protection (HTML escaping in JavaScript)  
✅ No direct user input in triggers  

## Future Enhancements (Optional)
- 🔮 WebSocket support for real-time push notifications
- 🔮 Email notifications for critical alerts
- 🔮 SMS notifications via API
- 🔮 Notification preferences/settings
- 🔮 Notification categories filter
- 🔮 Archive old notifications (auto-cleanup)

## Support
For issues or questions:
1. Check the troubleshooting section above
2. Verify all files are properly uploaded
3. Check browser console and PHP error logs
4. Ensure MySQL triggers are active

## Version
**Version:** 1.0  
**Date:** November 26, 2025  
**Compatible with:** Lumbangan BMIS (lumbangan.sql schema)  
**Database:** MySQL/MariaDB 5.7+  
**PHP:** 7.4+  
**Browser:** Modern browsers with JavaScript enabled
