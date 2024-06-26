<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseGoal;
use App\Models\CourseLecture;
use App\Models\CourseSection;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get the instructor id
        $id = Auth::user()->id;

        // Get the instructor courses
        $courses = Course::where('instructor_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        // Return the view with the courses
        return view('instructor.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        // Get all categories
        $categories = Category::latest()->get();

        // Return the view with the categories
        return view('instructor.courses.create', compact('categories'));
    }


    /**
     * Store Course
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Validation
        $request->validate([
            'video' => 'required|mimes:mp4|max:10000',
        ]);

        // Image Intervention
        $image = $request->file('course_image');
        $save_url = null;
        if ($image) {
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            // create new image instance
            $imageManager = ImageManager::gd()->read($image);
            $imageManager->resize(370, 246);
            $imageManager->toJpeg(80)->save(('upload/course/thumbnail/' . $name_gen));

            $save_url = 'upload/course/thumbnail/' . $name_gen;
        }

        // Video
        $video = $request->file('video');
        $videoName = time() . '.' . $video->getClientOriginalExtension();
        $video->move(public_path('upload/course/video/'), $videoName);
        $save_video = 'upload/course/video/' . $videoName;

        // Course Data
        $course_id = Course::insertGetId([

            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'instructor_id' => Auth::user()->id,
            'course_title' => $request->course_title,
            'course_name' => $request->course_name,
            'course_name_slug' => strtolower(str_replace(' ', '-', $request->course_name)),
            'description' => $request->description,
            'video' => $save_video,

            'label' => $request->label,
            'duration' => $request->duration,
            'resources' => $request->resources,
            'certificate' => $request->certificate,
            'selling_price' => $request->selling_price,
            'discount_price' => $request->discount_price,
            'prerequisites' => $request->prerequisites,

            'bestseller' => $request->bestseller,
            'featured' => $request->featured,
            'highestrated' => $request->highestrated,
            'status' => 1,
            'course_image' => $save_url,
            'created_at' => Carbon::now(),
        ]);
        /// Course Goals Add Form

        $goals = count($request->course_goals);
        if ($goals != NULL) {
            for ($i = 0; $i < $goals; $i++) {
                $courseGoal = new CourseGoal();
                $courseGoal->course_id = $course_id;
                $courseGoal->goal_name = $request->course_goals[$i];
                $courseGoal->save();
            }
        }
        /// End Course Goals Add Form

        $notification = array(
            'message' => 'Course Inserted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('instructor.courses.index')->with($notification);
    }

    /**
     * Edit Course
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id): \Illuminate\Contracts\View\View
    {
        // Get Course By ID
        $course = Course::find($id);

        // Get All Category
        $categories = Category::latest()->get();

        // Get All Sub Category
        $subcategories = SubCategory::latest()->get();

        $goals = CourseGoal::where('course_id',$id)->get();
        // Return Edit Course View
        return view('instructor.courses.edit', compact('course', 'categories', 'subcategories', 'goals'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $course = Course::find($request->course_id);

        $course->update([
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'course_title' => $request->course_title,
            'course_name' => $request->course_name,
            'course_name_slug' => strtolower(str_replace(' ', '-', $request->course_name)),
            'description' => $request->description,
            'label' => $request->label,
            'duration' => $request->duration,
            'resources' => $request->resources,
            'certificate' => $request->certificate,
            'selling_price' => $request->selling_price,
            'discount_price' => $request->discount_price,
            'prerequisites' => $request->prerequisites,
            'bestseller' => $request->bestseller,
            'featured' => $request->featured,
            'highestrated' => $request->highestrated,
        ]);

        $notification = [
            'message' => 'Course Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('instructor.courses.index')->with($notification);
    }

    public function delete($id){
        $course = Course::find($id);
        @unlink($course->course_image);
        @unlink($course->video);

        Course::find($id)->delete();

        $goalsData = CourseGoal::where('course_id',$id)->get();
        foreach ($goalsData as $item) {
            $item->goal_name;
            CourseGoal::where('course_id',$id)->delete();
        }

        $notification = array(
            'message' => 'Course Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }

    /**
     * Get Sub Category By Category ID
     *
     * @param int $category_id
     * @return string
     */
    public function getSubCategory(int $category_id): string
    {
        // Get sub category by category id
        $subcat = SubCategory::where('category_id', $category_id)
            ->orderBy('subcategory_name', 'ASC')
            ->get();

        // Return sub category json data
        return json_encode($subcat);
    }

    public function updateCourseImage(Request $request){

        $course_id = $request->id;
        $oldImage = $request->old_img;

        $image = $request->file('course_image');
        if ($image) {
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            // create new image instance
            $imageManager = ImageManager::gd()->read($image);
            $imageManager->resize(370, 246);
            $imageManager->toJpeg(80)->save(('upload/course/thumbnail' . $name_gen));
            $save_url = 'upload/course/thumbnail' . $name_gen;

            if (file_exists($oldImage)) {
                unlink($oldImage);
            }

            Course::find($course_id)->update([
                'course_image' => $save_url,
                'updated_at' => Carbon::now(),
            ]);

            $notification = array(
                'message' => 'Course Image Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        return redirect()->back();

    }

    public function updateCourseVideo(Request $request){

        $course_id = $request->vid;
        $oldVideo = $request->old_vid;

        $video = $request->file('video');
        $videoName = time().'.'.$video->getClientOriginalExtension();
        $video->move(public_path('upload/course/video/'),$videoName);
        $save_video = 'upload/course/video/'.$videoName;

        if (file_exists($oldVideo)) {
            unlink($oldVideo);
        }

        Course::find($course_id)->update([
            'video' => $save_video,
            'updated_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Course Video Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }

    public function updateCourseGoal(Request $request){

        $cid = $request->id;

        if ($request->course_goals == NULL) {
            return redirect()->back();
        } else{

            CourseGoal::where('course_id',$cid)->delete();

            $courseGoals = count($request->course_goals);

                for ($i=0; $i < $courseGoals; $i++) {
                    $courseGoal = new CourseGoal();
                    $courseGoal->course_id = $cid;
                    $courseGoal->goal_name = $request->course_goals[$i];
                    $courseGoal->save();
                }  // end for
        } // end else

        $notification = array(
            'message' => 'Course Goals Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }// End Method

    /**
     * Create a new resource in the specified format.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function createCourseLecture($id){

        // Get the course data
        $course = Course::find($id);
        $sections = CourseSection::where('course_id',$id)->latest()->get();

        // Return the view with the course data
        return view('instructor.courses.section.add_course_lecture', compact('course', 'sections'));

    }// End Method

    /**
     * Store a newly created Course Section in the database.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCourseSection(Request $request){

        // Get the course id
        $cid = $request->id;

        // Insert the new course section
        CourseSection::create([
            'course_id' => $cid,
            'section_title' => $request->section_title,
        ], ['timestamp' => true]);

        // Create the success notification
        $notification = array(
            'message' => 'Course Section Added Successfully',
            'alert-type' => 'success'
        );

        // Redirect the user to the previous page with the notification
        return redirect()->back()->with($notification);

    }// End Method

    /**
     * Save a new course lecture
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveLecture(Request $request){
        // Validate the request data
        $validator = Validator::make($request->input(), [
            'lecture_title' => 'required',
            'course_id' => 'required',
            'section_id' => 'required',
            'lecture_url' => 'required',
            'content' => 'required',
        ]);

        // If the validation fails, return a json response with the errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new course lecture instance
        $lecture = new CourseLecture();

        // Set the course, section, title, url, and content attributes
        $lecture->course_id = $request->course_id;
        $lecture->section_id = $request->section_id;
        $lecture->lecture_title = $request->lecture_title;
        $lecture->url = $request->lecture_url;
        $lecture->content = $request->content;

        // Save the lecture to the database
        $lecture->save();

        // Return a json response with a success message
        return response()->json(['success' => 'Lecture Saved Successfully']);

    }// End Method

    public function editLecture($id){

        $lecture = CourseLecture::find($id);
        return view('instructor.courses.lecture.edit_course_lecture',compact('lecture'));

    }// End Method

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCourseLecture(Request $request){
        // Get the id of the lecture to be updated
        $lid = $request->id;

        // Update the lecture in the database
        CourseLecture::find($lid)->update([
            'lecture_title' => $request->lecture_title,
            'url' => $request->url,
            'content' => $request->content,
        ]);

        // Create the success notification
        $notification = array(
            'message' => 'Course Lecture Updated Successfully',
            'alert-type' => 'success'
        );

        // Redirect the user to the previous page with the notification
        return redirect()->back()->with($notification);

    }// End Method

    /**
     * Delete a course lecture
     *
     * @param int $id The id of the lecture to be deleted
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteLecture($id){

        // Delete the lecture from the database
        CourseLecture::find($id)->delete();

        // Create the success notification
        $notification = array(
            'message' => 'Course Lecture Delete Successfully',
            'alert-type' => 'success'
        );
        // Redirect the user to the previous page with the notification
        return redirect()->back()->with($notification);

    }// End Method


    /**
     * Delete a course section
     *
     * @param int $id The id of the section to be deleted
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteSection($id){

        // Get the section from the database
        $section = CourseSection::find($id);

        // Delete all the lectures that belongs to the section from the database
        $section->lectures()->delete();

        // Delete the section from the database
        $section->delete();

        // Create the success notification
        $notification = array(
            'message' => 'Course Section Delete Successfully',
            'alert-type' => 'success'
        );
        // Redirect the user to the previous page with the notification
        return redirect()->back()->with($notification);

    }// End Method
}
