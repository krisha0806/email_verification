# Email Verification with GitHub Timeline Updates

A PHP-based system that allows users to register their email, receive GitHub timeline summaries, and unsubscribe — with CRON automation and a complete verification flow.

---

## Features

- Email verification through one-time codes
- GitHub timeline summary emails for registered users
- CRON job runs every 5 minutes to trigger updates
- Secure unsubscribe system via verification
- Uses `registered_emails.txt` to manage user list

---

## Tech Stack

- PHP
- CRON (for scheduling)
- File handling for email storage
- Basic SMTP (or PHP mail) for email sending

---

## How It Works

1. Users enter their email through a form in `index.php`
2. A verification code is emailed to confirm identity
3. Upon verification, the email is stored in `registered_emails.txt`
4. A CRON job executes `cron.php` every 5 minutes to send updates
5. Users can opt out anytime via `unsubscribe.php`

---

## File Structure Overview

- `index.php` – Email input form and verification handler
- `functions.php` – Contains core logic (verification, mailing, summary, etc.)
- `cron.php` – Triggers email summary sending via CRON
- `unsubscribe.php` – Handles email unsubscription
- `registered_emails.txt` – Acts as the user database
- `setup_cron.sh` – Script to install CRON job
- `email_errors.log` – Logs email delivery issues (for debugging)

---

## How to Run

1. Set up a local PHP server or deploy to a PHP-supported host
2. Configure your mail settings in `functions.php` if needed
3. Run `setup_cron.sh` to schedule `cron.php` every 5 minutes (on Linux/macOS)
4. Alternatively, simulate CRON with a loop if you're on Windows

---

## Notes

- Only files inside the `src/` directory were modified
- File structure and function names were not altered
- Email addresses are not hardcoded
- All logic adheres to the guidelines provided in the task

---

## License

This project is for educational and demonstration purposes. You are free to clone, modify, or build on top of it with credit.
