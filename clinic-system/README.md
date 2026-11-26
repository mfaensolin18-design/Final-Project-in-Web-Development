# Clinic One: Appointment and Record System

Final project in Web Development (field: Health and Wellness).

A web system that lets a small clinic book appointments without double-booking, keep patient records with visit notes, and view doctor schedules. Front-desk staff and doctors log in with separate roles.

## Folder structure
| Folder | Contents |
| --- | --- |
| `php-version/` | Full system: PHP, MySQL, role-based login (runs on XAMPP) |
| `demo/` | Browser-only demo (HTML, CSS, JavaScript). Open `index.html` directly |
| `docs/` | Documentation (Word), presentation (PowerPoint), ERD image |

## Features
- Public "Doctors on duty" page that anyone can open without logging in. When no doctor is on duty it tells patients not to come to the clinic
- Patient sign-up (Create account). Patients send a booking request with their name, age, gender and reason for visit. Staff confirm it and choose the doctor, date and time based on who is on duty. Patients follow the status under My appointments
- Patients can donate by GCash, Maya or online banking with an optional description. Staff check each donation and the clinic balance grows when they mark it received
- Staff can upload a portal background picture on the Manage team page; everyone sees it
- Staff-only **Manage team** page: add front desk staff, doctors and midwives with their login accounts and working days
- Login with front-desk, doctor and midwife roles
- Dashboard with today's schedule and key counts
- Appointment booking with free and taken time slots; days off are blocked
- Appointment list with status filter; complete or cancel visits
- Patient search, registration, visit notes and printable records
- Doctor weekly schedules and workload

## Run the PHP/MySQL version (XAMPP)
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy the `php-version` folder to `C:\xampp\htdocs\` and rename it `clinic`.
3. Open http://localhost/phpmyadmin, choose **Import**, select `database.sql`, and click Import.
4. Open http://localhost/clinic/install.php once to create the demo users, then delete `install.php`.
5. Open http://localhost/clinic/duty.php (public page) or http://localhost/clinic/login.php

| Role | Username | Password |
| --- | --- | --- |
| Front desk | `staff` | `staff123` |
| Doctor | `drsantos` | `doctor123` |
| Midwife | `midwife` | `midwife123` |
| Patient | create your own at `register.php` | |

If your MySQL root user has a password, set it in `config.php`.

## Run the browser demo
Open `demo/index.html` in a browser. Data is saved in your browser only. The demo logins are the same as above and are for demonstration only.

## Technologies
HTML5, CSS3, JavaScript, PHP 8 (PDO), MySQL/MariaDB, XAMPP, Git and GitHub.

## Database
Tables: `patients`, `doctors`, `users`, `appointments`, `visit_notes`. A unique rule on doctor, date and time blocks double-booking. See `docs/erd.png`.

## Security notes
Prepared statements, hashed passwords, form tokens and escaped output are used in the PHP version. Change the demo passwords before any real use.

## Withdrawals (staff)
On the Donations page staff first create a withdrawal password (with confirmation), then withdraw by online wallet (GCash, Maya) or bank transfer (BDO, PBI, MetroBank, LandBank). Wallet withdrawals need the amount, wallet name, wallet number and the withdrawal password. Bank withdrawals need the amount, bank account number, account name and the withdrawal password. A withdrawal cannot be more than the available balance.

## Donations note
The system does not move money, and withdrawals only record what staff sent. Patients send money from their own GCash, Maya or banking app to the clinic's account and submit the reference number. Staff verify it against their own records before marking it received. Replace the sample account numbers on the Donations page. Automatic payments would need a payment provider (for example PayMongo or the GCash and Maya merchant tools).
