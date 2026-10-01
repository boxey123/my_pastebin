# My Pastebin Site

A paste sharing website. Users upload blocks of text, called pastes, and share them with others via a link. A paste can be anything from a snippet of code to notes or plain text.

This project is to be used by anyone who needs to share text quickly, from developers to people that just need to share long notes. It is a very simple UI. There is nothing complex about using this site and that was one of my main objectives when I started designing it.

## Features

- Simple registration, login and logout
- A home page showing the 5 most recent public pastes
- Public and unlisted pastes
- Optional password protection
- Optional expiry date, paste will be deleted on the date provided
- Public profiles for every user, and a private profile view that lists all of your own pastes
- Input validation on every form, client-side and server-side
- A print layout for pastes

## Built with

- PHP for the backend (no framework), used to process HTML form data and manage user sessions
- MySQL for user accounts and pastes, queried with mysqli prepared statements
- JavaScript, using the Fetch API and managing elements of the DOM with it

## Running it locally

### Requirements

You will need Git, PHP 8.1 or later and MySQL, and if you are using a Mac this setup goes through the installation using Homebrew.

**Windows**: Install [XAMPP](https://www.apachefriends.org) which includes both PHP and MySQL. Run through the setup wizard and remember the install path that you configured. The default settings will work fine for this project. Add these two folders below to your PATH so that you will be able to run the `php` and `mysql` commands in Command Prompt.

```
C:\xampp\php
C:\xampp\mysql\bin
```

You may need to edit the start of the path depending on how you configured the install directory.

To add these to your PATH, search for "Edit the system environment variables" in the start menu, once that is open click `Environment Variables`, select `Path` under your user variables, click `Edit`, and add each folder as a new entry (make sure you save these before closing).

Start MySQL from the XAMPP control panel, and use the php command when starting the server (step 4).

**macOS**: Install [Homebrew](https://brew.sh) which will allow you to easily install PHP and MySQL.

Once Homebrew has been installed run the commands below to install them, and then start MySQL.

```bash
brew install php mysql
brew services start mysql
```

#### Making sure both commands work

Open a new terminal (Command Prompt on Windows) and run the following commands to verify that both services work.

```bash
php -v
mysql --version
```

Each command should print a version number. If either aren't recognised:

**Windows**
Double-check the folders you added match where XAMPP is installed, then close and reopen your Command Prompt. If this fails, you can open the XAMPP Control Panel app and press the `Shell` button. The commands `php` and `mysql` will be available to use in this shell without issue.

**Mac**
Double-check you ran the correct install command and make sure you closed the previous terminal and are entering the commands into a new one.

### Setup

1. Clone the repo

```bash
git clone https://github.com/boxey123/my_pastebin.git
cd my_pastebin
```

2. Create the database from the schema file. Replace `mysql_username` with your MySQL username. The `-p` flag will prompt you for the password of the provided user. Remove it if the account has no password (for example, the default `root` account usually does not have a password set on a fresh install).

**Windows**

```bash
mysql -u mysql_username -p < queries\project_queries.sql
```

**Mac**

```bash
mysql -u mysql_username -p < queries/project_queries.sql
```

If you have created a new user please ensure they have permission to create databases and tables, and read and write to the database.

3. Configure your database settings. Copy `config.example.php`, rename the copy to `config.php`, and fill in the details for your MySQL server. The default database name will be `my_pastebin`. You can change this in the `queries/project_queries.sql` file if you wish.

4. Start the site with PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

5. Open http://localhost:8000

6. Stop the server by pressing `Ctrl + C`.

Errors are logged to `error.log` in the project root rather than shown on the page.

## Project structure

```
public/               Pages served to the browser
queries/              Database schema
docs/                 ERD, use case diagram and wireframes
config.example.php    Template for the database config
```

## Security review

In my original project every input was validated and that was the minimum requirement for the SQA. When I started getting back to work on this project and doing some more testing I found that it definitely was not enough on its own. I found there were a large number of XSS vulnerabilities (which I've now fixed) within this project, along with other miscellaneous bugs that were never fixed during my rush of getting this project completed. Below is a table of vulnerabilities I found during my recent testing, and below that is a table of miscellaneous bugs.

### Vulnerabilities

| Issue                                                                                                                     | Impact                                                                                                                                             | Fix                                                                                                                                                                                                          |
| ------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Stored XSS on the home page. Paste titles were echoed into the HTML without escaping.                                     | A title such as `<svg onload=alert(1)>` ran a script for every visitor on the home page.                                                           | I escaped titles and usernames with `htmlspecialchars()`.                                                                                                                                                    |
| Reflected XSS on login and register. The submitted username and password were echoed back into the form without escaping. | Submitting `"><img src=x onerror=alert(3)>` as the username (after editing the maxlength) ran the script. The password field had the same problem. | I escaped usernames and stopped automatically refilling passwords.                                                                                                                                           |
| DOM-based XSS on the password unlock page. Server data was put straight into `innerHTML` without escaping.                | Entering the correct password to a paste titled `<img src=x onerror=alert(2)>` ran the script.                                                     | I changed to use `textContent` and built the author link with JavaScript.                                                                                                                                    |
| Credentials hardcoded.                                                                                                    | All details for the database were committed.                                                                                                       | I moved settings into a git-ignored `config.php`. Nothing of real use was committed as it was XAMPP's default username and password. It should be reset and removed from git history if it had been useable. |
| Expiry bypass. Password protected pastes would never expire.                                                              | Expired pastes could still be unlocked.                                                                                                            | It now checks the expiry before the paste can be interacted with.                                                                                                                                            |

### Miscellaneous bugs

| Issue                              | Impact                                                                                  | Fix                                                                       |
| ---------------------------------- | --------------------------------------------------------------------------------------- | ------------------------------------------------------------------------- |
| Deleted users broke the home page. | One paste by a deleted user would stop the whole list.                                  | It now displays an "Unknown" author instead of stopping.                  |
| Title length not checked.          | Titles over 30 characters would crash the page.                                         | Limited titles on the form and performed proper validation on the server. |
| Username regex.                    | Usernames were allowed to use characters that they were not supposed to be able to use. | Used `[A-Za-z0-9_]` with a matching pattern client-side.                  |

## SQA Advanced Higher project

The first version of this project was made for my SQA Advanced Higher Computing Science project (2025-26). It wasn't ever really supposed to be anything more than that but I have recently had an interest in "improving" it from the version I submitted.

### What I learned during making this project

- PHP was a language I had never used prior to this project and I think I've got some good knowledge with it now
- It greatly improved my planning skills as I had never really sat down and planned out a full project before
- I gained better knowledge on XSS vulnerabilities and how to prevent them
