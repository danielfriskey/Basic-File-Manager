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

	$file_path = $_POST['filePath'] ?? null;
	$new_name  = $_POST['newName'] ?? null;

	if (!$file_path || !$new_name) {
		echo json_encode(["error" => "Missing file path or new name."]);
		exit;
	}

	$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
	if (!preg_match('/^[a-zA-Z0-9_\-]+\.(jpg|jpeg|png|gif)$/i', $new_name)) {
		echo json_encode(["error" => "Invalid file name. Only alphanumeric characters, underscores, dashes, and a proper image extension are allowed."]);
		exit;
	}

	$real_base = ensure_files_directory_exists();
	$real_file = realpath($real_base . '/' . $file_path);

	if ($real_file === false || strpos($real_file, $real_base) !== 0) {
		echo json_encode(["error" => "Invalid file path."]);
		exit;
	}

	$new_file_path = dirname($real_file) . '/' . basename($new_name);
	if (file_exists($new_file_path)) {
		echo json_encode(["error" => "A file with that name already exists."]);
		exit;
	}

	if (rename($real_file, $new_file_path)) {
		echo json_encode(["success" => "File renamed successfully.", "newPath" => basename($new_file_path)]);
	} else {
		echo json_encode(["error" => "Failed to rename file."]);
	}
	
?>