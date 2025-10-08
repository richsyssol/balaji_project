<?php 
    include 'include/header.php'; 
    include 'include/head.php'; 
    include 'session_check.php';
?>

<section class="d-flex pb-5">
    <?php include 'include/navbar.php'; ?>
    <div class="container p-5 ">
        <div class="ps-5">
            <div>
                <h1>Image</h1>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Image</li>
              </ol>
            </nav>
        </div>
        <div class="bg-white con-tbl p-5">
   
            <!-- Image Selection -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card image-card" data-image-src="asset/image/store.jpeg">
                        <div class="card-body text-center">
                            <img src="asset/image/store.jpeg" alt="Store Image" class="img-fluid mb-3" style="max-height: 300px;">
                            <div class="form-check">
                                <input class="form-check-input image-select" type="radio" name="selectedImage" id="image1" value="asset/image/store.jpeg" checked>
                                <label class="form-check-label" for="image1">
                                    Select Store Image
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card image-card" data-image-src="asset/image/todo.png">
                        <div class="card-body text-center">
                            <img src="asset/image/todo.png" alt="Todos Image" class="img-fluid mb-3" style="max-height: 300px;">
                            <div class="form-check">
                                <input class="form-check-input image-select" type="radio" name="selectedImage" id="image2" value="asset/image/todo.png">
                                <label class="form-check-label" for="image2">
                                    Select Todos Image
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="mb-4">
                <h4>Preview</h4>
                <div id="imagePreview" class="text-center border p-3">
                    <img id="previewImage" src="asset/image/store.jpeg" alt="Selected Image" style="max-height: 400px; max-width: 100%;">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-3">
                <button id="downloadBtn" class="btn btn-primary">Download Selected Image</button>
                <button onclick="printSelectedImage()" class="btn btn-secondary">Print Selected Image</button>
            </div>

        </div>
    </div>
</section>

<script>
    // Update preview when image selection changes
    document.addEventListener('DOMContentLoaded', function() {
        const imageRadios = document.querySelectorAll('.image-select');
        const previewImage = document.getElementById('previewImage');
        const downloadBtn = document.getElementById('downloadBtn');
        const imageCards = document.querySelectorAll('.image-card');

        // Add click event to image cards for better UX
        imageCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('form-check-input')) {
                    const radio = this.querySelector('.image-select');
                    radio.checked = true;
                    updatePreview(radio.value);
                    updateActiveCard();
                }
            });
        });

        // Update preview when radio changes
        imageRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                updatePreview(this.value);
                updateActiveCard();
            });
        });

        // Download button event
        downloadBtn.addEventListener('click', function() {
            const selectedImage = document.querySelector('input[name="selectedImage"]:checked').value;
            const link = document.createElement('a');
            link.href = selectedImage;
            link.download = selectedImage.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        function updatePreview(imageSrc) {
            previewImage.src = imageSrc;
        }

        function updateActiveCard() {
            imageCards.forEach(card => {
                const radio = card.querySelector('.image-select');
                if (radio.checked) {
                    card.classList.add('border-primary', 'border-2');
                } else {
                    card.classList.remove('border-primary', 'border-2');
                }
            });
        }

        // Initialize active card
        updateActiveCard();
    });

    function printSelectedImage() {
        const selectedImage = document.querySelector('input[name="selectedImage"]:checked').value;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print Image</title>
                    <style>
                        @page {
                            size: A4 landscape;
                            margin: 5mm;
                        }
                        body, html {
                            margin: 0;
                            padding: 0;
                            height: 100%;
                            width: 100%;
                            box-sizing: border-box;
                        }
                        .print-container {
                            padding: 5px;
                            text-align: center;
                            box-sizing: border-box;
                            height: 100%;
                            width: 100%;
                        }
                        img {
                            max-width: 100%;
                            max-height: 95%;
                            width: auto;
                            height: auto;
                            page-break-inside: avoid;
                            break-inside: avoid;
                            border: 1px solid #ccc;
                            padding: 10px;
                            background: #fff;
                        }
                    </style>
                </head>
                <body onload="window.print(); setTimeout(() => window.close(), 500);">
                    <div class="print-container">
                        <img src="${selectedImage}" alt="Image to Print">
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>

<style>
    .image-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .image-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .image-card.border-primary {
        box-shadow: 0 4px 15px rgba(0,123,255,0.3);
    }
    .form-check-input {
        cursor: pointer;
    }
    .form-check-label {
        cursor: pointer;
        font-weight: 500;
    }
</style>

<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>