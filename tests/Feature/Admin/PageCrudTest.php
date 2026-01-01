<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();

        SystemConfig::create(['content_mode' => 'cms', 'setup_completed' => true]);
        SystemConfig::clearCache();
    }

    public function test_guests_cannot_access_pages_index()
    {
        $response = $this->get(route('admin.pages.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_pages_index()
    {
        $response = $this->actingAs($this->user)->get(route('admin.pages.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/pages/Index'));
    }

    public function test_pages_index_displays_pages()
    {
        $pages = Page::factory()->count(5)->create();

        $response = $this->actingAs($this->user)->get(route('admin.pages.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/pages/Index')
            ->has('pages.data', 5)
        );
    }

    public function test_pages_index_can_filter_by_status()
    {
        Page::factory()->published()->count(3)->create();
        Page::factory()->draft()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('admin.pages.index', ['status' => 'draft']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('pages.data', 2)
        );
    }

    public function test_pages_index_can_filter_by_type()
    {
        Page::factory()->create(['type' => 'document']);
        Page::factory()->create(['type' => 'navigation']);
        Page::factory()->create(['type' => 'group']);

        $response = $this->actingAs($this->user)->get(route('admin.pages.index', ['type' => 'document']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('pages.data', 1)
        );
    }

    public function test_pages_index_can_search()
    {
        Page::factory()->create(['title' => 'Installation Guide']);
        Page::factory()->create(['title' => 'Getting Started']);

        $response = $this->actingAs($this->user)->get(route('admin.pages.index', ['search' => 'Installation']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('pages.data', 1)
        );
    }

    public function test_authenticated_users_can_access_create_page()
    {
        $response = $this->actingAs($this->user)->get(route('admin.pages.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/pages/Create'));
    }

    public function test_users_can_create_a_page()
    {
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'type' => 'document',
            'content' => 'This is test content',
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.pages.store'), $pageData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'source' => 'cms',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_page_creation_requires_title()
    {
        $response = $this->actingAs($this->user)->post(route('admin.pages.store'), [
            'slug' => 'test-page',
            'type' => 'document',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_users_can_edit_a_page()
    {
        $page = Page::factory()->create();

        $response = $this->actingAs($this->user)->get(route('admin.pages.edit', $page));

        $response->assertStatus(200);
        $response->assertInertia(fn ($inertiaPage) => $inertiaPage
            ->component('admin/pages/Edit')
            ->has('page')
            ->where('page.id', $page->id)
        );
    }

    public function test_users_can_update_a_page()
    {
        $page = Page::factory()->create(['title' => 'Original Title']);

        $response = $this->actingAs($this->user)->put(route('admin.pages.update', $page), [
            'title' => 'Updated Title',
            'slug' => $page->slug,
            'type' => $page->type,
            'content' => 'Updated content',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_update_creates_version_history()
    {
        $page = Page::factory()->create(['content' => 'Original content']);

        $this->actingAs($this->user)->put(route('admin.pages.update', $page), [
            'title' => $page->title,
            'slug' => $page->slug,
            'type' => $page->type,
            'content' => 'Updated content',
            'status' => $page->status,
        ]);

        $this->assertDatabaseHas('page_versions', [
            'page_id' => $page->id,
            'content' => 'Original content',
        ]);
    }

    public function test_users_can_delete_a_page()
    {
        $page = Page::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.pages.destroy', $page));

        $response->assertRedirect(route('admin.pages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_cannot_delete_page_with_children()
    {
        $parent = Page::factory()->create(['type' => 'group']);
        Page::factory()->child($parent)->create();

        $response = $this->actingAs($this->user)->delete(route('admin.pages.destroy', $parent));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('pages', ['id' => $parent->id]);
    }

    public function test_users_can_duplicate_a_page()
    {
        $page = Page::factory()->create(['title' => 'Original Page']);

        $response = $this->actingAs($this->user)->post(route('admin.pages.duplicate', $page));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'title' => 'Original Page (Copy)',
            'status' => 'draft',
        ]);
    }

    public function test_users_can_publish_a_page()
    {
        $page = Page::factory()->draft()->create();

        $response = $this->actingAs($this->user)->post(route('admin.pages.publish', $page));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $page->refresh();
        $this->assertEquals('published', $page->status);
    }

    public function test_users_can_unpublish_a_page()
    {
        $page = Page::factory()->published()->create();

        $response = $this->actingAs($this->user)->post(route('admin.pages.unpublish', $page));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $page->refresh();
        $this->assertEquals('draft', $page->status);
    }

    public function test_users_can_reorder_pages()
    {
        $page1 = Page::factory()->create(['order' => 0]);
        $page2 = Page::factory()->create(['order' => 1]);

        $response = $this->actingAs($this->user)->post(route('admin.pages.reorder'), [
            'pages' => [
                ['id' => $page1->id, 'order' => 1],
                ['id' => $page2->id, 'order' => 0],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, $page1->fresh()->order);
        $this->assertEquals(0, $page2->fresh()->order);
    }
}
