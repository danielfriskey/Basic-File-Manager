<?php
	
    // Base URL for your site hosted locally
    $base_dir = "http://localhost/file_manager/";
    // Directory where files are stored (relative to the current directory)
    $files_dir = "files/";
	
	// Allowed MIME types and file extensions
	$allowed_mime_types = [
		'image/jpeg',
		'image/png',
		'image/gif',
		'model/stl',
		'model/x.stl-ascii',
		'model/x.stl-binary',
		'application/sla',
		'application/octet-stream',
		'video/mp4'
	];
	$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'stl', 'mp4'];
	
	// Array of file names to ignore (add any others as needed)
	$ignored_files = ['index.html'];
	

?>
