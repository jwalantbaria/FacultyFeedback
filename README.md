# Faculty Feedback Report Generator (PHP + MySQL)

A Core PHP + MySQL application to upload results and student feedback sheets, process analytics, and generate a printable/exportable faculty feedback report.

## Folder Structure

```text
FacultyFeedback/
├── app/
│   ├── config/config.php
│   ├── controllers/FacultyFeedbackController.php
│   ├── helpers/Database.php
│   ├── models/
│   ├── services/
│   └── views/
├── database/schema.sql
├── public/
│   ├── index.php
│   └── uploads/
├── sample_files/
├── composer.json
└── README.md
```

## Features

- Faculty input form with validation:
  - Academic year
  - Semester
  - Subject (existing or new)
  - Number of students
- Secure upload modules for:
  - Result file (CSV/XLSX)
  - Student feedback file (CSV/XLSX)
- Flexible feedback parser:
  - Supports simple `Q1..Q12`
  - Supports Google Form export columns with long question statements
- Automatic processing:
  - Average feedback score
  - Question-wise averages (12 parameters)
  - Grade-wise feedback analysis
  - Total responses
- Report page with Bootstrap UI + Chart.js
- Export report to PDF via DomPDF

## Setup

1. Create database and tables:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Configure environment variables as needed:
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
4. Start local server:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
5. Open:
   - `http://localhost:8000`

## File format expectations

### Results file header (required)
- Enrollment No
- Student Name
- Grade

### Feedback file header
Supported options:

1. **Simple format**
   - Enrollment No (optional but recommended)
   - Q1, Q2, ... Q12
   - (optional) Timestamp, Email Address, Student Name, Opinion

2. **Google Form export format**
   - Timestamp
   - Email Address
   - Student's Name (optional but desirable)
   - Enrollment No (optional but desirable)
   - 12 feedback question columns (1 to 5 scale)
   - Opinion text column

## Security notes

- File extension + MIME validation
- Randomized upload filenames
- PDO prepared statements
- Server-side validation for all required inputs

## PDF export

Use **Export to PDF** button on report page. DomPDF must be installed (`composer install`).
