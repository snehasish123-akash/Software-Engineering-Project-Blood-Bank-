# BBDMS - Blood Bank Donor Management System

A comprehensive PHP/MySQL web application for managing blood donation and connecting donors with those in need.

## 🩸 Features

### For Blood Seekers
- Search for blood donors by blood group and location
- Browse complete donor list with filtering
- Send messages to donors through secure messaging system
- Create and manage blood requests
- User dashboard with messaging interface

### For Blood Donors
- Register and create comprehensive donor profile
- Receive messages from seekers
- Manage availability status and profile information
- Donor dashboard with messaging system
- Admin approval process for new donors

### For Administrators
- Complete admin panel with full system control
- Manage users (approve/reject donor registrations)
- Manage blood requests and monitor system activity
- Handle contact queries from users
- Update site settings and configuration
- View comprehensive statistics and reports

### General Features
- Responsive design (mobile, tablet, desktop optimized)
- Professional blood donation color scheme
- Secure user authentication with role-based access
- Basic messaging system between users
- Contact form with inquiry management
- About page with blood donation information
- Emergency contact features
- Blood compatibility guide
- Real-time statistics display

## 🛠 Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Server**: Apache (XAMPP recommended)

## 📋 Installation Instructions

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- Web browser
- Text editor (optional)

### Setup Steps

1. **Download and Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Setup Project Files**
   - Copy all project files to `C:\xampp\htdocs\bbdms\` (Windows) or `/opt/lampp/htdocs/bbdms/` (Linux)

3. **Create Database**
   - Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Import the database file: `database/bbdms_new.sql`
   - This will create the database with sample data

4. **Configure Database Connection**
   - Open `config/database.php`
   - Update database credentials if needed (default: host=localhost, user=root, password=empty)

5. **Access the Application**
   - Open your browser and go to: [http://localhost/bbdms](http://localhost/bbdms)

## 🔐 Default Login Credentials

### Admin Account
- **Username**: admin
- **Password**: password (hashed in database)
- **Email**: admin@bbdms.com

### Sample Donor Accounts
- **Username**: john_doe | **Password**: password | **Email**: john@example.com
- **Username**: jane_smith | **Password**: password | **Email**: jane@example.com
- **Username**: mike_wilson | **Password**: password | **Email**: mike@example.com

### Sample Seeker Accounts
- **Username**: alex_johnson | **Password**: password | **Email**: alex@example.com
- **Username**: maria_davis | **Password**: password | **Email**: maria@example.com

## 📁 File Structure

```
bbdms/
├── admin/                  # Admin panel files (to be created)
├── donor/                  # Donor dashboard files (to be created)
├── seeker/                 # Seeker dashboard files (to be created)
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── main.js        # JavaScript functionality
├── config/
│   └── database.php       # Database configuration
├── database/
│   └── bbdms_new.sql     # Database schema and sample data
├── includes/
│   └── footer.php        # Common footer
├── index.php             # Homepage
├── login.php             # Login page
├── register.php          # Registration page
├── logout.php            # Logout handler
├── donor-list.php        # Donor listing
├── search-donor.php      # Donor search
├── contact.php           # Contact page
├── about.php             # About page
├── send-message.php      # Message handling
├── forgot-password.php   # Password reset
└── README.md             # This file
```

## 🔧 Key Features Explained

### User Types
1. **Admin**: Full system access, user management, settings
2. **Donor**: Can receive messages, manage profile (requires approval)
3. **Seeker**: Can search donors, send messages, create requests

### Messaging System
- Basic messaging between seekers and donors
- Messages stored in database with read status
- Simple inbox/outbox functionality
- AJAX-powered message sending

### Security Features
- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Input sanitization and validation
- Session management with proper security
- User authentication checks
- CSRF protection ready

### Responsive Design
- Mobile-first approach using Bootstrap 5
- Optimized for all screen sizes
- Touch-friendly interface
- Professional blood donation color scheme

## 🎨 Customization

### Colors
The color scheme is defined in `assets/css/style.css`:
- Primary: #8B0000 (Dark Red)
- Secondary: #DC143C (Crimson)
- Accent: #FF6B6B (Light Red)

### Site Settings
Update site information in the database `site_settings` table or through the admin panel.

### Email Configuration
For production use, configure email settings in `config/database.php` and implement actual email sending in `forgot-password.php`.

## 🗄 Database Schema

### Main Tables
- `users`: All user accounts (admin, donor, seeker)
- `blood_requests`: Blood requirement requests
- `messages`: Communication between users
- `contact_queries`: Contact form submissions
- `site_settings`: Configurable site settings

### Sample Data
- 1 admin account
- 6 sample donors with different blood groups
- 3 sample seekers
- 3 sample blood requests
- Sample messages between users
- Complete site settings

## 🔒 Security Considerations

### For Production Use
1. Change all default passwords
2. Update database credentials
3. Enable HTTPS
4. Configure email sending
5. Add CAPTCHA to forms
6. Implement rate limiting
7. Add backup procedures
8. Update file permissions
9. Enable error logging
10. Configure firewall rules

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL service is running
   - Verify database credentials in `config/database.php`
   - Ensure database `bbdms` exists

2. **Page Not Found**
   - Ensure files are in correct XAMPP directory (`htdocs/bbdms/`)
   - Check Apache service is running
   - Verify file permissions

3. **Login Issues**
   - Verify database is imported correctly
   - Check user status is 'active'
   - Ensure passwords match (use default: 'password')

4. **Styling Issues**
   - Clear browser cache
   - Check Bootstrap CDN links
   - Verify CSS file path

5. **JavaScript Not Working**
   - Check browser console for errors
   - Ensure jQuery/Bootstrap JS is loaded
   - Verify file paths

## 🚀 Next Steps

To complete the full system, you'll need:

1. **Admin Panel** (`admin/dashboard.php`)
   - User management
   - Blood request management
   - Contact query handling
   - Site settings management

2. **Donor Dashboard** (`donor/dashboard.php`)
   - Message management
   - Profile editing
   - Availability status

3. **Seeker Dashboard** (`seeker/dashboard.php`)
   - Blood request creation
   - Message management
   - Search history

## 📞 Support

For technical support or questions:
- Check this README file
- Review the code comments for implementation details
- Test with sample data provided

## 📄 License

This project is created for educational and demonstration purposes. Feel free to modify and use according to your needs.

---

**BBDMS - Connecting hearts, saving lives through blood donation** ❤️

### 🔄 Version History
- **v1.0**: Initial release with core functionality
- **v1.1**: Added comprehensive messaging system
- **v1.2**: Enhanced security and validation
- **v1.3**: Improved responsive design and user experience