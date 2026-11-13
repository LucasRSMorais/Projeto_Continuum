<?php
// 1. Inclui o ficheiro de configuração de CORS e aplica as regras
require_once __DIR__ . '/../../config/cors.php'; // Ajuste o caminho se o ficheiro estiver noutra pasta
applyCorsHeaders();

// 2. O cors.php não define o Content-Type para JSON, por isso definimos aqui:
header('Content-Type: application/json; charset=utf-8');

// 3. Inclui a ligação à base de dados. 
// Se falhar, o database.php já devolve um erro 500 e encerra o script (exit).
// Se tiver sucesso, disponibiliza a variável global $pdo configurada e segura.
require_once __DIR__ . '/../../config/database.php'; // Ajuste o caminho se o ficheiro estiver noutra pasta

try {
    // 4. Recebe o ID do setor via parâmetro GET (ex: ?id_setor=1)
    // Se não for passado, assume 1 (UTI Cardiológica) como teste padrão.
    $id_setor = isset($_GET['id_setor']) ? intval($_GET['id_setor']) : 1; 

    // 5. Query SQL consultando a nossa VIEW
    $query = "
        SELECT 
            fila_id, 
            paciente_nome, 
            origem, 
            gravidade, 
            tempo_espera_minutos 
        FROM vw_dash_fila_admissoes 
        WHERE id_setor = :id_setor 
        ORDER BY 
            FIELD(gravidade, 'alta', 'media', 'baixa'), -- Utentes graves primeiro
            tempo_espera_minutos DESC                   -- Seguidos por quem espera há mais tempo
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_setor', $id_setor, PDO::PARAM_INT);
    // 6. Executa a query
    $stmt->execute();
    // O seu database.php já define PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
    // por isso basta chamar fetchAll()
    $pacientes = $stmt->fetchAll(); 

    // 7. Devolve o JSON final para o React
    echo json_encode([
        'success' => true,
        'data' => $pacientes
    ]);

} catch(PDOException $e) {
    // Se a query falhar (ex: a VIEW não foi criada corretamente ou a coluna mudou)
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na consulta de dados: ' . $e->getMessage()
    ]);
}
?>