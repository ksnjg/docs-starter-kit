<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        // Navigation Tabs (top-level categories like Mintlify)
        $documentation = Page::create([
            'title' => 'Documentation',
            'slug' => 'documentation',
            'type' => 'navigation',
            'icon' => 'book',
            'status' => 'published',
            'order' => 1,
            'is_default' => true,
        ]);

        $guides = Page::create([
            'title' => 'Guides',
            'slug' => 'guides',
            'type' => 'navigation',
            'icon' => 'compass',
            'status' => 'published',
            'order' => 2,
        ]);

        $apiReference = Page::create([
            'title' => 'API Reference',
            'slug' => 'api-reference',
            'type' => 'navigation',
            'icon' => 'code',
            'status' => 'published',
            'order' => 3,
        ]);

        $changelog = Page::create([
            'title' => 'Changelog',
            'slug' => 'changelog',
            'type' => 'navigation',
            'icon' => 'history',
            'status' => 'published',
            'order' => 4,
        ]);

        // Groups under Documentation
        $gettingStarted = Page::create([
            'title' => 'Getting Started',
            'slug' => 'getting-started',
            'type' => 'group',
            'icon' => 'rocket',
            'parent_id' => $documentation->id,
            'status' => 'published',
            'order' => 1,
            'is_expanded' => true,
        ]);

        $configuration = Page::create([
            'title' => 'Configuration',
            'slug' => 'configuration',
            'type' => 'group',
            'icon' => 'settings',
            'parent_id' => $documentation->id,
            'status' => 'published',
            'order' => 2,
        ]);

        $customization = Page::create([
            'title' => 'Customization',
            'slug' => 'customization',
            'type' => 'group',
            'icon' => 'palette',
            'parent_id' => $documentation->id,
            'status' => 'published',
            'order' => 3,
        ]);

        // Documents under Getting Started
        Page::create([
            'title' => 'Introduction',
            'slug' => 'introduction',
            'type' => 'document',
            'parent_id' => $gettingStarted->id,
            'content' => $this->introductionContent(),
            'status' => 'published',
            'order' => 1,
            'seo_title' => 'Introduction - Docs Starter Kit',
            'seo_description' => 'Welcome to Docs Starter Kit - an open-source documentation platform.',
        ]);

        Page::create([
            'title' => 'Installation',
            'slug' => 'installation',
            'type' => 'document',
            'parent_id' => $gettingStarted->id,
            'content' => $this->installationContent(),
            'status' => 'published',
            'order' => 2,
            'seo_title' => 'Installation - Docs Starter Kit',
            'seo_description' => 'Learn how to install Docs Starter Kit.',
        ]);

        Page::create([
            'title' => 'Quick Start',
            'slug' => 'quick-start',
            'type' => 'document',
            'parent_id' => $gettingStarted->id,
            'content' => $this->quickStartContent(),
            'status' => 'published',
            'order' => 3,
            'seo_title' => 'Quick Start - Docs Starter Kit',
            'seo_description' => 'Get up and running with Docs Starter Kit in minutes.',
        ]);

        // Documents under Configuration
        Page::create([
            'title' => 'Environment Variables',
            'slug' => 'environment-variables',
            'type' => 'document',
            'parent_id' => $configuration->id,
            'content' => $this->envContent(),
            'status' => 'published',
            'order' => 1,
        ]);

        Page::create([
            'title' => 'Content Modes',
            'slug' => 'content-modes',
            'type' => 'document',
            'parent_id' => $configuration->id,
            'content' => $this->contentModesContent(),
            'status' => 'published',
            'order' => 2,
        ]);

        // Documents under Customization
        Page::create([
            'title' => 'Theming',
            'slug' => 'theming',
            'type' => 'document',
            'parent_id' => $customization->id,
            'content' => $this->themingContent(),
            'status' => 'published',
            'order' => 1,
        ]);

        // Groups under Guides
        $tutorials = Page::create([
            'title' => 'Tutorials',
            'slug' => 'tutorials',
            'type' => 'group',
            'icon' => 'graduation-cap',
            'parent_id' => $guides->id,
            'status' => 'published',
            'order' => 1,
        ]);

        Page::create([
            'title' => 'Creating Your First Page',
            'slug' => 'creating-first-page',
            'type' => 'document',
            'parent_id' => $tutorials->id,
            'content' => $this->firstPageContent(),
            'status' => 'published',
            'order' => 1,
        ]);

        // Groups under API Reference
        $pagesApi = Page::create([
            'title' => 'Pages',
            'slug' => 'pages',
            'type' => 'group',
            'icon' => 'file-text',
            'parent_id' => $apiReference->id,
            'status' => 'published',
            'order' => 1,
        ]);

        Page::create([
            'title' => 'List Pages',
            'slug' => 'list-pages',
            'type' => 'document',
            'parent_id' => $pagesApi->id,
            'content' => $this->listPagesApiContent(),
            'status' => 'published',
            'order' => 1,
        ]);

        Page::create([
            'title' => 'Get Page',
            'slug' => 'get-page',
            'type' => 'document',
            'parent_id' => $pagesApi->id,
            'content' => $this->getPageApiContent(),
            'status' => 'published',
            'order' => 2,
        ]);

        // Document under Changelog
        Page::create([
            'title' => 'v1.0.0',
            'slug' => 'v1-0-0',
            'type' => 'document',
            'parent_id' => $changelog->id,
            'content' => $this->changelogContent(),
            'status' => 'published',
            'order' => 1,
        ]);
    }

    private function introductionContent(): string
    {
        return <<<'MARKDOWN'
# Introduction

Welcome to **Docs Starter Kit** - an open-source documentation platform built with Laravel, Vue.js, and TypeScript.

## What is Docs Starter Kit?

Docs Starter Kit is a modern, feature-rich documentation platform that you can clone and customize for your own projects. It's designed to be:

- **Easy to set up** - Get started in minutes
- **Highly customizable** - Theme, layout, and branding options
- **Developer-friendly** - Built with modern tools you already know
- **Production-ready** - SEO optimized and performant

## Key Features

- **Dual Content Mode**: Choose between Git-based or Database CMS
- **Beautiful UI**: Modern, responsive design with dark mode
- **Rich Editor**: TipTap-powered WYSIWYG editor
- **Feedback System**: Collect user feedback on pages
- **LLM Ready**: Auto-generated llms.txt files
MARKDOWN;
    }

    private function installationContent(): string
    {
        return <<<'MARKDOWN'
# Installation

Follow these steps to install Docs Starter Kit on your local machine.

## Requirements

- PHP 8.4 or higher
- Composer
- Node.js 18+ and npm
- MySQL, PostgreSQL, or SQLite

## Steps

```bash
# Clone the repository
git clone https://github.com/crony-io/docs-starter-kit.git
cd docs-starter-kit

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed

# Start development server
npm run dev
```

Then visit `http://localhost:8000` to see your documentation site.
MARKDOWN;
    }

    private function quickStartContent(): string
    {
        return <<<'MARKDOWN'
# Quick Start

Get up and running with Docs Starter Kit in just a few steps.

## 1. Access the Admin Panel

Navigate to `/dashboard` and log in with:
- **Email**: admin@example.com
- **Password**: password

## 2. Create Your First Page

1. Go to **Pages** in the sidebar
2. Click **Create Page**
3. Choose the page type (Navigation, Group, or Document)
4. Fill in the title and content
5. Click **Create Page**

## 3. Customize Your Site

Visit **Settings** to customize:
- Site name and logo
- Theme colors
- Navigation structure
MARKDOWN;
    }

    private function envContent(): string
    {
        return <<<'MARKDOWN'
# Environment Variables

Configure your Docs Starter Kit installation using environment variables.

## Basic Configuration

```env
APP_NAME="My Documentation"
APP_URL=https://docs.example.com
APP_ENV=production
APP_DEBUG=false
```

## Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=docs
DB_USERNAME=root
DB_PASSWORD=secret
```

## Content Mode

```env
# Use 'cms' for database mode or 'git' for GitHub sync
DOCS_CONTENT_MODE=cms
```
MARKDOWN;
    }

    private function contentModesContent(): string
    {
        return <<<'MARKDOWN'
# Content Modes

Docs Starter Kit supports two content management modes.

## CMS Mode (Default)

All content is stored in the database and managed through the admin panel.

**Best for:**
- Non-technical teams
- Visual editing requirements
- Quick content updates

## Git Mode

Content is synced from a GitHub repository.

**Best for:**
- Developer documentation
- Open source projects
- Version-controlled content

```env
DOCS_CONTENT_MODE=git
DOCS_GIT_REPO=https://github.com/your-org/docs
DOCS_GIT_BRANCH=main
DOCS_GIT_TOKEN=your_github_token
```
MARKDOWN;
    }

    private function themingContent(): string
    {
        return <<<'MARKDOWN'
# Theming

Customize the appearance of your documentation site.

## Theme Settings

Access theme settings in the admin panel under **Settings > Theme**.

### Colors

- **Primary Color**: Main accent color
- **Secondary Color**: Supporting color
- **Background**: Page background color

### Typography

- **Font Family**: Choose from Google Fonts
- **Code Font**: Monospace font for code blocks

### Dark Mode

Toggle dark mode support for your site.

## Custom CSS

Inject custom CSS through the admin settings for advanced customization.
MARKDOWN;
    }

    private function firstPageContent(): string
    {
        return <<<'MARKDOWN'
# Creating Your First Page

This tutorial walks you through creating your first documentation page.

## Step 1: Navigate to Pages

Click on **Pages** in the admin sidebar.

## Step 2: Click Create Page

Click the **Create Page** button in the top right.

## Step 3: Choose Page Type

Select the appropriate type:
- **Navigation**: Top-level tab (Documentation, Guides, etc.)
- **Group**: Sidebar section/folder
- **Document**: Actual content page

## Step 4: Fill in Details

1. Enter a **Title**
2. The **Slug** is auto-generated
3. Select a **Parent** (for groups and documents)
4. Write your **Content** using the editor
5. Set the **Status** to Published

## Step 5: Save

Click **Create Page** to save your new page.
MARKDOWN;
    }

    private function listPagesApiContent(): string
    {
        return <<<'MARKDOWN'
# List Pages

Retrieve a list of all published documentation pages.

## Endpoint

```http
GET /api/v1/pages
```

## Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction",
      "slug": "introduction",
      "type": "document"
    }
  ]
}
```

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | Filter by page type |
| `parent_id` | integer | Filter by parent ID |
MARKDOWN;
    }

    private function getPageApiContent(): string
    {
        return <<<'MARKDOWN'
# Get Page

Retrieve a single page by its slug or path.

## Endpoint

```http
GET /api/v1/pages/{path}
```

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `path` | string | Full path to the page |

## Response

```json
{
  "data": {
    "id": 1,
    "title": "Introduction",
    "slug": "introduction",
    "content": "# Introduction\n\nWelcome to...",
    "type": "document"
  }
}
```
MARKDOWN;
    }

    private function changelogContent(): string
    {
        return <<<'MARKDOWN'
# v1.0.0

*Released: December 2024*

## Features

- Initial release of Docs Starter Kit
- Dual content mode (CMS and Git)
- TipTap rich text editor
- Theme customization
- Feedback collection system
- LLM-ready documentation files
- Mobile-responsive design
- SEO optimization

## What's Next

- Search integration
- Multi-language support
- Version control for pages
- API documentation generator
MARKDOWN;
    }
}
