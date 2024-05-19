<?php

//use SebastianBergmann\CodeCoverage\Report\Xml\Project;
// use Endroid\QrCode\QrCode;
// use Endroid\QrCode\Writer\PngWriter;
// use Endroid\QrCode\Encoding\Encoding;
// use Endroid\QrCode\ErrorCorrectionLevel;
// use BaconQrCode\Renderer\Image\Png;
// use BaconQrCode\Writer;
// use BaconQrCode\Encoder\QrCode;
// use BaconQrCode\Renderer\ImageRenderer;
// use BaconQrCode\Renderer\ImageRendererInterface;


require_once __DIR__ . '/../../config/Database.php';
//require __DIR__ . '/../../vendor/autoload.php';
class Student
{
    // Properties
    private $id;
    private $name;
    private $email;
    private $password;
    private $faculty;
    private $level;
    private $qrCode;
    private $sectionGroupNumber;
    private $image;

    private $db; // Database connection instance

    // Constructor
    public function __construct($db, $id = null, $name = '', $email = '', $password = '', $faculty = '', $level = '', $qrCode = '', $sectionGroupNumber = '',$image='')
    {
        $this->db = $db;
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->faculty = $faculty;
        $this->level = $level;
        $this->qrCode = $qrCode;
        $this->sectionGroupNumber = $sectionGroupNumber;
        $this->image =$image;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }
    public function getPassword()
    {
        return $this->password;
    }

