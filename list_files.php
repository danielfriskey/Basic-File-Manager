<?php
	require_once __DIR__ . '/assets/config.php'; // Include config file
	
	// Enable error reporting for debugging
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	header('Content-Type: application/json');

	/**
	 * Ensures the "files" directory exists in the current directory.
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

	/**
	 * Recursively scans a directory and returns an array of file details.
	 * Only includes files with allowed extensions and ignores files in the ignored list.
	 *
	 * @param string $dir Full filesystem path to scan.
	 * @param string $relative_dir Relative path for public URL access.
	 * @return array List of file details.
	 */
	function scan_dir_recursive($dir, $relative_dir = '') {
		global $base_dir, $files_dir, $allowed_extensions, $ignored_files;
		$results = [];
		$items = scandir($dir);
		foreach ($items as $item) {
			// Skip current/parent directories
			if ($item === '.' || $item === '..') continue;
			// Skip any ignored files
			if (in_array($item, $ignored_files)) continue;
			
			$full_path = $dir . '/' . $item;
			$relative_path = ($relative_dir === '') ? $item : $relative_dir . '/' . $item;
			
			if (is_dir($full_path)) {
				// Recursively scan subdirectories
				$results = array_merge($results, scan_dir_recursive($full_path, $relative_path));
			} else {
				// Check if file extension is allowed
				$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
				if (!in_array($ext, $allowed_extensions)) continue;
				
				$short_name = (strlen($item) > 20) ? substr($item, 0, 17) . '...' : $item;
				$results[] = [
					'name'       => $item,
					'short_name' => $short_name,
					'size'       => filesize($full_path),
					'mtime'      => filemtime($full_path),
					'url'        => $base_dir . $files_dir . $relative_path, // Public URL
					'path'       => $relative_path
				];
			}
		}
		return $results;
	}

	$local_files_dir = ensure_files_directory_exists();
	$file_list = scan_dir_recursive($local_files_dir);

	$response = [
		'directory' => $files_dir,
		'files'     => $file_list,
	];

	echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>