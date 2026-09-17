# Online Examination Portal

## 📌 Project Overview

The Online Examination Portal is a web-based examination management system designed to conduct online tests efficiently and securely.

The system allows administrators to create and manage examinations, questions, candidates, and results. Students can register/login, attend available examinations, submit answers, and view their results.

This project provides a simple, responsive, and user-friendly interface for conducting online examinations.

---

## 🎯 Objectives

The main objectives of this project are:

- Conduct examinations online.
- Reduce manual examination work.
- Allow administrators to manage exams and questions.
- Allow students to attend examinations from anywhere.
- Automatically evaluate objective-type questions.
- Generate examination results automatically.
- Store student, examination, question, and result information securely.
- Provide an easy-to-use and responsive interface.

---

## 🚀 Features

### 👨‍🎓 Student Features

- Student registration
- Student login/logout
- View available examinations
- View examination instructions
- Start examination
- Answer multiple-choice questions
- Navigate between questions
- Examination timer
- Submit examination
- Automatic result calculation
- View marks and result status
- View previous examination results

### 👨‍💼 Admin Features

- Admin login
- Admin dashboard
- Manage students
- Create examinations
- Update examinations
- Delete examinations
- Add questions
- Edit questions
- Delete questions
- Set examination duration
- Set total marks
- View student submissions
- View examination results
- Manage examination status

---

## 🛠️ Technologies Used

### Frontend

- HTML5
- CSS3
- Bootstrap 5
- JavaScript

### Backend

- PHP

### Database

- MySQL

### Development Environment

- XAMPP
- Apache
- MySQL
- phpMyAdmin

---

## 📂 Project Structure

```text
exam-portal/
│
├── admin/
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   ├── exams.php
│   ├── add_exam.php
│   ├── edit_exam.php
│   ├── delete_exam.php
│   ├── questions.php
│   ├── add_question.php
│   ├── edit_question.php
│   ├── delete_question.php
│   └── results.php
│
├── student/
│   ├── dashboard.php
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── exam.php
│   ├── submit_exam.php
│   └── result.php
│
├── config/
│   └── db.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── js/
│   │   └── script.js
│   │
│   └── images/
│
├── index.php
├── README.md
└── database.sql
