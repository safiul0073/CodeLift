# Laravel Auto Updater

A Laravel package that enables remote project updates via downloadable ZIP files. It allows your Laravel application to check for updates, download them, extract, and apply updates programmatically via an admin interface.

---

## 🚀 Features

- Check for available updates from a remote API
- Download and extract ZIP update packages
- Safely apply updates, skipping sensitive directories (like `.env`, `vendor`, etc.)
- Automatically clear cache and run migrations
- Log updates to a remote server (optional)

---

## 📦 Installation

### 1. Add the Package to Your Laravel Project

Add this to your `composer.json`:

```json
"repositories": [
  {
    "type": "path",
    "url": "packages/safiul0073/code-lifter"
  }
]
