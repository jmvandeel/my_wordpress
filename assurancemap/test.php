<form enctype="multipart/form-data" method="post" role="form">
    <div class="form-group">
        <label for="exampleInputFile">File Upload</label>
        <input type="file" name="file" id="file" size="150">
    </div>
    <button type="submit" class="btn btn-default" name="Import" value="Import">Upload</button>
</form>

<?php

require_once 'excel_reader2.php';

if(isset($_POST["Import"]))
{
       
    $filename=$_FILES["file"]["tmp_name"];
    if($_FILES["file"]["size"] > 0)
    {
        $file = fopen($filename, "r");
		
        $data = new Spreadsheet_Excel_Reader($filename);
		
		$counter = 1;
		$i = 1;
		$j = 1;
		$arr = array();


		
		while (!$data->val($i,$j) == NULL){

				do {
					//echo $data->val($i,$j)." | ";
					$arr[$i][$j] = $data->val($i, $j);			
					$j++;
				} while (!$data->val($i,$j) == NULL);	
			$i++; $j = 1; //echo "<br>";
		}
		
		//echo "The Array<br>";
		//print_r($arr).PHP_EOL;
		echo "<br>The json<br>";
		$json = json_encode($arr);
		print_r($json);

        fclose($file);
        //echo 'CSV File has been successfully Imported';
        //header('Location: test.php');
    }
    else
        echo 'Invalid File:Please Upload CSV File';
}
?>