<?php

require_once 'config/Database.php';
require_once 'App/Models/Student.php';
require_once 'App/Controllers/StudentController.php';
require_once 'App/Models/Course.php';
require_once 'App/Controllers/CourseController.php';
require_once 'App/Models/TeachingAssistant.php';
require_once 'App/Controllers/TeachingAssistantController.php';
require_once 'App/Models/Section.php';
require_once 'App/Controllers/SectionController.php';
require_once 'App/Models/Attendance.php';
require_once 'App/Controllers/AttendanceController.php';
require_once 'App/Models/Security.php';
require_once 'App/Controllers/SecurityController.php';
require_once 'App/Models/StudentCourse.php';
//require './vendor/autoload.php';

// Database connection
$db = new Database();


// Create instances of the controllers
$studentController = new StudentController($db);
$courseController = new CourseController($db);
$teachingAssistantController = new TeachingAssistantController($db);
$sectionController = new SectionController($db);
$attendanceController = new AttendanceController($db);
$securityController = new SecurityController($db);

// Simple router setup
if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['REQUEST_METHOD'])) {
    $request = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($request) {
        case '/':
        case '':
            echo "Welcome to the Student Management System API!";
            break;
        case '/student':
            if ($method == 'GET') {
                if (isset($_GET['student_id'])) {
                    echo $studentController->show($_GET['student_id']);
                } else {
                    echo $studentController->index();
                }
            } elseif ($method == 'POST') {
                echo $studentController->store($_POST);
            } elseif ($method == 'PUT') {
                echo $studentController->update($_GET['id'], $_POST);
            } elseif ($method == 'DELETE') {
                echo $studentController->destroy($_GET['id']);
            }
            break;
        case '/courses':
            if ($method == 'GET') {
                if (isset($_GET['course_id'])) {
                    echo $courseController->show($_GET['course_id']);
                } else {
                    echo $courseController->index();
                }
            } elseif ($method == 'POST') {
                echo $courseController->store($_POST);
            } elseif ($method == 'PUT') {
                parse_str(file_get_contents("php://input"), $_POST);
                if (isset($_GET['course_id'])) {
                    echo $courseController->update($_GET['course_id'], $_POST);
                }
            } elseif ($method == 'DELETE') {

                echo $courseController->destroy($_GET['id']);
            }
            break;
        case '/teaching-assistants':
            if ($method == 'GET') {
                if (isset($_GET['ta_id'])) {
                    echo $teachingAssistantController->show($_GET['ta_id']);
                } else {
                    echo $teachingAssistantController->index();
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                echo $teachingAssistantController->store($data);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                echo $teachingAssistantController->update($_GET['ta_id'], $data);
            } elseif ($method == 'DELETE') {

                echo $teachingAssistantController->destroy($_GET['course_id']);
            }
            break;
        case '/sections':
            if ($method == 'GET') {
                if (isset($_GET['section_id'])) {
                    echo $sectionController->show($_GET['section_id']);
                } else {
                    echo $sectionController->index();
                }
            } elseif ($method == 'POST') {
                echo $sectionController->store($_POST);
            } elseif ($method == 'PUT') {
                parse_str(file_get_contents("php://input"), $_PUT);
                if (isset($_GET['section_id'])) {
                    echo $sectionController->update($_GET['section_id'], $_PUT);
                }
            } elseif ($method == 'DELETE') {
                if (isset($_GET['section_id'])) {
                    echo $sectionController->destroy($_GET['section_id']);
                }
            }
            break;
        case '/attendance':
            if ($method == 'GET') {
                if (isset($_GET['attendance_id'])) {
                    echo $attendanceController->show($_GET['attendance_id']);
                } else {
                    echo $attendanceController->index();
                }
            } elseif ($method == 'POST') {
                echo $attendanceController->store($_POST);
            } elseif ($method == 'PUT') {
                parse_str(file_get_contents("php://input"), $_POST);
                if (isset($_GET['id'])) {
                    echo $attendanceController->update($_GET['attendance_id'], $_POST);
                }
            } elseif ($method == 'DELETE') {
                echo $attendanceController->destroy($_GET['attendance_id']);
            }
            break;
        case '/security':
            if ($method == 'GET') {
                if (isset($_GET['security_id'])) {
                    echo $securityController->show($_GET['security_id']);
                } else {
                    echo $securityController->index();
                }
            } elseif ($method == 'POST') {
                echo $securityController->store($_POST);
            } elseif ($method == 'PUT') {
                parse_str(file_get_contents("php://input"), $_POST);
                if (isset($_GET['id'])) {
                    echo $securityController->update($_GET['security_id'], $_POST);
                }
            } elseif ($method == 'DELETE') {
                echo $securityController->destroy($_GET['security_id']);
            }
            break;
        case '/login':
            if ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                echo $studentController->login($data['email'], $data['password']);
            }
            break;
        case '/gate-entry':
                if ($method == 'POST') {
                    // Get POST data
                    $data = json_decode(file_get_contents('php://input'), true);
                    // Call handleGateEntry function
                    echo $studentController->processQRcode($data['qr_code']);
                }
            break;
        case '/record-attendance':
                if ($method == 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $result = $attendanceController->recordAttendance(
                        $data['qr_code'],
                        $data['section_id'],
                        $data['course_id'],
                        $data['week_number'],
                        $data['absence_status'],
                        $data['timestamp']
                    );
                    
                    // Check if the result is an array
                    if (is_array($result)) {
                        // Convert the array to a JSON-encoded string
                        $result = json_encode($result);
                    }
                    
                    // Echo the result
                    echo $result;
                }
                break;


        default:
            http_response_code(404);
            echo json_encode(['error' => 'Not Found Or Wrong Request']);
            break;
    }
} else {
    echo "This script should be accessed via an HTTP request.";
}





///////////////////////////////////////////////////////////////

// case '/mark-attendance':
//     if ($method == 'POST') {
//         // Get POST data
//         $data = json_decode(file_get_contents('php://input'), true);
//         // Call markAttendance function
//         echo markAttendance($data['qr_code'], $data['section_id']);
//     }
//     break;

// Function to handle attendance marking
// function markAttendance($studentQRCode, $sectionId)
// {
//     global $studentController, $sectionController, $attendanceController;
//     try {
//         // Validate QR code
//         $student = $studentController->getByQRCode($studentQRCode);
//         if (!$student) {
//             throw new Exception("Invalid QR code");
//         }

//         // Check if student is enrolled in the section
//         $section = $sectionController->show($sectionId);
//         if (!$section) {
//             throw new Exception("Section not found");
//         }

//         // Mark attendance
//         $attendanceData = [
//             'student_id' => $student->getId(),
//             'section_id' => $sectionId,
//             'status' => 'Absent', // Assuming absent by default
//             'date' => date('Y-m-d')
//         ];
//         $attendanceController->store($attendanceData);

//         return json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
//     } catch (Exception $e) {
//         return json_encode(['success' => false, 'error' => $e->getMessage()]);
//     }
// }

// // Function to handle gate entry
// function handleGateEntry($studentQRCode)
// {
//     global $studentController, $securityController;
//     try {
//         // Validate QR code
//         $student = $studentController->getByQRCode($studentQRCode);
//         if (!$student) {
//             throw new Exception("Invalid QR code");
//         }

//         // Grant access
//         $response = $securityController->accessControl($studentQRCode);

//         return $response; // Response from security controller
//     } catch (Exception $e) {
//         return json_encode(['success' => false, 'error' => $e->getMessage()]);
//     }
// }
