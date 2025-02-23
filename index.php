<?php
ob_start();
session_start();
include("./funcionesphp/conexionbd.php");
$bd = conectardb();
include("./funcionesphp/funcionesusuarios.php");
include("./funcionesphp/block.php");
include("clases/obras.class.php");
include("funcionesphp/funcionesobras.php");
include("funcionesphp/funcionesmarcapaginas.php");
include("clases/usuarios.class.php");
include("clases/marcapaginas.class.php");
include("clases/categorias.class.php");
include("funcionesphp/funcionescategorias.php");
include("funcionesphp/funcionesbiblioteca.php");
$categorias = categorias($bd);
$ses = isset($_SESSION['tipo']) ? $_SESSION['tipo'] : 0;
if (isset($_SESSION['usuario'])) {
    $usuario = unusuarioporcodigo($bd, $_SESSION['usuario']);
    isblock($usuario->getestado());
    $biblioteca = tubiblioteca($bd,  $_SESSION['usuario']);
    $obras_guardadas = obrasguardadasporid($bd, $biblioteca);
}
include("Includes/header.php");
?>
<link rel="stylesheet" href="css/index.css">
</head>

<body>
    <?php
    include("Includes/nav.php");
    $desplazamiento = $_GET['desplazamiento'] ?? 0;
    $orden = $_GET['orden'] ?? "likes";
    $ordnum = 0;
    $num_filas = 20;
    $pagina = $_GET['pag'] ?? 1;
    $buscarpor = $_GET['buscarpor'] ?? "";
    if (isset($_GET["buscarpor"])) {
        $buscarpor = urlencode($buscarpor);
    }
    $inputbuscapor = urldecode($buscarpor);
    $cat = $_GET['categoria'] ?? 0;
    if (isset($_GET["orden"])) {
        switch ($orden) {
            case 0:
                $orden = "likes";
                $ordnum = 0;
                break;

            case 1:
                $orden = "lecturas";
                $ordnum = 1;
                break;

            case 2:
                $orden = "terminada";
                $ordnum = 2;
                break;

            case 3:
                $orden = "id";
                $ordnum = 3;
                break;

            default:
                $orden = "likes";
                break;
        }

        if (isset($_GET["buscarpor"])) {
            if ($cat == 0) {
                $obras = obraspalabras($bd, $desplazamiento, $orden, $_GET["buscarpor"], $ses);
                $total = totalobraspalabras($bd, $desplazamiento, $orden, $_GET["buscarpor"], $ses);
            } else {
                $obras = obraspalabrasconcat($bd, $desplazamiento, $orden, $_GET["buscarpor"], $cat, $ses);
                $total = totalobraspalabrasconcat($bd, $desplazamiento, $orden, $_GET["buscarpor"], $cat, $ses);
            }
        } else {
            if ($cat == 0) {
                $obras = obras($bd, $desplazamiento, $orden, $ses);
                $total = totalobras($bd, $ses);
            } else {
                $obras = filtrarobras1($bd, $desplazamiento, $orden, $cat, $ses);
                $total = totalobras1($bd, $desplazamiento, $orden, $cat, $ses);
            }
        }
    } else {
        $obras = obras($bd, $desplazamiento, $orden, $ses);
        $total = totalobras($bd, $ses);
    }
    $usuarios = arrayusuariosporid($bd);
    
    ?>
    <div class="jumbotron jumbotron-fluid bg-dark">

        <div class="jumbotron-background">
            <img src="imagenes/pulp.jpg" class="blur ">
        </div>

        <div class="container text-white">
            <h1 class="display-5">¡Bienvenido a Pulp World!</h1>
            <p class="lead">Pulp World es una plataforma online de lectura y escritura que nace con el proposito de que la gente pueda leer, escribir y públicar relatos de una forma sencilla</p>
            <hr class="my-4">
            <?php if (!isset($_SESSION['usuario'])) {
            ?>
                <p>Si no estas registrado podras leer todas las obras que quieras,
                    pero para poder comentar, escribir obras, seguir usuarios y dar me gustas tendrás que registrarte
                </p>
                <a class="btn btn-primary btn-lg" data-toggle="modal" data-target="#registro" href="#" role="button">¿No estas registrado? Hazlo!</a>
            <?php } else {
            ?>
                <p>No seas timido, lee lo que quieras, da me gusta, comenta, sigue a los usuarios que quieras que te avise si publican una nueva obra y en general disfruta de esta plataforma
                </p>
                <a class="btn btn-primary btn-lg" href="new.php" role="button">Crear nueva obra</a>
            <?php } ?>

        </div>
        <!-- /.container -->


    </div>
    <div id="content">
        <h1 class="display-3 text-center mb-5">Obras Pulp</h1>
    </div>

    <div class="container-fluid mb-3">
        <div class="row">
            <div class="col-md-12">
                <form action="./funcionesphp/filtrar.php" method="POST" class="form-horizontal" role="form">
                    <div class="input-group" id="adv-search">

                        <input type="text" class="form-control" id="textobusqueda" name="textobusqueda" placeholder="Busqueda avanzada" value="<?php echo "{$inputbuscapor}"; ?>">
                        <div class="input-group-btn">
                            <div class="btn-group" role="group">
                                <div class="dropdown dropdown-lg">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span>Opciones</button>
                                    <div class="dropdown-menu dropdown-menu-right" role="menu">


                                        <div class="form-group">
                                            <label for="filter">Categorias</label>
                                            <select class="form-control" id="categorias" name="categoria">
                                                <option value="0" selected>Todas las categorias</option>
                                                <?php
                                                for ($i = 0; $i < count($categorias); $i++) {
                                                    if (isset($_GET["categoria"]) && $categorias[$i]->getid() == $cat) {
                                                        echo "<option selected value='{$categorias[$i]->getid()}'>{$categorias[$i]->getnombre()}</option>";
                                                    } else {
                                                        echo "<option value='{$categorias[$i]->getid()}'>{$categorias[$i]->getnombre()}</option>";
                                                    }
                                                }

                                                ?>
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            <label for="filter">Ordenar por</label>
                                            <select class="form-control" id="orden" name="orden">
                                                <?php
                                                $arrayord = ["Likes", "Mas leidos", "Terminados", "Recientes"];
                                                for ($i = 0; $i < count($arrayord); $i++) {
                                                    if (isset($_GET["orden"]) && $i == $ordnum) {
                                                        echo "<option selected value=$i>{$arrayord[$i]}</option>";
                                                    } else {
                                                        echo "<option value=$i>{$arrayord[$i]}</option>";
                                                    }
                                                }
                                                ?>

                                            </select>
                                        </div>

                                      

                                        <input type="submit" id="busqueda" name="busqueda" class="btn btn-primary busqueda" value="Buscar"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></input>

                                    </div>
                                </div>
                                <input type="submit" id="busqueda1" name="busqueda1" class="btn btn-primary busqueda" value="Buscar"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></input>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <?php $linea = 20;

    ?>

    <div id="coleccion" class="card-group">
        <div class="container-fluid mb-3">
            <div class="row">
                <?php for ($i = 0; $i < 12; $i++) {


                ?>
                    <?php if ($i < $total - $desplazamiento) {
                        $generos = generos($bd, $obras[$i]->getid());

                    ?>
                        <a class="noDecoration" <?php echo "href=obra.php?obra={$obras[$i]->getid()}";  ?>>

                            <div class="card col-md-3 col-xs-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                                <img style="width: 80%; height: 20rem; display:block;
margin:auto; " class="card-img-top" src=<?php echo "Imagenes/Obras/" . $obras[$i]->getportada(); ?> alt="Card image cap">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php
                                        echo $obras[$i]->gettitulo();
                                        ?>
                                    </h5>
                                    <p style="word-break: break-all;" class="card-text text-justify">
                                        <?php echo $obras[$i]->getsinopsis();
                                        ?>
                                    </p>
                                    <?php
                                    if (trim($obras[$i]->getsinopsis()) != '' && strlen(trim($obras[$i]->getsinopsis())) > 203) {
                                    ?>
                                        <a style="color:blue;" data-toggle="modal" data-target=<?php echo "#0" . $obras[$i]->getid(); ?> href="#">Leer mas</a>
                                    <?php } ?>
                                    <div class="modal fade" id=<?php echo "0" . $obras[$i]->getid(); ?> tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <?php
                                                        echo $obras[$i]->gettitulo();
                                                        ?>
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div  style=" height: 500px; overflow-y: scroll;" class="modal-body">
                                                    <div style="height: 300px;">
                                                        <img style="width: 70%; height: 20rem;" class="rounded mx-auto d-block mh-100" src=<?php echo "Imagenes/Obras/" . $obras[$i]->getportada(); ?> />
                                                    </div>
                                                    <p class="text-justify mt-3">
                                                        <?php
                                                        echo $obras[$i]->getsinopsis();
                                                        ?>
                                                    </p>
                                                </div>
                                                <div class="modal-footer">

                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                    <a <?php echo "href=obra.php?obra={$obras[$i]->getid()}";  ?> class="btn btn-primary">Ver Obra</a>

                                                </div>

                                            </div>

                                        </div>
                                    </div>


                                </div>
                                <p class="text-justify ml-4"><strong>Escrito por: </strong><?php
                                                                                            echo " <a href='usuario.php?user={$obras[$i]->getautor()}' >{$usuarios[$obras[$i]->getautor()]->getusuario()}</a>";

                                                                                            ?></p>


                                <div class="text-justify ml-4">
                                    <strong>Likes</strong> <i class="fas fa-thumbs-up text-danger"> <?php echo $obras[$i]->getlikes(); ?></i>
                                    <strong>Lecturas</strong> <i class="fas fa-eye text-primary"> <?php echo $obras[$i]->getlecturas(); ?></i><br><br>
                                </div>
                                <div class="text-justify ml-4">
                                    <p id="estadoobra">Estado: <strong><?php
                                                                        if ($obras[$i]->getterminada() == 0) {
                                                                            echo "Sin terminar";
                                                                        } else {
                                                                            echo "Terminada";
                                                                        }
                                                                        ?></strong></p>
                                </div>
                                <?php
                                if (isset($_SESSION["tipo"])) {
                                    if ($_SESSION["tipo"] == 1) {
                                ?>
                                        <div class="text-center mt-2 mb-2"><?php
                                                                            if ($obras[$i]->getpublico() == 0) {
                                                                                echo "<strong class='text-danger'>Obra sin publicar</strong>";
                                                                            } else {
                                                                                echo "<strong class='text-success'>Obra publicada</strong>";
                                                                            }
                                                                            echo "</div>";


                                                                            ?>
                                            <div id="obrastate" class="text-center mt-2 mb-2 text-danger">
                                                <?php
                                                if ($obras[$i]->getestado() == 0) {
                                                ?>

                                                    <strong>Obra Bloqueada</strong>

                                        <?php }
                                                echo "</div>";
                                            }
                                        } ?>
                                        <?php if(count($generos)>2){
                                            echo  "<div class='text-center'>";
                                        }else{
                                            echo  "<div class='text-center mb-3'>";
                                        }  ?>

                                            <?php
                                            foreach ($generos as $key => $value) {
                                                echo " <p class='btn btn-primary'>{$value->getnombre()}</p>";
                                            }
                                            ?></div>
                                        <div class="card-footer text-center">
                                            <a style="color:white" <?php echo "href=obra.php?obra={$obras[$i]->getid()}";  ?> class="btn btn-primary">Ver Obra</a>
                                            <?php if (isset($_SESSION["usuario"])) { ?>
                                                <input type="hidden" id="biblioteca" value="<?php echo $biblioteca ?>">
                                                <?php
                                                if (count($obras_guardadas) > 0) {
                                                    if (array_key_exists($obras[$i]->getid(), $obras_guardadas)) {
                                                ?>

                                                        <button value="<?php echo $obras[$i]->getid(); ?>" class="btn btn-danger quitarobra"><i class="fas fa-book-open"> Quitar</i></button>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <button value="<?php echo $obras[$i]->getid(); ?>" class="btn btn-success guardarobra"><i class="fas fa-book-open"> Guardar</i></button>
                                                    <?php

                                                    }
                                                } else {
                                                    ?>
                                                    <button value="<?php echo $obras[$i]->getid(); ?>" class="btn btn-success guardarobra"><i class="fas fa-book-open"> Guardar</i></button>
                                            <?php }
                                            } ?>
                                        </div>

                                            </div>
                        </a>
                    <?php } else {

                    ?>
                        <div class="card ml-4" style="border: none;">
                        </div>
                <?php    }
                } ?>


            </div>
        </div>
    </div>


    <nav aria-label="...">
        <ul class="pagination justify-content-center mt-5">
            <?php
            if ($desplazamiento > 0) {
                $pagant = $pagina - 1;
                $prev = $desplazamiento - 12;
                $url = $_SERVER["PHP_SELF"] . "?categoria=$cat&orden=$ordnum&desplazamiento=$prev&buscarpor=$buscarpor&pag=$pagant#content";
                echo "<li class='page-item active'>";
                echo  "<a class='page-link mr-4' href=$url tabindex='-1'>Anterior</a>";
            } else {

                echo "<li class='page-item disabled'>";
                echo  "<a class='page-link mr-4' href='#' tabindex='-1'>Anterior</a>";
            }

            ?>

            </li>
            <?php
            $o=  $pagina>4 ? $pagina :0;
            $o=$o>0?$o-1:$o;

            if($o>0){
                while ($o % 4) {
                    $o--;
                }
                
            }
            for ($i = 0; $i < 48; $i += 12) {
                if($i>$total){
                    break;
                }
                $o++;
                $url = $_SERVER["PHP_SELF"] . "?categoria=$cat&orden=$ordnum&desplazamiento=$i&pag=$o&buscarpor=$buscarpor#content";
                if ($pagina == $o) {
                    echo "<li class='page-item active'>
                <a class='page-link' href=$url>$o <span class='sr-only'>(current)</span></a>
            </li>";
                } else {
                    echo  "<li class='page-item'><a class='page-link' href=$url>$o</a></li>";
                }
            }

            if ($total > ($desplazamiento + 12)) {
                $prox = $desplazamiento + 12;
                $pagsec = $pagina + 1;
                $url = $_SERVER["PHP_SELF"] . "?categoria=$cat&orden=$ordnum&desplazamiento=$prox&buscarpor=$buscarpor&pag=$pagsec#content";
                echo "<li class='page-item active'>";
                echo  "<a class='page-link ml-4' href=$url tabindex='-1'>Siguiente</a>";
            } else {
                echo "<li class='page-item disabled'>";
                echo  "<a class='page-link ml-4' href='#' tabindex='-1'>Siguiente</a>";
            }

            ?>
            </li>
        </ul>
    </nav>

    <?php if (isset($_GET['alerta'])) { ?> <script>
            alert("<?php echo $_GET['alerta']; ?>")
        </script> <?php } ?>

    <!-- Modal -->
    <script>
        $('.dropdown-menu').on('click', function(event) {
            event.stopPropagation();
        });

        $(document).on("ready", function() {
            $(".card-text").each(function() {
                max_chars = 280;
                limite_text = $(this).html();
                if (limite_text.length > max_chars) {
                    limite = limite_text.substr(0, max_chars) + " ...";
                    $(this).text(limite);
                }
            });
        });
    </script>
    <?php
    include("Includes/footer.php")
    ?>
</body>

</html>