    public function getFaculty()
    {
        return $this->faculty;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getQRCode()
    {
        return $this->qrCode;
    }

    public function getSectionGroupNumber()
    {
        return $this->sectionGroupNumber;
    }
    public function getimage(){
        return $this->image;
    }

    // Setters
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function setFaculty($faculty)
    {
        $this->faculty = $faculty;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function setQRCode($qrCode)
    {
        $this->qrCode = $qrCode;
    }

    public function setSectionGroupNumber($sectionGroupNumber)
    {
        $this->sectionGroupNumber = $sectionGroupNumber;
    }
    public function setimage($image){
        $this->image= $image;
    }

    // Get a student by ID
    public static function getById($db, $id)
    {
        try {
            $stmt = $db->prepare("SELECT * FROM student WHERE student_id = :student_id");
            $stmt->bindParam(':student_id', $id);
            $stmt->execute();

            $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($studentData) {
                return new Student(
                    $db,
                    $studentData['student_id'],
                    $studentData['name'],
                    $studentData['email'],
                    $studentData['password'],
                    $studentData['faculty'],
                    $studentData['level'],
                    $studentData['qr_code'],
                    $studentData['section_group_number']
                );
            } else {
                return null; // Student not found
            }
        } catch (Exception $e) {
            throw new Exception("Error fetching student by ID: " . $e->getMessage());
        }
    }
    // Login method to authenticate user
    public static function login($db, $email, $password)
    {
        try {
            $stmt = $db->prepare("SELECT * FROM student WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($studentData && password_verify($password, $studentData['password'])) {
                return new Student(
                    $db,
                    $studentData['student_id'],
                    $studentData['name'],
                    $studentData['email'],
                    $studentData['password'],
                    $studentData['faculty'],
                    $studentData['level'],
                    $studentData['qr_code'],
                    $studentData['section_group_number']
                );
            } else {
                return null; // Invalid credentials
            }
        } catch (Exception $e) {
            throw new Exception("Error during login: " . $e->getMessage());
        }
    }
    // Get student by email
    public function getByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM student WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all students
    public static function getAll($db)
    {
        // Prepare and execute SELECT query
        try {
            $stmt = $db->query("SELECT * FROM student");
            $studentsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $students = [];
            foreach ($studentsData as $studentData) {
                $students[] = new Student(
                    $db,
                    $studentData['student_id'],
                    $studentData['name'],
                    $studentData['email'],
                    $studentData['password'],
                    $studentData['faculty'],
                    $studentData['level'],
                    $studentData['qr_code'],
                    $studentData['section_group_number']
                );
            }
            return $students;
        } catch (Exception $e) {
            throw new Exception("Error fetching all students: " . $e->getMessage());
        }
    }

    // Save student to the database
    public function save()
    {
        try {
            if ($this->id) {
                $stmt = $this->db->prepare("UPDATE student SET name = :name, email = :email, password = :password, faculty = :faculty, level = :level, qr_code = :qr_code, section_group_number = :section_group_number WHERE student_id = :student_id");
                
                $stmt->execute([
                    ':student_id' => $this->id,
                    ':name' => $this->name,
                    ':email' => $this->email,
                    ':password' => $this->password,
                    ':faculty' => $this->faculty,
                    ':level' => $this->level,
                    ':qr_code' => $this->qrCode,
                    ':section_group_number' => $this->sectionGroupNumber
                ]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO student (name, email, password, faculty, level, qr_code, section_group_number) VALUES (:name, :email, :password, :faculty, :level, :qr_code, :section_group_number)");
                $stmt->execute([
                    ':name' => $this->name,
                    ':email' => $this->email,
                    ':password' => $this->password,
                    ':faculty' => $this->faculty,
                    ':level' => $this->level,
                    ':qr_code' => $this->qrCode,
                    ':section_group_number' => $this->sectionGroupNumber
                ]);
                $this->id = $this->db->lastInsertId();
            }
        } catch (Exception $e) {
            throw new Exception("Error saving student: " . $e->getMessage());
        }
    }
    // Delete student from the database
    public function delete()
    {
        try {
            if ($this->id) {
                $stmt = $this->db->prepare("DELETE FROM student WHERE student_id = :student_id");
                $stmt->bindParam(':student_id', $this->id);
                $stmt->execute();
            }
        } catch (Exception $e) {
            throw new Exception("Error deleting student: " . $e->getMessage());
        }
    }
     // Get student by QR code
     public static function getByQRCode($db, $qrCode)
     {
         try {
             $stmt = $db->prepare("SELECT * FROM student WHERE qr_code = :qr_code");
             $stmt->bindParam(':qr_code', $qrCode);
             $stmt->execute();
             $studentData = $stmt->fetch(PDO::FETCH_ASSOC);
 
             if ($studentData) {
                 return new Student(
                     $db,
                     $studentData['student_id'],
                     $studentData['name'],
                     $studentData['email'],
                     $studentData['password'],
                     $studentData['faculty'],
                     $studentData['level'],
                     $studentData['qr_code'],
                     $studentData['section_group_number'],
                     $studentData['image']
                 );
             } else {
                 return null; // Student not found
             }
         } catch (Exception $e) {
             throw new Exception("Error fetching student by QR code: " . $e->getMessage());
         }
     }



     //////////////////////////////////////////////////////////////////
    //Function to Generate QR Code
    // public function getQRCodeData()
    // {
    //     if (empty($this->qrCode)) {
    //         try {
    //             $this->generateQRCode(); // Generate QR code and store its path in $this->qrCode
    //             $this->saveQRCodePath(); // Save the QR code path to the database
    //         } catch (\Exception $e) {
    //             // Handle error during QR code generation
    //             throw new \Exception("Failed to generate QR code: " . $e->getMessage());
    //         }
    //     }

    //     return $this->qrCode; // Return the QR code image path
    // }

    // private function generateQRCode()
    // {
    //     try {
    //         // Create a QR code instance
    //         $qrCode = QrCode::create('student_id:' . $this->id . '-' . uniqid());
    //         $qrCode->setEncoding(new Encoding('UTF-8'));
    //         $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::Low);
    //         $qrCode->setSize(25); // Set the size of the QR code image

            

    //         // Specify the directory to save the QR code images
    //         $imagePath = __DIR__ . '/../../QRCodeImage/';

    //         // Generate a unique filename for the QR code image
    //         $filename = $this->id . '.png';
    //         $filePath = $imagePath . $filename;

    //         // Write the QR code image to a file
    //         $writer = new PngWriter();
    //         $writer->writeFile($qrCode, $filePath);
    //         // header('Content-Type: ' . $writer->getMimeType());

    //         // Set the QR code file path in the object
    //         $this->qrCode = $filename;

    //         return $filePath;

    //     } catch (\Exception $e) {
    //         throw new \Exception("Failed to create QR code object: " . $e->getMessage());
    //     }
    // }

    // private function saveQRCodePath()
    // {
    //     // Update the database record with the QR code image path
    //     try {
    //         $stmt = $this->db->prepare("UPDATE student SET qr_code = :qr_code WHERE student_id = :student_id");
    //         $stmt->execute([
    //             ':student_id' => $this->id,
    //             ':qr_code' => $this->qrCode
    //         ]);
    //     } catch (\Exception $e) {
    //         throw new \Exception("Failed to save QR code path to database: " . $e->getMessage());
    //     }
    // }


   
}
