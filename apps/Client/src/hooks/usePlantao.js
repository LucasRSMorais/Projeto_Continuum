import { useCallback, useEffect, useState } from 'react';
import { buildApiUrl } from '../config/api';

async function fetchPlantao(sectorId, signal) {
  const response = await fetch(
    buildApiUrl(`Plantoes/plantao.php?id_setor=${sectorId}`),
    { credentials: 'include', signal }
  );
  const result = await response.json();
  if (!response.ok || !result.success) {
    throw new Error(result.message || 'Não foi possível consultar o plantão.');
  }
  return result.data;
}

export function usePlantao(sectorId) {
  const [plantao, setPlantao] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  const refresh = useCallback(async (signal) => {
    try {
      const data = await fetchPlantao(sectorId, signal);
      setError('');
      setPlantao(data);
    } catch (requestError) {
      if (requestError.name !== 'AbortError') {
        setError(requestError.message || 'Não foi possível consultar o plantão.');
      }
    } finally {
      if (!signal?.aborted) {
        setLoading(false);
      }
    }
  }, [sectorId]);

  useEffect(() => {
    const controller = new AbortController();
    fetchPlantao(sectorId, controller.signal)
      .then((data) => {
        setError('');
        setPlantao(data);
      })
      .catch((requestError) => {
        if (requestError.name !== 'AbortError') {
          setError(requestError.message || 'Não foi possível consultar o plantão.');
        }
      })
      .finally(() => {
        if (!controller.signal.aborted) {
          setLoading(false);
        }
      });
    return () => controller.abort();
  }, [sectorId]);

  useEffect(() => {
    const timer = window.setInterval(() => refresh(), 60_000);
    return () => window.clearInterval(timer);
  }, [refresh]);

  const performAction = useCallback(async (action, descricaoFato = '') => {
    setSaving(true);
    setError('');
    setMessage('');
    try {
      const response = await fetch(buildApiUrl('Plantoes/plantao.php'), {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action,
          id_setor: Number(sectorId),
          descricao_fato: descricaoFato,
        }),
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || 'Não foi possível atualizar o plantão.');
      }
      setMessage(result.message || 'Plantão atualizado.');
      await refresh();
      return true;
    } catch (requestError) {
      setError(requestError.message || 'Não foi possível atualizar o plantão.');
      return false;
    } finally {
      setSaving(false);
    }
  }, [refresh, sectorId]);

  return { plantao, loading, saving, error, message, performAction };
}
