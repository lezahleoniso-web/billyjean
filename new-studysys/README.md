# UniClub — Student Club Registration System
## Midterm Group Project | PHP + MySQL + XAMPP

---

## 📁 Project Structure

```
student-club-registration/
├── index.php        ← Homepage
├── register.php     ← Registration form (JS validation + photo upload)
├── process.php      ← PHP backend processor (validation + DB insert)
├── success.php      ← Success page after registration
├── db.php           ← Database connection config
├── database.sql     ← SQL schema — import this first!
├── uploads/         ← Profile photo storage (must be writable)
└── README.md        ← This file
```

---

## 🚀 Setup Instructions (XAMPP)

### Step 1 — Copy project folder
Place the entire `student-club-registration/` folder inside:
```
C:\xampp\htdocs\student-club-registration\
```

### Step 2 — Start XAMPP
Open **XAMPP Control Panel** → Start **Apache** and **MySQL**.

### Step 3 — Import the database
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **Import** (top menu)
3. Click **Choose File** → select `database.sql`
4. Click **Go**
   - This creates `uniclub_db` with the `students` and `clubs` tables automatically.

### Step 4 — Check folder permissions
Make sure the `uploads/` folder is writable.  
On Windows with XAMPP this is usually automatic.

### Step 5 — Run the project
Open your browser and go to:
```
http://localhost/student-club-registration/
```

---

## ✅ Features

| Feature                  | File           |
|--------------------------|----------------|
| Homepage with club list  | index.php      |
| Registration form        | register.php   |
| JS form validation       | register.php   |
| Email format validation  | register.php   |
| Password strength meter  | register.php   |
| Confirm password check   | register.php   |
| Profile photo upload     | register.php   |
| PHP backend processing   | process.php    |
| Password hashing (bcrypt)| process.php    |
| Duplicate email/ID check | process.php    |
| MySQL data storage       | process.php    |
| Success page             | success.php    |

---

## 🗄️ Database Table: `students`

| Column         | Type         | Description                  |
|----------------|--------------|------------------------------|
| id             | INT (PK, AI) | Auto-increment primary key   |
| student_id     | VARCHAR(20)  | Unique student ID number     |
| first_name     | VARCHAR(60)  | First name                   |
| last_name      | VARCHAR(60)  | Last name                    |
| full_name      | VARCHAR(120) | Combined full name           |
| email          | VARCHAR(120) | University email (unique)    |
| course         | VARCHAR(80)  | Degree program               |
| year_level     | VARCHAR(20)  | Academic year                |
| club           | VARCHAR(80)  | Chosen student club          |
| password_hash  | VARCHAR(255) | bcrypt hashed password       |
| profile_photo  | VARCHAR(255) | Path to uploaded photo       |
| status         | ENUM         | active / inactive / pending  |
| created_at     | DATETIME     | Registration timestamp       |

---

## 🔍 Verify Data in phpMyAdmin

After registering a test account, run this query:
```sql
SELECT id, student_id, full_name, email, club, created_at
FROM students
ORDER BY created_at DESC;
```

---

## 👥 Suggested Group Roles

| Role           | Responsibility                                    |
|----------------|---------------------------------------------------|
| Frontend Dev   | index.php, register.php styling & layout          |
| JS Developer   | Form validation, password strength, photo preview |
| Backend Dev    | process.php, db.php, PHP logic                    |
| Database Admin | database.sql, phpMyAdmin demo                     |
| Tester         | Test all edge cases, duplicate entries, errors    |
| Presenter      | Lead walkthrough, explain code during Q&A         |

---

## 🎓 Presentation Checklist

- [ ] XAMPP running (Apache + MySQL green)
- [ ] Database imported and visible in phpMyAdmin
- [ ] Homepage loads at `http://localhost/student-club-registration/`
- [ ] Registration form submits successfully
- [ ] Data visible in phpMyAdmin after registration
- [ ] Error messages show for invalid inputs
- [ ] Password hashing visible in DB (not plain text)
- [ ] Profile photo uploads to `/uploads/` folder
