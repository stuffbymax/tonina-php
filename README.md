# Log in info for the demo

You can try the demo [here](https://stuffbymax-playground.free.nf/):

* **Username:** `demo`
* **Password:** `demo`

---

# What is Tonina-PHP?

* **Tonina-PHP** is a **lightweight, self-hosted music player** written in **PHP**.
* It allows you to **stream and organize your music** directly from your server without relying on third-party platforms.
* Designed to be **simple and fast**, it’s ideal for small setups or personal use.
* Being PHP-based, it can be easily **deployed on shared hosting** or **local servers**.
* Supports **common audio formats** and provides a **web interface** for easy playback.
* Minimal dependencies, which makes it **easy to install and maintain**.

---

# Installation Guide

1. Upload Tonina-PHP to your server.

2. Run install.php in your browser to complete the setup.

3. Once installed, you have an music server

---

# additional info 

- If you’re not using Apache, you may need to delete the .htaccess file.

- For local installations, .htaccess may not be necessary at all.

- If you’re **not using Apache**, `.htaccess` won’t work. Here’s how to handle it on other servers:

### Nginx
Add rules to your server block in `nginx.conf`:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
### Lighttpd
```Lighttpd
url.rewrite = (
    "^/(.*)$" => "/index.php/$1"
)
```

# PHP Built-in Server (Local Testing) and Running on a Local Network

### local
- No .htaccess needed. Start the server with:
```php
php -S localhost:8000
```

## Find your local IP address:

1. Windows: ipconfig → look for IPv4 Address (e.g., 192.168.1.95)

1. Mac/Linux: ifconfig or ip addr → look for inet under your active network (e.g., 192.168.1.95)

2. Local Network

```php
php -S 192.168.1.95:8000
```
<img width="366" height="53" alt="image" src="https://github.com/user-attachments/assets/594859b8-c961-4e42-b5b5-f80764345dee" />


3. Access Tonina-PHP from another device Open a browser on another device in the same network and go to:
4. http://192.168.1.95:8000

5. Firewall settings:
Ensure incoming connections on the port (default 8000) are allowed.
---

## extra

## how to pronaunce `Tonina`

- T → like English “t”

- o → like English “o” in “pot”

- n → like English “n”

- i → like English “ee” in “see”

- n → like English “n”

- a → like English “a” in “father”

- “Toh-nee-nah”





