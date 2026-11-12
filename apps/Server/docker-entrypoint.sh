
#!/bin/sh
set -eu

# Garante que os diretorios temporarios existam
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Verifica a configuracao do Apache
apache2ctl -t

# Confirma que somente o MPM prefork esta carregado
apache2ctl -M | grep -q 'mpm_prefork_module'

if apache2ctl -M | grep -Eq 'mpm_(event|worker)_module'; then
    echo "ERRO: Outro MPM esta carregado junto com o prefork."
    exit 1
fi

echo "Apache validado com sucesso!"

exec apache2-foreground
