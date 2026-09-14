<?php include '../src/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/index.css">
    <title>KYbrary</title>
</head>
<body>
    <header>
        <nav>
            <div class="pageTitle">
                <h1><a href="#">KYbrary</a></h1>

                <div>
                    <a href="register.php"><button>Registrarse</button></a>
                    <a href="login.php"><button>Iniciar Sesion</button></a>
                </div>
            </div>
            <div class="menuItem">
                <button class="menu-btn" id="openBtn"><img src="icons/iconMenu.png" alt="" width="36px" height="30px"></button>
            </div>
        </nav>
    </header>
    
    <?php renderSidebar(); ?>

    <main>
        <section id="introduction">
            <article>
                <h1>Bienvenido a KYbrary</h1>
                <hr>
                <p>El sistema bibliotecario definitivo, <br>simple pero eficiente, <br>ligero pero potente.</p>
            </article>

            <article class="cardApply">
                <!-- Incluir borde animado -->
                <div>
                    <p>
                        ¡Pruebelo ahora!
                        Pongase en contacto
                        con nuestro equipo
                        y agende una demo
                        ahora mismo.                        
                    </p>
                    <a href="catalogo.php"><button>Mas informacion</button></a>
                </div>
            </article>
        </section>

        <section id="tendencyBooks">
            <h2>Libros en tendencia</h2>
            <div class="book-wrapper">
                <article class="cardBook">
                    <div>
                        <img src="icons/book.png" alt="">
                    </div>
                    <h2>Titulo</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi, natus?</p>
                </article>

                <article class="cardBook">
                    <div>
                        <img src="icons/book.png" alt="">
                    </div>
                    <h2>Titulo</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio, reiciendis!</p>
                </article>

                <article class="cardBook">
                    <div>
                        <img src="icons/book.png" alt="">
                    </div>
                    <h2>Titulo</h2>
                    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quaerat, repellat?</p>
                </article>
            </div>
        </section>

        <section id="recentBooks">
            <h2>Nuestros libros mas recientes</h2>
            <div class="book-wrapper">
                <article class="cardBook">
                    <div>
                        <img src="icons/book.png" alt="">
                    </div>
                    <h2>Titulo</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi, natus?</p>
                </article>

                <article class="cardBook">
                    <div>
                        <img src="icons/book.png" alt="">
                    </div>
                    <h2>Titulo</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio, reiciendis!</p>
                </article>

                <article class="cardBook">
                    <div>
                        <img src="icons/book.png" alt="">
                    </div>
                    <h2>Titulo</h2>
                    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quaerat, repellat?</p>
                </article>
            </div>
        </section>

        <section id="literaryGenres">
            <article class="cardGenre">
                <h3>Ciencia Ficcion</h3>
            </article>

            <article class="cardGenre">
                <h3>Fantasía</h3>
            </article>

            <article class="cardGenre">
                <h3>Libros Históricos</h3>
            </article>
        </section>
    </main>

    <footer>
        <p>Copyright &copy;2026 <a href="nosotros.php">Waos Company</a> Todos los derechos reservados</p>
        <p>Contacto: a.alfarogonzalez@liceorbl.cl</p>
    </footer>
    <script src="js/sidebar.js"></script>
</body>
</html>