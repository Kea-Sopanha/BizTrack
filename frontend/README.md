# BizTrack Frontend

This is the React + Vite frontend for BizTrack, a small business inventory and sales dashboard. It connects to the Laravel backend API and powers the login, dashboard, product, sales, and purchase flows.

## Overview

The frontend expects the Laravel backend to run locally on port 8000. Vite is configured to proxy `/api` requests to the backend, so the browser can call the same API paths without CORS issues.

## Stack

- React 19
- Vite 8
- Tailwind CSS
- ESLint

## Getting Started

### Install dependencies

```bash
cd frontend
npm install
```

### Run locally

```bash
npm run dev
```

Open the app at:

```text
http://localhost:5173
```

### Backend requirement

Make sure the Laravel backend is running at:

```text
http://localhost:8000
```

## Available Scripts

```bash
npm run dev
```
Starts the Vite development server.

```bash
npm run build
```
Builds the production bundle.

```bash
npm run preview
```
Previews the production build.

```bash
npm run lint
```
Runs ESLint checks.

## Project Structure

```text
frontend/
├── public/
├── src/
│   ├── assets/
│   ├── lib/
│   ├── pages/
│   ├── App.jsx
│   ├── App.css
│   ├── index.css
│   └── main.jsx
├── index.html
├── package.json
├── vite.config.js
├── tailwind.config.js
├── postcss.config.js
├── eslint.config.js
└── README.md
```

## Notes

- Auth tokens are stored in localStorage under `biztrack_token`.
- Dashboard data refreshes automatically with polling.
- This frontend is meant to be used alongside the Laravel backend in `backend/`.
