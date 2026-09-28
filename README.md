# LearnHub - Learning Management System

LearnHub is a web-based Learning Management System (LMS) developed using PHP and MySQL. It provides a platform where administrators can manage courses and users, teachers can manage learning materials and assessments, and students can enroll in courses, attend quizzes, submit assignments, and track their learning progress.

---

## Features

### 👨‍💼 Admin

- Admin dashboard
- Manage courses
- Create, edit and delete courses
- Manage course categories
- Assign courses to teachers
- View course details
- Manage certificates
- Generate and send certificates
- Monitor students and teachers

### 👨‍🏫 Teacher

- Teacher dashboard
- View assigned courses
- Upload and manage lessons
- Create assignments
- Review student assignment submissions
- Create quizzes
- Publish quizzes
- View student quiz submissions
- Grade student papers
- Manage course learning materials

### 👨‍🎓 Student

- Student registration and login
- Student dashboard
- Browse available courses
- Enroll in courses
- View enrolled courses
- Access course lessons
- Take quizzes
- Submit quiz answers
- Submit assignments
- Upload assignment files
- View results and grades
- Complete courses
- Receive course completion certificates

---

## Main Modules

The system is divided into several major modules:

### Authentication

- User registration
- User login
- Session-based authentication
- Role-based access control
- Logout functionality

### Course Management

- Course creation
- Course editing
- Course deletion
- Course categories
- Course images
- Course descriptions
- Course pricing
- Teacher-course assignment

### Learning Management

- Course lessons
- Lesson materials
- Student course enrollment
- Learning progress
- Course completion

### Assignment System

Teachers can create assignments for their courses.

Students can:

- View assignments
- Write answers
- Upload files
- Submit assignments
- Submit assignments before or after the deadline

Late submissions are automatically detected by comparing the submission time with the assignment deadline.

### Quiz System

- Teachers can create quizzes
- Add questions and options
- Publish quizzes
- Students can take published quizzes
- Student answers are stored
- Quiz results are recorded
- Teachers can review submissions

### Grading System

The system supports:

- Student submissions
- Assignment grading
- Final grading
- Course results

### Certificate Management

The certificate module allows administrators to:

- Track eligible students
- Generate certificates
- Track certificate status
- Send certificates
- View certificate information

Certificate statuses include:

- Pending
- Generated
- Sent

### Enrollment & Payment

Students can enroll in courses through the enrollment system.

The system supports:

- Free courses
- Paid courses
- Course enrollment
- Payment page interface
- Enrollment tracking

---

## User Roles

LearnHub uses role-based access control.

| Role | Main Responsibilities |
|------|------------------------|
| Admin | Manage users, courses, categories, teachers and certificates |
| Teacher | Manage assigned courses, lessons, assignments and quizzes |
| Student | Enroll in courses, learn lessons, take quizzes and submit assignments |

---

## Technology Stack

### Frontend

- HTML5
- CSS3
- JavaScript

### Backend

- PHP

### Database

- MySQL

### Server

- Apache / XAMPP
