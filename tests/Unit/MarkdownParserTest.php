<?php

namespace Tests\Unit;

use App\Services\MarkdownParser;
use Tests\TestCase;

class MarkdownParserTest extends TestCase
{
    private MarkdownParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new MarkdownParser;
    }

    public function test_parse_extracts_frontmatter_title()
    {
        $markdown = <<<'MD'
---
title: My Custom Title
---

# Content heading
Some content here.
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertEquals('My Custom Title', $result['title']);
    }

    public function test_parse_generates_title_from_path_when_not_in_frontmatter()
    {
        $markdown = <<<'MD'
# Content heading
Some content here.
MD;

        $result = $this->parser->parse($markdown, 'docs/getting-started.md');

        $this->assertEquals('Getting started', $result['title']);
    }

    public function test_parse_extracts_slug_from_frontmatter()
    {
        $markdown = <<<'MD'
---
title: Test
slug: custom-slug
---

Content
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertEquals('custom-slug', $result['slug']);
    }

    public function test_parse_generates_slug_from_path_when_not_in_frontmatter()
    {
        $markdown = <<<'MD'
# Content
MD;

        $result = $this->parser->parse($markdown, 'docs/getting-started/installation.md');

        $this->assertEquals('installation', $result['slug']);
    }

    public function test_parse_extracts_seo_description()
    {
        $markdown = <<<'MD'
---
title: Test
description: This is the SEO description
---

Content
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertEquals('This is the SEO description', $result['seo_description']);
    }

    public function test_parse_extracts_order()
    {
        $markdown = <<<'MD'
---
title: Test
order: 5
---

Content
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertEquals(5, $result['order']);
    }

    public function test_parse_defaults_order_to_zero()
    {
        $markdown = <<<'MD'
---
title: Test
---

Content
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertEquals(0, $result['order']);
    }

    public function test_parse_extracts_status()
    {
        $markdown = <<<'MD'
---
title: Test
status: draft
---

Content
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertEquals('draft', $result['status']);
    }

    public function test_parse_defaults_status_to_published()
    {
        $markdown = <<<'MD'
---
title: Test
---

Content
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertEquals('published', $result['status']);
    }

    public function test_parse_extracts_hierarchy_from_path()
    {
        $markdown = '# Content';

        $result = $this->parser->parse($markdown, 'docs/getting-started/installation.md');

        $this->assertEquals(['getting-started', 'installation'], $result['hierarchy']);
    }

    public function test_parse_extracts_hierarchy_with_deep_nesting()
    {
        $markdown = '# Content';

        $result = $this->parser->parse($markdown, 'docs/api/v2/endpoints/users.md');

        $this->assertEquals(['api', 'v2', 'endpoints', 'users'], $result['hierarchy']);
    }

    public function test_parse_stores_git_path()
    {
        $markdown = '# Content';
        $path = 'docs/getting-started/installation.md';

        $result = $this->parser->parse($markdown, $path);

        $this->assertEquals($path, $result['git_path']);
    }

    public function test_parse_separates_content_from_frontmatter()
    {
        $markdown = <<<'MD'
---
title: Test
---

This is the body content.
MD;

        $result = $this->parser->parse($markdown, 'docs/test.md');

        $this->assertStringContainsString('This is the body content.', $result['content']);
        $this->assertStringNotContainsString('title:', $result['content']);
    }

    public function test_render_to_html_converts_markdown()
    {
        $markdown = '# Heading';

        $html = $this->parser->renderToHtml($markdown);

        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('Heading', $html);
    }

    public function test_render_to_html_supports_tables()
    {
        $markdown = <<<'MD'
| Header 1 | Header 2 |
|----------|----------|
| Cell 1   | Cell 2   |
MD;

        $html = $this->parser->renderToHtml($markdown);

        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<th>', $html);
        $this->assertStringContainsString('<td>', $html);
    }

    public function test_render_to_html_supports_strikethrough()
    {
        $markdown = '~~deleted text~~';

        $html = $this->parser->renderToHtml($markdown);

        $this->assertStringContainsString('<del>', $html);
    }

    public function test_render_to_html_supports_task_lists()
    {
        $markdown = <<<'MD'
- [x] Completed task
- [ ] Incomplete task
MD;

        $html = $this->parser->renderToHtml($markdown);

        $this->assertStringContainsString('type="checkbox"', $html);
    }

    public function test_parse_meta_file_parses_json()
    {
        $json = '{"title": "Section Title", "order": 1}';

        $result = $this->parser->parseMetaFile($json);

        $this->assertEquals(['title' => 'Section Title', 'order' => 1], $result);
    }

    public function test_parse_meta_file_returns_empty_array_on_invalid_json()
    {
        $invalidJson = 'not valid json';

        $result = $this->parser->parseMetaFile($invalidJson);

        $this->assertEquals([], $result);
    }
}
