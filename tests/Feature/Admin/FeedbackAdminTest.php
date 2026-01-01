<?php

namespace Tests\Feature\Admin;

use App\Models\FeedbackForm;
use App\Models\FeedbackResponse;
use App\Models\Page;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackAdminTest extends TestCase
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

    public function test_guests_cannot_access_feedback_index()
    {
        $response = $this->get(route('admin.feedback.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_feedback_index()
    {
        $response = $this->actingAs($this->user)->get(route('admin.feedback.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/feedback/Index'));
    }

    public function test_feedback_index_displays_responses()
    {
        $page = Page::factory()->create();
        FeedbackResponse::factory()->forPage($page)->count(5)->create();

        $response = $this->actingAs($this->user)->get(route('admin.feedback.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('responses.data', 5)
            ->has('stats')
        );
    }

    public function test_feedback_index_can_filter_by_page()
    {
        $page1 = Page::factory()->create();
        $page2 = Page::factory()->create();

        FeedbackResponse::factory()->forPage($page1)->count(3)->create();
        FeedbackResponse::factory()->forPage($page2)->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('admin.feedback.index', ['page_id' => $page1->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('responses.data', 3)
        );
    }

    public function test_feedback_index_can_filter_by_helpful_status()
    {
        $page = Page::factory()->create();
        FeedbackResponse::factory()->forPage($page)->helpful()->count(4)->create();
        FeedbackResponse::factory()->forPage($page)->notHelpful()->count(2)->create();

        $response = $this->actingAs($this->user)->get(route('admin.feedback.index', ['is_helpful' => 'true']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('responses.data', 4)
        );
    }

    public function test_users_can_delete_feedback_response()
    {
        $feedback = FeedbackResponse::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.feedback.destroy', $feedback));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('feedback_responses', ['id' => $feedback->id]);
    }

    public function test_users_can_access_forms_page()
    {
        FeedbackForm::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('admin.feedback.forms'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/feedback/Forms')
            ->has('forms', 3)
        );
    }

    public function test_users_can_create_feedback_form()
    {
        $response = $this->actingAs($this->user)->get(route('admin.feedback.forms.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/feedback/CreateForm'));
    }

    public function test_users_can_store_feedback_form()
    {
        $formData = [
            'name' => 'Test Form',
            'trigger_type' => 'negative',
            'fields' => [
                ['name' => 'comment', 'type' => 'textarea', 'label' => 'Comments', 'required' => false],
            ],
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->post(route('admin.feedback.forms.store'), $formData);

        $response->assertRedirect(route('admin.feedback.forms'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedback_forms', [
            'name' => 'Test Form',
            'trigger_type' => 'negative',
        ]);
    }

    public function test_users_can_edit_feedback_form()
    {
        $form = FeedbackForm::factory()->create();

        $response = $this->actingAs($this->user)->get(route('admin.feedback.forms.edit', $form));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/feedback/EditForm')
            ->has('form')
        );
    }

    public function test_users_can_update_feedback_form()
    {
        $form = FeedbackForm::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($this->user)->put(route('admin.feedback.forms.update', $form), [
            'name' => 'Updated Name',
            'trigger_type' => 'always',
            'fields' => $form->fields,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.feedback.forms'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedback_forms', [
            'id' => $form->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_users_can_delete_feedback_form()
    {
        $form = FeedbackForm::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('admin.feedback.forms.destroy', $form));

        $response->assertRedirect(route('admin.feedback.forms'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('feedback_forms', ['id' => $form->id]);
    }

    public function test_feedback_export_returns_csv()
    {
        $page = Page::factory()->create();
        FeedbackResponse::factory()->forPage($page)->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('admin.feedback.export', ['format' => 'csv']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_feedback_export_returns_json()
    {
        $page = Page::factory()->create();
        FeedbackResponse::factory()->forPage($page)->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('admin.feedback.export', ['format' => 'json']));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }
}
