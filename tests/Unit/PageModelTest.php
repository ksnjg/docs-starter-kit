<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\PageVersion;
use App\Models\SystemConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_generates_slug_from_title_on_create()
    {
        $page = Page::factory()->create([
            'title' => 'Getting Started Guide',
            'slug' => null,
        ]);

        $this->assertEquals('getting-started-guide', $page->slug);
    }

    public function test_page_keeps_explicit_slug()
    {
        $page = Page::factory()->create([
            'title' => 'Getting Started Guide',
            'slug' => 'custom-slug',
        ]);

        $this->assertEquals('custom-slug', $page->slug);
    }

    public function test_page_has_parent_relationship()
    {
        $parent = Page::factory()->create(['type' => 'group']);
        $child = Page::factory()->child($parent)->create();

        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_page_has_children_relationship()
    {
        $parent = Page::factory()->create(['type' => 'group']);
        Page::factory()->child($parent)->count(3)->create();

        $this->assertCount(3, $parent->children);
    }

    public function test_children_are_ordered_by_order_field()
    {
        $parent = Page::factory()->create(['type' => 'group']);
        Page::factory()->child($parent)->create(['order' => 2, 'title' => 'Second']);
        Page::factory()->child($parent)->create(['order' => 1, 'title' => 'First']);
        Page::factory()->child($parent)->create(['order' => 3, 'title' => 'Third']);

        $children = $parent->children;

        $this->assertEquals('First', $children[0]->title);
        $this->assertEquals('Second', $children[1]->title);
        $this->assertEquals('Third', $children[2]->title);
    }

    public function test_scope_published_filters_by_status()
    {
        Page::factory()->published()->count(3)->create();
        Page::factory()->draft()->count(2)->create();

        $published = Page::published()->get();

        $this->assertCount(3, $published);
    }

    public function test_scope_draft_filters_by_status()
    {
        Page::factory()->published()->count(3)->create();
        Page::factory()->draft()->count(2)->create();

        $drafts = Page::draft()->get();

        $this->assertCount(2, $drafts);
    }

    public function test_scope_root_level_filters_pages_without_parent()
    {
        $parent = Page::factory()->create();
        Page::factory()->child($parent)->count(2)->create();

        $rootPages = Page::rootLevel()->get();

        $this->assertCount(1, $rootPages);
    }

    public function test_scope_from_git_filters_by_source()
    {
        Page::factory()->fromGit()->count(2)->create();
        Page::factory()->create(['source' => 'cms']);

        $gitPages = Page::fromGit()->get();

        $this->assertCount(2, $gitPages);
    }

    public function test_is_published_returns_correct_boolean()
    {
        $published = Page::factory()->published()->create();
        $draft = Page::factory()->draft()->create();

        $this->assertTrue($published->isPublished());
        $this->assertFalse($draft->isPublished());
    }

    public function test_is_draft_returns_correct_boolean()
    {
        $published = Page::factory()->published()->create();
        $draft = Page::factory()->draft()->create();

        $this->assertFalse($published->isDraft());
        $this->assertTrue($draft->isDraft());
    }

    public function test_is_from_git_returns_correct_boolean()
    {
        $gitPage = Page::factory()->fromGit()->create();
        $cmsPage = Page::factory()->create(['source' => 'cms']);

        $this->assertTrue($gitPage->isFromGit());
        $this->assertFalse($cmsPage->isFromGit());
    }

    public function test_publish_updates_status()
    {
        $page = Page::factory()->draft()->create();

        $page->publish();

        $this->assertEquals('published', $page->fresh()->status);
    }

    public function test_unpublish_updates_status()
    {
        $page = Page::factory()->published()->create();

        $page->unpublish();

        $this->assertEquals('draft', $page->fresh()->status);
    }

    public function test_archive_updates_status()
    {
        $page = Page::factory()->published()->create();

        $page->archive();

        $this->assertEquals('archived', $page->fresh()->status);
    }

    public function test_create_version_creates_page_version()
    {
        $page = Page::factory()->create(['content' => 'Original content']);

        $version = $page->createVersion();

        $this->assertInstanceOf(PageVersion::class, $version);
        $this->assertEquals('Original content', $version->content);
        $this->assertEquals(1, $version->version_number);
    }

    public function test_create_version_increments_version_number()
    {
        $page = Page::factory()->create();

        $version1 = $page->createVersion();
        $version2 = $page->createVersion();
        $version3 = $page->createVersion();

        $this->assertEquals(1, $version1->version_number);
        $this->assertEquals(2, $version2->version_number);
        $this->assertEquals(3, $version3->version_number);
    }

    public function test_get_full_path_returns_slug_path()
    {
        $navigation = Page::factory()->create(['type' => 'navigation', 'slug' => 'docs']);
        $group = Page::factory()->child($navigation)->create(['type' => 'group', 'slug' => 'getting-started']);
        $page = Page::factory()->child($group)->create(['slug' => 'installation']);

        Cache::flush();

        $fullPath = $page->getFullPath();

        $this->assertEquals('docs/getting-started/installation', $fullPath);
    }

    public function test_get_breadcrumbs_returns_hierarchy()
    {
        $parent = Page::factory()->create(['title' => 'Parent', 'slug' => 'parent']);
        $child = Page::factory()->child($parent)->create(['title' => 'Child', 'slug' => 'child']);

        $breadcrumbs = $child->breadcrumbs;

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals('Parent', $breadcrumbs[0]['title']);
        $this->assertEquals('Child', $breadcrumbs[1]['title']);
    }

    public function test_build_tree_returns_hierarchical_array()
    {
        $parent = Page::factory()->create(['type' => 'navigation', 'parent_id' => null]);
        Page::factory()->child($parent)->count(2)->create();

        $tree = Page::buildTree();

        $this->assertCount(1, $tree);
        $this->assertArrayHasKey('children', $tree[0]);
        $this->assertCount(2, $tree[0]['children']);
    }

    public function test_should_be_searchable_for_published_documents()
    {
        $publishedDoc = Page::factory()->published()->create(['type' => 'document']);
        $draftDoc = Page::factory()->draft()->create(['type' => 'document']);
        $publishedGroup = Page::factory()->published()->create(['type' => 'group']);

        $this->assertTrue($publishedDoc->shouldBeSearchable());
        $this->assertFalse($draftDoc->shouldBeSearchable());
        $this->assertFalse($publishedGroup->shouldBeSearchable());
    }

    public function test_to_searchable_array_returns_expected_fields()
    {
        Cache::flush();

        $page = Page::factory()->published()->create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => '<p>Some content</p>',
            'seo_description' => 'SEO desc',
            'type' => 'document',
        ]);

        $searchable = $page->toSearchableArray();

        $this->assertEquals($page->id, $searchable['id']);
        $this->assertEquals('Test Page', $searchable['title']);
        $this->assertEquals('test-page', $searchable['slug']);
        $this->assertEquals('Some content', $searchable['content']);
        $this->assertEquals('document', $searchable['type']);
    }

    public function test_get_edit_on_github_url_returns_null_for_cms_pages()
    {
        $page = Page::factory()->create(['source' => 'cms']);

        $this->assertNull($page->edit_on_git_hub_url);
    }

    public function test_get_edit_on_github_url_returns_url_for_git_pages()
    {
        SystemConfig::create([
            'content_mode' => 'git',
            'git_repository_url' => 'https://github.com/owner/repo',
            'git_branch' => 'main',
        ]);
        SystemConfig::clearCache();

        $page = Page::factory()->fromGit()->create([
            'git_path' => 'docs/test.md',
        ]);

        $url = $page->edit_on_git_hub_url;

        $this->assertEquals('https://github.com/owner/repo/edit/main/docs/test.md', $url);
    }
}
