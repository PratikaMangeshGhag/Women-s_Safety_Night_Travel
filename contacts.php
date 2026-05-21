<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$logged_in = isset($_SESSION['user_id']);
$success = "";
$error = "";
$activeNav = 'contacts';
$allowed_relationships = ['Mother', 'Father', 'Sister', 'Brother', 'Friend', 'Partner', 'Other'];

if ($logged_in) {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['add_contact'])) {
        $name = trim($_POST['contact_name'] ?? '');
        $phone = trim($_POST['contact_phone'] ?? '');
        $relationship = $_POST['relationship'] ?? '';
        if ($name === '' || strlen($name) < 2) {
            $error = "Please enter a valid contact name.";
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $error = "Contact phone number must be exactly 10 digits.";
        } elseif (!in_array($relationship, $allowed_relationships, true)) {
            $error = "Please choose a valid relationship.";
        } else {
            $sql = "INSERT INTO emergency_contacts (user_id, contact_name, contact_phone, relationship) VALUES ('$user_id', '$name', '$phone', '$relationship')";
            if (mysqli_query($conn, $sql)) {
                $success = "Contact added successfully!";
            } else {
                $error = "Failed to add contact.";
            }
        }
    }

    if (isset($_GET['delete'])) {
        $contact_id = $_GET['delete'];
        $sql = "DELETE FROM emergency_contacts WHERE contact_id='$contact_id' AND user_id='$user_id'";
        mysqli_query($conn, $sql);
        header("Location: contacts.php");
        exit();
    }

    if (isset($_POST['update_contact'])) {
        $contact_id = (int) ($_POST['contact_id'] ?? 0);
        $name = trim($_POST['contact_name'] ?? '');
        $phone = trim($_POST['contact_phone'] ?? '');
        $relationship = $_POST['relationship'] ?? '';
        if ($contact_id <= 0) {
            $error = "Invalid contact selected.";
        } elseif ($name === '' || strlen($name) < 2) {
            $error = "Please enter a valid contact name.";
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $error = "Contact phone number must be exactly 10 digits.";
        } elseif (!in_array($relationship, $allowed_relationships, true)) {
            $error = "Please choose a valid relationship.";
        } else {
            $sql = "UPDATE emergency_contacts SET contact_name='$name', contact_phone='$phone', relationship='$relationship' WHERE contact_id='$contact_id' AND user_id='$user_id'";
            if (mysqli_query($conn, $sql)) {
                $success = "Contact updated successfully!";
            } else {
                $error = "Failed to update contact.";
            }
        }
    }

    $contacts = mysqli_query($conn, "SELECT * FROM emergency_contacts WHERE user_id='$user_id'");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeNight - Contacts</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: url('bg.png') no-repeat center center fixed;
            background-size: cover;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.75);
            z-index: 0;
        }

        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 50px;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .logo span { color: #e74c3c; }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover { color: #e74c3c; }
        .nav-links a.active { color: #e74c3c; }

        .nav-buttons { display: flex; gap: 12px; }

        .btn-outline {
            padding: 8px 20px;
            border: 1.5px solid #fff;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-outline:hover { background: #fff; color: #1a1a2e; }

        .btn-solid {
            padding: 8px 20px;
            background: #e74c3c;
            border: 1.5px solid #e74c3c;
            border-radius: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-solid:hover { background: #c0392b; }

        .page-wrapper {
            position: relative;
            z-index: 1;
            padding: 110px 60px 60px;
            max-width: 960px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 36px;
        }

        .page-header h2 {
            font-size: 36px;
            font-weight: 700;
            color: #fff;
        }

        .page-header h2 span { color: #e74c3c; }

        .page-header p {
            color: rgba(255,255,255,0.4);
            font-size: 14px;
            margin-top: 8px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .alert.success {
            background: rgba(46,204,113,0.12);
            border: 1px solid rgba(46,204,113,0.35);
            color: #2ecc71;
        }

        .alert.error {
            background: rgba(231,76,60,0.12);
            border: 1px solid rgba(231,76,60,0.35);
            color: #e74c3c;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }

        .card {
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.2);
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }

        .card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            font-size: 13px;
            color: #fff;
            outline: none;
            transition: all 0.3s;
        }

        select option { background: #1a1a2e; color: #fff; }
        input::placeholder { color: rgba(255,255,255,0.2); }
        input:focus, select:focus {
            border-color: #e74c3c;
            background: rgba(255,255,255,0.09);
        }

        .submit-btn {
            width: 100%;
            padding: 13px;
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 6px;
            letter-spacing: 0.3px;
        }

        .submit-btn:hover {
            background: #c0392b;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(231,76,60,0.35);
        }

        .contacts-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .contact-card {
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.15);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: border-color 0.3s;
        }

        .contact-card:hover { border-color: rgba(231,76,60,0.45); }

        .contact-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(231,76,60,0.15);
            border: 1.5px solid rgba(231,76,60,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            color: #e74c3c;
            flex-shrink: 0;
        }

        .contact-info { flex: 1; }

        .contact-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 3px;
        }

        .contact-info p {
            font-size: 12px;
            color: rgba(255,255,255,0.4);
        }

        .contact-badge {
            background: rgba(231,76,60,0.12);
            border: 1px solid rgba(231,76,60,0.25);
            color: #e74c3c;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 500;
            white-space: nowrap;
        }

        .contact-actions { display: flex; gap: 8px; }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            text-decoration: none;
        }

        .edit-btn {
            background: rgba(52,152,219,0.12);
            border: 1px solid rgba(52,152,219,0.25);
        }

        .edit-btn:hover { background: rgba(52,152,219,0.28); }

        .delete-btn {
            background: rgba(231,76,60,0.12);
            border: 1px solid rgba(231,76,60,0.25);
        }

        .delete-btn:hover { background: rgba(231,76,60,0.28); }

        /* NOT LOGGED IN */
        .not-logged-in {
            grid-column: 1 / -1;
            padding: 80px 10px 40px;
        }

        .not-logged-in h1 {
            font-size: 76px;
            font-weight: 700;
            color: #e74c3c;
            line-height: 1.05;
            margin-bottom: 24px;
            text-align: left;
        }

        .not-logged-in .sub-text {
            font-size: 15px;
            color: rgba(255,255,255,0.45);
            text-align: left;
            margin-bottom: 8px;
        }

        .not-logged-in .sub-text a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid rgba(231,76,60,0.4);
            padding-bottom: 1px;
            transition: border-color 0.3s;
        }

        .not-logged-in .sub-text a:hover {
            border-color: #e74c3c;
        }

        /* EMPTY STATE (logged in, no contacts) */
        .empty-box {
            background: linear-gradient(135deg, rgba(20,20,40,0.95), rgba(40,10,10,0.9));
            border: 1px solid rgba(231,76,60,0.15);
            border-radius: 14px;
            padding: 50px 30px;
            text-align: center;
        }

        .empty-box p {
            font-size: 14px;
            color: rgba(255,255,255,0.35);
            margin-top: 14px;
            line-height: 1.8;
        }

        /* EDIT MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.75);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active { display: flex; }

        .modal {
            background: linear-gradient(135deg, #14142a, #2a0a0a);
            border: 1px solid rgba(231,76,60,0.3);
            border-radius: 18px;
            padding: 34px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        }

        .modal h3 {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 24px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .cancel-btn {
            flex: 1;
            padding: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cancel-btn:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>

<?php include 'includes/navigation.php'; ?>

<div class="page-wrapper">
    <div class="page-header">
        <h2>Emergency <span>Contacts</span></h2>
        <p>Manage the people who will be notified in case of an emergency</p>
    </div>

    <?php if($success != ""): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error != ""): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="grid">

        <?php if($logged_in): ?>

        <!-- ADD CONTACT FORM -->
        <div class="card">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                Add New Contact
            </h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="contact_name" placeholder="Contact's full name" minlength="2" maxlength="60" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="contact_phone" placeholder="Contact's phone number" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter a 10-digit phone number." required>
                </div>
                <div class="form-group">
                    <label>Relationship</label>
                    <select name="relationship">
                        <option value="Mother">Mother</option>
                        <option value="Father">Father</option>
                        <option value="Sister">Sister</option>
                        <option value="Brother">Brother</option>
                        <option value="Friend">Friend</option>
                        <option value="Partner">Partner</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <button type="submit" name="add_contact" class="submit-btn">Add Contact</button>
            </form>
        </div>

        <!-- CONTACTS LIST -->
        <div class="contacts-list">
            <?php if(mysqli_num_rows($contacts) > 0): ?>
                <?php while($c = mysqli_fetch_assoc($contacts)): ?>
                    <div class="contact-card">
                        <div class="contact-avatar">
                            <?php echo strtoupper(substr($c['contact_name'], 0, 1)); ?>
                        </div>
                        <div class="contact-info">
                            <h4><?php echo $c['contact_name']; ?></h4>
                            <p><?php echo $c['contact_phone']; ?></p>
                        </div>
                        <div class="contact-badge"><?php echo $c['relationship']; ?></div>
                        <div class="contact-actions">
                            <button class="action-btn edit-btn" onclick="openEdit(<?php echo $c['contact_id']; ?>, '<?php echo $c['contact_name']; ?>', '<?php echo $c['contact_phone']; ?>', '<?php echo $c['relationship']; ?>')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <a href="contacts.php?delete=<?php echo $c['contact_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this contact?')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-box">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <p>No emergency contacts yet.<br>Add one using the form on the left.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php else: ?>

        <!-- NOT LOGGED IN -->
        <div class="not-logged-in">
            <h1>No contacts<br>Available.</h1>
            <p class="sub-text"><a href="login.php">Login</a> to view your contacts</p>
            <p class="sub-text">New here? <a href="register.php">Create an account</a></p>
        </div>

        <?php endif; ?>

    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>Edit Contact</h3>
        <form method="POST" action="">
            <input type="hidden" name="contact_id" id="edit_id">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="contact_name" id="edit_name" minlength="2" maxlength="60" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="contact_phone" id="edit_phone" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter a 10-digit phone number." required>
            </div>
            <div class="form-group">
                <label>Relationship</label>
                <select name="relationship" id="edit_relationship">
                    <option value="Mother">Mother</option>
                    <option value="Father">Father</option>
                    <option value="Sister">Sister</option>
                    <option value="Brother">Brother</option>
                    <option value="Friend">Friend</option>
                    <option value="Partner">Partner</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeEdit()">Cancel</button>
                <button type="submit" name="update_contact" class="submit-btn" style="flex:1">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, phone, relationship) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_relationship').value = relationship;
    document.getElementById('editModal').classList.add('active');
}

function closeEdit() {
    document.getElementById('editModal').classList.remove('active');
}
</script>

</body>
</html>
