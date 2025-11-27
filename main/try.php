<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8">
    <title>Saad_Hetar</title>

</head>

<body>

    <?php
    echo "<h1>Saad AL-Hetar</h1>";
    echo "<hr>";

    ?>

    <form action="try.php" method="post">
        Name: <input type="text" name="name"> <br> <br>
        <input type="submit">
    </form>
    <br>

    <form action="try.php" method="post">
        enter name: <input type="text" name="student"> <br>
        <input type="submit">
    </form>
   
    <?php
        // if (isset($_POST["name"])) {
        //     $var = $_POST["name"];
        //     echo $var;
        // }

        $d = new admin(1);
        $d->read_all_customer();

        $a = time();
        echo date('Y-m-d H:i:s');
        
        // $card_num = null;

        // if($card_num === null) {$card_num = 1;}
        // echo $card_num;

        //$students = array("saad" => "A+", "mashaal" => "B+");
        //echo $students[$_POST["student"]];


        try
        {
            
        }
        catch(PDOException $e)
        {
            return "failed to , <br>".$e->getMessage();
        }

        // $pdo->beginTransaction();
        //  $pdo->commit();
        // $pdo->rollBack
    ?>

</body>

</html>