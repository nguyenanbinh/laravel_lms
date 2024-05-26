<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseGoal;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Display the details of a course.
     *
     * @param int $id The ID of the course.
     * @param string $slug The slug of the course.
     * @return \Illuminate\View\View The view for the course details.
     */
    public function courseDetails($id, $slug)
    {
        // Retrieve the course with the given ID.
        $course = Course::find($id);

        // Retrieve the course goals for the given course ID.
        $goals = CourseGoal::where('course_id', $id)
            ->orderBy('id', 'DESC') // Order the goals by ID in descending order
            ->get();

        // Retrieve the instructor's courses in descending order
        $ins_id = $course->instructor_id;
        $instructorCourses = Course::where('instructor_id', $ins_id)
            ->orderBy('id', 'DESC') // Order the courses by ID in descending order
            ->get();

        // Retrieve the latest categories
        $categories = Category::latest()->get();

        // Retrieve related courses
        $cat_id = $course->category_id;
        $relatedCourses = Course::where('category_id', $cat_id)
            ->where('id', '!=', $id) // Exclude the current course
            ->orderBy('id', 'DESC') // Order the courses by ID in descending order
            ->limit(3) // Limit the number of related courses to 3
            ->get();

        // Return the view for the course details with the course and goals.
        return view(
            'frontend.course.course_details', // The view to be returned
            compact('course', 'goals', 'instructorCourses', 'categories', 'relatedCourses') // The data to be passed to the view
        );
    } // end method courseDetails

    /**
     * Display a list of courses in a category.
     *
     * @param int $id The ID of the category.
     * @param string $slug The slug of the category.
     * @return \Illuminate\View\View The view for the category course list.
     */
    public function categoryCourse($id, $slug)
    {
        // Retrieve the courses in the given category with the status set to 1 (active).
        $courses = Course::where('category_id', $id)->where('status', '1')->get();

        // Retrieve the category with the given ID.
        $category = Category::where('id', $id)->first();

        // Retrieve the latest categories.
        $categories = Category::latest()->get();

        // Return the view for the category course list with the courses, category, and categories.
        return view('frontend.category.category_all',
            compact('courses', 'category', 'categories')
        );
    } // end method categoryCourse

    /**
     * Display a list of courses in a subcategory.
     *
     * @param int $id The ID of the subcategory.
     * @param string $slug The slug of the subcategory.
     * @return \Illuminate\View\View The view for the subcategory course list.
     */
    public function subCategoryCourse($id, $slug)
    {
        // Retrieve the courses in the given subcategory with the status set to 1 (active).
        $courses = Course::where('subcategory_id', $id)->where('status', '1')->get();

        // Retrieve the subcategory with the given ID.
        $subcategory = SubCategory::where('id', $id)->first();

        // Retrieve the latest categories.
        $categories = Category::latest()->get();

        // Return the view for the subcategory course list with the courses, subcategory, and categories.
        return view('frontend.category.subcategory_all',
            compact('courses', 'subcategory', 'categories')
        );
    } // end method subCategoryCourse

    /**
     * Display the details of an instructor.
     *
     * @param int $id The ID of the instructor.
     * @return \Illuminate\View\View The view for the instructor details.
     */
    public function InstructorDetails($id)
    {
        /**
         * Retrieve the instructor with the given ID.
         */
        $instructor = User::find($id);

        /**
         * Retrieve the courses taught by the given instructor.
         */
        $courses = Course::where('instructor_id', $id)->get();

        /**
         * Return the view for the instructor details with the instructor and courses.
         */
        return view('frontend.instructor.instructor_details', compact('instructor', 'courses'));
    } // end method InstructorDetails
}
