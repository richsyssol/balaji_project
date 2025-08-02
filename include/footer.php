<?php include 'header.php'; ?>

<footer class="text-white text-center py-3 page-content" id="content" style="background-color:#00486b; width:93%; margin: 11px 6% 1% 6%; border-radius: 20px;">
    <div class="container">
        <div>
            <h5>DO THE DIGITAL :- Go for online payment try to avoid cheque payment.</h5>
        </div>
        <div style="display: flex; justify-content: center;">
            <ul style="display: flex; list-style: none; padding: 0; gap: 15px; align-items: center;">
                <li>
                    <input type="checkbox" id="priority">
                    <label for="priority">Priority</label>
                </li>
                <li>
                    <input type="checkbox" id="speed">
                    <label for="speed">Speed</label>
                </li>
                <li>
                    <input type="checkbox" id="accuracy">
                    <label for="accuracy">Accuracy</label>
                </li>
                <li>
                    <input type="checkbox" id="deadline">
                    <label for="deadline">Deadline</label>
                </li>
                <li>
                    <input type="checkbox" id="delivery">
                    <label for="delivery">Delivery</label>
                </li>
            </ul>
        </div>
    </div>
</footer>





<!-- Scroll to Top Button -->
<button id="scrollToTop" class="scroll-btn">⬆</button>

<!-- Scroll to Bottom Button -->
<button id="scrollToBottom" class="scroll-btn">⬇</button>

<script>
    // Scroll to Top
    document.getElementById("scrollToTop").addEventListener("click", function() {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // Scroll to Bottom
    document.getElementById("scrollToBottom").addEventListener("click", function() {
        window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
    });


    // Disable screen data copying
    // document.addEventListener('contextmenu', event => event.preventDefault()); // Disable right-click

    // document.addEventListener('keydown', function (event) {
    //     if (event.ctrlKey && (event.key === 'c' || event.key === 'x' || event.key === 'u')) {
    //         event.preventDefault();
    //         alert("Copying is disabled!");
    //     }
    // });
    
    </script>



<?php include 'header1.php'; ?>