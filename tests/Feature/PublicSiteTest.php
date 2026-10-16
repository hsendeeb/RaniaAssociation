<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_featured_published_courses(): void
    {
        $featured = Course::factory()->published()->create(['is_featured' => true, 'title' => 'دورة مميزة']);
        Course::factory()->create(['title' => 'دورة مسودة']);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Home')
                ->has('featuredCourses', 1)
                ->where('featuredCourses.0.title', $featured->title)
            );
    }

    public function test_courses_index_only_lists_published_courses(): void
    {
        $published = Course::factory()->published()->create(['title' => 'دورة منشورة']);
        Course::factory()->create(['title' => 'دورة مسودة']);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Courses/Index')
                ->has('courses.data', 1)
                ->where('courses.data.0.title', $published->title)
            );
    }

    public function test_courses_can_be_filtered_by_category(): void
    {
        $category = Category::factory()->create();
        $matching = Course::factory()->published()->create(['category_id' => $category->id]);
        Course::factory()->published()->create();

        $this->get(route('courses.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Courses/Index')
                ->has('courses.data', 1)
                ->where('courses.data.0.slug', $matching->slug)
            );
    }

    public function test_courses_can_be_searched_by_title(): void
    {
        Course::factory()->published()->create(['title' => 'أساسيات البرمجة']);
        Course::factory()->published()->create(['title' => 'دورة التصميم']);

        $this->get(route('courses.index', ['q' => 'البرمجة']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Courses/Index')
                ->has('courses.data', 1)
            );
    }

    public function test_course_show_returns_published_courses_with_lessons(): void
    {
        $course = Course::factory()->published()->has(Lesson::factory()->count(2))->create();

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Courses/Show')
                ->where('course.slug', $course->slug)
                ->has('course.lessons', 2)
            );
    }

    public function test_draft_courses_return_a_404(): void
    {
        $course = Course::factory()->create();

        $this->get(route('courses.show', $course->slug))->assertNotFound();
    }

    public function test_instructors_index_lists_active_instructors(): void
    {
        Instructor::factory()->create();
        Instructor::factory()->create(['is_active' => false]);

        $this->get(route('instructors.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Instructors/Index')
                ->has('instructors', 1)
            );
    }

    public function test_instructor_show_lists_only_published_courses(): void
    {
        $instructor = Instructor::factory()->create();
        Course::factory()->published()->create(['instructor_id' => $instructor->id]);
        Course::factory()->create(['instructor_id' => $instructor->id]);

        $this->get(route('instructors.show', $instructor))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('site/Instructors/Show')
                ->where('instructor.id', $instructor->id)
                ->has('instructor.courses', 1)
            );
    }

    public function test_inactive_instructors_return_a_404(): void
    {
        $instructor = Instructor::factory()->create(['is_active' => false]);

        $this->get(route('instructors.show', $instructor))->assertNotFound();
    }

    public function test_about_and_contact_pages_render(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('site/About'));

        $this->get(route('contact'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('site/Contact'));
    }

    public function test_contact_form_validates_input(): void
    {
        $this->post(route('contact.send'), [])
            ->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_contact_form_submits_successfully(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.send'), [
                'name' => 'أحمد',
                'email' => 'ahmed@example.com',
                'message' => 'مرحبًا، أرغب بالتسجيل في دورة.',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');
    }
}
