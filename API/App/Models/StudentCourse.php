<?php

require_once __DIR__ . '/../../config/Database.php';

class StudentCourse
{
    private $db;
    private $student_course_id;
    private $student_id;
    private $course_id;
    private $semester;

    public function __construct($db, $student_course_id = null, $student_id = null, $course_id = null, $semester = null)
    {
        $this->db = $db;
        $this->student_course_id = $student_course_id;
        $this->student_id = $student_id;
        $this->course_id = $course_id;
        $this->semester = $semester;
    }

    // Getters
    public function getId()
    {
        return $this->student_course_id;
    }

    public function getStudentId()
    {
        return $this->student_id;
    }

    public function getCourseId()
    {
        return $this->course_id;
    }

    public function getSemester()
    {
        return $this->semester;
    }

    // Setters
    public function setStudentId($student_id)
    {
        $this->student_id = $student_id;
    }

    public function setCourseId($course_id)
    {
        $this->course_id = $course_id;
    }

    public function setSemester($semester)
    {
        $this->semester = $semester;
    }

    // Create a new student_course entry
    public function store()
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO student_course (student_id, course_id, semester) VALUES (:student_id, :course_id, :semester)");
            $stmt->execute([
                'student_id' => $this->student_id,
                'course_id' => $this->course_id,
                'semester' => $this->semester
            ]);
            $this->student_course_id = $this->db->lastInsertId();
            return json_encode(['success' => true, 'message' => 'Student enrolled in course successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Remove a student_course entry
    public function destroy()
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM student_course WHERE student_course_id = :student_course_id");
            $stmt->execute(['student_course_id' => $this->student_course_id]);
            return json_encode(['success' => true, 'message' => 'Student disenrolled from course successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get all courses for a student
    public static function getCoursesByStudentId($db, $student_id)
    {
        try {
            $stmt = $db->prepare("SELECT * FROM student_course WHERE student_id = :student_id");
            $stmt->execute(['student_id' => $student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get all students for a course
    public static function getStudentsByCourseId($db, $course_id)
    {
        try {
            $stmt = $db->prepare("SELECT * FROM student_course WHERE course_id = :course_id");
            $stmt->execute(['course_id' => $course_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
