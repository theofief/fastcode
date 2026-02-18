# � Gestion‑Entreprise

**Product code training & reference tool • Symfony backend**

A small Symfony application created for a hypothetical grocery business,
allowing staff to look up and memorize product codes and details.

---

## 🧩 Project Overview

This repo contains a backend system that stores product information,
codes and metadata. It’s intended as a learning aid for employees in an
épicerie (grocery store) to retain item codes and quickly retrieve details.

Key aspects:

- Symfony-based backend with routing and entity management
- Doctrine ORM for product & user records
- Simple authentication to access the dashboard
- Basic Twig views for listing and searching products

The idea is not a full commerce app but a code‑reference and training tool.

---

## 🌐 Features

- ✅ Add, edit and delete products (code, name, description)
- ✅ User login/registration with Symfony security
- ✅ Search product by code or name
- ✅ View product list with pagination
- ✅ Breadcrumb navigation and Bootstrap styles

---

## ⚙️ Technologies

- **PHP 8 / Symfony 6** – framework, controllers, security
- **Doctrine ORM** – database layer
- **Twig** – templating engine
- **Bootstrap 5** – basic UI styling
- **MySQL** – database (configured for MAMP)

---

## 📁 Repository Structure

```
gestion-entreprise/
├── public/            # front controller + assets
├── src/
│   ├── Controller/    # Symfony controllers (HomeController, ...)   
│   ├── Entity/        # Doctrine entities (Product, User)
│   ├── Repository/    # Custom query logic
│   └── Security/      # access control, handlers
├── templates/         # Twig views (base, product/list, ...)
├── config/            # Symfony configuration files
├── migrations/        # Doctrine migration classes
├── tests/             # PHPUnit tests
├── var/               # cache & logs
├── bin/               # console & phpunit
├── composer.json      # PHP dependencies
└── README.md          # this file
```

---

## 🚀 Setup Instructions

1. Clone the repository:
   ```bash
   git clone <repo-url> gestion-entreprise
   cd gestion-entreprise
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy `.env` to `.env.local` and adjust the
   `DATABASE_URL` (MAMP example):
   ```
   DATABASE_URL="mysql://root:root@127.0.0.1:8889/gestion_entreprise?serverVersion=8.0"
   ```
4. Create the database and run migrations:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```
5. Start Symfony server:
   ```bash
   symfony serve
   # or: php -S 127.0.0.1:8000 -t public
   ```
6. Open `http://127.0.0.1:8000` in your browser and log in.

---

## 📝 Usage Notes

- Use the dashboard to add or search products by code/name.
- The app is meant for practice and as a quick reference; no complex
  e‑commerce features are included.

---

## 📜 License

MIT License – free to use, modify and share with attribution.

---

## 👨‍💻 Author

Théo Fief — Computer Science student & backend developer


Powered by Symfony, built for learning and coding fun.