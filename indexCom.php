<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("connection.php");
include 'comments.inc.php';

$postID = '';
$commentID ='';
$comment = '';
$userID = $_SESSION['userID'];

if (isset($_GET['commentId'])) {
    $commentID = $_GET['commentId'];
}

if (isset($_GET['postId'])) {
    $postID = $_GET['postId'];
}

$sqlPost = "SELECT * FROM posts WHERE postID = '$postID'";
$resultPost = $conn->query($sqlPost);
// Kiểm tra xem có bài viết nào hay không
if ($resultPost->num_rows > 0) {
    $post = $resultPost->fetch_assoc();//bài viết hiện tại
    $userIDPost = $post['userIDPost'];
    $sql1 = "select * from users WHERE userID = '$userIDPost'";
    $result1 = $conn->query($sql1);
    $row1 = $result1->fetch_assoc();//thông tin về user hiện tại
}



if ($_SERVER['REQUEST_METHOD'] ==='POST') {
    
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    if($id == '1') {
        $commentID = $_POST['commentID'];
        $comment = $_POST['comment'];
        $sql = "UPDATE comments SET comment = '$comment', dateOfComment = default WHERE commentID = '$commentID'";
        $result = $conn->query($sql);
    } else if($id == '2') {
        $postID = isset($_GET['postId']) ? $_GET['postId'] : '';
        setComments($conn, $postID);
    } else if($id == '3') {
        $commentID = $_POST['commentID'];
        deleteComments($conn, $userID, $postID, $commentID);
    } else if($id == '4') {
        $commentID = 'CMT'.str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        $comment = $_POST['comment'];
        $repCommentID = $_POST['repCommentID'];
        $userIDNotice = $_POST['userIDComment'];//user sẽ nhận tbao

        //cập nhật bảng posts
        $sql = "UPDATE posts SET numberComments = numberComments + 1 WHERE postID = '$postID'";
        $result = $conn->query($sql);

        //insert vào bảng cmt
        $stmt = $conn->prepare("INSERT INTO comments (commentID, userIDComment, postIDComment, repCommentID, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $commentID, $userID, $postID, $repCommentID, $comment);
        $result = $stmt->execute();

        // ghi vào interact post
        $sql = "SELECT * FROM interactposts WHERE userIDInteract = '$userID' AND postIDInteract = '$postID'";
        $result = $conn->query($sql);
        if($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO interactposts (userIDInteract, postIDInteract) VALUES (?, ?)");
            $stmt->bind_param("ss", $userID, $postID);
            $result = $stmt->execute();
        }
        $sql = "UPDATE interactposts SET isComment = isComment + 1 WHERE userIDInteract = '$userID' AND postIDInteract = '$postID'";
        $result = $conn->query($sql);

        //ghi vào bảng notice (reply)
        $noticeID = 'NO'.str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        $fullName = $row1['fullName'];
        $message = 'Người dùng: '.$fullName.' đã reply một comment của bạn.';
        if($userIDNotice != $userID) {
            $stmt = $conn->prepare("INSERT INTO notices (noticeID, userIDNotice, userIDDo, postIDNotice, commentIDNotice, message) VALUES (?, ?, ?, ?, ?, ?);");
            $stmt->bind_param("ssssss", $noticeID, $userIDNotice, $userID, $postID, $commentID, $message);
            $result = $stmt->execute();
        }
        //ghi vào bảng notice(cmt trong post)
        $noticeID = 'NO'.str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        $titlePost = $post['titlePost'];
        $userIDNotice = $userIDPost;
        $message = 'Người dùng: '.$fullName.' đã comment bài viết '.$titlePost.' của bạn.';
        if($userIDNotice != $userID) {
            $stmt = $conn->prepare("INSERT INTO notices (noticeID, userIDNotice, userIDDo, postIDNotice, commentIDNotice, message) VALUES (?, ?, ?, ?, ?, ?);");
            $stmt->bind_param("ssssss", $noticeID, $userIDNotice, $userID, $postID, $commentID, $message);
            $result = $stmt->execute();
        }
    }
    header("Location: indexCom.php?postId=$postID");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Title of the document</title>
    <link rel="stylesheet" type="text/css" href="comment.css">
    <link rel="stylesheet" type="text/css" href="post.css">
    <link rel="icon" type="png" href="uploads/uet.png">
</head>

<body>
    <div id="comments-section" >
        <?php
        $imageHtml = $post['imagePost'] ? "<p style='text-align: center;'><img class='post-image' style='width: 250px; height: auto;' src='{$post['imagePost']}' alt='Post Image'></p>" : '';
        ?>
        <div style="background-color: #E8E8C3" style="border-radius: 10%;">            
            <a href="profile.php?userId=<?php echo $post['userIDPost']  ?>" style='text-overflow: ellipsis;text-decoration:none;'>
                <div class='comment-container' style='display: flex; align-items: center;'>
                    <img src="<?php echo $row1['linkAva'] ?>" class="w3-circle" style="height:60px;width:60px;border-radius: 50%; object-fit: cover; margin-right: 10px;" alt="Avatar">
                    <div style='text-align: left;'>     
                        <span class='user-name' style="width: 300px"><?php echo $row1['fullName'] ?></span><br>
                    </div>
                </div>
            </a>

            <p style="text-align: right; font-size: small; font-weight: 600"><i><?php echo $post['dateOfPost']; ?></i></p>
            <h1 style="text-align: left; margin: 0px 50px 0px 50px"><i><?php echo $post['titlePost']; ?></i></h1>
            <?php echo $imageHtml; ?>
            <div class='description-container' style='height: auto; max-height: 400px; resize:none; overflow-y: auto;'>
                <p><?php echo $post['descriptionPost']; ?></p>
            </div>
        </div>
        <hr style="border-width: 10px">

        <form method='POST' action="indexCom.php?postId=<?php echo $postID; ?>&id=2">
            <input type='hidden' name='userIDComment'>
            <textarea  style='height: 200px; resize:none' name='comment' required></textarea><br>
            <div class='reaction-comment-container'>
                <div class='like-container' id='likeContainer_{$postID}'>
                    <span class='reaction-count'><?php echo $post['numberReactions'] ?></span>
                    <div class='like-button' id='likeButton_{$postID}'>
                        <a href="processLike.php?userId=<?php echo $userID ?>&postId=<?php echo $postID ?>" class='like-button' style="text-decoration:none;">❤️</a>
                    </div>
                </div>
            
                <div>                    
                    <button onclick="goBackForum()"> BACK</button>
                    <button type='submit' name='commentSubmit'>Comment</button>
                </div>
            </div>
        </form>
        <hr style='border-width: 10px; border-color:#2D0258;'>

        <?php
        getComments($conn, $userID, $postID);
        ?>

    </div>

    <script src="app.js"></script>
    <script>
        function goBackForum() {
            window.location.href = "forum.php?category=recently&page=1";
        }
    </script> 
</body>

</html>