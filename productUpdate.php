<?php
/* use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('base');

$adminSession = $obj->get('Magento\Backend\App\Action\Context');
$adminSession->getAuth()->getUser();
//var_dump($adminSession->getData());
if($adminSession->getId()){
    echo 'Logged in';
}else{
   echo 'Not logged in';
} */

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

$sku = @$_GET['sku'];
$sql = "SELECT  entity_id FROM catalog_product_entity WHERE sku='$sku'";
$result = $conn->query($sql);
$row = $result->fetch_object();
$productId = @$row->entity_id;
?>
<form method="get">
    <label></label>
    <input type="text" value="<?php echo @$_GET['sku']?>" name="sku">
    <br><br>
    <input  type="submit"  value="Get Product Options"/>
</form>
<br>
<?php

if(isset($_POST['ischange'])){

    if(isset($_POST['ischange']['options'])){
        $options = $_POST['ischange']['options'];
        foreach($options as $optionId=>$value){
            $sql = "UPDATE catalog_product_option_title SET title='".escape($_POST['options'][$optionId]['title'])."' WHERE option_id=".$optionId;
            $conn->query($sql);          
            if(isset($_POST['options'][$optionId]['price'])){
                $sql = "UPDATE catalog_product_option_price SET price='".$_POST['options'][$optionId]['price']."' WHERE option_id=".$optionId;
                //$conn->query($sql);
            }          
            if(isset($_POST['options'][$optionId]['sku'])){
                $sql = "UPDATE catalog_product_option SET sku='".escape($_POST['options'][$optionId]['sku'])."' WHERE option_id=".$optionId;
                //$conn->query($sql);
            }
        }
    }
    if(isset($_POST['ischange']['optionType'])){
        $optionTypes = $_POST['ischange']['optionType'];
        foreach($optionTypes as $optionTypeId=>$value){           
            $sql = "UPDATE catalog_product_option_type_value SET sku='".escape($_POST['optionType'][$optionTypeId]['sku'])."' WHERE option_type_id=".$optionTypeId;
            $conn->query($sql);
            $sql = "UPDATE catalog_product_option_type_title SET title='".escape($_POST['optionType'][$optionTypeId]['title'])."' WHERE option_type_id=".$optionTypeId;
            $conn->query($sql);
            $sql = "UPDATE catalog_product_option_type_price SET price='".$_POST['optionType'][$optionTypeId]['price']."' WHERE option_type_id=".$optionTypeId;
            $conn->query($sql);            
        }
    }    

//echo '<pre>'; print_r($_POST); echo '</pre>';
    
}

if($productId){
    
    $sql = "SELECT value FROM `catalog_product_entity_varchar` WHERE `attribute_id` = 70 AND `entity_id`=".$productId;
    $result = $conn->query($sql);
    $row = $result->fetch_object();
    $productName = @$row->value;
    echo "<h1>$productName</h1>";

    $sql = "SELECT  cpo.*, cpot.option_title_id,cpot.title,cpop.option_price_id,cpop.price FROM catalog_product_option as cpo 
    LEFT JOIN catalog_product_option_title AS cpot ON cpot.option_id = cpo.option_id 
    LEFT JOIN catalog_product_option_price AS cpop ON cpop.option_id = cpo.option_id 
    WHERE cpo.product_id=$productId";
    $result = $conn->query($sql);
    ?>

    <?php
    if ($result->num_rows > 0) {
        echo '<form method="post">';
    
        echo '<table cellspacing="5" cellpadding="5" width="100%">';
        
        while($row = $result->fetch_assoc()) {

           // echo '<pre>'; print_r($row); echo '</pre>';die;
            $colspan = 3;
            $hasOptionType = (in_array($row['type'],array('drop_down','multiple','checkbox')))? true : false ;
            $optionId=$row['option_id'];
            echo '<tr>
            <th>Option Title</th>';
            if(!$hasOptionType){ 
                echo '<th>Price</th>';
                echo '<th>Sku</th>';
               
            }
            echo '</tr>';
            echo '<tr>
                    <td>
                        <input type="checkbox" name="ischange[options]['.$optionId.']" />
                        <input type="text" name="options['.$optionId.'][title]" value="'.$row['title'].'" />                        
                    </td>';
            if(!$hasOptionType){ 
            echo    '<td>
                        <input type="text" name="options['.$optionId.'][price]" value="'.$row['price'].'" />                            
                    </td>';
            echo    '<td>
                        <input type="text" name="options['.$optionId.'][sku]" value="'.$row['sku'].'" />                           
                    </td>';
            }
            echo '</tr>';        
            $sql = "SELECT  cpotv.*, cpott.*,cpotp.* FROM catalog_product_option_type_value as cpotv
            LEFT JOIN catalog_product_option_type_title AS cpott ON cpott.option_type_id = cpotv.option_type_id 
            LEFT JOIN catalog_product_option_type_price AS cpotp ON cpotp.option_type_id = cpotv.option_type_id
            WHERE cpotv.option_id = ".$optionId;
            $resultInner = $conn->query($sql);
        
            if ($resultInner->num_rows > 0) {
                echo '<tr><td colspan="'.$colspan.'"><table>';
                echo '<tr><th>Title</th><th>Price</th><th>SKU</th></tr>';
                while($row = $resultInner->fetch_assoc()) {
                    $optionTypeId = $row['option_type_id'];
                    echo '<tr>
                    <td><input type="text" name="optionType['.$optionTypeId.'][title]" value="'.$row['title'].'"></td></td>
                    <td><input type="text" name="optionType['.$optionTypeId.'][price]" value="'.$row['price'].'"></td></td>
                    <td><input type="text" name="optionType['.$optionTypeId.'][sku]" value="'.$row['sku'].'">
                        <input type="checkbox" name="ischange[optionType]['.$optionTypeId.']" />
                    </td></td>
                    </tr>';
                }
                echo ' </table></td></tr>';
            }
            echo '<tr><td colspan="'.$colspan.'"><hr><br></td></tr>';
        }
        echo '</table>';
        echo '<input class="submitbtn" type="submit" name="save" value="Save"/>';
        echo '</form>';
    }
}
$conn->close();

function escape($text){
   global $conn;
   return $conn->real_escape_string($text);
}
?>

<style>
th{text-align:left;}
.submitbtn{
    
    position: fixed;
    right : 200px;    
    top: 50px;
    cursor:pointer;
}
</style>