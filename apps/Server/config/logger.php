<?php

/**
 * Logger de auditoria e segurança para o backend do projeto Continuum.
 *
 * Objetivo:
 * - deixar de usar arquivo local de log em container;
 * - persistir eventos em tabela do MySQL;
 * - manter dados minimamente sensíveis;
 * - facilitar retenção e alertas por trigger/event scheduler.
 */
class SecurityLogger
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }

        $local = $parts[0];
        $domain = $parts[1];

        if (strlen($local) <= 2) {
            return substr($local, 0, 1) . '***@' . $domain;
        }

        return substr($local, 0, 2) . '***@' . $domain;
    }

    public static function redactText(string $value): string
    {
        return strlen($value) > 4 ? substr($value, 0, 2) . '***' : '***';
    }

    public function write(
        ?int $userId,
        string $eventType,
        string $severity,
        string $message,
        array $details = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $endpoint = null,
        ?string $requestId = null
    ): void {
        $sql = "
            INSERT INTO security_logs
            (usuario_id, event_type, severity, message, details, ip_address, user_agent, endpoint, request_id)
            VALUES
            (:usuario_id, :event_type, :severity, :message, :details, :ip_address, :user_agent, :endpoint, :request_id)
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $userId,
            ':event_type' => $eventType,
            ':severity' => $severity,
            ':message' => $message,
            ':details' => json_encode($details, JSON_UNESCAPED_UNICODE),
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
            ':endpoint' => $endpoint,
            ':request_id' => $requestId,
        ]);
    }

    public static function logSecurityEvent(
        PDO $pdo,
        ?int $userId,
        string $eventType,
        string $message,
        array $details = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $endpoint = null
    ): void {
        $logger = new self($pdo);
        $logger->write(
            $userId,
            $eventType,
            'SECURITY',
            $message,
            $details,
            $ipAddress,
            $userAgent,
            $endpoint,
            bin2hex(random_bytes(8))
        );
    }
}
