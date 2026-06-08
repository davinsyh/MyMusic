# AI Coding Rules & Performance Guidelines

This document outlines the strict rules, Dos, and Don'ts for the AI Assistant when generating code for this Laravel Livewire project. The primary goal is maintainability, security, professional UI, and extremely fast performance (Zero-Sluggishness).

## 1. General Coding Rules (DOs)
* **DO** write clean, modular, and DRY (Don't Repeat Yourself) code.
* **DO** follow Laravel best practices: Keep Controllers thin, push complex business logic into Service classes or Actions.
* **DO** use strict typing in PHP (type hinting for arguments and return types).
* **DO** comment complex logic, especially when dealing with the YTMusic API wrapper or audio streaming buffer.
* **DO** wait for user feedback. After completing one step from the PRD, wait for the user to test and confirm before generating code for the next step.

## 2. General Restrictions (DON'Ts)
* **DON'T** hallucinate or invent unsupported third-party packages. If you need a package (e.g., for YTMusic), ask the user or propose a verified, existing package first.
* **DON'T** rewrite or delete existing functional code unless explicitly instructed by the user to refactor.
* **DON'T** hardcode API keys, database credentials, or plain text passwords. Always utilize the `.env` file and Laravel's `config()` helper.
* **DON'T** output the entire file content if only a small change is needed. Use targeted code snippets or diffs to save context window and reading time.

## 3. Strict Performance Optimization (Anti-Sluggish Rules)
To ensure the application loads instantly and feels incredibly fast, you MUST adhere to these performance constraints:

* **Prevent N+1 Query Problems:** **DON'T** ever use lazy loading in loops. **DO** strictly use Eager Loading (`with()`) for all Eloquent relationships.
* **Mandatory Caching with Redis:** **DO** set the `CACHE_DRIVER` to `redis` in the `.env` file. All YTMusic API responses (search queries, track details, top charts) MUST be cached using Redis with an appropriate TTL (e.g., 30-60 minutes). This is critical to bypass API latency and prevent rate-limiting.
* **Livewire Optimization:** * **DON'T** pass huge eloquent models directly to Livewire components if only a few properties are needed. Pass arrays or primitive data types when possible to reduce payload size.
    * **DO** use Livewire's `#[Lazy]` attribute or placeholder loading states (`wire:loading`) for components that fetch external API data, so the initial page renders instantly.
* **Database Indexing:** **DO** ensure proper indexing on database columns that are frequently queried.
* **Pagination & Lazy Rendering:** **DON'T** load all saved libraries or search results at once. **DO** implement pagination or infinite scrolling for lists containing more than 20 items.
* **Asset Optimization:** **DO** ensure Alpine.js and custom scripts are deferred or loaded asynchronously so they don't block the main thread.

## 4. UI/UX Rules & Styling Restrictions
* **NO EMOJIS:** **DON'T** ever use emojis in the HTML, Blade files, or UI for visual representation.
* **USE ICONS:** **DO** strictly use professional vector icon libraries (e.g., inline SVGs, Heroicons, Lucide icons, or the specific icons provided by the component library) for all visual elements.
* **DO** respect the `npx typeui.sh pull doodle` components. Do not write custom inline CSS unless absolutely necessary. Rely on Tailwind CSS utility classes.
* **DO** ensure the UI does not shift abruptly (Cumulative Layout Shift) when Livewire components re-render. Use skeletons or loading spinners gracefully.