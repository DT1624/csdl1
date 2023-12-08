<?php
session_start();

if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']); // Xóa thông báo sau khi sử dụng
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="png" href="images/uet.png">
    <title>Forum trao đổi</title>
    <style>
        #gender {
            font-size: 120%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div>
            <?php
            $loginType = 'none';
            $registerType = 'none';
            if (isset($_SESSION['loginSucces'])) {
                $loginType = 'block';
                $registerType = 'none';
            } else {
                $loginType = 'none';
                $registerType = 'block';
            }
            ?>
            <form id="register-form" class="form" action="register.php" method="post"
                style="display: <?= $registerType ?>;">
                <h1>Register</h1>
                <label class="label" for="firstname">Firstname</label>
                <input class="input" type="text" id="firstname" name="firstname">

                <label class="label" for="lastname">Lastname</label>
                <input class="input" type="text" id="lastname" name="lastname">

                <label class="label" for="username">Username</label>
                <input class="input" type="text" id="username" name="username" minlength="6" required>

                <label class="label" for="password">Password</label>
                <input class="input" type="password" id="password" minlength="6" name="password" required>

                <label class="label" for="birthday">Birthday</label>
                <input class="input" type="date" id="birthday" name="birthday" max="2026-01-01" min="1950-01-01"
                    oninput="validateYear(this)" required>

                <select id="gender" name="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>

                <br><input class="input" type="submit" value="Đăng Ký">
                <br><button type="button" onclick="loginForm()">Switch to Login</button>
            </form>
            <form id="login-form" class="form" action="login_process.php" method="post"
                style="display: <?= $loginType ?>;">
                <h1>Login</h1>
                <label class="label" for="username">Username</label>
                <input class="input" type="text" id="username" name="username" required><br>

                <label class="label" for="password">Password</label>
                <input class="input" type="password" id="password" name="password" required><br>

                <br><input class="input" type="submit" value="Đăng nhập">
                <br><button type="button" onclick="registerForm()">Switch to Register</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>