<?php
session_start();
require_once("connection.php");
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']); // Xóa thông báo sau khi sử dụng
}

$userID = '';
if (isset($_GET['userId'])) {
    $userID = $_GET['userId'];
}
$sql = "SELECT * FROM users WHERE userID = '$userID'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullname'];
    //$oldPass = password_hash($_POST['oldpassword'], PASSWORD_BCRYPT, ['cost' => 12]);
    $oldPass = $_POST['oldpassword'];
    $newPass = $_POST['newpassword'];
    $rePass = $_POST['repassword'];
    if(strlen($oldPass) + strlen($newPass) + strlen($rePass) == 0) {
        $file = $user['linkAva'];
        if (isset($_FILES["file"])) {
            $file_name = $_FILES["file"]["name"];
            $file_tmp = $_FILES["file"]["tmp_name"];
            $file_size = $_FILES["file"]["size"];

            $upload_directory = "avaUser/";

            // Generate a unique filename using timestamp and original file extension
            $new_filename = $userID . '.' . pathinfo($file_name, PATHINFO_EXTENSION);

            $target_file = $upload_directory . $new_filename;

            if ($file_size > 0 && move_uploaded_file($file_tmp, $target_file)) {
                $file = $target_file;
            }
        }
        if (empty($file))
            $file = 'avaUser/anonymous.png';
        $stmt = $conn->prepare("UPDATE users SET fullName = ?, linkAva = ? WHERE userID = ?");
        $stmt->bind_param('sss', $fullName, $file, $userID);
        $result = $stmt->execute();
        header("Location: profile.php?userId=$userID");
        exit();
    } else if (!password_verify($oldPass, $user['password'])) {
        $_SESSION['message'] = "Mật khẩu cũ của bạn nhập không đúng!";
        header("Location: editProfile.php?userId=$userID");
        exit();
    } else {
        if (password_verify($newPass, $user['password'])) {
            $_SESSION['message'] = "Mật khẩu mới của bạn phải khác mật khẩu gần đây!";
            header("Location: editProfile.php?userId=$userID");
            exit();
        } else {
            if (strlen($newPass) < 6) {
                $_SESSION['message'] = "Mật khẩu mới cần ít nhất 6 ký tự.";
                header("Location: editProfile.php?userId=$userID");
                exit();
            } else {
                if ($newPass !== $rePass) {
                    $_SESSION['message'] = "Mật khẩu mới và mật khẩu nhập lại không khớp!";
                    header("Location: editProfile.php?userId=$userID");
                    exit();
                } else {
                    $file = $user['linkAva'];
                    if (isset($_FILES["file"])) {
                        $file_name = $_FILES["file"]["name"];
                        $file_tmp = $_FILES["file"]["tmp_name"];
                        $file_size = $_FILES["file"]["size"];

                        $upload_directory = "avaUser/";

                        // Generate a unique filename using timestamp and original file extension
                        $new_filename = $userID . '.' . pathinfo($file_name, PATHINFO_EXTENSION);

                        $target_file = $upload_directory . $new_filename;

                        if ($file_size > 0 && move_uploaded_file($file_tmp, $target_file)) {
                            $file = $target_file;
                        }
                    }
                    if (empty($file))
                        $file = 'avaUser/anonymous.png';
                    $stmt = $conn->prepare("UPDATE users SET fullName = ?, password = ?, linkAva = ? WHERE userID = ?");

                    if (!empty($newPass)) {
                        // Mã hóa mật khẩu mới
                        $hashedNewPass = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
                    }

                    $stmt->bind_param('ssss', $fullName, $hashedNewPass, $file, $userID);

                    $result = $stmt->execute();
                    header("Location: profile.php?userId=$userID");
                    exit();
                }
            }
        }
    }

}

; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel='stylesheet' type='text/css' media='screen' href='demo.css'>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="editProfile.css">
    <link rel="icon" type="png" href="uploads/uet.png">
</head>

<body>
    <h2>Edit profile</h2><br>
    <div style="align-items: center;">

        <form action="editProfile.php?userId=<?php echo $userID ?>" method="post" enctype="multipart/form-data">
            <img id="avatar-preview" src="<?php echo $user['linkAva']; ?>" alt="Avatar Preview" style="width: 100px; height: 100px; border: 1px solid #99f; border-radius: 50%; object-fit: cover;display: flex;
            flex-direction: row;margin: 10px auto;
            align-items: center;text-align: center;"><br>
            <label class="label" for="fullname">Full Name</label>
            <input class="input" type="text" id="fullname" name="fullname" value="<?php echo $user['fullName'] ?>" minlength="1" required>
            <label class="label" for="oldpassword">Old Password</label>
            <input class="input" type="password" id="oldpassword" name="oldpassword">
            <label class="label" for="password">New Password</label>
            <input class="input" type="password" id="newpassword" name="newpassword">
            <label class="label" for="repassword">New Password(re-enter)</label>
            <input class="input" type="password" id="repassword" name="repassword">

            <!-- <label for="file">Avatar<label>
            <input type="file" name="file" style="text-align: center; border: 1px solid #99f;" accept="image/*"><br> -->
            <label for="file">Link Avatar</label>
            <input type="file" name="file" id="file" style="display: none;" accept="image/*">
            <label style="width:auto; background-color: #F67C60; text-align: center; border-radius: 10px;"
                onclick="document.getElementById('file').click();">Choose File</label>
            <br>
            <hr>
            <button class="input" type="submit" style="margin-right: 10%;">Update</button> 
            <button onclick="goBackProfile()" > BACK</button>

        </form>
    </div>

    <script>
        function goBackProfile() {
            //window.location.href = "profile.php?";
            window.location.href = "profile.php?userId" + $userID;
        }
    </script>

    <script>
        document.getElementById('file').addEventListener('change', function () {
            var preview = document.getElementById('avatar-preview');
            var fileInput = document.getElementById('file');
            var file = fileInput.files[0];

            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block'; // Hiển thị khung ảnh
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = ''; // Xóa đường dẫn ảnh nếu không có ảnh
                preview.style.display = 'none'; // Ẩn khung ảnh
            }
        });
    </script>
</body>

</html>