import { useState } from 'react';
import {
  MainContainer,
  ShiftToolbar,
  KPISection,
  OverviewSection,
  HighPrioritySection,
} from './styles';
import KPI_Card from '../../../Cards/Dashboard/KPI_Card';
import CriticalEventsCard from '../../../Cards/Dashboard/Critical_Events';
import WaitingLine_Card from '../../../Cards/Dashboard/Waiting_line/index.jsx';
import Pending_Card from '../../../Cards/Dashboard/Pending/index.jsx';
import BedMovements_Card from '../../../Cards/Dashboard/Bed_movements/index.jsx';
import UtiBedMapCard from '../../../Cards/Dashboard/UTI_Bed_Map';
import EnfermariaBedMapCard from '../../../Cards/Dashboard/Enfermaria_Bed_Map';
import WaitingBySectorCard from '../../../Cards/Dashboard/Waiting_By_Sector';
import { useDashboardCards } from '../../../../hooks/useDashboardCards';
import { useDashboardKpis } from '../../../../hooks/useDashboardKpis';
import { usePlantao } from '../../../../hooks/usePlantao';
import { buildApiUrl } from '../../../../config/api';
import { ShiftDialog } from './styles';

function Main() {
  const [sectorId, setSectorId] = useState('4');
  const [handoffOpen, setHandoffOpen] = useState(false);
  const [handoffDescription, setHandoffDescription] = useState('');
  const {
    patients,
    pending,
    movements,
    criticalEvents,
    utiBeds,
    enfermariaBeds,
    loading,
    error,
  } = useDashboardCards(
    buildApiUrl(`Cards/get_dashboard_cards.php?id_setor=${sectorId}`)
  );
  const {
    kpis,
    loading: kpisLoading,
    error: kpisError,
  } = useDashboardKpis(
    buildApiUrl(`KPIs/get_dashboard_kpis.php?id_setor=${sectorId}`)
  );
  const {
    plantao,
    loading: shiftLoading,
    saving: shiftSaving,
    error: shiftError,
    message: shiftMessage,
    performAction,
  } = usePlantao(sectorId);

  const currentShift = plantao?.sectorId === Number(sectorId) ? plantao : null;
  const isMine = currentShift?.isMine === true;
  const isUnassigned = currentShift?.status === 'aberto' && currentShift?.doctorId === null;
  const canManage = currentShift?.canManage === true;
  const actionLabel = !canManage
    ? 'Login necessário'
    : isMine
      ? 'Passar Plantão'
      : isUnassigned
        ? 'Assumir Plantão'
        : currentShift?.status === 'encerrado'
          ? 'Plantão encerrado'
          : `Assumido por ${currentShift?.doctorName || 'outro profissional'}`;

  const handleShiftAction = async () => {
    if (isMine) {
      setHandoffDescription('');
      setHandoffOpen(true);
      return;
    }
    if (isUnassigned) {
      await performAction('assumir');
    }
  };

  const submitHandoff = async (event) => {
    event.preventDefault();
    const completed = await performAction('passar', handoffDescription);
    if (completed) {
      setHandoffOpen(false);
      setHandoffDescription('');
    }
  };

  return (
    <MainContainer>
      <ShiftToolbar aria-label="Informações do plantão">
        <label>
          Setor:
          <select
            aria-label="Selecionar setor"
            value={sectorId}
            onChange={(event) => setSectorId(event.target.value)}
          >
            <option value="1">Clínica Geral</option>
            <option value="2">Cirúrgico</option>
            <option value="3">Obstétrico</option>
            <option value="4">UTI-Cardiologia</option>
            <option value="5">Neurologia</option>
            <option value="6">Isolamento</option>
            <option value="7">Enfermaria</option>
          </select>
        </label>
        <span className="shift-time">
          {shiftLoading || !currentShift
            ? 'Consultando plantão…'
            : `Plantão Atual: ${currentShift.period}`}
        </span>
        {currentShift?.doctorName && (
          <span className="shift-doctor">
            {currentShift.status === 'encerrado' ? 'Encerrado por' : 'Responsável'}: {currentShift.doctorName}
          </span>
        )}
        <button
          type="button"
          onClick={handleShiftAction}
          disabled={
            shiftLoading
            || shiftSaving
            || !canManage
            || (!isMine && !isUnassigned)
          }
        >
          {shiftSaving ? 'Salvando…' : actionLabel}
        </button>
        {(shiftError || shiftMessage) && (
          <span
            className={`shift-message${shiftError ? ' is-error' : ''}`}
            role={shiftError ? 'alert' : 'status'}
          >
            {shiftError || shiftMessage}
          </span>
        )}
      </ShiftToolbar>
      {handoffOpen && (
        <ShiftDialog
          role="dialog"
          aria-modal="true"
          aria-labelledby="handoff-title"
          onSubmit={submitHandoff}
        >
          <div className="dialog-backdrop" />
          <section className="dialog-card">
            <h2 id="handoff-title">Passar Plantão</h2>
            <p>
              Registre as informações relevantes. A confirmação encerra o
              plantão e grava a passagem em Eventos Adversos.
            </p>
            <label htmlFor="handoff-description">Descrição do fato</label>
            <textarea
              id="handoff-description"
              name="descricao_fato"
              value={handoffDescription}
              onChange={(event) => setHandoffDescription(event.target.value)}
              maxLength={5000}
              required
              autoFocus
              disabled={shiftSaving}
            />
            {shiftError && <p className="dialog-error" role="alert">{shiftError}</p>}
            <div className="dialog-actions">
              <button
                type="button"
                className="cancel-button"
                onClick={() => setHandoffOpen(false)}
                disabled={shiftSaving}
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="confirm-button"
                disabled={shiftSaving || !handoffDescription.trim()}
              >
                {shiftSaving ? 'Registrando…' : 'Confirmar'}
              </button>
            </div>
          </section>
        </ShiftDialog>
      )}
      <KPISection>
        <KPI_Card
          title="Taxa de Ocupação"
          value={kpis.occupancy.percentage}
          unit="%"
          detail={`${kpis.occupancy.occupied} de ${kpis.occupancy.total} leitos ocupados`}
          gauge
          icon="/src/assets/icons/group-svgrepo-com.svg"
          backgroundColor="#087f5b"
          loading={kpisLoading}
          error={kpisError}
        />
        <KPI_Card
          title="Eventos Adversos"
          subtitle="(Últ. Turno)"
          value={kpis.adverseEventsLastShift}
          detail={`${kpis.adverseEventsLastShift} ${
            kpis.adverseEventsLastShift === 1 ? 'evento' : 'eventos'
          } nas últimas 12 horas`}
          icon="/src/assets/icons/chart.svg"
          backgroundColor="#d97706"
          loading={kpisLoading}
          error={kpisError}
        />
        <KPI_Card
          title="Alertas Críticos"
          subtitle="hoje"
          value={kpis.criticalAlertsToday}
          icon="/src/assets/icons/alert.svg"
          backgroundColor="#e83e4b"
          loading={kpisLoading}
          error={kpisError}
        />
        <KPI_Card
          title="Pacientes Alto Risco"
          value={kpis.highRiskPatients}
          detail={`${kpis.highRiskPatients} ${
            kpis.highRiskPatients === 1 ? 'paciente' : 'pacientes'
          }`}
          icon="/src/assets/icons/hhourglass-svgrepo-com.svg"
          backgroundColor="#087f83"
          loading={kpisLoading}
          error={kpisError}
        />
      </KPISection>
      <OverviewSection aria-label="Resumo clínico e mapa de leitos">
        <CriticalEventsCard events={criticalEvents} loading={loading} error={error} />
        <UtiBedMapCard beds={utiBeds} loading={loading} error={error} />
        <EnfermariaBedMapCard
          beds={enfermariaBeds}
          loading={loading}
          error={error}
        />
        <WaitingBySectorCard
          patients={patients}
          loading={loading}
          error={error}
        />
      </OverviewSection>
      <HighPrioritySection>
        <WaitingLine_Card patients={patients} loading={loading} error={error} />
        <Pending_Card tasks={pending} loading={loading} error={error} />
        <BedMovements_Card tasks={movements} loading={loading} error={error} />
      </HighPrioritySection>
    </MainContainer>
  );
}

export default Main;