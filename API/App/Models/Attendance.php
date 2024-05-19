<?php

require_once __DIR__ . '/../../config/Database.php';

class Attendance
{
    // Properties
    private $id;
    private $studentId;
    private $sectionId;
    private $courseId;
    private $weekNumber;
    private $sectionGroupNumber;
    private $absenceStatus;
    private $timestamp;

    private $db; // Database connection instance

    // Constructor
    public function __construct($db, $id = null, $studentId = '', $sectionId = '', $courseId = '', $weekNumber = '', $sectionGroupNumber = '', $absenceStatus = '', $timestamp = '')
    {
        $this->db = $db;

        if (!is_numeric($studentId) || !is_numeric($sectionId) || !is_numeric($courseId) || !is_numeric($weekNumber) || !is_numeric($sectionGroupNumber)) {
            throw new InvalidArgumentException("Invalid student ID, section ID, course ID, week number, or section group number");
        }

        $this->id = $id;
        $this->studentId = $studentId;
        $this->sectionId = $sectionId;
        $this->courseId = $courseId;
        $this->weekNumber = $weekNumber;
        $this->sectionGroupNumber = $sectionGroupNumber;
        $this->absenceStatus = $absenceStatus;
        $this->timestamp = $timestamp;
    }
    // Getters
    public function getId()
    {
        return $this->id;
    }
    
    public function getStudentId()
    {
        return $this->studentId;
    }

    public function getSectionId()
    {
        return $this->sectionId;
    }

    public function getCourseId()
    {
        return $this->courseId;
    }

    public function getWeekNumber()
    {
        return $this->weekNumber;
    }

    public function getSectionGroupNumber()
    {
        return $this->sectionGroupNumber;
    }

    public function getAbsenceStatus()
    {
        return $this->absenceStatus;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    // Setters
    public function setStudentId($studentId)
    {
        $this->studentId = $studentId;
    }

    public function setSectionId($sectionId)
    {
        $this->sectionId = $sectionId;
    }

    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
    }

    public function setWeekNumber($weekNumber)
    {
        $this->weekNumber = $weekNumber;
    }

    public function setSectionGroupNumber($sectionGroupNumber)
    {
        $this->sectionGroupNumber = $sectionGroupNumber;
    }

    public function setAbsenceStatus($absenceStatus)
    {
        $this->absenceStatus = $absenceStatus;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    // Get attendance by ID
    public static function getById($db, $id)
    {
        $stmt = $db->prepare("SELECT * FROM attendance WHERE attendance_id = :attendance_id");
        $stmt->execute(['attendance_id' => $id]);

        $attendanceData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($attendanceData) {
            return new Attendance(
                $db,
                $attendanceData['attendance_id'],
                $attendanceData['student_id'],
                $attendanceData['section_id'],
                $attendanceData['course_id'],
                $attendanceData['week_number'],
                $attendanceData['section_group_number'],
                $attendanceData['absence_status'],
                $attendanceData['timestamp']
            );
        } else {
            return null; // Attendance not found
        }
    }

    // Get all attendance records
    public static function getAll($db)
    {
        $stmt = $db->query("SELECT * FROM attendance");

        $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $attendance = [];

        foreach ($attendanceData as $data) {
            $attendance[] = new Attendance(
                $db,
                $data['attendance_id'],
                $data['student_id'],
                $data['section_id'],
                $data['course_id'],
                $data['week_number'],
                $data['section_group_number'],
                $data['absence_status'],
                $data['timestamp']
            );
        }

        return $attendance;
    }

    // Save attendance record to the database
    public function save()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE attendance SET student_id = :student_id, section_id = :section_id, course_id = :course_id, week_number = :week_number, section_group_number = :section_group_number, absence_status = :absence_status, timestamp = :timestamp WHERE attendance_id = :attendance_id");
            $stmt->execute([
                'attendance_id' => $this->id,
                'student_id' => $this->studentId,
                'section_id' => $this->sectionId,
                'course_id' => $this->courseId,
                'week_number' => $this->weekNumber,
                'section_group_number' => $this->sectionGroupNumber,
                'absence_status' => $this->absenceStatus,
                'timestamp' => $this->timestamp
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO attendance (student_id, section_id, course_id, week_number, section_group_number, absence_status, timestamp) VALUES (:student_id, :section_id, :course_id, :week_number, :section_group_number, :absence_status, :timestamp)");
            $stmt->execute([
                'student_id' => $this->studentId,
                'section_id' => $this->sectionId,
                'course_id' => $this->courseId,
                'week_number' => $this->weekNumber,
                'section_group_number' => $this->sectionGroupNumber,
                'absence_status' => $this->absenceStatus,
                'timestamp' => $this->timestamp
            ]);
            $this->id = $this->db->lastInsertId();
        }
    }

    // Delete attendance record from the database
    public function delete()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("DELETE FROM attendance WHERE attendance_id = :attendance_id");
            $stmt->execute(['attendance_id' => $this->id]);
        }
    }




    ///////////////////////////////////////////////////////////////

    // Method to get attendance of a specific student in a specific section, course, week number, and section group number
    // public static function getStudentAttendanceByDetails($db, $studentId, $sectionId, $courseId, $weekNumber, $sectionGroupNumber)
    // {
    //     try {
    //         $stmt = $db->prepare("SELECT * FROM attendance WHERE student_id = :student_id AND section_id = :section_id AND course_id = :course_id AND week_number = :week_number AND section_group_number = :section_group_number");
    //         $stmt->execute(['student_id' => $studentId, 'section_id' => $sectionId, 'course_id' => $courseId, 'week_number' => $weekNumber, 'section_group_number' => $sectionGroupNumber]);
    //         $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //         return $attendanceData;
    //     } catch (Exception $e) {
    //         throw new Exception("Failed to retrieve attendance: " . $e->getMessage());
    //     }
    // }

    // // Get attendance to record data by section ID, student ID, week number, and section group number
    // public static function getToRecord($db, $sectionId, $studentId, $weekNumber, $sectionGroupNumber)
    // {
    //     $stmt = $db->prepare("SELECT * FROM attendance WHERE section_id = :section_id AND student_id = :student_id AND week_number = :week_number AND section_group_number = :section_group_number");
    //     $stmt->execute(['section_id' => $sectionId, 'student_id' => $studentId, 'week_number' => $weekNumber, 'section_group_number' => $sectionGroupNumber]);
    //     $attendanceData = $stmt->fetch(PDO::FETCH_ASSOC);
    //     return $attendanceData;
    // }
}
