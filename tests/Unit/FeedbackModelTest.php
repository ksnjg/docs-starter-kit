<?php

namespace Tests\Unit;

use App\Models\FeedbackForm;
use App\Models\FeedbackResponse;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_form_has_responses_relationship()
    {
        $form = FeedbackForm::factory()->create();
        $page = Page::factory()->create();

        FeedbackResponse::factory()->withForm($form)->forPage($page)->count(3)->create();

        $this->assertCount(3, $form->responses);
    }

    public function test_feedback_form_scope_active()
    {
        FeedbackForm::factory()->create(['is_active' => true]);
        FeedbackForm::factory()->create(['is_active' => true]);
        FeedbackForm::factory()->inactive()->create();

        $activeForms = FeedbackForm::active()->get();

        $this->assertCount(2, $activeForms);
    }

    public function test_feedback_form_scope_for_positive()
    {
        FeedbackForm::factory()->forPositive()->create();
        FeedbackForm::factory()->create(['trigger_type' => 'always']);
        FeedbackForm::factory()->forNegative()->create();

        $positiveForms = FeedbackForm::forPositive()->get();

        $this->assertCount(2, $positiveForms);
    }

    public function test_feedback_form_scope_for_negative()
    {
        FeedbackForm::factory()->forNegative()->create();
        FeedbackForm::factory()->create(['trigger_type' => 'always']);
        FeedbackForm::factory()->forPositive()->create();

        $negativeForms = FeedbackForm::forNegative()->get();

        $this->assertCount(2, $negativeForms);
    }

    public function test_feedback_form_is_for_positive()
    {
        $positiveForm = FeedbackForm::factory()->forPositive()->create();
        $alwaysForm = FeedbackForm::factory()->create(['trigger_type' => 'always']);
        $negativeForm = FeedbackForm::factory()->forNegative()->create();

        $this->assertTrue($positiveForm->isForPositive());
        $this->assertTrue($alwaysForm->isForPositive());
        $this->assertFalse($negativeForm->isForPositive());
    }

    public function test_feedback_form_is_for_negative()
    {
        $negativeForm = FeedbackForm::factory()->forNegative()->create();
        $alwaysForm = FeedbackForm::factory()->create(['trigger_type' => 'always']);
        $positiveForm = FeedbackForm::factory()->forPositive()->create();

        $this->assertTrue($negativeForm->isForNegative());
        $this->assertTrue($alwaysForm->isForNegative());
        $this->assertFalse($positiveForm->isForNegative());
    }

    public function test_feedback_response_has_page_relationship()
    {
        $page = Page::factory()->create();
        $response = FeedbackResponse::factory()->forPage($page)->create();

        $this->assertEquals($page->id, $response->page->id);
    }

    public function test_feedback_response_has_form_relationship()
    {
        $form = FeedbackForm::factory()->create();
        $page = Page::factory()->create();
        $response = FeedbackResponse::factory()->withForm($form)->forPage($page)->create();

        $this->assertEquals($form->id, $response->feedbackForm->id);
    }

    public function test_feedback_response_scope_helpful()
    {
        $page = Page::factory()->create();
        FeedbackResponse::factory()->forPage($page)->helpful()->count(3)->create();
        FeedbackResponse::factory()->forPage($page)->notHelpful()->count(2)->create();

        $helpfulResponses = FeedbackResponse::helpful()->get();

        $this->assertCount(3, $helpfulResponses);
    }

    public function test_feedback_response_scope_not_helpful()
    {
        $page = Page::factory()->create();
        FeedbackResponse::factory()->forPage($page)->helpful()->count(3)->create();
        FeedbackResponse::factory()->forPage($page)->notHelpful()->count(2)->create();

        $notHelpfulResponses = FeedbackResponse::notHelpful()->get();

        $this->assertCount(2, $notHelpfulResponses);
    }

    public function test_feedback_response_is_helpful_method()
    {
        $page = Page::factory()->create();
        $helpful = FeedbackResponse::factory()->forPage($page)->helpful()->create();
        $notHelpful = FeedbackResponse::factory()->forPage($page)->notHelpful()->create();

        $this->assertTrue($helpful->isHelpful());
        $this->assertFalse($notHelpful->isHelpful());
    }

    public function test_feedback_response_is_not_helpful_method()
    {
        $page = Page::factory()->create();
        $helpful = FeedbackResponse::factory()->forPage($page)->helpful()->create();
        $notHelpful = FeedbackResponse::factory()->forPage($page)->notHelpful()->create();

        $this->assertFalse($helpful->isNotHelpful());
        $this->assertTrue($notHelpful->isNotHelpful());
    }

    public function test_feedback_form_fields_are_cast_to_array()
    {
        $form = FeedbackForm::factory()->withDetailedFields()->create();

        $this->assertIsArray($form->fields);
        $this->assertCount(3, $form->fields);
        $this->assertEquals('rating', $form->fields[0]['name']);
    }

    public function test_feedback_response_form_data_is_cast_to_array()
    {
        $page = Page::factory()->create();
        $form = FeedbackForm::factory()->create();

        $response = FeedbackResponse::factory()->withForm($form)->forPage($page)->create();

        $this->assertIsArray($response->form_data);
    }

    public function test_page_has_feedback_responses_relationship()
    {
        $page = Page::factory()->create();
        FeedbackResponse::factory()->forPage($page)->count(5)->create();

        $this->assertCount(5, $page->feedbackResponses);
    }
}
