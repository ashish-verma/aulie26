<?php
ini_set("display_errors",1);
$servername = "localhost";
$username = "root";
$password = "aulie*1234";
$database = 'auliestore';
// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$productId = 338 ;
$newProductId = 393;

$sql = "SELECT * FROM catalog_product_option WHERE product_id=".$newProductId." ORDER BY option_id DESC"  ;
$result = $conn->query($sql);
if($row = $result->fetch_assoc()) die('exist');


$sql = "SELECT * FROM catalog_product_option WHERE product_id=".$productId." ORDER BY option_id DESC"  ;
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    //echo '<pre>';print_r($row);echo '</pre>';   

    $optionId= $row['option_id'];
    unset($row['option_id']);
    $row['product_id'] = $newProductId;
    $optionRow = implode("', '",$row);
    
    $sql= "INSERT INTO catalog_product_option VALUES (NULL,'$optionRow')";
    $conn->query($sql);
    $newOptionId = $conn->insert_id;


    $sql = "SELECT * FROM catalog_product_option_title WHERE option_id=".$optionId ;
    $result3 = $conn->query($sql);    
    $row3 = $result3->fetch_assoc(); 
        //echo '<pre>';print_r($row3);echo '</pre>';die;
    if($row3){
        echo $row3['title'].'---';  

        unset($row3['option_title_id']);
        $row3['option_id'] = $newOptionId;
        $optionTitleRow = implode("', '",$row3);
        $sql= "INSERT INTO catalog_product_option_title VALUES (NULL,'$optionTitleRow')";
        $conn->query($sql);        
    }

    echo $row['type'].'---';

    $sql = "SELECT * FROM catalog_product_option_price WHERE option_id=".$optionId ;
    $result2 = $conn->query($sql);    
    $row2 = $result2->fetch_assoc();
        //echo '<pre>';print_r($row2);echo '</pre>';
    if($row2){
        echo $row2['price'].'---'.$row2['price_type'] ;
        unset($row2['option_price_id']);
        $row2['option_id'] = $newOptionId;
        $optionPriceRow = implode("', '",$row2);
        $sql= "INSERT INTO catalog_product_option_price VALUES (NULL,'$optionPriceRow')";
        $conn->query($sql);
    }
    
    echo '<br>';

    $sql = "SELECT * FROM catalog_product_option_type_value WHERE option_id=".$optionId ;
    $result4 = $conn->query($sql);    
    while($row4 = $result4->fetch_assoc()) {
        //echo '<pre>';print_r($row4);echo '</pre>';
        echo "- - -";

        $optionTypeId= $row4['option_type_id'];
        unset($row4['option_type_id']);
        $row4['option_id'] = $newOptionId;
        $optionTypeRow = implode("', '",$row4);
        
        $sql= "INSERT INTO catalog_product_option_type_value VALUES (NULL,'$optionTypeRow')";
        $conn->query($sql);
        $newOptionTypeId = $conn->insert_id;

        
        $sql = "SELECT * FROM catalog_product_option_type_title WHERE option_type_id=".$optionTypeId ;
        $result5 = $conn->query($sql);    
        $row5 = $result5->fetch_assoc();
            //echo '<pre>';print_r($row5);echo '</pre>';
        if($row5){
            echo $row5['title'].'---';
            unset($row5['option_type_title_id']);
            $row5['option_type_id'] = $newOptionTypeId;
            $optionTypeTitleRow = implode("', '",$row5);
            $sql= "INSERT INTO catalog_product_option_type_title VALUES (NULL,'$optionTypeTitleRow')";
            $conn->query($sql);
        }

        $sql = "SELECT * FROM catalog_product_option_type_price WHERE option_type_id=".$optionTypeId ;
        $result6 = $conn->query($sql);    
        $row6 = $result6->fetch_assoc();
        //echo '<pre>';print_r($row6);echo '</pre>';
        if($row6){
            echo $row6['price'].'---'.$row6['price_type'].'---';
            unset($row6['option_type_price_id']);
            $row6['option_type_id'] = $newOptionTypeId;
            $optionTypePriceRow = implode("', '",$row6);
            $sql= "INSERT INTO catalog_product_option_type_price VALUES (NULL,'$optionTypePriceRow')";
            $conn->query($sql);
        }

        $row4['sku'];
        
        echo '<br>';


    }
}