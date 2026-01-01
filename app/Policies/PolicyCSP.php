<?php

namespace App\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Presets\Basic;

class PolicyCSP extends Basic
{
    public function configure(Policy $policy): void
    {
        // Call the parent configuration to set up the default directives
        parent::configure($policy);

        // Customize additional directives
        $policy
            ->add(Directive::FONT, ['data:'])
            ->add(Directive::IMG, [
                'data:',
                'ui-avatars.com',
                'avatars.githubusercontent.com',
                'challenges.cloudflare.com',
                'googleusercontent.com',
                'lh3.googleusercontent.com',
                'https://www.w3.org/2000/svg',
            ]);

        // Cloudflare Analytics
        $policy
            ->add(Directive::CONNECT, 'cloudflareinsights.com')
            ->add(Directive::SCRIPT, 'static.cloudflareinsights.com');

        // Cloudflare Turnstile
        $policy
            ->add([Directive::FRAME, Directive::SCRIPT, Directive::CONNECT], 'challenges.cloudflare.com');

        // Clarity
        $policy
            ->add([Directive::CONNECT, Directive::SCRIPT, Directive::IMG], 'https://*.clarity.ms')
            ->add([Directive::CONNECT, Directive::SCRIPT, Directive::IMG], 'https://c.bing.com');

        // Google Services (Analytics, Tag Manager, etc.)
        $policy
            ->add(Directive::SCRIPT, [
                '*.google-analytics.com',
                '*.analytics.google.com',
                '*.googletagmanager.com',
                'googletagmanager.com',
                'tagmanager.google.com',
                'www.googletagmanager.com',
                'static.cloudflareinsights.com',
            ])
            ->add(Directive::CONNECT, [
                '*.google-analytics.com',
                '*.analytics.google.com',
                '*.googletagmanager.com',
                '*.g.doubleclick.net',
                '*.google.com',
                'pagead2.googlesyndication.com',
            ])
            ->add(Directive::IMG, [
                '*.google-analytics.com',
                '*.googletagmanager.com',
                '*.g.doubleclick.net',
                '*.google.com',
                'ssl.gstatic.com',
                'www.gstatic.com',
                'raw.githubusercontent.com',
            ])
            ->add(Directive::FRAME, [
                'td.doubleclick.net',
                'www.googletagmanager.com',
            ])
            ->add(Directive::STYLE, [
                'https://fonts.googleapis.com',
                // Chrome XML viewer inline styles hashes for sitemap.xml
                "'sha256-47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU='",
                "'sha256-p08VBe6m5i8+qtXWjnH/AN3klt1l4uoOLsjNn8BjdQo='",
            ])
            ->add(Directive::FONT, ['https://fonts.gstatic.com']);

        // Plausible Analytics
        $policy
            ->add(Directive::SCRIPT, 'plausible.io')
            ->add(Directive::CONNECT, 'plausible.io');
    }
}
