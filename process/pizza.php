<?php

include_once("conn.php");

$method = $_SERVER["REQUEST_METHOD"];

// Restage dos dados, montagem do pedido
if ($method === "GET") {

    $bordasQuery = $conn->query("SELECT * FROM bordas;");

    $bordas = $bordasQuery->fetchAll();

    $massasQuery = $conn->query("SELECT * FROM massas;");

    $massas = $massasQuery->fetchAll();


    $saboresQuery = $conn->query("SELECT * FROM sabores;");

    $sabores = $saboresQuery->fetchAll();

    // print_r($sabores); exit;

    // Crição do pedido
} else if ($method === "POST") {

    $data = $_POST;

    $borda = $data["borda"];
    $massa = $data["massa"];
    $sabores = $data["sabores"];

    // validação de sabores máximos
    if (count($sabores) > 3) {

        $_SESSION["msg"] = "Selecione no máximo 3 sabores!";
        $_SESSION["status"] = "warning";
    } else {

        // salvando borda e massa na pizza
        $smt = $conn->prepare("INSERT INTO pizzas (borda_id, massa_id) VALUE (:borda, :massa)");

        // filtrando inputs
        $smt->bindParam(":borda", $borda, PDO::PARAM_INT);
        $smt->bindParam(":massa", $massa, PDO::PARAM_INT);

        $smt->execute();

        // Resgatando último id da última pizza
        $pizzaId = $conn->lastInsertId();

        $smt = $conn->prepare("INSERT INTO pizza_sabor (pizza_id, sabor_id) VALUES (:pizza, :sabor) ");

        // Repetição até terminar de salvar todos os sabores
        foreach ($sabores as $sabor) {

            // Filtrando os inputs
            $smt->bindParam(":pizza", $pizzaId, PDO::PARAM_INT);
            $smt->bindParam(":sabor", $sabor, PDO::PARAM_INT);

            $smt->execute();
        }

        // Criar  o pedido da pizza
        $smt = $conn->prepare("INSERT INTO  pedidos (pizza_id, status_id) VALUES (:pizza, :status)");

        // status -> sempre inicia com 1, que é em produção
        $statusId = 1;

        // filtrar inputs
        $smt->bindParam(":pizza", $pizzaId);
        $smt->bindParam(":status", $statusId);

        $smt->execute();

        // Exibir mensagem de sucesso
        $_SESSION["msg"] = "Pedido realizado com sucesso";
        $_SESSION["status"] = "success";
    }

    // Retorna para página inicial
    header("Location: ..");
}
