---
title: Introduction
description: Welcome to Docs Starter Kit - an open-source documentation platform
seo_title: Introduction - Docs Starter Kit
order: 1
status: published
---

# Introduction

Welcome to **Docs Starter Kit** - an open-source documentation platform built with Laravel, Vue.js, and TypeScript, using Inertia.js.

## What is Docs Starter Kit?


Docs Starter Kit is a modern, feature-rich documentation platform that you can clone and customize for your own projects. It provides everything you need to create beautiful, functional documentation websites.

### Key Features

- **Dual Content Mode**: Choose between Git-based synchronization or Database CMS
- **Beautiful UI**: Modern, responsive design with dark mode support
- **Rich Editor**: TipTap-powered WYSIWYG editor for content creation
- **Hierarchical Structure**: Organize docs with navigation tabs, groups, and documents
- **Feedback System**: Collect user feedback with customizable forms
- **LLM Ready**: Auto-generated `llms.txt` files for AI consumption
- **SEO Optimized**: Built-in meta tags, sitemaps, and structured data
- **Version History**: Track changes and restore previous versions

## Why Inertia.js?

Docs Starter Kit uses Inertia.js to combine the best of both worlds:

- **No API needed**: Direct communication between Laravel controllers and Vue components
- **SPA experience**: Client-side navigation without full page reloads
- **Simple state management**: Props from Laravel replace complex state stores
- **SEO friendly**: Server-side rendering support for documentation pages
- **Type-safe**: Full TypeScript support from backend to frontend
- **Form handling**: Built-in form helpers with validation error handling

## Tech Stack

### Frontend
- Vue 3 with Composition API
- TypeScript for type safety
- Tailwind CSS for styling
- TipTap for rich text editing
- Vite as build tool

### Backend
- Laravel 12
- PHP 8.4+
- MySQL/PostgreSQL/SQLite
- Redis for caching (optional)
- Laravel Scout for search
- Laravel Fortify for authentication

## Content Management Modes

### Git Mode
Imagine you are a developer or a maintainer of the OWASP Project: you're faced with tons of info about new vulnerabilities probably each week, and you probably need to roll up your sleeves and start coding and documenting new ways to prevent them possibly within that same short amount of time.

If you belong to a team like that, where you must keep updating your Markdown documentation in your GitHub repository as soon as new info comes, or if you're a part of a dev team of just about any size for that matter, as long as you are already comfortable with using GitHub, then our Git Mode is for you.

Git Mode lets you connect to your GitHub repository and automatically sync your documentation with each PR.
It also supports Webhooks, so you can set up a GitHub Action that will notify the app that it needs to update the docs.



### CMS Mode
Now imagine you are a copywriter, a product specialist or a video game fan building a guide, and you need to update your documentation to include new features or changes. You're probably a more visual person and you are more comfortable writing directly from an admin panel, draggin' and droppin' media files and looking at the final result as soon as you press _Save_. If so, then our CMS Mode is for you.

CMS Mode lets you format your texts thanks to a built-in WYSIWYG editor, drag & drop pictures, videos and audio files and re-arrange your documentation pages as you like. All without looking at a single line of code (unless you consider Markdown to be code). Plus, your version history would be stored in the database, instead of Git.



## Next Steps

Ready to get started? Head to the [Installation](/docs/documentation/getting-started/installation) guide to set up your documentation site.
