<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Events</title>
    <link rel="stylesheet" href="styles1.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="logo-section">
            <img src="SCHEDULE RBG.png" alt="Schedule Logo" class="logo1">
        </div>
        
        <div class="separator"></div>
        
        <div class="nav-menu">
            <a href="Dashboard.php" class="nav-item" id="dashboard">
                <img src="dashboard-icon.png" alt="Dashboard" class="nav-icon">
                <span>Dashboard</span>
            </a>
            <a href="MyEvents.php" class="nav-item" id="my-events">
                <img src="my-events-icon.png" alt="My Events" class="nav-icon">
                <span>My Events</span>
            </a>
            <a href="NewEvent.php" class="nav-item" id="new-event">
                <img src="new-event-icon.png" alt="New Event" class="nav-icon">
                <span>New Event</span>
            </a>
            <a href="AvailableEvent.php" class="nav-item" id="available-events">
                <img src="available-events-icon.png" alt="Available Events" class="nav-icon">
                <span>Available Events</span>
            </a>
        </div>

        <div class="bottom-menu">
            <a href="Profile.php" class="nav-item" id="profile">
                <img src="profile-icon.png" alt="Profile" class="nav-icon">
                <span>Profile</span>
            </a>
            <a href="Logout.php" class="nav-item" id="logout">
                <img src="logout-icon.png" alt="Log Out" class="nav-icon">
                <span>Log Out</span>
            </a>
        </div>


    </div>

    <!-- Main Content Area -->
    <div class="participants-main-content">
        <div class="participants-in-main-content">
            <div class="header">
                <h1>Participants List</h1>
                <p>Manage and track event participants effectively,</p>
            </div>

            <div class="separator-line"></div>
            
            <p class="description">
                Select an event to see the participants list.
            </p>

            <div class="search-bar">
                <input type="text" placeholder="Search events..." class="search-input">
                <button class="search-button">Search</button>
            </div>

            <div class="filter-container">
                <select id="filter" class="filter-select" onchange="applyFilter(this.value)">
                    <option value="">Filter by Category</option>
                    <option value="all">All</option>
                    <option value="business-and-finance">Business & Finance</option>
                    <option value="technology-and-innovation">Technology & Innovation</option>
                    <option value="health-and-wellness">Health & Wellness</option>
                    <option value="personal-and-professional-development">Personal & Professional Development</option>
                </select>
                <script>
                    function applyFilter(filterValue) {
                        console.log('Filter applied:', filterValue);
                        // Add your logic here to handle the filter change
                    }
                </script>
            </div>

            <div class="participants-list-container">
                <div class="participants-search-bar">
                    <input type="text" placeholder="Search participants..." class="participants-search-input">
                    <button class="participants-search-button">Search</button>
                </div>
                <div class="participants-list">
                    <div class="participants-list-labels">
                        <p class="label">Name</p>
                        <p class="label">Email</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Get all navigation items
        const navItems = document.querySelectorAll('.nav-item');
        
        // Get current page URL
        const currentPage = window.location.pathname;
        
        // Remove 'active' class from all navigation items
        navItems.forEach(function(item) {
        item.classList.remove('active');
        });
        
        // Find which nav item matches the current page and set it as active
        navItems.forEach(function(item) {
        // Get the href attribute
        const href = item.getAttribute('href');
        
        // Extract just the filename from the href
        const hrefPage = href.split('/').pop();
        
        // Extract just the filename from the current URL
        const currentPageName = currentPage.split('/').pop();
        
        // Check if this nav item corresponds to the current page
        if (
            currentPageName === hrefPage || 
            (currentPageName === 'Dashboard.php' && item.id === 'dashboard') ||
            (currentPageName === '' && item.id === 'dashboard') ||
            (currentPageName === 'ViewEvent.php' && item.id === 'my-events') ||
            (currentPageName === 'ParticipantsList.php' && item.id === 'my-events')    
        ) {
            item.classList.add('active');
        }
        });

        // Add click event listeners for navigation within the same page
        navItems.forEach(function(item) {
        item.addEventListener('click', function() {
            // We don't need to do anything here since the page will reload
            // and the above code will set the correct active state
        });
        });
        });
    </script>
    
</body>
</html>