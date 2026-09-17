# SmartExam Pro - Application Context

## Project Overview

**SmartExam Pro** is a comprehensive online examination platform built with PHP and MySQL. It provides a secure, user-friendly system for conducting online exams with multiple sections, automated grading, and detailed performance tracking.

---

## Application Description

SmartExam Pro is a web-based exam management system designed to facilitate online educational assessments. It enables administrators to create and manage exams, while students can register, take exams, and receive instant feedback on their performance.

### Key Information
- **Application Name**: SmartExam Pro
- **Platform**: Web-based (PHP + MySQL)
- **Server**: XAMPP with Apache
- **Database**: MySQL
- **Architecture**: Monolithic PHP application
---

## Core Features


### 1. **User Management**
- User registration with validation
- Student profile creation (Name, DOB, Email, Phone, College, Year of Study, Branch, Skills)
- Admin account with special privileges
- Secure password hashing using PHP's `password_hash()`
- Session-based authentication

### 2. **Exam Creation & Management**
- Create exams with detailed information (title, description, date, time)
- Set exam duration (in minutes)
- Define grace period (start and end times)
- Organize exams into multiple sections
- Assign skill requirements to exams
- Add questions with four answer options (MCQ format)
- Set correct answers for auto-grading

### 3. **Question Management**
- Add questions to specific exams and sections
- Support for multiple-choice questions (MCQ)
- Store questions with question text and four options (A, B, C, D)
- Save correct answer for automatic evaluation

### 4. **Exam Execution**
- Secure exam start process
- Passkey-based exam access control
- Timer-based exam duration with countdown
- Section-wise question organization
- Real-time answer submission
- Automatic page break handling
- Grace period enforcement

### 5. **Grading & Results**
- Automatic evaluation of answers
- Score calculation
- Performance reporting
- Answer review

### 6. **Admin Dashboard**
- Centralized control panel for administrators
- Exam creation and management interface
- Question management tools
- User activity tracking
- Dashboard overview with statistics

### 7. **User Dashboard**
- View available exams
- Track upcoming exams
- View past exam results
- Access previous exam history
- Edit user profile

### 8. **Additional Features**
- PDF generation for reports (using FPDF library)
- FAQ section
- Learning resources
- Terms and conditions for online exams
- Bootstrap UI framework for responsive design
- SweetAlert2 for user-friendly notifications
- CSRF token protection for form submissions

---

## User Roles

### 1. **Administrator**
- Login with hardcoded credentials (admin@smartexam.com)
- Create and manage exams
- Add questions and set correct answers
- View exam statistics
- Manage exam settings (dates, times, grace periods)
- Add sections to exams

### 2. **Student/User**
- Register and create account
- View available exams
- Take exams within the grace period
- Submit answers and receive instant results
- View past exam records
- Manage personal profile
- Enter exams using passkeys

---

## Technology Stack

### Backend
- **Language**: PHP (server-side scripting)
- **Database**: MySQL (data persistence)
- **Database Connection**: MySQLi (procedural and object-oriented)
- **Session Management**: PHP Sessions

### Frontend
- **HTML5**: Structure and markup
- **CSS3**: Styling and custom stylesheets
- **Bootstrap 5.3.6**: Responsive UI framework
- **JavaScript**: Client-side interactions
- **SweetAlert2**: Beautiful alert dialogs

### Additional Libraries
- **FPDF**: PDF document generation (for reports)
- **Font Files**: Multiple font support for PDF generation

---

## Database Structure (Inferred)

### Main Tables

#### `registration`
Stores user registration information
- `registration_id` - Primary key
- `fullname` - Student full name
- `dob` - Date of birth
- `email` - Email address
- `phone` - Phone number
- `college` - College/University name
- `yearofstudy` - Year of study
- `passingoutyear` - Expected graduation year
- `branch` - Academic branch/department
- `skills` - Comma-separated list of skills
- `password` - Hashed password

#### `exam`
Stores exam information
- `exam_id` - Primary key
- `examtitle` - Title of the exam
- `examdescription` - Detailed description
- `examdate` - Exam date
- `examtime` - Exam time
- `duration` - Duration in minutes
- `gracestart` - Grace period start time
- `graceend` - Grace period end time
- `skillRequired` - Required skill for the exam

