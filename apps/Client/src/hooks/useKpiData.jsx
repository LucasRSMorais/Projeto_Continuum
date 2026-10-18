import { useState, useEffect } from 'react';

export function useKpiData(url) {
  const [kpiData, setKpiData] = useState({ value: 0, max: 100 });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchKpi = async () => {
      setLoading(true);
      try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
          setKpiData({ value: data.kpi_value, max: 100 }); 
        } else {
          setError(data.error || "Erro ao carregar KPI");
        }
      } catch (err) {
        console.error("Erro na requisição:", err);
        setError("Falha na comunicação com o servidor.");
      } finally {
        setLoading(false);
      }
    };

    fetchKpi();
  }, [url]);

  return { kpiData, loading, error };
}