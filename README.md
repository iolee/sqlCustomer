Customer SQL Management System
A PHP-based web application for managing customer data, styled with CSS and backed by a MySQL database. This project is fully containerized using Docker for easy setup.

🚀 Quick Start (Using Docker)
Follow these steps to get the project running on your local machine in minutes.

1. Clone the Repository
Open your terminal and run:

git clone https://github.com/iolee/sqlCustomer.git
cd sqlCustomer

2. Prerequisites (Install Docker)
If you don't have Docker installed, you'll need it to run the environment:

Download: Docker Desktop for Windows/Mac

Install: Run the installer. On Windows, ensure "Use WSL 2 instead of Hyper-V" is checked.

Restart: You may need to restart your computer after installation.

3. Launch the Application
Open Docker Desktop and wait until the engine is running (the whale icon turns green).

In your terminal (inside the project folder), run:

docker compose up -d
Note: This will automatically set up the PHP server and import the database schema from the /sql folder.

4. Access the App
Once the containers are running, open your browser and go to: 👉 http://localhost:8080

🛠️ Tech Stack
Frontend: HTML5, CSS3

Backend: PHP 8.2 (Apache)

Database: MySQL 8.0

Deployment: Docker & Docker Compose

📁 Project Structure
mainmenu.php - Main entry point and navigation menu.

connectdb.php - Database connection settings (configured for Docker).

/css - Styling and visuals.

/sql - Contains init.sql for automatic database initialization.