#### `sections`
Organizes questions into sections within an exam
- `section_id` - Primary key
- `exam_id` - Foreign key to exam
- `section_name` - Name of the section
- `noofquestions` - Number of questions in section

#### `questions`
Stores exam questions
- `question_id` - Primary key
- `exam_id` - Foreign key to exam
- `questiontext` - The question content
- `optiona` - Option A
- `optionb` - Option B
- `optionc` - Option C
- `optiond` - Option D
- `correctanswer` - Correct answer (A/B/C/D)

#### `user_answers`
Tracks student answers
- `answer_id` - Primary key
- `registration_id` - Foreign key to user
- `exam_id` - Foreign key to exam
- `question_id` - Foreign key to question
- `selected_answer` - Student's selected answer
- `timestamp` - When the answer was submitted

---

## Key Application Files

### Authentication & User Management
- **login.php** - User and admin login interface
- **registration.php** - Student registration form
- **edit_profile.php** - User profile editing

### Exam Management (Admin)
- **create_exam.php** - Create new exams with sections
- **add_question.php** - Add questions to exams
- **admin.php** - Admin welcome page and navigation hub

### Exam Execution (Student)
- **enter_exam_passkey.php** - Passkey entry for exam access
- **exam_instructions.php** / **exam_instraction.php** - Display exam instructions
- **start_exam.php** - Main exam interface with timer
- **startexam.php** - Exam start handler
- **submitexam.php** - Process exam submission and calculate score

### User Dashboard
- **dashboard.php** - Main user dashboard
- **pastexam.php** - View past exam records
- **upcoming_exam.php** - View upcoming exams

### Utilities & Support
- **config.php** - Database configuration and connection
- **learn.html** - Learning resources
- **FAQ.htm** - Frequently asked questions
- **reference.php** - Reference materials
- **f_pdf.php** - PDF generation functions
- **fpdf.php** - FPDF library

### Styling
- **login.css** - Login page styling
- **admin.css** - Admin interface styling
- **fpdf.css** - PDF-specific styling

### Documentation
- **install.txt** - Installation instructions
- **license.txt** - License information
- **changelog.htm** - Version changes and updates

---

## Authentication & Security

### Login Flow
1. User enters email and password on login.php
2. System checks for hardcoded admin credentials (admin@smartexam.com)
3. For regular users, credentials are verified against the registration table
4. Passwords are compared using `password_verify()` for security
5. Session is created upon successful authentication
6. User redirected to appropriate dashboard (admin.php or dashboard.php)

### Security Features
- **Password Hashing**: Uses PHP's `password_hash()` with PASSWORD_DEFAULT algorithm
- **Session Management**: PHP sessions for user tracking
- **CSRF Protection**: CSRF tokens in forms (visible in create_exam.php)
- **Prepared Statements**: MySQLi prepared statements in critical areas
- **Input Validation**: Trimming and type casting of user inputs

---

## Exam Workflow

### For Administrators
1. Login to admin.php
2. Navigate to create_exam.php
3. Enter exam details (title, description, date, time, duration, grace period)
4. Define sections within the exam
5. Add questions via add_question.php
6. Set correct answers for auto-grading
7. Exam becomes available to students

### For Students
1. Register via registration.php
2. Login with credentials
3. View available/upcoming exams on dashboard.php
4. Enter exam passkey on enter_exam_passkey.php
5. Read instructions on exam_instructions.php
6. Take exam on start_exam.php with countdown timer
7. Submit exam via submitexam.php
8. Receive instant score and feedback
9. View results on pastexam.php

---

## File Organization

