USE railway;

START TRANSACTION;

INSERT INTO pacientes_enfermarias (
    id_enfermaria,
    id_paciente,
    id_evento,
    id_leito,
    movimentacao_leito
)
SELECT e.id, p.id, NULL, l.id, 'Admissão de demonstração'
FROM (
    SELECT 'zeca.melo@continuum.local' AS email, 'ENF-701' AS numero
    UNION ALL SELECT 'breno.pires@continuum.local', 'ENF-702'
    UNION ALL SELECT 'diego.viana@continuum.local', 'ENF-703'
    UNION ALL SELECT 'fernando.monteiro@continuum.local', 'ENF-704'
) AS demo
JOIN pacientes p ON p.email = demo.email
JOIN enfermarias e ON e.id_setor = 7
JOIN leitos l ON l.id_enfermaria = e.id AND l.numero = demo.numero
WHERE l.status = 'disponivel'
  AND NOT EXISTS (
      SELECT 1 FROM pacientes_enfermarias existing
      WHERE existing.id_paciente = p.id
  )
  AND NOT EXISTS (
      SELECT 1 FROM pacientes_enfermarias existing
      WHERE existing.id_leito = l.id
  );

UPDATE leitos l
JOIN pacientes_enfermarias pe ON pe.id_leito = l.id
JOIN enfermarias e ON e.id = pe.id_enfermaria
JOIN pacientes p ON p.id = pe.id_paciente
SET l.status = 'ocupado'
WHERE e.id_setor = 7
  AND l.numero IN ('ENF-701', 'ENF-702', 'ENF-703', 'ENF-704')
  AND p.email IN (
      'zeca.melo@continuum.local',
      'breno.pires@continuum.local',
      'diego.viana@continuum.local',
      'fernando.monteiro@continuum.local'
  );

UPDATE enfermarias e
SET e.leitos_disponiveis = (
    SELECT COUNT(*)
    FROM leitos l
    WHERE l.id_enfermaria = e.id
      AND l.status = 'disponivel'
)
WHERE e.id_setor = 7;

COMMIT;
