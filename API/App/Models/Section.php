<?php

require_once __DIR__ . '/../../config/Database.php';


class Section
{
    // Properties
    private $id;
    private $courseId;
    private $taId;
    private $time;
    private $location;

    private $db; // Database connection instance

    // Constructor
    public function __construct($db, $id = null, $courseId = '', $taId = '', $time = '', $location = '')
    {
        $this->db = $db;
        $this->id = $id;
        $this->courseId = $courseId;
        $this->taId = $taId;
        $this->time = $time;
        $this->location = $location;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getCourseId()
    {
        return $this->courseId;
    }

    public function getTAId()
    {
        return $this->taId;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getLocation()
    {
        return $this->location;
    }

    // Setters
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
    }

    public function setTAId($taId)
    {
        $this->taId = $taId;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    // Get a section by ID
    public static function getById($db, $id)
    {
        $stmt = $db->prepare("SELECT * FROM sections WHERE section_id = :section_id");
        $stmt->execute(['section_id' => $id]);

        $sectionData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sectionData) {
            return new Section(
                $db,
                $sectionData['section_id'],
                $sectionData['course_id'],
                $sectionData['ta_id'],
                $sectionData['time'],
                $sectionData['location']
            );
        } else {
            return null; // Section not found
        }
    }

    // Get all sections
    public static function getAll($db)
    {
        $stmt = $db->query("SELECT * FROM sections");

        $sectionsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sections = [];

        foreach ($sectionsData as $sectionData) {
            $sections[] = new Section(
                $db,
                $sectionData['section_id'],
                $sectionData['course_id'],
                $sectionData['ta_id'],
                $sectionData['time'],
                $sectionData['location']
            );
        }

        return $sections;
    }

    // Save section to the database
    public function save()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE sections SET course_id = :course_id, ta_id = :ta_id, time = :time, location = :location WHERE section_id = :section_id");
            $stmt->execute([
                'section_id' => $this->id,
                'course_id' => $this->courseId,
                'ta_id' => $this->taId,
                'time' => $this->time,
                'location' => $this->location
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO sections (course_id, ta_id, time, location) VALUES (:course_id, :ta_id, :time, :location)");
            $stmt->execute([
                'course_id' => $this->courseId,
                'ta_id' => $this->taId,
                'time' => $this->time,
                'location' => $this->location
            ]);
            $this->id = $this->db->lastInsertId();
        }
    }

    // Delete section from the database
    public function delete()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("DELETE FROM sections WHERE section_id = :section_id");
            $stmt->execute(['section_id' => $this->id]);
        }
    }
    // Get the associated course for this section
    public function getCourse($db)
    {
        return Course::getById($db, $this->courseId);
    }

    // Get the associated teaching assistant for this section
    public function getTeachingAssistant($db)
    {
        return TeachingAssistant::getById($db, $this->taId);
    }
    // Get section by number
    public static function getByNumber($db, $sectionNumber)
    {
        $stmt = $db->prepare("SELECT * FROM student WHERE section_group_number = ?");
        $stmt->bind_param('i', $sectionNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
}

?>