```
exam/
├── Core Files
│   ├── config.php              - Database configuration
│   ├── login.php               - Login interface
│   ├── registration.php        - Registration form
│   ├── edit_profile.php        - User profile editor
│
├── Admin Panel
│   ├── admin.php               - Admin welcome page
│   ├── dashboard.php           - Admin/User dashboard
│   ├── create_exam.php         - Create exams
│   ├── add_question.php        - Add questions
│
├── Exam Execution
│   ├── enter_exam_passkey.php  - Passkey entry
│   ├── exam_instructions.php   - Instructions display
│   ├── start_exam.php          - Main exam interface
│   ├── startexam.php           - Exam start handler
│   ├── submitexam.php          - Submit and grade
│
├── User Features
│   ├── pastexam.php            - Past exam results
│   ├── upcoming_exam.php       - Upcoming exams
│   ├── user.php                - User profile management
│   ├── learn.html              - Learning resources
│   ├── reference.php           - Reference materials
│   ├── FAQ.htm                 - FAQs
│
├── Styling
│   ├── login.css               - Login styling
│   ├── admin.css               - Admin styling
│   ├── fpdf.css                - PDF styling
│   ├── context.md              - This documentation
│
├── PDF & Reporting
│   ├── f_pdf.php               - PDF generation
│   ├── fpdf.php                - FPDF library
│   ├── online_exam_passkey_termsandconditions.php
│
├── Utilities
│   ├── save_questions.php      - Question saving logic
│   ├── test.php, test1.php, test2.php - Testing files
│   ├── likhithsai.php, sai.php - Custom scripts
│
├── Documentation
│   ├── install.txt             - Installation guide
│   ├── license.txt             - License information
│   ├── changelog.htm           - Version history
│   ├── FPDF Documentation      - doc/ folder
│   └── FPDF Tutorial           - tutorial/ folder
```

---

## API-like Endpoints

### Authentication
- `POST /login.php` - User/Admin login
- `POST /registration.php` - New user registration
- `GET/POST /edit_profile.php` - Edit user profile

### Exam Management (Admin)
- `POST /create_exam.php` - Create new exam
- `POST /add_question.php` - Add questions to exam

### Exam Execution
- `POST /enter_exam_passkey.php` - Verify exam access
- `GET /exam_instructions.php` - Display exam instructions
- `POST /start_exam.php` - Initialize exam session
- `POST /submitexam.php` - Submit and grade exam (JSON response)

### Dashboard & Reports
- `GET /dashboard.php` - Main dashboard
- `GET /pastexam.php` - Past exam records
- `GET /upcoming_exam.php` - Upcoming exams

---

## Configuration

### Database Configuration (config.php)
```php
Server: localhost
Username: root
Password: (empty)
Database: exam
```

### Application Settings
- **Admin Email**: admin@smartexam.com
- **Application Title**: SmartExam Pro
- **Default Exam Duration**: Varies per exam (configurable)

---

## Dependencies

### PHP Extensions Required
- MySQLi (for database connection)
- Session (for user management)
- JSON (for AJAX responses)

### External Libraries & CDNs
- Bootstrap 5.3.6 (CDN)
- SweetAlert2 11 (CDN)
- FPDF (bundled library)

---

## Current Version & Maintenance

- **Status**: Active development
- **Last Updated**: 2026
- **Maintenance Notes**: See changelog.htm for version history

---

## Future Enhancement Possibilities

1. User role management (different admin levels)
2. Question bank with categorization
3. Advanced analytics and reporting
4. Mobile application support
5. Real-time proctoring features
6. Question shuffling and randomization
7. Negative marking system
8. Different question types (essay, matching, etc.)
9. Integration with external authentication systems
10. Performance analytics and insights

---

## Notes for Developers

1. **Database**: Ensure MySQL server is running before starting the application
2. **Sessions**: Clear session files periodically to manage storage
3. **Security**: Review and strengthen CSRF and SQL injection protections
4. **Input Validation**: Always validate and sanitize user inputs
5. **Error Handling**: Implement comprehensive error logging
6. **Testing**: Test with various browsers and screen sizes due to Bootstrap usage
7. **Documentation**: Keep inline comments and maintain this context.md file updated

---

## Getting Started

1. Place files in `/xampp/htdocs/exam/`
2. Ensure MySQL is running
3. Create database named `exam` and tables as per database structure
4. Access the application at `http://localhost/exam/login.php`
5. Login as admin with email: `admin@smartexam.com` (password may be empty)
6. Create exams and manage questions from admin dashboard

---

**Last Updated**: 2026-07-02
**Documentation Author**: Development Team
