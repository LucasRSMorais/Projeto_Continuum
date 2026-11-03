import { useEffect, useState } from 'react';

const emptyKpis = {
  occupancy: { percentage: 0, occupied: 0, total: 0 },
  adverseEventsLastShift: 0,
  criticalAlertsToday: 0,
  highRiskPatients: 0,
};

export function useDashboardKpis(url) {
  const [kpis, setKpis] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const controller = new AbortController();

    async function loadKpis() {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(url, {
          credentials: 'include',
          signal: controller.signal,
        });
        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Não foi possível carregar os KPIs.');
        }

        setKpis({
          occupancy: {
            percentage: Number(result.data?.occupancy?.percentage ?? 0),
            occupied: Number(result.data?.occupancy?.occupied ?? 0),
            total: Number(result.data?.occupancy?.total ?? 0),
          },
          adverseEventsLastShift: Number(result.data?.adverseEventsLastShift ?? 0),
          criticalAlertsToday: Number(result.data?.criticalAlertsToday ?? 0),
          highRiskPatients: Number(result.data?.highRiskPatients ?? 0),
        });
      } catch (requestError) {
        if (requestError.name !== 'AbortError') {
          console.error('Erro ao carregar os KPIs do painel:', requestError);
          setError(requestError.message || 'Falha na comunicação com o servidor.');
        }
      } finally {
        if (!controller.signal.aborted) {
          setLoading(false);
        }
      }
    }

    loadKpis();
    return () => controller.abort();
  }, [url]);

  return { kpis: kpis || emptyKpis, loading, error };
}
