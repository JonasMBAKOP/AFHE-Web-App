document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    const modal = document.getElementById('testimonialModal');
    const btn = document.querySelector('.btn-more');
    const closeBtn = document.querySelector('.close-modal');

    // When the user clicks on the button, open the modal
    btn.onclick = function(e) {
        e.preventDefault();
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Function to preview the image
    window.previewImage = function() {
        const preview = document.getElementById('preview');
        const file = document.getElementById('image').files[0];
        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
            preview.style.display = 'block';
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }

    // Show success message in modal if it exists
    const message = document.querySelector('.message');
    if (message) {
        modal.style.display = "block";
        // Hide message after 3 seconds
        setTimeout(() => {
            message.style.display = 'none';
        }, 3000);
    }
});
