<?php 

$titulo=$_POST["titulo"];
$sinopsis=$_POST["sinopsis"];
$img=$_POST["img"];
$obra=$_POST["obra"];
$cat=$_POST["cat"];

include("conexionbd.php");
$bd = conectardb();
include("funcionesobras.php");
include("funcionescategorias.php");
$array=explode(",", $cat);


cambiarobra($bd, $titulo, $sinopsis, $obra);

if($img!=""){
$nombre_archivo = $_FILES['file']['name'];
$ruta_temporal_archivo = $_FILES['file']['tmp_name'];
$ext = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
$nombretemporal = $_FILES['file']['tmp_name'];
$fichero = "$obra.$ext";
if (move_uploaded_file($nombretemporal, "../Imagenes/Obras/$fichero")) {
    cambiarfoto($bd,$fichero,$id);
}
}
borrargeneros($bd, $obra);
for ($i=0; $i <count($array) ; $i++) { 
    addgenero($bd,$obra,$array[$i]);
}


?>