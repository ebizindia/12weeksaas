<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Account Settings</h2>

            <!-- Success/Error Messages -->
            <div id="settings-message" class="alert" style="display:none;"></div>

            <!-- Settings Tabs -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        <i class="fas fa-user"></i> Profile
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                        <i class="fas fa-lock"></i> Password
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab">
                        <i class="fas fa-shield-alt"></i> Privacy
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                        <i class="fas fa-bell"></i> Notifications
                    </button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 p-4 bg-white" id="settingsTabContent">

                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <h4 class="mb-3">Profile Information</h4>
                    <form id="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fname" name="fname"
                                       value="<?= htmlspecialchars($profile['fname'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lname" name="lname"
                                       value="<?= htmlspecialchars($profile['lname'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email"
                                       value="<?= htmlspecialchars($profile['email'] ?? '') ?>" readonly disabled>
                                <small class="form-text text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="time_zone" class="form-label">Time Zone</label>
                                <select class="form-select" id="time_zone" name="time_zone">
                                    <?php
                                    $current_tz = $preferences['time_zone'] ?? 'Asia/Kolkata';
                                    $timezones = [
                                        'Asia/Kolkata' => 'India (IST)',
                                        'America/New_York' => 'Eastern Time (ET)',
                                        'America/Chicago' => 'Central Time (CT)',
                                        'America/Denver' => 'Mountain Time (MT)',
                                        'America/Los_Angeles' => 'Pacific Time (PT)',
                                        'Europe/London' => 'London (GMT)',
                                        'Europe/Paris' => 'Paris (CET)',
                                        'Australia/Sydney' => 'Sydney (AEST)',
                                        'UTC' => 'UTC'
                                    ];
                                    foreach ($timezones as $tz => $label) {
                                        $selected = ($tz === $current_tz) ? 'selected' : '';
                                        echo "<option value=\"$tz\" $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_format" class="form-label">Date Format</label>
                                <select class="form-select" id="date_format" name="date_format">
                                    <?php
                                    $current_format = $preferences['date_format'] ?? 'd-m-Y';
                                    $formats = [
                                        'd-m-Y' => 'DD-MM-YYYY (31-12-2025)',
                                        'm-d-Y' => 'MM-DD-YYYY (12-31-2025)',
                                        'Y-m-d' => 'YYYY-MM-DD (2025-12-31)'
                                    ];
                                    foreach ($formats as $fmt => $label) {
                                        $selected = ($fmt === $current_format) ? 'selected' : '';
                                        echo "<option value=\"$fmt\" $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Profile
                        </button>
                    </form>
                </div>

                <!-- Password Tab -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <h4 class="mb-3">Change Password</h4>
                    <form id="password-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="change_password">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                    <small class="form-text text-muted">Minimum 8 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Privacy Tab -->
                <div class="tab-pane fade" id="privacy" role="tabpanel">
                    <h4 class="mb-3">Privacy Settings</h4>
                    <form id="privacy-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="update_privacy">

                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Leaderboard Visibility</h5>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="leaderboard_visible" name="leaderboard_visible"
                                           value="1" <?= ($leaderboard_settings['leaderboard_visible'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="leaderboard_visible">
                                        Show me on the public leaderboard
                                    </label>
                                </div>
                                <p class="text-muted small">
                                    When enabled, your progress and rankings will be visible to other users on the leaderboard.
                                    Your goals and tasks remain private.
                                </p>

                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Display Name (Optional)</label>
                                    <input type="text" class="form-control" id="display_name" name="display_name"
                                           value="<?= htmlspecialchars($leaderboard_settings['display_name'] ?? '') ?>"
                                           placeholder="Leave blank to use your real name">
                                    <small class="form-text text-muted">
                                        Use a pseudonym if you prefer not to show your real name on the leaderboard
                                    </small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shield-alt"></i> Save Privacy Settings
                        </button>
                    </form>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-pane fade" id="notifications" role="tabpanel">
                    <h4 class="mb-3">Email Notification Preferences</h4>
                    <form id="notifications-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="update_notifications">

                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_weekly_summary" name="email_weekly_summary"
                                           value="1" <?= ($preferences['email_weekly_summary'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_weekly_summary">
                                        <strong>Weekly Progress Summary</strong><br>
                                        <small class="text-muted">Receive a weekly email with your progress report</small>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_achievements" name="email_achievements"
                                           value="1" <?= ($preferences['email_achievements'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_achievements">
                                        <strong>Achievement Notifications</strong><br>
                                        <small class="text-muted">Get notified when you unlock new achievements</small>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_reminders" name="email_reminders"
                                           value="1" <?= ($preferences['email_reminders'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_reminders">
                                        <strong>Daily Task Reminders</strong><br>
                                        <small class="text-muted">Receive daily reminders about pending tasks</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-bell"></i> Save Notification Preferences
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// Handle form submissions via AJAX
$(document).ready(function() {
    // Profile form
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        submitSettingsForm($(this), 'Profile');
    });

    // Password form
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        // Validate passwords match
        if ($('#new_password').val() !== $('#confirm_password').val()) {
            showMessage('Passwords do not match', 'danger');
            return;
        }
        submitSettingsForm($(this), 'Password');
    });

    // Privacy form
    $('#privacy-form').on('submit', function(e) {
        e.preventDefault();
        submitSettingsForm($(this), 'Privacy');
    });

    // Notifications form
    $('#notifications-form').on('submit', function(e) {
        e.preventDefault();
        submitSettingsForm($(this), 'Notifications');
    });

    function submitSettingsForm($form, section) {
        const formData = $form.serialize();
        const $btn = $form.find('button[type="submit"]');
        const originalText = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: 'account-settings.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.error_code === 0) {
                    showMessage(response.message, 'success');
                    if (section === 'Password') {
                        $form[0].reset(); // Clear password fields
                    }
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function() {
                showMessage('An error occurred. Please try again.', 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    }

    function showMessage(message, type) {
        const $msg = $('#settings-message');
        $msg.removeClass('alert-success alert-danger alert-info alert-warning')
            .addClass('alert-' + type)
            .html(message)
            .fadeIn();

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $msg.fadeOut();
        }, 5000);

        // Scroll to message
        $('html, body').animate({
            scrollTop: $msg.offset().top - 100
        }, 300);
    }
});
</script>
