<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseGoal;
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
}
