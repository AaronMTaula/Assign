================================================================================
CabsOnline - Web Development Assignment 3
Author: Aaron Taula - 15905800
================================================================================
 
FILES IN THIS SYSTEM
--------------------
booking.html     - Passenger taxi booking form
booking.js       - Validation and fetch logic for the booking page
booking.php      - Server-side handler: saves booking to database
admin.html       - Admin search, current bookings, and past rides page
admin.js         - Search, display, and assign logic for the admin page
admin.php        - Server-side handler: searches and assigns bookings
mysqlcommand.txt - SQL command to create the bookings table
readme.txt       - This file
 
================================================================================
HOW TO SET UP
================================================================================
 
1. CREATE THE DATABASE TABLE
   - Log into phpMyAdmin on webdev.aut.ac.nz
   - Select your database (cdt6246)
   - Click the SQL tab
   - Paste the contents of mysqlcommand.txt and click Go
 
2. DATABASE CREDENTIALS
   - The PHP files load credentials from:
     ../../files/settings.php  (two folders above htdocs)
   - That file defines: $host, $user, $pswd, $dbnm
   - These are already configured on the webdev server
 
3. UPLOAD FILES
   - Upload all files into: htdocs/assign/
   - No subfolders - all files go directly in the assign folder
 
================================================================================
HOW TO USE
================================================================================
 
BOOKING PAGE
  URL: http://webdev.aut.ac.nz/~cdt6246/assign/booking.html
  - Fill in the required fields (Name, Phone, Street Number, Street Name,
    Pick-up Date and Time) and click Book Taxi
  - Phone must be 10-12 digits, numbers only
  - Pick-up date and time cannot be in the past
  - A confirmation with booking reference number will appear on the page
 
ADMIN PAGE
  URL: http://webdev.aut.ac.nz/~cdt6246/assign/admin.html
  - The page automatically loads Current Bookings and Past Rides on open
  - To search: type a booking reference (e.g. BRN00001) and click
    Search Bookings to find a specific booking
  - To see upcoming: leave the search box empty and click Search Bookings
    to show all unassigned bookings with a pickup in the next 2 hours
  - Click Assign on any row to assign a taxi to that booking
 
================================================================================