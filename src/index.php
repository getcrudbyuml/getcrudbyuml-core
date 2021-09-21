<?php


use GetCrudByUML\controller\MainAPIController;

function autoload($classe) {
    
    $prefix = 'GetCrudByUML';
    $base_dir = './GetCrudByUML';
    $len = strlen($prefix);
    if (strncmp($prefix, $classe, $len) !== 0) {
        return;
    }
    $relative_class = substr($classe, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
    
}

spl_autoload_register('autoload');


if(isset($_REQUEST['api'])){
    
    MainAPIController::main();
    return;
    
}
?><!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>

<h1>Teste Digitando UM JSON</h1>

<form id="formJSON">
<div class="row">
	<div class="col-6 col-sm-12">
        <div class="form-floating">
          <textarea class="form-control" placeholder="Leave a comment here" id="fieldJSON" style="height: 100px"></textarea>
          <label for="floatingTextarea2">JSON</label>
        </div>
    </div>
    <div class="col-6 col-sm-12">
        <div class="form-floating">
          <textarea class="form-control" placeholder="Leave a comment here" id="fieldSQL" style="height: 100px"></textarea>
          <label for="floatingTextarea2">SQL</label>
        </div>
    </div>

</div>
<button type="submit">Enviar</button>
</form>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js" integrity="sha384-W8fXfP3gkOKtndU4JGtKDvXbO53Wy8SZCQHczT5FMiiqmQfUpWbYdTil/SxwZgAN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.min.js" integrity="sha384-skAcpIdS7UcVUC05LJ9Dxay8AXcDYfBJqt1CJ85S/CFujBsIzCIv+l9liuYLaMQ/" crossorigin="anonymous"></script>
    -->
    <script>
    	function submitForm(event){
    		event.preventDefault();
            const strData = document.querySelector("#fieldJSON").value;
            const data = JSON.parse(strData);
            console.log(strData);
            
            fetch("http://localhost/getcrudbyuml/getcrudbyuml-core/src/api/software", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
            })
            .then((response) => response.json())
            .then((data) => {
                console.log("Success:", data);
                document.querySelector("#fieldSQL").value = data['files']['database_sqlite.sql'];
                  
                
            })
            .catch((error) => {
                console.error("Error:", error);
            });


    
    	}
    	document.querySelector("#formJSON").addEventListener("submit", submitForm);
    </script>
  </body>
</html>