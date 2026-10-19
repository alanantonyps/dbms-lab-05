# College Fest Event Registration System (Q5)

- **Name:** Alan Antony P S
- **Roll Number:** 05
- **Question Number:** Q5 (College Fest Event Registration Database)

## Overview & Implementation Note
This web-based project manages college fest event registrations using XAMPP, PHP, and MySQL. The PHP scripts establish a secure connection to the database via `db.php` using the MySQLi extension. When users fill out and submit the form in `register.php`, an SQL `INSERT` query processes the input data and safely stores it into the transactional `registration` table linked via foreign keys. The report page (`report.php`) executes a multi-table SQL `JOIN` query (`SELECT`) across the `registration`, `participant`, and `events` tables to retrieve and present all registration records in a clean, structured tabular format.