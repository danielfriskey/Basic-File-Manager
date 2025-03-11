<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<!-- Disable caching -->
		<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>File Manager</title>
		<!-- Main CSS -->
		<link rel="stylesheet" href="assets/css/style.css">
		<!-- Font Awesome CSS -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
		<!-- SweetAlert2 CSS -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	</head>
	<body>
		<div class="container">
			<h1>File Manager</h1>
			<!-- Upload Section -->
			<div class="upload-section">
				<h2>Upload Files</h2>
				<div id="drag_drop_area" class="drag-drop-area">
					<p>Drag and drop files here or click to select files.</p>
					<input type="file" id="file_input" multiple>
				</div>
				<button id="upload_btn" class="btn">Upload</button>
				<!-- Selected Files (pre-upload) -->
				<div class="file-list" id="selected_files">
					<ul>
						<!-- Pre-upload files will be listed here -->
					</ul>
				</div>
			</div>
			<!-- Manager Section -->
			<div class="manager-section">
				<!-- Filter Controls for sorting and searching -->
				<div class="filter-controls">
					<div class="filter-item">
						<label for="sort_select">Sort By:</label>
						<!-- Default sort is 'newest' -->
						<select id="sort_select">
							<option value="alphabetical">Alphabetical</option>
							<option value="newest" selected>Newest</option>
							<option value="oldest">Oldest</option>
						</select>
					</div>
					<div class="filter-item">
						<label for="search_input">Search:</label>
						<input type="text" id="search_input" placeholder="Enter file name...">
					</div>
				</div>
				<!-- Uploaded Files Header with Count -->
				<h2>Uploaded Files ( <span id="file_count">0</span>) </h2>
				<button id="refresh_list_btn" class="btn">Refresh File List</button>
				<div id="file_structure">
					<!-- Dynamically loaded file list will appear here -->
				</div>
			</div>
		</div>
		<!-- Popup container (SweetAlert2) -->
		<div id="image_popup" class="image-popup"></div>
		<!-- jQuery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
		<!-- SweetAlert2 JS -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<!-- -->
		<script type="text/javascript" src="assets/js/script.js"></script>
	</body>
</html>