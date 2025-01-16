<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $genre = htmlspecialchars(trim($_POST['genre']));
    $file = $_FILES['file'];

    // Directories setup
    $uploadDir = 'uploads/';
    $submissionDir = 'submissions/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (!is_dir($submissionDir)) mkdir($submissionDir, 0755, true);

    // Validation
    if ($name && $email && $genre && $file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (in_array($file['type'], $allowedTypes)) {
            $uniqueFileName = uniqid('submission_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filePath = $uploadDir . $uniqueFileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $submission = [
                    'name' => $name,
                    'email' => $email,
                    'genre' => $genre,
                    'filepath' => $filePath,
                    'submitted_at' => date('Y-m-d H:i:s')
                ];
                file_put_contents($submissionDir . uniqid() . '.json', json_encode($submission));

                // Email Notification
                $to = 'admin@maplewoodreview.com';
                $subject = 'New Submission Received';
                $message = "New submission received:\n\nName: $name\nEmail: $email\nGenre: $genre\nFile: $filePath";
                $headers = "From: no-reply@maplewoodreview.com\r\n" .
                           "MIME-Version: 1.0\r\n" .
                           "Content-type: text/plain; charset=UTF-8";
                mail($to, $subject, $message, $headers);

                echo '<script>alert("Submission Successful!"); window.location.href="submission.html";</script>';
            } else {
                echo '<script>alert("File upload failed. Please try again."); window.location.href="submission.html";</script>';
            }
        } else {
            echo '<script>alert("Invalid file type. Allowed types: PDF, DOC, DOCX."); window.location.href="submission.html";</script>';
        }
    } else {
        echo '<script>alert("Invalid input. Please check your details and try again."); window.location.href="submission.html";</script>';
    }
}
?>
