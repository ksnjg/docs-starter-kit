---
title: Analytics
description: Configure Google Analytics, Plausible, and Microsoft Clarity
seo_title: Analytics Configuration - Docs Starter Kit
order: 4
status: published
---

# Analytics Configuration

Track visitor behavior and documentation usage with built-in analytics integrations.

## Supported Analytics Providers

Docs Starter Kit supports three analytics providers:

| Provider | Type | Privacy |
|----------|------|---------|
| Google Analytics 4 | Full-featured analytics | Standard |
| Plausible Analytics | Privacy-focused | High |
| Microsoft Clarity | Session recording & heatmaps | Standard |

You can use one, multiple, or all providers simultaneously.

## Google Analytics 4

### Setup

1. Create a GA4 property at [Google Analytics](https://analytics.google.com/)
2. Get your Measurement ID (starts with `G-`)
3. Go to **Settings > Advanced** in your admin panel
4. Enter the Measurement ID in **GA4 ID** field
5. Save changes

### Configuration

In the admin panel:

```
GA4 ID: G-XXXXXXXXXX
```

### Features

- Page view tracking
- User engagement metrics
- Traffic sources
- Custom events
- E-commerce tracking (if needed)

## Plausible Analytics

Plausible is a privacy-friendly, lightweight analytics alternative.

### Setup

1. Create an account at [Plausible.io](https://plausible.io/)
2. Add your site domain
3. Go to **Settings > Advanced** in your admin panel
4. Enter your domain in **Plausible Domain** field
5. Save changes

### Configuration

In the admin panel:

```
Plausible Domain: docs.example.com
```

### Benefits

- No cookies required
- GDPR compliant by default
- Lightweight script (~1KB)
- Open source available
- Simple, clean dashboard

## Microsoft Clarity

Clarity provides session recordings and heatmaps to understand user behavior.

### Setup

1. Create a project at [Microsoft Clarity](https://clarity.microsoft.com/)
2. Get your Project ID
3. Go to **Settings > Advanced** in your admin panel
4. Enter the Project ID in **Clarity ID** field
5. Save changes

### Configuration

In the admin panel:

```
Clarity ID: xxxxxxxxxx
```

### Features

- Session recordings
- Heatmaps (click, scroll, movement)
- Rage click detection
- Dead click detection
- JavaScript error tracking
- Free unlimited usage

## Privacy Considerations

### Cookie Consent

If using Google Analytics or Clarity, you may need cookie consent:

- Consider adding a cookie banner
- Respect Do Not Track headers
- Provide opt-out options

### Plausible Advantage

Plausible doesn't use cookies, making it ideal for:

- GDPR compliance without consent banners
- Privacy-conscious documentation sites
- Simpler legal compliance

## Disabling Analytics

To disable all analytics:

1. Go to **Settings > Advanced**
2. Clear all analytics ID fields
3. Save changes

## Verification

### Google Analytics

1. Install the [Google Analytics Debugger](https://chrome.google.com/webstore/detail/google-analytics-debugger) extension
2. Visit your documentation site
3. Check browser console for GA4 events

### Plausible

1. Visit your Plausible dashboard
2. Check "Real-time" view
3. Open your documentation in another tab
4. Verify your visit appears

### Clarity

1. Visit your Clarity dashboard
2. Wait a few hours for data processing
3. Check Recordings for your session

## Environment Variables

You can also configure analytics via environment variables:

```env
# These are managed through the admin panel
# and stored in the settings table
```

The admin panel settings take precedence and are stored in the database.

## Best Practices

1. **Start with Plausible**: If you're unsure, start with Plausible for simplicity
2. **Don't over-track**: Use only the providers you actually need
3. **Review regularly**: Check analytics weekly to understand user behavior
4. **Act on insights**: Use data to improve documentation structure
5. **Respect privacy**: Always consider user privacy when adding tracking
