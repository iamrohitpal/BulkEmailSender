# JobApply SaaS &bull; Job Application Email Sender

JobApply SaaS is a modern, high-performance web application built on **Laravel** and **Tailwind CSS** designed to help job seekers dispatch personalized, high-deliverability cover letters and resumes to multiple companies at scale using their own SMTP server configuration.

---

## 🚀 Key Features

*   **Custom SMTP Profiles:** Manage and test your own secure SMTP profiles (TLS/SSL configurations supported).
*   **Bulk CSV Campaigns:** Import a CSV file of prospective employers with key merge variables (`email`, `company_name`, `website`, `hr_name`, `position`, `resume_link`).
*   **Plain Text Custom Body & Subject:** Reverted from rich text editor to clean, native email composer fields for ultra-resilient styling on all modern desktop and mobile email clients.
*   **Dynamic Merge Fields:** One-click merge tags (`{{position}}`, `{{company_name}}`, etc.) dynamically inserted at the cursor's location.
*   **Resume Attachment System:** Choose between uploading a local resume file or passing a sharing link from Google Drive.
*   **PWA (Progressive Web App) Enabled:** Standalone install capabilities with offline capability using Service Workers.
*   **Fully Mobile-Responsive:** Complete mobile navigation, slide-over hamburger menus, and bottom navigations optimized for small screens.
*   **Dynamic Progress Tracking:** Polling updates active campaigns in real-time.

---

## ⚡ PWA & Mobile Optimization

The app is fully optimized for mobile devices and supports installing as a Progressive Web App (PWA):
1.  **Web App Manifest:** Serves metadata, name, theme colors (`#10b981`), icons (`192x192`, `512x512`), and scopes for native device wrapping.
2.  **Service Worker (`public/sw.js`):** Built-in network-first caching wrapper that caches local resources and static assets for quick offline page loading and asset fallback.
3.  **Responsive Layout:**
    *   **Desktop:** Left collapsible sidebar navigation.
    *   **Mobile:** Sticky top bar, slide-out drawer menu, and bottom navigation bar matching standard mobile app patterns.

---

## 📦 Getting Started

### Prerequisites

*   PHP 8.2+
*   Composer
*   MySQL/MariaDB database
*   Node.js & NPM (for frontend compilation via Vite)

### Installation

1.  **Clone & Install Dependencies:**
    ```bash
    git clone https://github.com/iamrohitpal/BulkEmailSender.git
    cd BulkEmailSender
    composer install
    npm install
    ```

2.  **Configure environment:**
    Copy `.env.example` to `.env` and set up database/application credentials:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3.  **Run Migrations:**
    ```bash
    php artisan migrate
    ```

4.  **Launch Frontend Compiler & Server:**
    ```bash
    npm run dev
    # In another terminal window:
    php artisan serve
    ```

5.  **Run Automated Tests:**
    ```bash
    php artisan test
    ```
