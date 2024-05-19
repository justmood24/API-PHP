<?php

require_once __DIR__ . '/../../config/Database.php';

class Course
{
    // Properties
    private $db;
    private $id;
    private $courseCode;
     
    // Constructor
    public function __construct($db, $id = null, $courseCode = '')
    {
        $this->db = $db;
        $this->id = $id;
        $this->courseCode = $courseCode;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getCourseCode()
    {
        return $this->courseCode;
    }

    // Setters
    public function setCourseCode($courseCode)
    {
        $this->courseCode = $courseCode;
    }

    // Get a course by ID
    public static function getById($db, $id)
    {
        $stmt = $db->prepare("SELECT * FROM courses WHERE course_id = :course_id");
        $stmt->execute(['course_id' => $id]);

        $courseData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($courseData) {
            return new Course(
                $db,
                $courseData['course_id'],
                $courseData['course_code']
            );
        } else {
            return null; // Course not found
        }
    }

    // Get all courses
    public static function getAll($db)
    {
        $stmt = $db->query("SELECT * FROM courses");

        $coursesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courses = [];

        foreach ($coursesData as $courseData) {
            $courses[] = new Course(
                $db,
                $courseData['course_id'],
                $courseData['course_code']
            );
        }

        return $courses;
    }

    // Save course to the database
    public function save()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE courses SET course_code = :course_code WHERE course_id = :course_id");
            $stmt->execute([
                'course_id' => $this->id,
                'course_code' => $this->courseCode
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO courses (course_code) VALUES (:course_code)");
            $stmt->execute([
                'course_code' => $this->courseCode
            ]);
            $this->id = $this->db->lastInsertId();
        }
    }

    // Delete course from the database
    public function delete()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("DELETE FROM courses WHERE course_id = :course_id");
            $stmt->execute(['course_id' => $this->id]);
        }
    }
}

?>
