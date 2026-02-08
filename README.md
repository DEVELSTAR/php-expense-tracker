# Akibworks Expense Tracker

A simple, responsive expense tracking web application built with vanilla PHP, HTML, CSS, and JavaScript.

## Features

- Add expenses with date, amount, and category
- View all expenses in a sortable table
- Filter expenses by category
- Delete expenses
- Real-time total calculation
- Mobile-friendly responsive design
- No authentication required

## Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **No frameworks or external libraries**

## Project Structure

```
expense-tracker/
├── index.html          # Main HTML file
├── css/
│   └── style.css       # Stylesheets
├── js/
│   └── app.js          # JavaScript application logic
├── api/
│   ├── db.php          # Database connection
│   ├── add_expense.php # Add expense API
│   ├── get_expenses.php # Get expenses API
│   └── delete_expense.php # Delete expense API
└── README.md           # This file
```

## Database Setup

### Create Database and Table

1. Log in to your cPanel and go to **MySQL Databases**
2. Create a new database named `akibworks_expenses`
3. Create a database user and assign it to the database with all privileges
4. Go to **phpMyAdmin** and select your database
5. Run the following SQL query to create the expenses table:

```sql
CREATE TABLE expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  expense_date DATE,
  total DECIMAL(10,2),
  category VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Deployment Instructions (cPanel)

### Step 1: Upload Files

1. Log in to your cPanel account
2. Go to **File Manager**
3. Navigate to the `public_html` directory (or your desired subdirectory)
4. Create a new folder called `expense-tracker`
5. Upload all the project files to this folder

### Step 2: Configure Database Connection

1. Open the `api/db.php` file in the File Manager editor
2. Update the database credentials:

```php
$host = 'localhost';           // Usually 'localhost' in cPanel
$dbname = 'akibworks_expenses'; // Your database name
$username = 'your_db_user';     // Your database username
$password = 'your_db_password'; // Your database password
```

3. Save the file

### Step 3: Set File Permissions

1. In File Manager, select the `api` folder
2. Right-click and choose **Change Permissions**
3. Set permissions to **755** (or keep default if working)
4. Ensure PHP files have **644** permissions

### Step 4: Access the Application

Open your web browser and navigate to:
```
https://yourdomain.com/expense-tracker/
```

## API Endpoints

### Add Expense
- **URL**: `api/add_expense.php`
- **Method**: POST
- **Body**: JSON with `expense_date`, `total`, `category`

### Get Expenses
- **URL**: `api/get_expenses.php`
- **Method**: GET
- **Query**: `?category=<category_name>` (optional filter)

### Delete Expense
- **URL**: `api/delete_expense.php`
- **Method**: DELETE
- **Body**: JSON with `id` or query parameter `?id=<expense_id>`

## Categories

The application supports the following expense categories:
- Both
- Domestic
- Akib
- Saniya
- Neha
- Family

## Security Considerations

- Input validation on both client and server side
- SQL injection prevention using prepared statements
- XSS prevention through proper output encoding
- CORS headers configured for API access

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `api/db.php`
   - Ensure database user has proper permissions
   - Verify database exists

2. **404 Errors**
   - Ensure files are uploaded to correct directory
   - Check file permissions
   - Verify .htaccess (if present) doesn't block access

3. **CORS Issues**
   - The API includes CORS headers
   - Ensure no conflicting .htaccess rules

### Testing

You can test the API endpoints using tools like:
- Postman
- curl commands
- Browser developer tools

Example curl command to get expenses:
```bash
curl -X GET "https://yourdomain.com/expense-tracker/api/get_expenses.php"
```

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## License

This project is proprietary to Akibworks.
