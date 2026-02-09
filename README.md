# Akibworks Expense Tracker

A simple expense tracking application with guest mode and user authentication.

## Features

- **Guest Mode**: Public expenses without registration
- **User Authentication**: Private expenses for registered users
- **Expense Management**: Add, view, and delete expenses
- **Filtering**: Filter by date and category
- **Categories**: Both, Domestic, Akib, Saniya, Neha, Family
- **Messages**: Optional notes for each expense
- **Responsive Design**: Mobile-friendly interface

## Project Structure

```
expense-tracker/
├── index.php              # Main expense UI (guest or logged-in)
├── login.html             # Login page
├── logout.php             # Logout logic
├── register.html           # Create user account
├── api/
│   ├── db.php             # Database connection
│   ├── add_expense.php    # Insert expense (guest or user)
│   ├── get_expenses.php   # Fetch expenses (based on session)
│   ├── delete_expense.php # Delete expense (based on session)
│   ├── login.php          # User authentication
│   ├── register.php       # User registration
│   └── check_auth.php     # Check authentication status
├── assets/
│   ├── css/
│   │   └── style.css     # Main stylesheet
│   └── js/
│       ├── app.js          # Main application logic
│       └── auth.js         # Authentication logic
├── sql/
│   └── schema.sql        # Database schema
├── .cpanel.yml           # Deployment configuration
└── README.md             # This file
```

## Setup Instructions

### 1. Database Setup

1. Create MySQL database named `akibwork_expense`
2. Import `sql/schema.sql` to create tables
3. Update database credentials in `api/db.php`:
   ```php
   $host = "localhost";
   $dbname = "akibwork_expense";
   $username = "your_db_username";
   $password = "your_db_password";
   ```

### 2. Server Requirements

- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx) with PHP support

### 3. Deployment

1. Upload all files to your web server
2. Ensure `api/` directory has proper permissions
3. Configure web server to handle `.php` files
4. Access `index.php` in your browser

## Usage

### Guest Mode
- Access the app directly without login
- Expenses are public (user_id = NULL)
- Only shows guest expenses

### User Mode
1. Click "Login" and register an account
2. Login with your credentials
3. Expenses are private to your account
4. Only your own expenses are visible

### Guest Login
- Username: `guest`
- Password: any password

## Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection protection with prepared statements
- Session-based authentication
- Input validation and sanitization
- User data isolation

## API Endpoints

- `POST api/login.php` - User authentication
- `POST api/register.php` - User registration
- `GET api/get_expenses.php` - Fetch expenses
- `POST api/add_expense.php` - Add expense
- `DELETE api/delete_expense.php?id=X` - Delete expense

## Categories

- Both
- Domestic
- Akib
- Saniya
- Neha
- Family

## Currency

All amounts are displayed in Indian Rupees (₹).

## Mobile Support

The application is fully responsive and works on:
- Desktop browsers
- Tablets
- Mobile phones
