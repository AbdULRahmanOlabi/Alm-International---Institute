<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: multipart/form-data');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

function verifyRecaptcha($response)
{
    $recaptcha_secret = '6LcGBxEqAAAAAPcHWdo6uKV-XlXBHJ4NWkfx6aDH';
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array('secret' => $recaptcha_secret, 'response' => $response);
    $options = array('http' => array('method' => 'POST', 'header' => "Content-type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query($data)));
    $context = stream_context_create($options);
    $verify = file_get_contents($url, false, $context);
    $captcha_success = json_decode($verify);
    return $captcha_success->success;
}

$data = json_decode(file_get_contents("php://input"), true);

$data = $_POST; // Get post data

// Improved file handling function
function handleFileUpload($fieldName, $targetDir)
{
    if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] == 0) {
        $targetFilePath = $targetDir . basename($_FILES[$fieldName]['name']);
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetFilePath)) {
            return $targetFilePath;
        }
    }
    return NULL;
}

$target_dir = "uploads/";
$fileNewRegistration = handleFileUpload('newRegistrationDocument', $target_dir);
$fileTransferDocument = handleFileUpload('transferDocument', $target_dir);
$fileStudySequence = handleFileUpload('studySequence', $target_dir);
$fileJalaa = handleFileUpload('jalaa', $target_dir);
$fileProofOfAge = handleFileUpload('ageProof', $target_dir);
$fileProofOfResidency = handleFileUpload('residencyProof', $target_dir);

try {
    $pdo = new PDO('sqlsrv:Server=localhost;Database=website_db', 'sa', 'olabi9591@$');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Make sure to sanitize input or use bound parameters to avoid SQL injection
    $stmt = $pdo->prepare("INSERT INTO students (firstName, lastName, country, dob, fatherName, class, motherName, gender, numberOfPreviousFailures, previousYearSituation, previousYearClass, transferredFromSchool, previousYearResult, fileNewRegistration, fileTransferDocument, fileStudySequence, fileJalaa, fileProofOfAge, fileProofOfResidency)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bindParam(1, $_POST['firstName']);
    $stmt->bindParam(2, $_POST['lastName']);
    $stmt->bindParam(3, $_POST['country']);
    $stmt->bindParam(4, $_POST['dob']);
    $stmt->bindParam(5, $_POST['fatherName']);
    $stmt->bindParam(6, $_POST['class']);
    $stmt->bindParam(7, $_POST['motherName']);
    $stmt->bindParam(8, $_POST['gender']);
    $stmt->bindParam(9, $_POST['numberOfPreviousFailures']);
    $stmt->bindParam(10, $_POST['previousYearSituation']);
    $stmt->bindParam(11, $_POST['previousYearClass']);
    $stmt->bindParam(12, $_POST['transferredFromSchool']);
    $stmt->bindParam(13, $_POST['previousYearResult']);
    $stmt->bindParam(14, $fileNewRegistration);
    $stmt->bindParam(15, $fileTransferDocument);
    $stmt->bindParam(16, $fileStudySequence);
    $stmt->bindParam(17, $fileJalaa);
    $stmt->bindParam(18, $fileProofOfAge);
    $stmt->bindParam(19, $fileProofOfResidency);

    $stmt->execute();

    if ($stmt->rowCount()) {
        http_response_code(200);
        echo json_encode(['message' => 'شكرا لتسجيلك. لقد تم إرسال المعلومات الخاصة بك بنجاح.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'فشل التسجيل، يرجى المحاولة مرة أخرى.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}
?>