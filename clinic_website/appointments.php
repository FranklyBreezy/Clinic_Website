<?php
session_start();
include('db.php');
include('header.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if the logged-in user is an admin
$is_admin = $_SESSION['role'] === 'admin';

// Handle updating the appointment status for admins
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && $is_admin) {
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['status'];

    $update_status_sql = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($update_status_sql);
    $stmt->bind_param('si', $new_status, $appointment_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Appointment status updated successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error updating status: ' . $conn->error . '</div>';
    }
}

// For normal users, allow them to schedule an appointment
if (!$is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['schedule_appointment'])) {
    $doctor_name = $_POST['doctor_name'];
    $appointment_date = $_POST['appointment_date'];
    $reason_for_visit = $_POST['reason_for_visit'];
    $user_id = $_SESSION['user_id'];

    // Insert the new appointment into the database
    $sql = "INSERT INTO appointments (user_id, doctor_name, appointment_date, reason_for_visit) 
            VALUES ('$user_id', '$doctor_name', '$appointment_date', '$reason_for_visit')";

    if ($conn->query($sql) === TRUE) {
        echo '<div class="alert alert-success">Appointment scheduled successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error scheduling appointment: ' . $conn->error . '</div>';
    }
}

// Fetch all appointments (admin view)
if ($is_admin) {
    $sql = "SELECT * FROM appointments ORDER BY appointment_date DESC";
    $result = $conn->query($sql);
}

?>

<div class="container">
    <?php if ($is_admin): ?>
        <h2>Manage Appointments</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Doctor Name</th>
                        <th>Appointment Date</th>
                        <th>Status</th>
                        <th>Reason for Visit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['appointment_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['appointment_date'])); ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason_for_visit']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                    <select name="status" class="form-control" required>
                                        <option value="pending" <?php if ($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                        <option value="confirmed" <?php if ($row['status'] == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                                        <option value="completed" <?php if ($row['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                        <option value="canceled" <?php if ($row['status'] == 'canceled') echo 'selected'; ?>>Canceled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-warning mt-2">Update Status</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No appointments found.</p>
        <?php endif; ?>
    <?php else: ?>
        <h2>Schedule an Appointment</h2>
        <form method="POST">
            <div class="form-group">
                <label for="doctor_name">Doctor Name</label>
                <input type="text" name="doctor_name" id="doctor_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="appointment_date">Appointment Date</label>
                <input type="datetime-local" name="appointment_date" id="appointment_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="reason_for_visit">Reason for Visit</label>
                <textarea name="reason_for_visit" id="reason_for_visit" class="form-control" required></textarea>
            </div>
            <button type="submit" name="schedule_appointment" class="btn btn-primary">Schedule Appointment</button>
        </form>
    <?php endif; ?>
</div>

<?php include('footer.php'); ?>
