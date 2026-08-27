# Campus Job Posting System

A university-centric web application connecting students with on-campus employment opportunities (e.g., Student Assistants, Office Clerks, IT Lab Assistants, Library Aides, Peer Tutors, and approved campus-affiliated employers).

## ?? Features

- **Students**: Browse job opportunities, filter by department/category, submit applications, and track application status.
- **Employers**: Post and manage job listings, review student applications, update candidate statuses, and download resumes.
- **Admin**: Manage categories, monitor platform activity, and generate reports.
- **Authentication**: Role-based access control (Student, Employer, Admin) with password strength validation.

## 🛠️ Tech Stack

- **Backend & Templating**: Native PHP (JSON file-based storage)
- **Frontend & Styling**: Bootstrap 5, Custom CSS3, Vanilla JavaScript (ES6)
- **Tunneling / Deployment**: Cloudflare Tunnel integration for live preview

## ?? Getting Started

### Local Development

1. Clone this repository:
   `ash
   git clone https://github.com/AlwinsonTGM/Campus-Job-Posting-System.git
   cd Campus-Job-Posting-System
   `
2. Start the built-in PHP server:
   `ash
   php -S 127.0.0.1:8000
   `
3. Open http://127.0.0.1:8000 in your browser.

### Using Cloudflare Tunnel
Run start-tunnel.bat to launch the PHP server and generate a public URL for team sharing.
