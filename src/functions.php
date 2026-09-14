<?php

function renderBooks() {
    ?>
        <div class="cBook">
            <img src="icons/book.png" alt="">
            <h1>Titulo</h1>
            <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Adipisci, deserunt?</p>
        </div>
    <?php
}

function renderSidebar() {
    ?>
    <div class="main-app-container">
        <aside class="sidebar" id="sidebar">
            <div class="nav-links">
                <a href="#" class="nav-item"><span>🏠</span> Inicio</a>
                <a href="#" class="nav-item"><span>⏱️</span> Ver más tarde</a>
                <a href="#" class="nav-item"><span>📂</span> Playlists</a>
            </div>
        </aside>
    </div>
    <?php
}

?>