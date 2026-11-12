
#!/bin/sh
set -eu

# Confere se os diretórios temporários existem
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Verifica a configuração do Apache
apache2ctl -t

# Confirma que somente o MPM prefork está carregado
apache2ctl -M | grep -q 'mpm_prefork_module'

if apache2ctl -M | grep -Eq 'mpm_(event|worker)_module'; then
    echo "ERRO: Outro MPM está carregado junto com o prefork."
    exit 1
fi

# Inicia o Apache em primeiro plano
exec apache2-foreground
