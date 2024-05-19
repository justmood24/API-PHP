<?php

require_once __DIR__ . '/../Models/Course.php';
require_once __DIR__ . '/../Models/StudentCourse.php';

class CourseController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db->getConnection();
    }

    //All Courses
    public function index()
    {
        try {
            $courses = Course::getAll($this->db);
            return json_encode(['success' => true, 'data' => $courses]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    //Get Course By ID
    public function show($id)
    {
        try {
            $course = Course::getById($this->db, $id);
            if ($course) {
                return json_encode(['success' => true, 'data' => $course]);
            } else {
                return json_encode(['success' => false, 'error' => 'Course not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    // Enroll a student in a course
    public function enroll($data)
    {
        try {
            // Ensure required data is provided
            if (!isset($data['student_id']) || !isset($data['course_id']) || !isset($data['semester'])) {
                throw new Exception("Student ID, Course ID, and Semester are required");
            }

            // Create new StudentCourse instance
            $studentCourse = new StudentCourse($this->db, null, $data['student_id'], $data['course_id'], $data['semester']);
            $result = $studentCourse->store();
            return $result;
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Disenroll a student from a course
    public function disenroll($student_course_id)
    {
        try {
            $studentCourse = new StudentCourse($this->db, $student_course_id);
            $result = $studentCourse->destroy();
            return $result;
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    
    public function store($data)
    {
        try {
            if (!isset($data['course_code'])) {
                throw new Exception('Course code is required');
            }
            $course = new Course(null, $data['course_code']);
            $course->save();
            return json_encode(['success' => true, 'message' => 'Course created successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function update($id, $data)
    {
        try {
            $course = Course::getById($this->db, $id);
            if ($course) {
                if (!isset($data['course_code'])) {
                    throw new Exception('Course code is required');
                }
                $course->setCourseCode($data['course_code']);
                $course->save();
                return json_encode(['success' => true, 'message' => 'Course updated successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Course not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $course = Course::getById($this->db, $id);
            if ($course) {
                $course->delete();
                return json_encode(['success' => true, 'message' => 'Course deleted successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Course not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
