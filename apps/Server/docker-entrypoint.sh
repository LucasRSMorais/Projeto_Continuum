
#!/bin/sh
set -eu

echo "========== VALIDANDO APACHE =========="

# Garante que os diretórios temporários existam
mkdir -p /tmp/apache2/run \
         /tmp/apache2/lock \
         /tmp/apache2/log

echo "=== VERIFICANDO MODULO MPM ==="

apache2ctl -M | grep mpm

# Impede a inicialização se houver MPM duplicado
if apache2ctl -M 2>&1 | grep -qE 'mpm_(event|worker)_module'; then
    echo "ERRO: MPM event ou worker encontrado!"
    exit 1
fi

echo "=== VALIDANDO CONFIGURACAO ==="

apache2ctl -t

echo "=== INICIANDO APACHE COMO WWW-DATA ==="

exec apache2-foreground
