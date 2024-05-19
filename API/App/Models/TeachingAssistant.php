<?php

require_once __DIR__ . '/../../config/Database.php';

class TeachingAssistant
{
    // Properties
    
    private $db;
    private $id;
    private $name;
    private $email;
    private $password;
 

    // Constructor
    public function __construct($db, $id = null, $name = '', $email = '',$password = '')
    {
        $this->db = $db;
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
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
        $this->password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
    }


    // Get all teaching assistants
    public static function getAll($db)
    {
        $stmt = $db->query("SELECT * FROM teaching_assistants");

        $tasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tas = [];

        foreach ($tasData as $taData) {
            $tas[] = new TeachingAssistant(
                $db,
                $taData['ta_id'],
                $taData['name'],
                $taData['email'],
                $taData['password'] 
            );
        }

        return $tas;
    }

    // Get a teaching assistant by ID
    public static function getById($db, $id)
    {
        $stmt = $db->prepare("SELECT * FROM teaching_assistants WHERE ta_id  = :ta_id ");
        $stmt->execute(['ta_id' => $id]);

        $taData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($taData) {
            return new TeachingAssistant(
                $db,
                $taData['ta_id'],
                $taData['name'],
                $taData['email'],
                $taData['password'] 
            );
        } else {
            return null; // Teaching Assistant not found
        }
    }
     // Login method to authenticate user
     public static function login($db, $email, $password)
     {
         $stmt = $db->prepare("SELECT * FROM teaching_assistants WHERE email = :email AND password = :password");
         $stmt->bind_param('ss', $email, $password);
         $stmt->execute();
         $result = $stmt->get_result();
 
         if ($result->num_rows === 1) {
             return $result->fetch_object("TeachingAssistant");
         } else {
             return null;
         }
     }
     // Get teaching assistant by email
     public function getByEmail($email)
     {
         $stmt = $this->db->prepare("SELECT * FROM teaching_assistants WHERE email = :email");
         $stmt->execute([$email]);
         return $stmt->fetch(PDO::FETCH_ASSOC);
     }

    
    // Save teaching assistant to the database
    public function save()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE teaching_assistants SET name = :name, email = :email, password = :password WHERE ta_id  = :ta_id ");
            $stmt->execute([
                'ta_id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password 
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO teaching_assistants (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password 
            ]);
            $this->id = $this->db->lastInsertId();
        }
    }

    // Delete teaching assistant from the database
    public function delete()
    {
        if ($this->id) {
            $stmt = $this->db->prepare("DELETE FROM teaching_assistants WHERE ta_id  = :ta_id ");
            $stmt->execute(['ta_id' => $this->id]);
        }
    }
    public function assignToCourse($courseId)
    {
        try {
            // Check if the association already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM taught_courses WHERE ta_id = :ta_id AND course_id = :course_id");
            $stmt->execute(['ta_id' => $this->id, 'course_id' => $courseId]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                throw new Exception("Teaching assistant is already assigned to this course.");
            }

            // Insert the association
            $stmt = $this->db->prepare("INSERT INTO taught_courses (ta_id, course_id) VALUES (:ta_id, :course_id)");
            $stmt->execute(['ta_id' => $this->id, 'course_id' => $courseId]);

            return json_encode(['success' => true, 'message' => 'Teaching assistant assigned to course successfully']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Get courses associated with the teaching assistant
    public function getCourses()
{
    try {
        $stmt = $this->db->prepare("SELECT courses.* FROM courses JOIN taught_courses ON courses.course_id = taught_courses.course_id WHERE taught_courses.ta_id = :ta_id");
        $stmt->execute(['ta_id' => $this->id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode(['success' => true, 'data' => $courses]);
    } catch (Exception $e) {
        return json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
}

?>
