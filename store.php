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
   
            <img id="imageToPrint" src="asset/image/store.jpeg" alt="Image" height="800px" width="100%">

            <div class="mt-3">
                <a href="asset/image/store.jpeg" download class="btn btn-primary">Download Image</a>
                <button onclick="printImage()" class="btn btn-secondary">Print Image</button>
            </div>

        </div>
    </div>
</section>


<script>
    function printImage() {
    const imgSrc = document.getElementById("imageToPrint").src;
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
                    <img src="${imgSrc}" alt="Image to Print">
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
}

</script>


<?php 
    include 'include/header1.php'; 
    include 'include/footer.php';
?>

