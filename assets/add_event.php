
<?php 

if ($_SERVER['REQUEST_METHOD']=='POST'){
    extract($_POST);

    if(empty($title) || empty($artist_name) || empty($descr) || empty($date) || empty($loc)){
        echo "Please fill in all the required fields.";
        exit;
    }

    $servername = "localhost";
    $username = "std";
    $password = "";
    $dbname = "db";

    try{
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = 'INSERT INTO events(title, artist_name, `desc`, date, location, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, \'pending\', NOW(), NOW())';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $artist_name, $descr, $date, $loc]);

        echo "Event added successfully!";

    } catch(Exception $ex){
        echo "Connection error: " . $ex->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>ADD EVENTS</title>
</head>
<body>
    <h2>Add New Event</h2>

    <form action="" method="post">
        <label for="title">Event Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>

        <label for="artist_name">Artist Name:</label><br>
        <input type="text" id="artist_name" name="artist_name" required><br><br>

        <label for="descr">Description:</label><br>
        <textarea id="descr" name="descr" required></textarea><br><br>

        <label for="date">Date:</label><br>
        <input type="date" id="date" name="date" required><br><br>

        <label for="loc">Location:</label><br>
        <input type="text" id="loc" name="loc" required><br><br>

        <input type="submit" name="addBtn" value="Add Event">
    </form>

</body>
</html>
