<div id="eventRegistrationModal" class="event-registration-modal">
    <div class="event-registration-modal-content">
        <span class="event-registration-close-button">&times;</span>
        <!--<h2>Event Registration</h2>-->
        <div class="event-form-container">
            <h2 class="registration-information">Registration Information</h2>
            <div class="event-direction-slots">
                <p class="direction">Fill in the form below to register for the event.</p>
                <p class="slots-available">Number of slots available</p>
            </div>
            <form method="post" action="EventRegistrationHandler.php">
                <div class="form-field">
                    <input type="text" name="name" placeholder="Name" required>
                </div>
                <div class="form-field">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-field">
                    <input type="tel" name="phone" placeholder="Phone Number" required>
                </div>
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary">REGISTER</button>
                </div>
                <div class="deadline-wrapper">
                    <p class="deadline">Deadline here</p>
                </div>
            </form>
        </div>
    </div>
</div>