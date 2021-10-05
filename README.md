<p  align="center">
<img src="https://i.imgur.com/UPLLVsW.png" width="350"></img>
	<p  align="center">Emulador de un servidor de juego de <a href="https://en.wikipedia.org/wiki/Panfu">Panfu</a> hecho en <a href="https://php.net">PHP</a> utilizando como base <a href="https://github.com/widd/kitsune">Kitsune</a>.</p>
</p>

<p align="center">Este README fue creado en 2019 pero fue un poquito actualizado en 2021.</p>

## Descripción
El proyecto comenzó su desarrollo en septiembre de 2017 y fue descontinuado a finales de 2018. La idea principal era poner en práctica y al mismo tiempo aprender php, por eso hay partes del código que no están muy bien logradas

Tiene algunas features customizadas como sistema de glows, aliases, un "bot", entre otras.

Tiene casi todas las características del juego correctamente implementadas. A continuación se listarán las "más importantes" junto a una breve explicación de su estado de funcionamiento.
|Nombre| Información |
|--|--|
| FourBoom | Funcional. No se han registrado errores hasta el momento. |
| Fútbol | El mini-juego de fútbol en el campo de deportes funciona sin errores hasta el momento.  |
| Piedra, papel o tijera | Funcional. No se han registrado errores hasta el momento. |
| Hot Bomb | El desarrollo de este mini-juego fue descontinuado antes de tiempo. |
| Carerras PokoPet |  Nunca fue implementado. |


## Requisitos
+ Es necesaria una versión de PHP ≥ 5.6 & < 7.4 (apróx.)
	+ Es necesaria la activación de la extensión cURL. 
+ Servidor MySQL (MariaDB también funciona).

## Instalación
Cree una nueva base de datos e importe el archivo [panfu.sql](panfu.sql) que contiene todas las estructuras de todas las bases de datos que se utilizarán.

## Configuración
Puede configurar los servidores editando el archivo [worlds.json](/util/worlds.json). Para cambiar los datos de la base de datos deberá de editar el código del archivo [Database.php](/PComponent/Database.php).

## Encender la aplicación
```bash
# Mediante el archivo batch (servidor por defecto: Bollyland)
$ RunGame

#Manualmente acompañado por el nombre del servidor
$ php Server.in.php Bollyland
```