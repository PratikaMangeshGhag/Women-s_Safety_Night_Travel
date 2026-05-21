<<<<<<< HEAD
# SafeNight

SafeNight is a women's safety web application project designed to support safer night travel. The website combines trip logging, emergency contact management, SOS alerting, unsafe area reporting, and a map-based route simulation into one system.

This project was built as a practical safety-focused platform that demonstrates how web technology can be used to support personal security, faster response, and better travel awareness.

## Project Idea

The main idea behind SafeNight is to reduce uncertainty during night travel by giving the user:

- a place to plan a route
- a way to log trips
- a way to store trusted emergency contacts
- a quick SOS feature
- a way to report unsafe locations
- a visual map interface to understand routes and danger zones

Instead of forcing the user to depend on multiple separate tools, SafeNight brings these actions together inside one application.

## Problem Statement

Night travel can feel unsafe, especially when the user:

- is travelling alone
- is unfamiliar with the route
- wants quick access to emergency actions
- wants trusted contacts to be ready in case of a problem
- needs awareness of potentially unsafe areas

SafeNight addresses this by combining awareness, preparation, and emergency response features in one interface.

## Objectives

- Provide a simple and accessible safety-focused web platform
- Allow users to register and securely log in
- Help users maintain emergency contact information
- Allow users to log and review their trips
- Provide an SOS emergency feature
- Allow users to report unsafe spots
- Present safety information visually through a map simulation

## Main Features

### 1. User Registration and Login

- New users can create an account
- Existing users can log in securely
- User sessions are maintained using PHP sessions
- Passwords are stored using hashed passwords

### 2. Dashboard Greeting

- After login, the user sees a welcome dashboard
- The dashboard fades and redirects to the home page
- This creates a smoother transition after authentication

### 3. Shared Navigation

- The navigation bar is stored in a reusable include file
- The active navigation item can be controlled page by page
- This improves maintainability and keeps the UI consistent

### 4. Safe Map

- The map page allows the user to enter a starting point and destination
- A demo route animation is shown on the simulated map
- Unsafe areas are displayed as red animated blobs
- Users can click on the map to report unsafe spots
- Users can also log the trip into the database

Important note:

The current map is a simulated map, not a live Google Maps integration. This decision was made because Google Maps and Places usage required paid API setup. For project demonstration purposes, a local animated map simulation was a better choice because it is reliable, cost-free, and still communicates the intended feature.

### 5. Trip Logging

- Trips can be saved from the map page using the `Log This Trip` button
- Saved trips are shown on the `My Trips` page
- Each trip stores the start location, destination, and status

### 6. Emergency Contacts

- Users can add, edit, and delete emergency contacts
- Contacts are stored in the database
- These contacts are used by the SOS feature

### 7. SOS Alert System

- The user can trigger an SOS alert
- SOS alert data is saved in the database
- Active SOS alerts can also be cancelled or marked resolved

### 8. Profile Page

- Users can view and update profile information
- Users can change their password
- The profile page also displays summary counts like trips, contacts, and SOS history

### 9. About Page

- The website includes a dedicated About page
- It explains the purpose of the project, its goals, and its main features

## Technologies Used

### Frontend

- HTML
- CSS
- JavaScript

Why these were used:

- HTML was used to structure the pages
- CSS was used to create the visual design and responsive layout
- JavaScript was used for interactions, transitions, route animation, and dynamic UI behavior

### Backend

- PHP

Why PHP was used:

- PHP is simple and effective for server-side web development
- It works very well with XAMPP
- It supports session handling easily
- It connects directly with MySQL
- It is suitable for academic projects and CRUD-based web applications

### Database

- MySQL

Why MySQL was used:

- It is widely used with PHP
- It is easy to manage using phpMyAdmin in XAMPP
- It is suitable for storing users, trips, contacts, unsafe spots, and SOS alerts

### Local Development Environment

- XAMPP

Why XAMPP was used:

- It provides Apache, PHP, and MySQL in one local environment
- It is easy to set up for development and project demos
- It is a common environment for PHP-based college projects

## Project Structure

Main files in the project:

- `index.php` - Home page
- `about.php` - About the project
- `map.php` - Safe map and route simulation
- `trips.php` - User trip history
- `contacts.php` - Emergency contact management
- `sos.php` - SOS alert page
- `profile.php` - User profile and account updates
- `login.php` - Login page
- `register.php` - Registration page
- `dashboard.php` - Post-login welcome screen
- `logout.php` - Logout handler
- `includes/navigation.php` - Shared navigation include

## Database Tables Used

Based on the current project code, the main database tables are:

- `users`
- `trips`
- `emergency_contacts`
- `sos_alerts`
- `unsafe_spots`

### Expected Purpose of Each Table

`users`

- stores user name
- stores email
- stores phone
- stores hashed password

`trips`

- stores user ID
- stores start location
- stores destination
- stores trip status

`emergency_contacts`

- stores contact name
- stores phone number
- stores relationship with user

`sos_alerts`

- stores user ID
- stores SOS coordinates
- stores whether the alert is resolved

`unsafe_spots`

- stores the reporting user
- stores latitude and longitude
- stores a description of the unsafe spot

