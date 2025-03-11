// Global variable to store the loaded file data
let all_files = [];
// Global array to hold new file names uploaded in the last operation
let new_uploaded_files = [];

$(document).ready(function() {
    // Cache DOM elements using snake_case naming
    const file_input = document.getElementById('file_input');
    const drag_drop_area = document.getElementById('drag_drop_area');
    const selected_files = document.querySelector('#selected_files ul');
    let files_to_upload = [];

    // Drag-and-drop event listeners for upload section
    drag_drop_area.addEventListener('dragover', function(e) {
        e.preventDefault();
        drag_drop_area.classList.add('dragging');
    });

    drag_drop_area.addEventListener('dragleave', function() {
        drag_drop_area.classList.remove('dragging');
    });

    drag_drop_area.addEventListener('drop', function(e) {
        e.preventDefault();
        drag_drop_area.classList.remove('dragging');
        files_to_upload = files_to_upload.concat(Array.from(e.dataTransfer.files));
        display_selected_files();
    });

    drag_drop_area.addEventListener('click', function() {
        file_input.click();
    });

    file_input.addEventListener('change', function() {
        files_to_upload = files_to_upload.concat(Array.from(file_input.files));
        display_selected_files();
    });

    /**
     * Display the list of files that are selected for upload (pre-upload).
     */
    function display_selected_files() {
        if (!selected_files) return;
        selected_files.innerHTML = '';
        files_to_upload.forEach(function(file, index) {
            const li = document.createElement('li');
            li.innerHTML = `
          <span>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
          <button class="remove-file" onclick="remove_file(${index})">Remove</button>
        `;
            selected_files.appendChild(li);
        });
        // Reset file input so same file can be re-selected if needed.
        file_input.value = '';
    }

    window.remove_file = function(index) {
        files_to_upload.splice(index, 1);
        display_selected_files();
    };

    // Upload files with a loading spinner until complete
    $('#upload_btn').click(function() {
        if (files_to_upload.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No files selected',
                text: 'Please select or drop some files before uploading.'
            });
            return;
        }
        // Show loading spinner for upload
        Swal.fire({
            title: 'Uploading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const form_data = new FormData();
        files_to_upload.forEach(function(file) {
            form_data.append('file[]', file);
        });

        $.ajax({
            url: 'upload.php',
            type: 'POST',
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                Swal.close(); // close the loading spinner
                console.log('Response from server:', response);
                new_uploaded_files = [];
                if (Array.isArray(response)) {
                    response.forEach(function(file_response) {
                        if (file_response.error === null) {
                            new_uploaded_files.push(file_response.path);
                            Swal.fire({
                                icon: 'success',
                                title: 'Upload Success',
                                text: 'File uploaded: ' + file_response.file,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Error',
                                text: 'Error uploading ' + file_response.file + ': ' + file_response.error
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unexpected Response',
                        text: 'The server returned an unexpected response.'
                    });
                }
                files_to_upload = [];
                display_selected_files();
                load_file_structure();
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.error('Error:', error, xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: 'An error occurred during the file upload.'
                });
            }
        });
    });

    // Refresh file list with a loading spinner and success alert
    $('#refresh_list_btn').click(function() {
        Swal.fire({
            title: 'Refreshing...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        load_file_structure();
    });

    // Load file structure from list_files.php
    function load_file_structure() {
        $.ajax({
            url: 'list_files.php',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(data) {
                Swal.close();
                console.log(data);
                all_files = data.files;
                render_file_list();
                Swal.fire({
                    icon: 'success',
                    title: 'File List Refreshed',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Load Error',
                    text: 'An error occurred while loading the file structure.'
                });
            }
        });
    }

    /**
     * Render the file list based on current sort and search filters.
     * Newly uploaded files will be briefly highlighted.
     */
    function render_file_list() {
        let filtered_files = all_files.slice(); // copy array
        const search_query = $('#search_input').val().toLowerCase();
        const sort_type = $('#sort_select').val();

        if (search_query) {
            filtered_files = filtered_files.filter(function(file) {
                return file.name.toLowerCase().includes(search_query);
            });
        }

        if (sort_type === 'alphabetical') {
            filtered_files.sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
        } else if (sort_type === 'newest') {
            filtered_files.sort(function(a, b) {
                return b.mtime - a.mtime;
            });
        } else if (sort_type === 'oldest') {
            filtered_files.sort(function(a, b) {
                return a.mtime - b.mtime;
            });
        }

        $('#file_count').text(filtered_files.length);
        const file_structure = $('#file_structure');
        file_structure.empty();
        const dir_element = $(`
        <div class="directory">
          <!--<h3>files</h3>-->
          <ul class="file-items"></ul>
        </div>
      `);
        const file_list_el = dir_element.find('.file-items');

        filtered_files.forEach(function(file) {
            const is_image = /\.(jpg|jpeg|png|gif)$/i.test(file.name);
            const file_item = $(`
          <li class="file-item" data-file="${file.path}">
            <div class="thumb">
              ${is_image ? `<img src="${file.url}" alt="${file.name}">` : ''}
            </div>
            <span class="file-info" title="${file.name}">${file.short_name}</span>
            <div class="file-actions">
              <button class="btn" onclick="download_file('${file.url}')">
                <i class="fa fa-download"></i>
              </button>
              <button class="btn" onclick="prompt_rename('${file.path}')">
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn-danger" onclick="delete_file('${file.path}', this)">
                <i class="fa fa-trash"></i>
              </button>
              <button class="btn copy-link-btn" onclick="copy_link('${file.url}', this)">
                <i class="fa fa-copy"></i>
              </button>
            </div>
          </li>
        `);
            if (new_uploaded_files.indexOf(file.path) !== -1) {
                file_item.addClass('highlight');
                setTimeout(function() {
                    file_item.removeClass('highlight');
                }, 2000);
            }
            file_list_el.append(file_item);
        });

        file_structure.append(dir_element);
        new_uploaded_files = [];
    }

    $('#sort_select').on('change', render_file_list);
    $('#search_input').on('input', render_file_list);

    window.download_file = function(file_url) {
        const link = document.createElement('a');
        link.href = file_url;
        link.download = file_url.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    /**
     * Delete file with confirmation.
     * The corresponding row fades out on success.
     */
    window.delete_file = function(file_path, btn) {
        Swal.fire({
            title: 'Delete File?',
            text: 'Are you sure you want to delete this file?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_file.php',
                    type: 'POST',
                    data: {
                        filePath: file_path
                    },
                    dataType: 'json',
                    cache: false,
                    success: function(response) {
                        if (response.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Error',
                                text: response.error
                            });
                        } else {
                            // Fade out the row before reloading list
                            const row = $(`li.file-item[data-file="${file_path}"]`);
                            row.fadeOut(500, function() {
                                row.remove();
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.success,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                        load_file_structure();
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Error',
                            text: 'An error occurred while deleting the file.'
                        });
                    }
                });
            }
        });
    };

    /**
     * Rename file using SweetAlert2 input.
     */
    window.prompt_rename = function(file_path) {
        const current_name = file_path.split('/').pop();
        Swal.fire({
            title: 'Rename File',
            text: 'Enter new name for the file (with extension):',
            input: 'text',
            inputValue: current_name,
            showCancelButton: true,
            confirmButtonText: 'Rename',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a new file name.';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const new_name = result.value;
                if (!new_name) return;
                $.ajax({
                    url: 'rename_file.php',
                    type: 'POST',
                    data: {
                        filePath: file_path,
                        newName: new_name
                    },
                    dataType: 'json',
                    cache: false,
                    success: function(response) {
                        if (response.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Rename Error',
                                text: response.error
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'File Renamed',
                                text: response.success,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            load_file_structure();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Rename Error',
                            text: 'An error occurred while renaming the file.'
                        });
                    }
                });
            }
        });
    };

    /**
     * Copy the file URL to the clipboard and show feedback.
     */
    window.copy_link = function(file_url, btn) {
        navigator.clipboard.writeText(file_url)
            .then(() => {
                const original_html = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check"></i>';
                Swal.fire({
                    icon: 'success',
                    title: 'Link Copied!',
                    text: 'File URL has been copied to clipboard.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                setTimeout(() => {
                    btn.innerHTML = original_html;
                }, 2000);
            })
            .catch(err => {
                console.error('Failed to copy:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Copy Failed',
                    text: 'Could not copy the link to clipboard.'
                });
            });
    };

    // Initial load of file structure
    load_file_structure();
});