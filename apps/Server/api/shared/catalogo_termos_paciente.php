<?php

function catalogoTermosPaciente(): array
{
    return [
        'politica_privacidade' => [
            'titulo' => 'Política de Privacidade',
            'url' => '/politica_privacidade.pdf',
            'versao' => '1.0',
            'hash' => '68EE8E330687BF968DEE5162D3DE04EB14C92ADF83AF8A082CC0EB7A71EC31E0',
            'revogavel' => false,
            'informativo' => true,
            'aceitavel' => false,
        ],
        'termo_uso_servico' => [
            'titulo' => 'Termo de Uso e Serviço - Portal do Paciente',
            'url' => '/document/terms/patient/Termos%20de%20Uso%20e%20Servi%C3%A7o%20-%20Portal%20do%20Paciente.pdf',
            'versao' => '1.0',
            'hash' => '59DD0962BBE1BF58D944A0281213E07524100FE3165AB4A291C41A3986CC1D07',
            'revogavel' => false,
            'informativo' => false,
            'aceitavel' => true,
        ],
        'dados_sensiveis' => [
            'titulo' => 'Termo de Consentimento para Tratamento de Dados Pessoais Sensíveis',
            'url' => '/document/terms/patient/Termo_Consentimento_Dados_Sensiveis.pdf',
            'versao' => '1.0',
            'hash' => '72DA121AE70805848D78B37C3D668E3F4324EC3BBC3F11E496CBCE28A2B93BA6',
            'revogavel' => true,
            'informativo' => false,
            'aceitavel' => false,
        ],
        'uso_imagem' => [
            'titulo' => 'Termo de Autorização para Uso de Imagem e Dados de Prontuário',
            'url' => '/document/terms/patient/Termo_Autorizacao_Imagem.pdf',
            'versao' => '1.0',
            'hash' => 'ABBDDE30729941D1D2092936C9B4C095BD671C57FDB7D7163113F925A617FF22',
            'revogavel' => true,
            'informativo' => false,
            'aceitavel' => false,
        ],
        'consentimento_procedimento' => [
            'titulo' => 'Termo de Consentimento para Procedimento',
            'url' => '/document/terms/patient/Termo_Consentimento_Procedimento.pdf',
            'versao' => '1.0',
            'hash' => '4D4F3649ADC2ECD558F9ED8E6A15E4301B3D428F81495B0BAAC78C903AEE68B6',
            'revogavel' => false,
            'informativo' => false,
            'aceitavel' => false,
        ],
        'recusa_tratamento' => [
            'titulo' => 'Termo de Recusa de Tratamento',
            'url' => '/document/terms/patient/Termo_Recusa_Tratamento.pdf',
            'versao' => '1.0',
            'hash' => '842808F738113BE26E634E7DE7DE21D862FF4FE988F14050DB9A474E5AD58099',
            'revogavel' => false,
            'informativo' => false,
            'aceitavel' => false,
        ],
    ];
}