## Key Design Choices

### 1. Shared Navigation Include

The navigation bar was moved into a separate include file to avoid repeating the same code across many pages. This makes the project easier to maintain and update.

### 2. Session-Based Authentication

Sessions were chosen because they are simple and effective for login handling in PHP applications. They are enough for the current project scope.

### 3. Simulated Map Instead of Paid API

Originally, live map APIs were considered. However, the external API setup required paid services and extra configuration. For a project demo, using a simulated animated map was the better decision because:

- it avoids billing issues
- it works offline in a controlled demo environment
- it keeps the visual feature understandable
- it still demonstrates the project idea effectively

### 4. Simple and Direct UI

The UI uses bold colors, clear calls to action, and easy navigation so that important safety functions remain visible and accessible.

## How the System Works

### User Flow

1. The user registers or logs in
2. The user reaches the dashboard greeting
3. The user can navigate to the map page
4. On the map page, the user can:
   - enter start and destination
   - view a route simulation
   - log the trip
   - report unsafe spots
5. The user can open `My Trips` to review saved trips
6. The user can manage emergency contacts
7. The user can trigger SOS when needed
8. The user can view or update profile information

## Security-Related Points Implemented

- Passwords are hashed using PHP password hashing
- Session handling is used after login
- Protected pages redirect unauthenticated users to login
- Logout destroys the session and returns the user to the home page

## Current Limitations

It is useful to mention these honestly during the presentation:

- The map is currently simulated, not live GPS-based routing
- The application does not yet send real SMS or live notifications
- SQL queries are written directly and can be improved using prepared statements
- Input validation and sanitization can be improved further
- The project is intended as a functional prototype and academic demonstration

## Future Improvements

- Add real-time map and routing APIs
- Add SMS or WhatsApp emergency alert integration
- Add live location sharing with contacts
- Add admin moderation for unsafe spot reports
- Add trip completion tracking
- Improve security using prepared statements and stronger validation
- Add mobile-first UI optimization

## Why This Project Is Valuable

This project is valuable because it does not focus only on a technical interface. It addresses a real-world social issue using web technology. It shows how software can be used to improve confidence, preparedness, and accessibility in personal safety scenarios.

## Points To Mention During Presentation

You can use these points while presenting:

- SafeNight is a women’s safety web platform for safer night travel
- The project combines route planning, trip logging, SOS support, unsafe spot reporting, and emergency contacts in one place
- It is built using PHP, MySQL, HTML, CSS, JavaScript, and XAMPP
- PHP and MySQL were chosen because they are practical, easy to integrate, and suitable for a database-driven academic web project
- The navigation was modularized into a separate include file to improve maintainability
- User authentication is session-based, and passwords are hashed
- The map was intentionally changed to a simulation because paid API dependence was not practical for this demo
- The trip logging, contacts, SOS alerts, and unsafe spot reporting are database-driven
- The project demonstrates both frontend UI design and backend CRUD/database integration
- The focus of the project is practical usability, clarity, and safety-oriented workflow

## Short Presentation Script

You can say something like this:

“SafeNight is a women’s safety web application designed to support safer night travel. The main goal of the project is to bring together safety-related tools like route planning, emergency contacts, SOS alerting, trip logging, and unsafe spot reporting into one website.

This project is built using HTML, CSS, JavaScript, PHP, MySQL, and XAMPP. I chose PHP and MySQL because they are well suited for session-based login systems and database-driven applications, and they are practical for implementing CRUD operations in a college project.

One important design decision was moving the navigation bar into a reusable include file, which improves consistency and maintainability across pages. Another important design decision was replacing the live map API with a simulated map interface. This was done because paid API dependency was not practical for the project demo, but I still wanted to demonstrate route visualization and unsafe area awareness.

The website includes login and registration, a dashboard greeting, a safe map page, a My Trips page, emergency contacts management, an SOS feature, a profile page, and an About page. Overall, the project focuses on solving a real safety-related problem through an integrated and user-friendly web interface.”

## How To Run The Project

1. Start Apache and MySQL in XAMPP
2. Place the project inside `htdocs`
3. Copy `.env.example` to `.env`
4. Update `.env` with your local database credentials
5. Import or create the database `safenight_db`
6. Make sure the required tables exist
7. Open:

`http://localhost/safenight/`

## Environment Configuration

This project uses a local `.env` file for database credentials.

- `.env` is ignored by Git and should stay local
- `.env.example` is the safe template you can upload to GitHub
- Update these values in `.env`:

`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`

## Final Summary

SafeNight is a database-driven PHP web project that demonstrates how a safety-focused travel platform can be designed for women travelling at night. It combines authentication, data management, emergency features, and a route visualization experience into one integrated system.

For an academic project, it demonstrates:

- frontend design
- backend logic
- session handling
- database integration
- modular code reuse
- user-centered problem solving
=======
# Women-s_Safety_Night_Travel
SafeNight is a women’s safety web app for safer night travel, combining trip logging, emergency contacts, SOS alerts, route planning, and unsafe area reporting in one  platform.
>>>>>>> 64383aca398e7a9dbb63538bf5e146805f3d8276
