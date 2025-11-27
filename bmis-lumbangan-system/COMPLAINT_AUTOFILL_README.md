# Complaint Autofill Implementation Guide

## Overview
This implementation adds user_id foreign key to the incidents table and implements autofill functionality for complaint forms. When filing a complaint, users can search for registered residents by name and automatically populate their information.

## Database Changes

### 1. Migration SQL
Run the migration script to add the user_id foreign key:

```bash
mysql -u root -p lumbangansystem < migrate_incidents_add_user_fk.sql
```

This script will:
- Add `user_id` column to `incidents` table
- Create foreign key relationship with `users` table
- Add appropriate indexes
- Attempt to link existing complaints with registered users

### 2. Schema Updates
The `incidents` table now includes:
- `user_id` (bigint UNSIGNED, nullable) - Links to users.id
- Foreign key constraint with ON DELETE SET NULL
- Index on user_id for performance

## Files Created/Modified

### New Files:
1. **migrate_incidents_add_user_fk.sql** - Database migration script
2. **app/api/search_users.php** - API endpoint for searching registered users
3. **app/assets/js/complaint/complaint_autofill.js** - Frontend autofill functionality

### Modified Files:
1. **incidents.sql** - Updated schema with user_id column
2. **app/models/Complaint.php** - Updated to handle user_id in queries
3. **app/views/complaint/admin.php** - Included autofill script

## How It Works

### 1. Search Functionality
When a user types in the "Complainant Name" field:
- After 300ms delay, searches for matching registered users
- Shows dropdown with user suggestions
- Displays name, phone, and address for each match

### 2. Auto-fill Process
When a user is selected from the dropdown:
- Fills complainant name (full name from persons table)
- Fills contact number (from users.mobile)
- Fills address (from households.address)
- Sets gender (from persons.sex)
- Sets birthdate (from persons.birthdate)
- Sets complainant type to "Resident"
- Stores user_id in hidden field

### 3. Data Storage
When the complaint is saved:
- `user_id` is stored if a registered user was selected
- `user_id` is NULL if complainant is not in the system
- All other fields work as before (backward compatible)

## Features

### Search Capabilities:
- Searches by first name, last name, or full name
- Case-insensitive matching
- Partial name matching
- Returns up to 20 results
- Only shows active users

### User Experience:
- Real-time search with debouncing
- Visual dropdown with user information
- Success notification when auto-filled
- Manual override supported (clears user_id if fields are edited)
- Closes dropdown when clicking outside

### Security:
- Requires logged-in session
- SQL injection prevention via prepared statements
- XSS protection via HTML escaping
- CSRF protection recommended (add to form)

## API Endpoint

### Search Users
```
GET /app/api/search_users.php?q={search_term}
```

**Parameters:**
- `q` (required) - Search term for user name

**Response:**
```json
[
  {
    "user_id": 1,
    "person_id": 1,
    "full_name": "Adrian Cruz",
    "first_name": "Adrian",
    "middle_name": "",
    "last_name": "Cruz",
    "suffix": "",
    "mobile": "09170000001",
    "email": "adrian.cruz@example.com",
    "gender": "male",
    "birthdate": "1990-01-15",
    "address": "Blk 2 Lt 25, Bougainvillea Street"
  }
]
```

## Usage Instructions

### For Administrators:
1. Open the complaint form
2. Start typing in the "Complainant Name" field
3. Select a user from the dropdown (if they're registered)
4. Verify auto-filled information
5. Fill in remaining required fields
6. Submit the form

### For Developers:

#### To use autofill in other forms:
```javascript
// The script auto-initializes, but you can also manually init:
ComplaintAutofill.init();

// Or trigger a search programmatically:
ComplaintAutofill.search('search term');
```

#### To customize the search endpoint:
Edit the CONFIG object in `complaint_autofill.js`:
```javascript
const CONFIG = {
    searchDelay: 300,      // ms delay before search
    minSearchLength: 2,     // min characters to trigger search
    apiEndpoint: '../app/api/search_users.php'  // API URL
};
```

## Benefits

### 1. Data Accuracy:
- Reduces typos in complainant information
- Ensures consistent name formatting
- Links complaints to registered users automatically

### 2. User Convenience:
- Faster complaint filing
- Less manual data entry
- Immediate feedback via dropdown

### 3. Data Integrity:
- Foreign key ensures referential integrity
- Easy to query complaints by user
- Supports future features (user complaint history, notifications)

### 4. Analytics:
Query complaints by registered users:
```sql
SELECT u.*, COUNT(i.id) as complaint_count
FROM users u
LEFT JOIN incidents i ON i.user_id = u.id
GROUP BY u.id;
```

Get user's complaint history:
```sql
SELECT i.*
FROM incidents i
WHERE i.user_id = ?
ORDER BY i.created_at DESC;
```

## Backward Compatibility

The implementation is fully backward compatible:
- `user_id` is nullable - non-registered complainants work as before
- Existing complaints continue to function
- `complainant_name` field is still required and searchable
- All existing queries work without modification

## Testing Checklist

- [ ] Run migration script successfully
- [ ] Verify foreign key constraint exists
- [ ] Test search with existing user names
- [ ] Test autofill with selected user
- [ ] Test manual entry (non-registered complainant)
- [ ] Verify complaint saves with user_id
- [ ] Verify complaint saves without user_id (NULL)
- [ ] Test search with partial names
- [ ] Test dropdown closes on outside click
- [ ] Test manual field editing clears user_id
- [ ] Verify notification appears on autofill
- [ ] Test on different browsers

## Troubleshooting

### Search not working:
1. Check browser console for errors
2. Verify API endpoint URL is correct
3. Check user is logged in
4. Verify database connection in search_users.php

### Autofill not populating:
1. Check if user data exists in database
2. Verify field names match in form
3. Check browser console for JavaScript errors
4. Ensure persons/users tables have data

### Database errors:
1. Verify migration script ran successfully
2. Check foreign key constraint exists
3. Ensure users table exists
4. Verify column types match

## Future Enhancements

Potential improvements:
1. Add CSRF token to search API
2. Implement complaint history panel for users
3. Add email notifications when complaint status changes
4. Create user dashboard showing their complaints
5. Add filters for user-specific complaint view
6. Implement complaint analytics by user demographics
7. Add photo upload for complainant identification

## Support

For issues or questions:
1. Check this README
2. Review browser console for errors
3. Check server error logs
4. Verify database schema matches migration
5. Test API endpoint independently

## Version History

**v1.0.0** - November 27, 2025
- Initial implementation
- User search API
- Autofill functionality
- Database migration script
- Full backward compatibility
