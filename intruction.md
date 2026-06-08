# Project Specification: Music Streaming App (Spotify Clone)

## 1. Project Overview
Build a full-stack responsive web application for music streaming, inspired by Spotify. The application will use Laravel as the core backend, MySQL for the database, and a dedicated Python Microservice using the `ytmusicapi` library for fetching music tracks.

## 2. Tech Stack & Critical Constraints
* **Main Backend:** Laravel (latest version)
* **Microservice Backend:** Python (FastAPI or Flask) + `ytmusicapi` library.
* **Database:** MySQL
* **Cache:** Redis (Mandatory for caching Python Microservice responses)
* **Authentication:** Standard Laravel Session-based Auth (e.g., Laravel Breeze or custom Auth). No Google OAuth.
* **Frontend UI & State Management:** Laravel Livewire 3 + Alpine.js + Tailwind CSS.
    * *CRITICAL NOTE FOR AI:* To ensure the audio player doesn't stop during page navigation, you MUST utilize **Livewire 3's `wire:navigate`** attribute for all internal routing. Do NOT use standard `href` links that trigger full page reloads.
* **Styling Component:** Execute `npx typeui.sh pull doodle` to pull the specific UI components.
* **Audio Player:** JavaScript/Alpine.js-based persistent audio player globally scoped so it persists across Livewire component updates.

## 3. Database Schema Requirements (MySQL)
All data must be stored locally in MySQL. Create migrations for the following tables/structures:

1.  **Users Table:**
    * `id`, `username` (unique), `name`, `email` (unique), `password`, `avatar` (nullable), `role` (enum: 'user', 'admin' default 'user'), `theme_preference` (enum: 'dark', 'light' default 'dark'), `timestamps`.
2.  **Play_Histories Table:**
    * `id`, `user_id` (foreign key, nullable for guest tracking), `yt_track_id`, `track_title`, `artist_name`, `thumbnail_url`, `played_at` (timestamp).
3.  **Saved_Libraries Table:**
    * `id`, `user_id` (foreign key), `yt_track_id`, `track_title`, `artist_name`, `thumbnail_url`, `saved_at` (timestamp).

## 4. Core Features

### A. Authentication & Guest Access (CRITICAL)
* **Guest Access:** Users DO NOT need to be logged in to search, browse, or listen to music.
* **Standard Login/Registration:** Implement traditional registration and login using `email` or `username` and `password`.
* **Protected Features:** Only logged-in users can save tracks to their Library or record Play History. 

### B. User Application (4 Main Features)
1.  **Beranda (Home) - Public:** Displays greeting, top charts, and recently played.
2.  **Search (Pencarian) - Public:** Livewire-powered real-time search bar querying the Python Microservice.
3.  **Library (Koleksi) - Protected (Auth Required):** Displays "saved" songs and play history.
4.  **Akun (Account):** Guest shows Login/Register. Auth shows profile, theme toggle, and Logout.

### C. Music Player - Public
* A persistent bottom bar player outside the dynamic Livewire `{{ $slot }}`.
* Features: Play, Pause, Next, Previous, Volume Control, Progress Bar.

### D. Admin Dashboard - Protected (Admin Only)
* Manage users, view total plays, and monitor system activities.

## 5. Architectural Flow (Laravel <-> Python)
* Laravel MUST NOT execute Python scripts directly via `shell_exec()`.
* The Python application will run as a standalone REST API on a different port (e.g., `localhost:8000`).
* Laravel will communicate with the Python API via Laravel's `Http` facade.
* Laravel MUST cache the responses from the Python API using Redis to ensure Zero-Sluggishness.

## 6. Execution Steps for AI
Please implement this project step-by-step. Do not move to the next step until the current one is fully functional:
1.  **Step 1:** Setup the Python Microservice. Create a virtual environment, install `ytmusicapi` and FastAPI/Flask. Build the endpoints for searching tracks and getting streaming URLs. Test it independently.
2.  **Step 2:** Setup Laravel, configure MySQL and Redis in `.env`, install Livewire 3, TailwindCSS, and run `npx typeui.sh pull doodle`.
3.  **Step 3:** Setup database migrations and models with relationships.
4.  **Step 4:** Implement standard Session-based Authentication.
5.  **Step 5:** Build the Base Layout with Livewire, Dark/Light mode, and `wire:navigate`.
6.  **Step 6:** Build a Laravel Service class to communicate with the Python Microservice, implementing Redis caching.
7.  **Step 7:** Build the global persistent Audio Player component.
8.  **Step 8:** Build the 4 main Livewire views and integrate the music data.
9.  **Step 9:** Build the Admin Dashboard.