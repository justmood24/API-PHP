<?php

require_once __DIR__ . '/../Models/Student.php';
require_once __DIR__ . '/../Models/StudentCourse.php';

class StudentController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db->getConnection();
    }

     // All Students
     public function index()
     {
         try {
             $students = Student::getAll($this->db);
             return json_encode(['success' => true, 'data' => $students]);
         } catch (Exception $e) {
             return json_encode(['success' => false, 'error' => $e->getMessage()]);
         }
     }
 
     // Get Student by ID
     public function show($id)
{
    try {
        $student = Student::getById($this->db, $id);
        if ($student) {
            return json_encode(['success' => true, 'data' => $student]);
        } else {
            return json_encode(['success' => false, 'error' => 'Student not found']);
        }
    } catch (Exception $e) {
        return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
     // Login method to authenticate user
    //  public function login($name, $password)
    //  {
    //      try {
    //          // Authenticate user using the provided username and password
    //          $student = Student::login($this->db, $name, $password);
    //          if ($student) {
    //              return json_encode(['success' => true, 'data' => $student]);
    //          } else {
    //              return json_encode(['success' => false, 'error' => 'Invalid name or password']);
    //          }
    //      } catch (Exception $e) {
    //          return json_encode(['success' => false, 'error' => $e->getMessage()]);
    //      }
    //  }
    public function login($email, $password)
    {
        try {
            // Authenticate user using the provided username and password
            $student = Student::login($this->db, $email, $password);
            return $student;
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

    // Create New Student
    public function store($data)
    {
        try {
            // Check if the required keys exist in the $data array
            if (
                !isset($data['name']) || !isset($data['email']) || !isset($data['faculty']) ||
                !isset($data['level']) || !isset($data['qr_code']) || !isset($data['section_group_number'])
            ) {
                throw new Exception("Required fields are missing");
            }

            // Access array keys after verifying their existence
            $name = $data['name'];
            $email = $data['email'];
            $faculty = $data['faculty'];
            $level = $data['level'];
            $qrCode = $data['qr_code'];
            $sectionGroupNumber = $data['section_group_number'];

            // Now you can proceed with your logic, such as creating a new Student instance and saving it to the database
            $student = new Student($this->db, null, $name, $email,null, $faculty, $level, $qrCode, $sectionGroupNumber);
            $student->save();

            return json_encode(['success' => true, 'message' => 'Student record created successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    //Update Student By ID
    public function update($id, $data)
    {
        try {
            $student = Student::getById($this->db, $id);
            if ($student) {
                if (!isset($data['name']) || !isset($data['email']) || !isset($data['faculty']) ||
                    !isset($data['level']) || !isset($data['qr_code']) || !isset($data['section_group_number'])) {
                    throw new Exception("Required fields are missing");
                }
               
                $student->setName($data['name']);
                $student->setEmail($data['email']);
                $student->setFaculty($data['faculty']);
                $student->setLevel($data['level']);
                $student->setQrCode($data['qr_code']);
                $student->setSectionGroupNumber($data['section_group_number']);
                $student->save();
                return json_encode(['success' => true, 'message' => 'Student updated successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Student not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Delete Student By ID 
    public function destroy($id)
    {
        try {
            $student = Student::getById($this->db, $id);
            if ($student) {
                $student->delete();
                return json_encode(['success' => true, 'message' => 'Student deleted successfully']);
            } else {
                return json_encode(['success' => false, 'error' => 'Student not found']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    // Function To Generate QR Code of Student
    
    
    // Fucntion to confirm Gate Entry 
    public function processQRCode($qrCode)
    {
        try {
            if (!$qrCode) {
                throw new Exception("QR code data is required");
            }

            //$qrCode = $_POST['qr_code'];

            // Get student information based on the QR code
            $student = Student::getByQRCode($this->db, $qrCode);

            if (!$student) {
                throw new Exception("Student not found");
            }

            // Return the student information as a JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'student_id' => $student->getId(),
                'name' => $student->getName(),
                'faculty' => $student->getFaculty(),
                'level' => $student->getLevel(),
                'image'=>$student->getimage()
                
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }


    ///////////////////////////////////////////////////////////////

    
    // public static function scanQRCode($db, $studentId)
    // {
    //     try {
            
    //         $student = Student::getById($db, $studentId);
            
    //         if ($student) {
    //             // Verification successful, allow entry
    //             $studentName = $student->getName();
    //             $studentId = $student->getId();
    //             $studentFaculty = $student->getFaculty();
    //             $studentLevel = $student->getLevel();
    //             $studentQrcode = $student->getQRCode();
                
    //             echo "Welcome, $studentName! Student ID: $studentId,
    //             Student Faculty: $studentFaculty, Student Level: $studentLevel,
    //             Student QR Code: $studentQrcode"; 
    //         } else {
    //             // Deny entry if student not found
    //             echo "Access denied. Student not found.";
    //         }
    //     } catch (Exception $e) {
    //         // Handle any exceptions
    //         echo "Error: " . $e->getMessage();
    //     }
    // }

    // public function createStudent($name, $email, $password, $faculty, $level)
    // {
    //     $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    //     $student = new Student(null, $name, $email, $hashedPassword, $faculty, $level);
    //     $qrCodeData = $student->getQRCodeData();

    //     $sql = "INSERT INTO student (name, email, password, faculty, level, qr_code) VALUES (?, ?, ?, ?, ?, ?)";
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->bind_param(
    //         'ssssss',
    //         $student->getName(),
    //         $student->getEmail(),
    //         $student->getPassword(),
    //         $student->getFaculty(),
    //         $student->getLevel(),
    //         $qrCodeData
    //     );

    //     if ($stmt->execute()) {
    //         $studentId = $this->db->insert_id;
    //         $response = [
    //             "status" => "success",
    //             "message" => "Student created successfully (ID: $studentId)",
    //         ];
    //         echo json_encode($response);
    //     } else {
    //         $response = [
    //             "status" => "error",
    //             "message" => "Failed to create student",
    //         ];
    //         echo json_encode($response);
    //     }
    // }

    // public function getByQRCode($qrCode)
    // {
    //     try {
    //         // Perform database query to fetch student by QR code
    //         $stmt = $this->db->prepare("SELECT * FROM student WHERE qr_code = :qr_code");
    //         $stmt->execute([$qrCode]);
    //         $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

    //         if ($studentData) {
    //             // Create and return Student object
    //             return new Student(
    //                 $this->db,
    //                 $studentData['student_id'],
    //                 $studentData['name'],
    //                 $studentData['email'],
    //                 $studentData['password'],
    //                 $studentData['faculty'],
    //                 $studentData['level'],
    //                 $studentData['qr_code'],
    //                 $studentData['section_group_number']
    //             );
    //         } else {
    //             return null; // Student not found
    //         }
    //     } catch (Exception $e) {
    //         // Handle database errors or exceptions
    //         throw new Exception("Error fetching student by QR code: " . $e->getMessage());
    //     }
    // }

    // Function to handle potential errors 
    // public function getStudentQRCode($studentId)
    // {
    //     try {
    //         $sql = "SELECT * FROM student WHERE student_id = :student_id";
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->bind_param(':student_id', $studentId);
    //         $stmt->execute();
    //         $result = $stmt->get_result();

    //         if ($result->num_rows === 1) {
    //             $studentData = $result->fetch_assoc();
    //             $qrCodeData = base64_decode($studentData['qr_code']);

    //             $response = [
    //                 "status" => "success",
    //                 "message" => "Student QR code retrieved",
    //                 "qr_code" => $qrCodeData
    //             ];

    //             echo json_encode($response);
    //         } else {
    //             $response = [
    //                 "status" => "error",
    //                 "message" => "Invalid student ID",
    //             ];
    //             echo json_encode($response);
    //         }
    //     } catch (Exception $e) {
    //         echo "Error: " . $e->getMessage();
    //         return false; // Or return specific error code
    //     }
    // }
    // public function accessControl($qrCode)
    // {
    //     try {
    //         // Perform database query to check if the QR code exists in the students table
    //         $stmt = $this->db->prepare("SELECT * FROM students WHERE qr_code = ?");
    //         $stmt->execute([$qrCode]);
    //         $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

    //         if ($studentData) {
    //             // Student with the provided QR code exists
    //             $response = [
    //                 "status" => "success",
    //                 "message" => "Access granted to student: " . $studentData['name']
    //             ];
    //         } else {
    //             // Student with the provided QR code does not exist
    //             $response = [
    //                 "status" => "error",
    //                 "message" => "Invalid QR code"
    //             ];
    //         }

    //         return json_encode($response);
    //     } catch (Exception $e) {
    //         // Handle database errors or exceptions
    //         throw new Exception("Error checking access control: " . $e->getMessage());
    //     }
    // }
}
