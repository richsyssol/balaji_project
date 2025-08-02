
<nav class="navbar px-3" style="background-color:#1da5e9; border-radius: 29px; width: 93%; margin: 11px 6% 0% 6%; z-index:1;">
    <button id="toggleNavbar" type="button" class="btn btn-light bg-white rounded-pill shadow-sm px-4 "><i class="fa fa-bars mr-2"></i></button>
    <!-- <a class="navbar-brand" href="#">Navbar</a> -->


    <!-- show logout session timer -->
    <div 
        id="session-timer" style="font-size: 18px; font-weight: bold; color: black;">
    </div>

    <ul class="nav nav-pills">
     
      <li class="nav-item dropdown me-5">
        <a class="nav-link dropdown-toggle text-light fw-bold" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
            <?php 
            // session_start(); // Ensure session is started
            if (isset($_SESSION['role'])) {
                echo strtoupper($_SESSION['role']);  // Displays "ADMIN" or "USER"
            } 
            ?>
        </a>


        <ul class="dropdown-menu">
          <li><a class="dropdown-item text-dark" href="logout">Logout</a></li>
         
        </ul>
      </li>
    </ul>
</nav>


<script>

// session time out script for auto logout

function updateSessionTimer() {
    fetch("session_check.php?fetch=time")
        .then(response => response.json())
        .then(data => {
            if (data.remaining_time <= 0) {
                window.location.href = "login.php?timeout=true"; // Auto logout when session expires
            } else {
                let hours = Math.floor(data.remaining_time / 3600);
                let minutes = Math.floor((data.remaining_time % 3600) / 60);
                let seconds = data.remaining_time % 60;
                
                document.getElementById("session-timer").textContent = 
                    `LogOut in: ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
        })
        .catch(error => console.error("Error fetching session time:", error));
}

// Fetch and update session timer every second
setInterval(updateSessionTimer, 1000);

// Initial fetch
updateSessionTimer();



</script>