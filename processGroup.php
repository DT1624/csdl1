<?php
    require_once("connection.php");
    session_start();
    if (isset($_SESSION['message'])) {
        echo "<script>alert('" . $_SESSION['message'] . "');</script>";
        unset($_SESSION['message']); // Xóa thông báo sau khi sử dụng
    }
    $categoryGroup = $_POST["categoryGroup"];
    $categoryGroup = strtolower($categoryGroup);
    $stmt = $conn->prepare("SELECT categoryGroup FROM groupss WHERE categoryGroup = LOWER('$categoryGroup')");
    $result = $stmt->execute();

    if ($result) {
        $stmt->store_result();
        $numRows = $stmt->num_rows;

        $stmt2 = $conn->prepare("SELECT * FROM groupss");
        $result2 = $stmt2->execute();
        $stmt2->store_result();
        $num = $stmt2->num_rows + 1;
        
        if ($numRows === 0) {
            $stmt1 = $conn->prepare("INSERT INTO groupss (groupID, categoryGroup) VALUES(?, ?)");
            $stmt1->bind_param("ss", $num, $categoryGroup);
            $result1 = $stmt1->execute();
            $stmt1->close();
            echo "Tạo Group mới thành công";
        } else {
            echo "Đã tồn tại tên Group";
        }
    } else {
        // Xử lý lỗi nếu có
        echo "Lỗi: " . $stmt->error;
    }
    $stmt->close();
?>