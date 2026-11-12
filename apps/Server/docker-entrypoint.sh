
#!/bin/sh
set -eu

echo "=== DIAGNOSTICO DOS MODULOS MPM ==="

ls -la /etc/apache2/mods-enabled/mpm_* || true

echo "=== LOADMODULE ENCONTRADOS ==="

grep -Rni "LoadModule mpm_" /etc/apache2/ || true

echo "=== TESTE DE CONFIGURACAO ==="

apache2ctl -t

exec apache2-foreground
