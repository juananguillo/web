<?php 

if(isset($_POST["accion"])){
include("conexionbd.php");
$bd = conectardb();
$id1=isset($_POST["id1"])?$_POST["id1"]:0;
$id2=isset($_POST["id2"])?$_POST["id2"]:0;
$accion=$_POST["accion"];
include('funcionesmensajes.php');
include("../clases/mensajes.class.php");
include('funcionesusuarios.php');
include("../clases/usuarios.class.php");

switch ($accion) {
    case 'listar':
       $array= mensajes($bd, $id1, $id2);
       $totalm= countmensajes($bd, $id1,  $id2);
       echo "<input id='receptor' type='hidden' value='{$id2}'>
       <input id='totalm' type='hidden' value='{$totalm}'>";
       foreach ($array as $key => $value) {
          if($value->getid_emisor()==$id1){
            echo "<div class='mt-1  border rounded border-primary mdiv1'><p class='text text-justify'><strong>".$value->getcontenido()."</strong></p></div><br>";
        }
        else{
          echo "<div class='mt-1  border rounded border-success mdiv2'><p class='text text-justify'><strong>".$value->getcontenido()."</strong></p></div><br>";
        }
       }

        break;
       
    case 'insertar':
        crearmensaje($bd, $id1, $id2, $_POST["contenido"]);

        break;

    case 'comprobar':
      echo countmensajes($bd, $id1, $id2);

        break;

   case 'filtrar':
    $usuariosfiltrados= filtrarusuariosporpalabrastodos($bd, 0, "id", $_POST["palabras"], 0);
    $id_chats= emisoresyreceptores($bd, $id1);
    $array=[];
    foreach ($usuariosfiltrados as $key => $value) {
   
   if(in_array($value->getid(), $id_chats)){
    array_push($array,$value->getid());
   }
   $usuarios= arrayusuariosporid($bd);
 
    }
    foreach ($array as $key => $value) {
      $chat_user=$usuarios[$value];
      $t= sinleermen($bd, $id1, $chat_user->getid());
      echo"
        <div  id='{$chat_user->getid()}' style='cursor: pointer; overflow:hidden;' class='border mt-2 mb-3 chatid'>
        <img align='left' class='foto rounded-circle mt-1 mr-2' src='Imagenes/Usuarios/{$chat_user->getfoto()}'>
          <a href='usuario.php?user='$value>{$chat_user->getusuario()}</a>";
           if($t>0){
             echo "<span class='badge badge-primary'><$t</span>";
            } 
           echo "<p>{$chat_user->getemail()}</p>
         
        </div>";

          }
   

    break;
}
}

?>