import { useEffect, useState } from 'react';

const emptyData = {
  patients: [],
  pending: [],
  movements: [],
};

export function useDashboardCards(url) {
  const [data, setData] = useState(emptyData);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const controller = new AbortController();

    async function loadDashboardCards() {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(url, {
          credentials: 'include',
          signal: controller.signal,
        });
        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Não foi possível carregar os dados do painel.');
        }

        setData({
          patients: Array.isArray(result.data?.patients) ? result.data.patients : [],
          pending: Array.isArray(result.data?.pending) ? result.data.pending : [],
          movements: Array.isArray(result.data?.movements) ? result.data.movements : [],
        });
      } catch (requestError) {
        if (requestError.name !== 'AbortError') {
          console.error('Erro ao carregar os dados do painel:', requestError);
          setError(requestError.message || 'Falha na comunicação com o servidor.');
        }
      } finally {
        if (!controller.signal.aborted) {
          setLoading(false);
        }
      }
    }

    loadDashboardCards();
    return () => controller.abort();
  }, [url]);

  return { ...data, loading, error };
}
