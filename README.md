Here’s a **README** file you can use for your GitHub project:

---

# Personal Portfolio of Abdullah Md. Shahporan

A personal portfolio management system where I can manage Education and Projects. The system includes features like form responses, education management, project management, and an admin login system with a user-friendly UI.

## Features:

* **Admin Login**: Secured login with email and password.( Locked By me so that none other can enter my admin)
* **Education Management**: I can add, view, and manage educational details such as degree, institution, logo, and description.
* **Project Management**: I can add, view, and manage project details like project name, description, video, and link.
* **Form Responses**: I can view, manage, and respond to form submissions from the website visitors.
* **Responsive Design**: The website is fully responsive with smooth transitions for different screen sizes.
* **Cookie Consent**: Upon the first visit, a consent form for cookies is displayed.

## Tech Stack:

* **Frontend**: HTML, CSS, JavaScript
* **Backend**: PHP
* **Database**: MySQL
* **Hosting**: Localhost (XAMPP or any PHP-enabled server)

## Project Structure:

```plaintext
├── admin/
│   ├── education_logos/
│   ├── project_videos/
│   ├── education_delete.php
│   ├── education_edit.php
│   ├── education_render.php
│   ├── education_upload.php
│   ├── project_delete.php
│   ├── project_edit.php
│   ├── project_render.php
│   ├── project_upload.php
│   ├── submit_form.php
│   ├── view_responses.php
├── assets/
│   ├── script.js
│   └── style.css
├── admin.html
├── login.html
├── index.html
└── README.md
```

## Database Structure:

   * Created a new database named `portfolio_db`.
   * I had follow the below database structure

     ```sql
     CREATE TABLE education (
       id INT AUTO_INCREMENT PRIMARY KEY,
       institution VARCHAR(255) NOT NULL,
       degree VARCHAR(255) NOT NULL,
       field VARCHAR(255),
       logo_path VARCHAR(255),
       start_year INT,
       end_year INT,
       description TEXT,
       sort_order INT DEFAULT 0,
       is_active TINYINT(1) DEFAULT 1,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );

     CREATE TABLE project (
       id INT AUTO_INCREMENT PRIMARY KEY,
       title VARCHAR(180) NOT NULL,
       description TEXT,
       link_url VARCHAR(500),
       video_path VARCHAR(255),
       sort_order INT DEFAULT 0,
       is_active TINYINT(1) DEFAULT 1,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );
      CREATE TABLE form (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(255) NOT NULL,
       contact_number VARCHAR(20) NOT NULL,
       email VARCHAR(255) NOT NULL,
       description TEXT NOT NULL,
       submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );
     ```


