<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>UNO's game</title>
	<link rel="stylesheet" href="index.css">
</head>
<body>
	
	<?php 
	include_once 'carta.class.php';
	include_once 'baraja.class.php';
	include_once 'partida.class.php';
	include_once 'jugador.class.php';

	//****************************************** INICIALIZO SESIÓN ******************************************************
	session_start();

	if (!isset($_SESSION['partida'])) {
		require_once 'formulario_uno.php';
		$_SESSION['partida'] = new Partida(); // Crear objeto de la clase partida y guardarlo en la sesión
		$_SESSION['partida']->baraja->crea_baraja();
	}




	//******************************************* AL RECIBIR SUBMIT FORMULARIO *********************************************
		if(isset($_REQUEST['njugadors'])){// Si se reciben datos del formulario (click submit)....
			//1. Capturar numero de jugadores y su cantidad de cartas
			 $_SESSION['partida']->numero_jugadores  = $_REQUEST['njugadors'];
			 $_SESSION['partida']->numero_cartas  = $_REQUEST['ncartas'];

			 //Mezclar la Baraja
			 $_SESSION['partida']->baraja->mezcla();
			//Creo el array jugadores
			for ($i=0; $i < $_SESSION['partida']->numero_jugadores ; $i++){ // Hasta numero de jugadores
				$objetoJugador = new Jugador($i); // crea objeto
				array_push ($_SESSION['partida']->array_jugadores,$objetoJugador); // Lo añades a la lista jugadores
				//3. Repartir cartas a jugadores
				for ($r=0; $r < $_SESSION['partida']->numero_cartas; $r++) { //Hasta el numero de cartas
					$lastcard = array_pop($_SESSION['partida']->baraja->conjunto_cartas); // Eliminas el ultimo elemento (ultima crta) del array conjunto_cartas y lo guardas en $lastcard
					array_push ($_SESSION['partida']->array_jugadores[$i]->mano->conjunto_cartas,$lastcard); // Añades en 'mano', la ultima carta
					//print_r($_SESSION['partida']->array_jugadores[$i]->mano->conjunto_cartas);
					//print_r ($lastcard);
					echo '<br>';
					
				}

			}


			//4. Sacar una carta a la mesa.
			
				$carta_robada = array_pop($_SESSION['partida']->baraja->conjunto_cartas);
				$_SESSION['partida']->carta_en_mesa = array_pop($_SESSION['partida']->baraja->conjunto_cartas); //Extraigo la ultima carta del array conjunto cartas (propiedad de clase baraja) y la meto en la propiedad carta en mesa (del objeto partida).

				if(is_numeric($_SESSION['partida']->carta_en_mesa->numero)){	
					//$_SESSION['partida']->carta_en_mesa->pinta_carta(); //Pinto la carta con el metodo del objeto 
					$carta_robada = array_pop($_SESSION['partida']->baraja->conjunto_cartas);
				}else{
					array_push($_SESSION['partida']->baraja->conjunto_cartas, $carta_robada);
					$_SESSION['partida']->baraja->mezcla();
					//Mientras el numero no sea numerico, quitar un elemento de la baraja.
					while (!(is_numeric($_SESSION['partida']->carta_en_mesa->numero))){
						$_SESSION['partida']->carta_en_mesa = array_pop($_SESSION['partida']->baraja->conjunto_cartas);

					}
				}
		
				$_SESSION['partida']->jugar();
			
	}

	//*****CUANDO CLICAS UNA CARTA **************

	if(isset($_REQUEST['indice'])){ //Si recibe elemento 'n'....
		//1.Miro que carta es la que he clicado (indice).
		$posicion_carta = $_REQUEST['posicion'];
		$indice_carta = $_REQUEST['indice'];
		$numero_carta = $_REQUEST['numero'];
		$palo_carta = $_REQUEST['palo'];

//CONDICIONES DEL JUEGO UNO
	//1a condición --> Si numero o color son iguales, quito la carta del jugador y la añado a la mesa. También augmento en 1 el turno.
		if (($numero_carta == $_SESSION['partida']->carta_en_mesa->numero) || ($palo_carta == $_SESSION['partida']->carta_en_mesa->palo)){
			//echo $_SESSION['partida']->turno;
			$carta_tirada = array_splice($_SESSION['partida']->array_jugadores[$_SESSION['partida']->turno]->mano->conjunto_cartas,$posicion_carta,1); // Extraigo una carta (que está en la posición que he hecho clich) de la "mano" del jugador que tiene el turno.

			$_SESSION['partida']->normas_uno($numero_carta,$palo_carta,$posicion_carta,$indice_carta);
			

			 
			$_SESSION['partida']->cambiar_turno();
			
			
			$_SESSION['partida']->jugar(); // Llamo al método jugar y muestro la partida, "f5"
			
			
		}else{ //Si no tienen ni mismo color ni mismo numero, aviso al jugador que no la puede tirar. Debe robar
			echo'<script type="text/javascript">
			alert("Error. Deben ser del mismo color o numero. Vuelve a intentarlo o roba si no tienes...");
			window.location.href="index_.php?turno='.$_SESSION['partida']->turno.'";
			</script>';	
		//2.Compararla con la carta que hay en la mesa
		//3. Si tienen el mismo palo o numero...
			//3.1. Sacar de la 'mano' y tirarla a la 'mesa'
			//3.2. Alert; Le toca al siguiente jugador
		//4. Sino...
			//4.1. Alert; revisa o roba carta.

		
		//echo ' La carta clicada es la numero '. $_REQUEST['n'];

		}

	}
	if(isset($_REQUEST['turno'])){
		//echo 'POR LO MENOS NO ME HE QUEDADO EN BLANCO... :)';

		$_SESSION['partida']->jugar();


	}


		
	//***************************************CUANDO CLICAS ROBAR CARTA **************************************************
	if(isset($_REQUEST['robar'])){
		$carta_robada = array_pop($_SESSION['partida']->baraja->conjunto_cartas);
		array_push($_SESSION['partida']->array_jugadores[($_SESSION['partida']->turno)]->mano->conjunto_cartas,$carta_robada);

		if (($carta_robada->numero == $_SESSION['partida']->carta_en_mesa->numero) || ($carta_robada->palo == $_SESSION['partida']->carta_en_mesa->palo)){

		}else{
			$_SESSION['partida']->cambiar_turno();

		}
		$_SESSION['partida']->jugar();
	}
	?>


</body>
</html>






