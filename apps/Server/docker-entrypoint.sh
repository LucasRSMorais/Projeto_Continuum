
#!/bin/sh
set -eu

echo "========== INICIANDO APACHE =========="

echo "=== USUARIO ATUAL ==="
whoami

# Cria uma pasta temporaria com os modulos do Apache
MODS_DIR="/tmp/apache2-mods-www-data"
CONF_FILE="/tmp/apache2-www-data.conf"

mkdir -p "$MODS_DIR"

# Copia os links dos modulos, mantendo somente o prefork
for arquivo in /etc/apache2/mods-enabled/*; do
    [ -e "$arquivo" ] || continue

    nome=$(basename "$arquivo")

    case "$nome" in
        mpm_*.load|mpm_*.conf)
            case "$nome" in
                mpm_prefork.*) ;;
                *) continue ;;
            esac
            ;;
    esac

    ln -sf "$arquivo" "$MODS_DIR/$nome"
done

echo "=== MODULOS MPM SELECIONADOS ==="
ls -la "$MODS_DIR"/mpm*

# Cria uma configuracao alternativa sem alterar /etc/apache2
sed "s#mods-enabled/#$MODS_DIR/#g" \
    /etc/apache2/apache2.conf > "$CONF_FILE"

# Carrega as variaveis do Apache
. /etc/apache2/envvars

echo "=== VALIDANDO CONFIGURACAO ==="
apache2 -t -f "$CONF_FILE"

echo "=== INICIANDO APACHE ==="
exec apache2 -f "$CONF_FILE" -D FOREGROUND
