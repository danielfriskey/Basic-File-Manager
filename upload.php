<?php
	
	// Include configuration
	require_once __DIR__ . '/assets/config.php';

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	header('Content-Type: application/json');

	/**
	 * Ensures the "files" directory exists.
	 *
	 * @return string The absolute path to the "files" directory.
	 */
	function ensure_files_directory_exists() {
		global $files_dir;
		$files_directory = __DIR__ . '/' . $files_dir;
		if (!file_exists($files_directory)) {
			mkdir($files_directory, 0775, true);
			chmod($files_directory, 0775);
		}
		return realpath($files_directory);
	}

	$log_to_file = false;
	$max_upload_size = ((int)(ini_get('upload_max_filesize')) * 1024 * 1024);
	
	/**
	 * Logs messages to a file if logging is enabled.
	 *
	 * @param string $message Message to log.
	 */
	function log_to_file($message) {
		global $log_to_file;
		if ($log_to_file) {
			$log_file = __DIR__ . '/upload_log.txt';
			$timestamp = date('Y-m-d H:i:s');
			file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
		}
	}

	if (!isset($_FILES['file'])) {
		log_to_file("No files uploaded.");
		echo json_encode(["error" => "No files uploaded."]);
		exit;
	}

	$files = $_FILES['file'];
	$responses = [];

	// Get the upload directory from config
	$upload_directory = ensure_files_directory_exists() . '/';

	foreach ($files['name'] as $index => $file_name) {
		$file_temp_name = $files['tmp_name'][$index] ?? null;
		$file_size      = $files['size'][$index] ?? 0;
		$file_error     = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;

		if ($file_error !== UPLOAD_ERR_OK) {
			$upload_errors = [
				UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
				UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
				UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
				UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
				UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
				UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
				UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload."
			];
			$error_message = $upload_errors[$file_error] ?? "Unknown upload error.";
			log_to_file("File upload error: $error_message");
			$responses[] = ["file" => $file_name, "error" => $error_message];
			continue;
		}

		if ($file_size > $max_upload_size) {
			log_to_file("File size exceeds the limit: $file_size bytes.");
			$responses[] = ["file" => $file_name, "error" => "The file exceeds the maximum allowed size."];
			continue;
		}

		$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		if (!in_array($ext, $allowed_extensions)) {
			log_to_file("Disallowed file extension: $ext");
			$responses[] = ["file" => $file_name, "error" => "Invalid file extension."];
			continue;
		}

		$file_info = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($file_info, $file_temp_name);
		finfo_close($file_info);
		if (!in_array($mime_type, $allowed_mime_types)) {
			log_to_file("Invalid MIME type: $mime_type");
			$responses[] = ["file" => $file_name, "error" => "The MIME type '$mime_type' is not allowed."];
			continue;
		}

		// Set target file path within the "files" folder
		$target_file = $upload_directory . $file_name;
		// If file exists, auto-rename by appending a numeric suffix
		$original_file_name = pathinfo($file_name, PATHINFO_FILENAME);
		$counter = 2;
		while (file_exists($target_file)) {
			$new_file_name = $original_file_name . '_' . $counter . '.' . $ext;
			$target_file = $upload_directory . $new_file_name;
			$counter++;
		}

		if (move_uploaded_file($file_temp_name, $target_file)) {
			chmod($target_file, 0664);
			log_to_file("File uploaded successfully: $target_file");
			$responses[] = ["file" => $file_name, "error" => null, "path" => basename($target_file), "size" => $file_size];
		} else {
			log_to_file("Failed to move uploaded file to: $target_file");
			$responses[] = ["file" => $file_name, "error" => "Failed to move the uploaded file."];
		}
	}

	echo json_encode($responses);
	
?>