<?php

require_once __DIR__ . '/../Models/Attendance.php';

class AttendanceController
{
  private $db;
  public function __construct($db)
  {
    $this->db = $db->getConnection();
  }

  public function index()
  {
    try {
      $attendance = Attendance::getAll($this->db);
      return json_encode(['success' => true, 'data' => $attendance]);
    } catch (Exception $e) {
      return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  public function show($id)
  {
    try {
      $attendance = Attendance::getById($this->db, $id);
      if ($attendance) {
        return json_encode(['success' => true, 'data' => $attendance]);
      } else {
        return json_encode(['success' => false, 'error' => 'Attendance record not found']);
      }
    } catch (Exception $e) {
      return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  public function store($data)
  {
    try {
      // Validate section ID
      if (!isset($data['section_id']) || !is_numeric($data['section_id'])) {
        throw new Exception("Invalid section ID");
      }
      // Validate student ID
      if (!isset($data['student_id']) || !is_numeric($data['student_id'])) {
        throw new Exception("Invalid student ID");
      }

      // You may want to validate other fields as well

      $attendance = new Attendance(
        $this->db,
        null,
        $data['student_id'],
        $data['section_id'],
        $data['course_id'],
        $data['week_number'],
        $data['section_group_number'],
        $data['absence_status'],
        $data['timestamp']
      );
      $attendance->save();
      return json_encode(['success' => true, 'message' => 'Attendance record created successfully']);
    } catch (Exception $e) {
      return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  public function update($id, $data)
  {
    try {
      $attendance = Attendance::getById($this->db, $id);
      if ($attendance) {
        $attendance->setStudentId($data['student_id']);
        $attendance->setSectionId($data['section_id']);
        $attendance->setCourseId($data['course_id']);
        $attendance->setWeekNumber($data['week_number']);
        $attendance->setSectionGroupNumber($data['section_group_number']);
        $attendance->setAbsenceStatus($data['absence_status']);
        $attendance->setTimestamp($data['timestamp']);
        $attendance->save();
        return json_encode(['success' => true, 'message' => 'Attendance record updated successfully']);
      } else {
        return json_encode(['success' => false, 'error' => 'Attendance record not found']);
      }
    } catch (Exception $e) {
      return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  public function destroy($id)
  {
    try {
      $attendance = Attendance::getById($this->db, $id);
      if ($attendance) {
        $attendance->delete();
        return json_encode(['success' => true, 'message' => 'Attendance record deleted successfully']);
      } else {
        return json_encode(['success' => false, 'error' => 'Attendance record not found']);
      }
    } catch (Exception $e) {
      return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  public function recordAttendance($qrCode, $sectionId, $courseId, $weekNumber, $absenceStatus,$timestamp)
  {
    try {
      // Extract student ID from QR code
      $studentId = $this->getStudentIdFromQRCode($qrCode);
      if ($studentId === null) {
        throw new Exception("Invalid QR code.");
      }

      // Record attendance in the database
      $this->saveAttendance($studentId, $sectionId, $courseId, $weekNumber, $absenceStatus,$timestamp);
      return ['status' => 'success', 'message' => 'Attendance recorded successfully'];
    } catch (Exception $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  private function getStudentIdFromQRCode($qrCode)
  {
    try {
      $stmt = $this->db->prepare("SELECT student_id FROM student WHERE qr_code = :qr_code");
      $stmt->bindParam(':qr_code', $qrCode);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ? $result['student_id'] : null;
    } catch (Exception $e) {
      throw new Exception("Error retrieving student ID from QR code: " . $e->getMessage());
    }
  }

  private function saveAttendance($studentId, $sectionId, $courseId, $weekNumber, $absenceStatus,$timestamp)
  {
    try {
      $stmt = $this->db->prepare("INSERT INTO attendance (student_id, section_id, course_id, week_number, section_group_number, absence_status, timestamp) VALUES (:student_id, :section_id, :course_id, :week_number, (SELECT section_group_number FROM student WHERE student_id = :student_id), :absence_status, :timestamp)");
      $stmt->execute([
        ':student_id' => $studentId,
        ':section_id' => $sectionId,
        ':course_id' => $courseId,
        ':week_number' => $weekNumber,
        ':absence_status' => $absenceStatus,
        ':timestamp' => $timestamp
      ]);
    } catch (Exception $e) {
      throw new Exception("Error saving attendance: " . $e->getMessage());
    }
  }


//////////////////////////////////////////////////////////////////////////

  // Function for student to attend section using QR code scanned by Teaching Assistant
  // public function attendSection($qrCode, $sectionId)
  // {
  //   try {
  //     // Validate student existence using QR code
  //     $sql = "SELECT * FROM student WHERE qr_code = ?";
  //     $stmt = $this->db->prepare($sql);
  //     $stmt->bind_param('s', $qrCode);
  //     $stmt->execute();
  //     $result = $stmt->get_result();

  //     if ($result->num_rows !== 1) {
  //       throw new Exception("Invalid student QR code"); // Throw for non-existent student
  //     }

  //     $studentData = $result->fetch_assoc();
  //     $studentId = $studentData['student_id'];

  //     // Check if student belongs to the scanned section
  //     $sql = "SELECT * FROM attendance WHERE student_id = ? AND section_id = ?";
  //     $stmt = $this->db->prepare($sql);
  //     $stmt->bind_param('ii', $studentId, $sectionId);
  //     $stmt->execute();
  //     $sectionResult = $stmt->get_result();

  //     if ($sectionResult->num_rows === 1) {
  //       // Record attendance if student belongs to the section
  //       $timestamp = time();  // Replace with actual timestamp generation
  //       $this->recordAttendance($studentId, $sectionId, $timestamp);

  //       $response = [
  //         "status" => "success",
  //         "message" => "Attendance recorded for student: " . $studentData['name'],
  //       ];
  //     } else {
  //       $response = [
  //         "status" => "error",
  //         "message" => "Student not registered for this section",
  //       ];
  //     }

  //     echo json_encode($response);
  //   } catch (Exception $e) {
  //     $response = [
  //       "status" => "error",
  //       "message" => "Error: " . $e->getMessage(),
  //     ];
  //     echo json_encode($response);
  //   }
  // }


  // // Function to record attendance 
  // private function recordAttendance($studentId, $sectionId, $timestamp)
  // {
  //   try {
  //     $sql = "INSERT INTO attendance (section_id, student_id, timestamp) VALUES (?, ?, ?)";
  //     $stmt = $this->db->prepare($sql);
  //     $stmt->bind_param('iii', $sectionId, $studentId, $timestamp);
  //     $stmt->execute();

  //     if ($stmt->affected_rows === 1) {
  //       $response = [
  //         "status" => "success",
  //         "message" => "Attendance recorded for student: " . $studentId,
  //       ];
  //     } else {
  //       $response = [
  //         "status" => "error",
  //         "message" => "Failed to record attendance",
  //       ];
  //     }

  //     return json_encode($response);
  //   } catch (Exception $e) {
  //     $response = [
  //       "status" => "error",
  //       "message" => "Error: " . $e->getMessage(),
  //     ];
  //     return json_encode($response);
  //   }
  // }
  // // Method to get attendance of a specific student in a specific section, course, week number, and section group number
  // public function getStudentAttendance($studentId, $sectionId, $courseId, $weekNumber, $sectionGroupNumber)
  // {
  //   try {
  //     $attendance = Attendance::getStudentAttendanceByDetails($this->db, $studentId, $sectionId, $courseId, $weekNumber, $sectionGroupNumber);
  //     return json_encode(['success' => true, 'data' => $attendance]);
  //   } catch (Exception $e) {
  //     return json_encode(['success' => false, 'error' => $e->getMessage()]);
  //   }
  // }

  // public function recordAttendanceByQRCode($qrCode, $sectionNumber, $weekNumber, $sectionGroupNumber)
  // {
  //   try {
  //     // Validate QR code and retrieve student information
  //     $student = Student::getByQRCode($this->db, $qrCode);
  //     if (!$student) {
  //       throw new Exception("Invalid QR code");
  //     }

  //     // Get section information based on section number
  //     $section = Section::getByNumber($this->db, $sectionNumber);
  //     if (!$section) {
  //       throw new Exception("Section not found");
  //     }

  //     // Check if the student is assigned to the provided section and week
  //     $sectionAttendance = Attendance::getToRecord($this->db, $section->getId(), $student->getId(), $weekNumber, $sectionGroupNumber);
  //     if ($sectionAttendance) {
  //       throw new Exception("Attendance already recorded for the specified section and week");
  //     }

  //     // Record attendance
  //     $attendance = new Attendance($this->db, null, $section->getId(), $student->getId(), date('Y-m-d H:i:s'));
  //     $attendance->save();

  //     return json_encode(['success' => true, 'message' => 'Attendance recorded successfully']);
  //   } catch (Exception $e) {
  //     return json_encode(['success' => false, 'error' => $e->getMessage()]);
  //   }
  // }
}
