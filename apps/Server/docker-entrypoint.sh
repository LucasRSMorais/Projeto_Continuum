echo "=== Módulos MPM habilitados ==="
ls -l /etc/apache2/mods-enabled/mpm* || true

echo "=== Diretivas LoadModule MPM ==="
grep -R "LoadModule mpm_" /etc/apache2 2>/dev/null || true

echo "=== Configuração Apache ==="
apache2ctl -M 2>&1 || true

exec apache2-foreground