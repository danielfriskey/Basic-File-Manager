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
	function log_to_file($message) {
		global $log_to_file;
		if ($log_to_file) {
			$log_file = __DIR__ . '/delete_log.txt';
			$timestamp = date('Y-m-d H:i:s');
			file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
		}
	}

	$file_path = $_POST['filePath'] ?? null;
	if (!$file_path) {
		log_to_file("No file path provided.");
		echo json_encode(["error" => "No file path provided."]);
		exit;
	}

	// Use the "files" folder from config as the base directory
	$real_base = ensure_files_directory_exists();
	$real_path = realpath($real_base . '/' . $file_path);

	if ($real_path === false || strpos($real_path, $real_base) !== 0) {
		log_to_file("Invalid file path: $file_path");
		echo json_encode(["error" => "Invalid file path."]);
		exit;
	}

	if (is_file($real_path)) {
		if (unlink($real_path)) {
			log_to_file("Deleted file: $real_path");
			echo json_encode(["success" => "File deleted successfully."]);
		} else {
			log_to_file("Failed to delete file: $real_path");
			echo json_encode(["error" => "Failed to delete file."]);
		}
	} else {
		log_to_file("File not found: $real_path");
		echo json_encode(["error" => "File not found."]);
	}
	
?>