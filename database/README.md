# School Website Database Setup

## Steps to Import Database

1. Open **phpMyAdmin** or MySQL terminal.
2. Create the database:
   ```sql
   CREATE DATABASE school_db;
Import the file:

In phpMyAdmin → Select school_db → Import → Choose school_db.sql.

Or using terminal:

mysql -u root -p school_db < school_db.sql

Default Login Accounts

Admin

Username: admin

Password: admin123

Teacher

Username: teacher

Password: teacher123